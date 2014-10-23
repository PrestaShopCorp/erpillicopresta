<?php
/**
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Illicopresta SA <contact@illicopresta.com>
*  @copyright 2007-2014 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php');
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrder.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrderState.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStock.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpSupplyOrderClasses.php';
if (Module::isEnabled('tntcarrier'))
{
    //cette classe ne peut être appelée que si le module existe et est actif. Sinon, une erreur sera générée
    require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpTntCarrier.php';
}
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminAdvancedOrderController extends ModuleAdminController
{
	public $bootstrap = true;
	public function __construct()
	{
		$this->table = 'order';
		$this->className = 'Order';
		$this->lang = false;
                $this->addRowAction('view');
		$this->explicitSelect = true;
		$this->allow_export = true;
		$this->deleted = false;
		$this->context = Context::getContext();

		$this->bulk_actions = array('multipleChangeStates' => array('text' => $this->l('Multiple change of status')));

                // get controller status
                $this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedOrder'));
                
		// template path
		$this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';

                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
                
		$this->_select = '
		a.id_currency,
		a.id_order AS id_pdf,
		a.id_order AS quickView,
		CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
		osl.`name` AS `osname`,
		os.`color`,
		IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new ';

		// additional select
		$this->_select .= '
		, ca.id_carrier as carrier_id,
		SUM(d.product_weight * d.product_quantity) as poid, a.id_order as document,
		GROUP_CONCAT(d.product_id, "-",d.product_attribute_id, "-", d.product_quantity, "-", a.id_order SEPARATOR " ") as stock ';

		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`
		AND osl.`id_lang` = '.(int)$this->context->language->id.')';

		// additional join
		$this->_join .= '
		LEFT JOIN `'._DB_PREFIX_.'carrier` ca ON (ca.`id_carrier` = a.`id_carrier`)
		LEFT JOIN `'._DB_PREFIX_.'order_detail` d ON ( d.`id_order` = a.`id_order`)';

		$this->_group = 'GROUP BY a.id_order';
		$this->_orderBy = 'id_order';
		$this->_orderWay = 'DESC';

		$statuses_array = array();
		$statuses = ErpOrderState::getOrderStates((int)$this->context->language->id);

		foreach ($statuses as $status)
			$statuses_array[$status['id_order_state']] = $status['name'];

		// get carrier list
		$carrier_array = $this->getListCarrier();

        if(_PS_VERSION_ < 1.6)
		{
			$this->no_link = true;
		}
		else 
		{
			$this->list_no_link = true;
		}	
                
		$this->fields_list = array(

			'quickView' => array(
				'title' => $this->l('Quick View'),
				'align' => 'center',
				'width' => 20,
				'search' => false,
				'orderby' => false,
				'callback' => 'renderQuickViewColumn',
                                'remove_onclick' => true
			),
			'id_order' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 40,
                                'remove_onclick' => true
			),
			'reference' => array(
				'title' => $this->l('Reference'),
				'align' => 'center',
				'width' => 65,
                                'remove_onclick' => true
			),
			'stock' => array(
					'title' => $this->l('Stock'),
					'width' => 20,
					'align' => 'center',
                                        'search' => false,
                                        'orderby' => false,
					'callback' => 'getStock',
                                        'remove_onclick' => true
			),
			'carrier_id' => array(
					'title' => $this->l('Carrier'),
					'width' => 70,
					'type' => 'select',
					'list' => $carrier_array,
					'filter_key' => 'ca!id_carrier',
					'filter_type' => 'int',
					'align' => 'center',
					'callback' => 'getCarrierImage',
                                        'remove_onclick' => true
			),
			'document' => array(
					'title' => $this->l('Document'),
					'width' => 30,
					'align' => 'center',
					'search' => false,
					'orderby' => false,
					'havingFilter' => false,
					'callback' => 'getLastDocument',
                                        'remove_onclick' => true
			),
			'new' => array(
				'title' => $this->l('New'),
				'width' => 25,
				'align' => 'center',
				'type' => 'bool',
				'tmpTableFilter' => true,
				'icon' => array(
					0 => 'blank.gif',
					1 => array(
						'src' => 'note.png',
						'alt' => $this->l('First customer order'),
					)
				),
				'orderby' => false,
                                'remove_onclick' => true
			),
			'customer' => array(
				'title' => $this->l('Customer'),
				'havingFilter' => true,
				'width' => 100,
                                'remove_onclick' => true
			),
			'total_paid_tax_incl' => array(
				'title' => $this->l('Total'),
				'width' => 70,
				'align' => 'right',
				'prefix' => '<b>',
				'suffix' => '</b>',
				'type' => 'price',
				'currency' => true,
                                'remove_onclick' => true
			),
			'payment' => array(
				'title' => $this->l('Payment'),
				'remove_onclick' => true,
				'width' => 100,
                                'remove_onclick' => true
			),
			'osname' => array(
				'title' => $this->l('Status'),
				'color' => 'color',
				'width' =>220,
				'type' => 'select',
				'list' => $statuses_array,
				'filter_key' => 'os!id_order_state',
				'filter_type' => 'int',
				'remove_onclick' => true,
				'callback' => 'getStatutsListe'
			),
			'date_add' => array(
				'title' => $this->l('Date'),
				'width' => 130,
				'align' => 'right',
				'type' => 'datetime',
				'filter_key' => 'a!date_add',
                                'remove_onclick' => true
			)
		);

		//$this->shopLinkType = 'shop';
		//$this->shopShareDatas = Shop::SHARE_ORDER;

		if (Tools::isSubmit('id_order'))
		{
			// Save context (in order to apply cart rule)
			$order = new ErpOrder((int)Tools::getValue('id_order'));
			if (!Validate::isLoadedObject($order))
				throw new PrestaShopException($this->l('Cannot load Order object'));
			$this->context->cart = new Cart($order->id_cart);
			$this->context->customer = new Customer($order->id_customer);
		}

		parent::__construct();
	}

        // Ouverture détail commande en blank
        public function displayViewLink($token, $id)
        {
            $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');

            $tpl->assign(array(
                'href' => self::$currentIndex.'&token='.$this->token.'&'.$this->identifier.'='.$id.'&vieworder',
                'action' => $this->l('View'),
				'target' => '_blank'
            ));
            
            return $tpl->fetch();
        }
        

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryUI('ui.datepicker');

		$this->addJqueryPlugin('cluetip', _MODULE_DIR_.'erpillicopresta/js/cluetip/');

		// add jquery dialog
		$this->addJqueryUI('ui.dialog');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_order.js');
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/jquery.cluetip.css');

	}

	public function initContent()
	{
		parent::initContent();
	}

	public function postProcess()
	{
		// Export PDF
		if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'generateInvoicesPDF3')
				$this->processGenerateInvoicesPDF3();

		if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'generateDeliverySlipsPDF2')
				$this->processGenerateDeliverySlipsPDF2();

		parent::postProcess();
	}

	public function ajaxProcess()
	{
		// get product list of order
		if ((Tools::isSubmit('action') && Tools::isSubmit('id')) && ( Tools::getValue('action') == 'detailsAjax' || Tools::getValue('action') == 'details'))
		{
			// override attributes
			$this->identifier = 'id_order_detail';
			$this->display = 'list';
			$this->lang = false;
			$this->explicitSelect = false;
			$this->actions = array();

			// get current lang id
			$lang_id = (int)$this->context->language->id;

			// Get order id
			$order_id = (int)Tools::getValue('id');

			$this->fields_list = array
			(
				'product_name' => array(
					'title' => $this->l('Product name'),
					'callback' => 'getProductLinkTag'
				),
				'product_quantity' => array(
					'title' => $this->l('Quantity'),
					'align' => 'center'
				),
				'product_reference' => array(
					'title' => $this->l('Product Reference')
				),
				'unit_price_tax_incl' => array(
					'title' => $this->l('Unit price ti'),
					'type' => 'price'
				),
				'total_price_tax_incl' => array(
					'title' => $this->l('Total price ti'),
					'type' => 'price'
				),
				'stock_level_color' => array(
					'title' => $this->l('Stock level'),
					'align' => 'center',
					'callback' => 'gestockLevelColor'
				),
			);
			// Load product attributes with sql override
			$this->table = 'order_detail';

			unset($this->_join);

			$this->_select = null;
			$this->_select = 'i.id_image, a.product_name, a.product_quantity, a.product_reference,a.unit_price_tax_incl, a.total_price_tax_incl';
                        
			$this->_join = ' LEFT JOIN '._DB_PREFIX_.'image i ON a.product_id = i.id_product
                            INNER JOIN '._DB_PREFIX_.'product_lang pl ON (a.product_id = pl.id_product AND pl.id_lang = '.(int)$this->context->language->id.') ';

			$this->_where = ' AND a.id_order = '.$order_id;
			$this->_group = ' GROUP BY a.id_order_detail ';

			// get list and force no limit clause in the request
			$this->getList($lang_id, 'a.product_name', 'ASC', 0, false);
			// Render list
			$helper = new HelperList();
			$helper->bulk_actions = array();
			$helper->toolbar_scroll = $this->toolbar_scroll;
			$helper->show_toolbar = false;
			$helper->actions = $this->actions;
			$helper->list_skip_actions = $this->list_skip_actions;
			$helper->no_link = true;
			$helper->shopLinkType = '';
			$helper->identifier = $this->identifier;
                        
			// Force render - no filter, form, js, sorting ...
			$helper->simple_header = true;
                        $helper->override_folder = 'advanced_order_ajax/';
                        
			$content = $helper->generateList($this->_list, $this->fields_list);

			echo Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content));
		}
                elseif (Tools::isSubmit('task') && Tools::getValue('task') == 'getOrdersWithSameProduct')
                        $this->ajaxGetOrdersWithSameProduct();
                elseif (Tools::isSubmit('task') && Tools::getValue('task') == 'getProducts')
                        $this->ajaxGetProducts();
		else
			echo 'error';
		die();
	}

	public function InitToolbar()
	{
		if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
		{
                    if ($this->display == '') // only on order list (display == '')
                    {
                            $this->toolbar_btn['update-selection'] = array(
                                            'href' => 'javascript:void(0)',
                                            'desc' => $this->l('Multiple change of status'),
                                            'class' => 'process-icon-duplicate'
                            );

                            if ($this->controller_status)
                            {
                                $this->toolbar_btn['expedition'] = array(
                                    'href' => 'javascript:void(0)',
                                    'desc' => $this->l('Shipment'),
                                    'class' => 'process-icon-new add_product'
                                );
                            }

                            $this->toolbar_btn['print_invoices_delivery'] = array(
                                'href' => '#',
                                'desc' => $this->l('Print documents'),
                                'class' => 'process-icon-preview'
                            );

                            parent::initToolbar();

                            $this->toolbar_btn['new'] = array(
                                'href' => 'index.php?controller=AdminOrders&add'.$this->table.'&token='.Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)$this->context->employee->id),
                                'desc' => $this->l('Add new'),
								'target' => '_blank'
								
                            );
                    }
		}
	}

	public function initToolBarTitle()
	{
		
	}

	public function initPageHeaderToolbar()
	{
		parent::initPageHeaderToolbar();

		if ($this->display == '') // only on order list (display == '')
                {
                    $this->page_header_toolbar_btn['update-selection'] = array(
                            'href' => 'javascript:void(0)',
                            'desc' => $this->l('Multiple change of status'),
                            'class' => 'process-icon-duplicate'
                    );

                    if ($this->controller_status) 
                    {
                        $this->page_header_toolbar_btn['expedition'] = array(
                                'href' => 'javascript:void(0)',
                                'desc' => $this->l('Shipment'),
                                'class' => 'icon-AdminParentShipping'
                        );
                    }

                    $this->page_header_toolbar_btn['print_invoices_delivery'] = array(
                            'href' => '#',
                            'desc' => $this->l('Print documents'),
                            'class' => 'process-icon-preview'
                    );

                    $this->page_header_toolbar_btn['new'] = array(
                            'href' => 'index.php?controller=AdminOrders&add'.$this->table.'&token='.Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)$this->context->employee->id),
                            'desc' => $this->l('Add new'),
							'target' => '_blank'
                    );
                }
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

		$nb_items = count($this->_list);
		for ($i = 0; $i < $nb_items; ++$i)
		{
			$item = &$this->_list[$i];

			if (Tools::isSubmit('ajax') && isset($item['id_order_detail']))
			{
				// no details for this row
				$this->addRowActionSkipList('details', array($item['id_order_detail']));

				if (Configuration::get ('PS_ADVANCED_STOCK_MANAGEMENT'))
				{
					$manager = StockManagerFactory::getManager();
					$quantity_stock = (int)$manager->getProductRealQuantities((int)$item['product_id'], (int)$item['product_attribute_id'] , null , true);
				}
				else
					$quantity_stock = (int)Product::getQuantity( (int)$item['product_id'], (int)$item['product_attribute_id']);

				// ges stock level color
				$item['stock_level_color'] = ErpSupplyOrderClasses::getStockLevelColor($quantity_stock);
			}
			else 
			{
				// add view link for each row
				$item['href_view_link'] = Tools::safeOutput('index.php?controller=AdminOrders&'.$this->identifier.'='.$item['id_order'].'&view'.$this->table.'&token='.Tools::getAdminTokenLite('AdminOrders'));
			}
		}
	}

	public function renderList ()
	{
                $this->toolbar_title = $this->l('Order Management');
                                
                $statuses_array = array();
		$statuses = ErpOrderState::getOrderStates((int)$this->context->language->id);

		foreach ($statuses as $status)
			$statuses_array[$status['id_order_state']] = $status['name'];

                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';

                $this->context->smarty->assign(array(
                    'token_mr' => (ModuleCore::isEnabled('mondialrelay')) ? MondialRelay::getToken('back') : 'false',
                    'token_expeditor' =>  (ModuleCore::isEnabled('expeditor')) ? Tools::getAdminToken('AdminExpeditor'.(int)(Tab::getIdFromClassName('AdminExpeditor')).(int)$this->context->employee->id) : 'false',
                    'id_employee' => (int)$this->context->employee->id,
                    'order_statuses' => $statuses_array,
                    'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
                    'template_path' => $this->template_path,
                    'expeditor_status' => Configuration::get('EXPEDITOR_STATE_EXP'),
                    //'MR_status' => $account_shop['MR_ORDER_STATE']
		));

                $this->tpl_list_vars['has_bulk_actions'] = 'true';

                // handle contient les messages d'erreur eventuels
                $handle = Tools::getValue('handle');
             
                //var_dump($handle);
                
		switch(trim($handle))
		{
			case '' :

			break;

			case 'false' :
				$this->confirmations[] = $this->l('All orders have been updated').'<br/>';
			break;

			default:
                                if (!empty($handle))
                                {
                                    // VERRUE MR : redscend du unicode degueulasse
                                    $handle = str_replace('u00e9', 'é', $handle);
                                    $handle = str_replace('u00ea', 'ê', $handle);
                                    
                                    // VERRUE : on prend en compte les commandes avec erreur : pas de transporteurs valide (on split sur le num de commande : #) ..
                                    $orderWithoutShipping = (strstr($handle, '#') != false) ? true : false;
                                    $errors = explode('<br/>',str_replace('#','<br/>',$handle));
                                   
                                    foreach ($errors as $key => $error)
                                    {
                                        if (!empty($error))
                                        {
                                            if(!$orderWithoutShipping)
                                                $message = $error; //.' - ('.$key.')';
                                            else
                                                $message = $error;
                                            $this->errors[] = Tools::displayError($message);
                                        }
                                    }
                                }
			break;
		}

		if (Tools::getValue('linkPDF') != '' && Tools::getValue('newState') != '')
		{

			// Si le statut nécéssite la génération de factures
			if (ErpOrderState::invoiceAvailable(Tools::getValue('newState')))
			{
				$pdf_link = new Link();
				$pdf_link = $pdf_link->getAdminLink("AdminAdvancedOrder", true).'&submitAction=generateInvoicesPDF3&id_orders='.Tools::getValue('linkPDF');
				$this->confirmations[] = '&nbsp;<a target="_blank" href="'.$pdf_link.'" alt="invoices">'.$this->l('Download all invoices').'<br/></a>';
			}

			// Si le statut nécéssite la génération de bon de livraison
			if (ErpOrderState::deliverySlipAvailable(Tools::getValue('newState')))
			{
				$pdf_link = new Link();
				$pdf_link = $pdf_link->getAdminLink("AdminAdvancedOrder", true).'&submitAction=generateDeliverySlipsPDF2&id_orders='.Tools::getValue('linkPDF');
				$this->confirmations[] = '&nbsp;<a target="_blank" href="'.$pdf_link.'" alt="delivery">'.$this->l('Download all delivery slip').'<br/></a>';
			}
		}

		if (Tools::getValue('linkPDFPrint') != '')
		{
			$invoices = '';
			$delivery = '';

			foreach (explode(',', Tools::getValue('linkPDFPrint')) as $id_order)
			{
				if (ErpOrderState::invoiceAvailable( ErpOrder::getIdStateByIdOrder($id_order))) 
                                    $invoices .= $id_order.',';
				if (ErpOrderState::deliverySlipAvailable( ErpOrder::getIdStateByIdOrder($id_order))) 
                                    $delivery .= $id_order.',';
			}

			if ($invoices != '')
			{
				$pdf_link = new Link();
				$pdf_link = $pdf_link->getAdminLink("AdminAdvancedOrder", true).'&submitAction=generateInvoicesPDF3&id_orders='.Tools::substr($invoices, 0, -1);
				$this->confirmations[] = '&nbsp;<a target="_blank" href="'.$pdf_link.'" alt="invoices">'.$this->l('Download all invoices').'</br></a>';
			}

			if ($delivery != '')
			{
				$pdf_link = new Link();
				$pdf_link = $pdf_link->getAdminLink("AdminAdvancedOrder", true).'&submitAction=generateDeliverySlipsPDF2&id_orders='.Tools::substr($delivery, 0, -1);
				$this->confirmations[] = '&nbsp;<a target="_blank" href="'.$pdf_link.'" alt="delivery">'.$this->l('Download all delivery slip').'</br></a>';
			}
                        
                        if ($invoices == '' && $delivery == '')
                            $this->errors[] = $this->l('The selected orders have no invoice or delivery !').'<br/>';
		}

		if (Tools::getValue('etiquettesMR') != '')
		{
			// On télécharge tout les PDF et on les zip, puis on les supprime et on affiche le lien vers le fichier zip
			$etiquettesMR = explode (' ', Tools::getValue('etiquettesMR'));
			unset ($etiquettesMR[count($etiquettesMR) - 1]);

			$zipPath = '../modules/erpillicopresta/export/mondialrelay.zip';

			$zip = new ZipArchive();
			if ($zip->open($zipPath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== true)
					throw new Exception($this->l('Impossible to create the zip archive containing the shipping labels to Mondial Relay carrier !').'<br/>');

			foreach ($etiquettesMR as $key => $i)
			{
				$zip->addFromString('mondialrelay_'.$key.'.pdf', Tools::file_get_contents($i));
			}

			$zip->close();

			//On propose le zip au téléchargement
			$this->confirmations[] = '&nbsp;<a target="_blank" href="'.$zipPath.'" alt="zip_file">'.$this->l('Download zip archive which contents all labels for Mondial Relay shipment').'<br/></a>';

			if (Tools::getValue('deliveryNumbersMR') != '')
			{
				// On récupère tout les numéros de suivis
				$numbers = explode(" ", Tools::getValue('deliveryNumbersMR'));
				unset($numbers[count($numbers)-1]);
				foreach ($numbers as $number)
				{
					$tabNumber = explode("-", $number);
					$order_carrier = new OrderCarrier(ErpOrder::getIdCarrierbyIdOrder((int)$tabNumber[1]));
					$order = new ErpOrder((int)$tabNumber[1]);

					// Update carrier
					$order->shipping_number = $tabNumber[0];
					$order->update();

					// Update order_carrier
					$order_carrier->tracking_number = pSQL($tabNumber[0]);
					$order_carrier->update();

				}
			}
		}
		if (Tools::getValue('expeditorCSV') != '')
		{
			// On crée le fichier CSV
			$csvPath = '../modules/erpillicopresta/export/expeditor_inet.csv';
			$fileCSV = fopen($csvPath, 'w');

			// On le remplis
			fwrite ($fileCSV, str_replace( ',', '', Tools::getValue('expeditorCSV')));

			//On le ferme
			fclose($fileCSV);

			// On crée le lien
			$this->confirmations[] = '&nbsp;<a target="_blank" href="'.$csvPath.'" alt="csv_file">'.$this->l('Download export file (CSV) for ExpeditorInet').'</br></a>';
		}

		if (Tools::getValue('idOthers') != '')
		{
                    
                    //BEGIN Initialisations pour TNT
                    if (Module::isEnabled('tntcarrier'))
                    {
                            $TNTCheck = false;

                            require_once _PS_MODULE_DIR_.'/tntcarrier/classes/PackageTnt.php';

                            if (class_exists('ZipArchive', false) && ($tnt_zip = new ZipArchive()))
                            {
                                // On met tout les pdf TNT dans un zip
                                $tnt_zip_path = 'erpillicopresta/export/tnt_'.date('Y-m-d_His').'_'.uniqid('file_').'.zip';
                                if ($tnt_zip->open(_PS_MODULE_DIR_.$tnt_zip_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== true)
                                        $this->errors[] = Tools::displayError($this->l('Failed to create a ZIP archive containing the shipping labels to TNT carrier !').'<br/>');
                                else
                                {
                                    // one or several id orders
                                    $id_others_order_array = strpos(Tools::getValue('idOthers'), ',') !== false ? explode(',', Tools::getValue('idOthers')) : (int)Tools::getValue('idOthers');

                                    //On parcours toutes les commandes n'étant ni ExpeditorInet ni MondialRelay
                                    foreach ((array)$id_others_order_array as $i=>$id_order)
                                    {
                                        // BEGIN Commande TNT
                                        $id_order = (int)$id_order;

                                        if (ErpOrder::isTntOrder($id_order))
                                        {
                                            // Changement status
                                            $currOrder = new ErpOrder($id_order);
                                            $currOrder->setCurrentState(4, $this->context->employee->id);

                                            //On commence par vérifier que le poids de la commande est valide ! Sinon, tnt plante.
                                            //echo($data['poid'] * 1000);die;

                                            // On récupère le numéro de suivi : creation d'une classe dédié à cette manip pour nouvelle version TNT : 
                                            // execution du hook générant le num de suivi sur l'OUVERTURE d'une commande .. donc ctrl c / ctrl v pour executer ici
                                            $erp_tntCarrier = new ErpTntCarrier();
                                            $generate = $erp_tntCarrier->generateShipping($id_order);

                                            $tnt = new PackageTnt($id_order);
                                            $tntNumber = $tnt->getShippingNumber();

                                            if (count($tntNumber) == 0)
                                            {
                                                $this->errors[] = Tools::displayError($this->l('Failed to get shipping number from TNT services : you have to fit the weight of the order.'));
                                                continue;
                                            }

                                            $tntNumber = $tntNumber[0]['shipping_number'];

                                            // Update order
                                            $order_carrier = new OrderCarrier(ErpOrder::getIdCarrierbyIdOrder((int)$id_order));
                                            $order = new ErpOrder((int)$id_order);
                                            $order->shipping_number = $tntNumber;
                                            $order->update();
                                            $order_carrier->tracking_number = pSQL($tntNumber);
                                            $order_carrier->update();

                                            // On ajoute le PDF au zip
                                            $tnt_zip->addFile(_PS_MODULE_DIR_.'/tntcarrier/pdf/'.$tntNumber.'.pdf', $tntNumber.'.pdf');

                                            $TNTCheck = true;

                                        }
                                        // END Commande TNT

                                        // SPLICE de idOther
                                        if(is_array($id_others_order_array))
                                            unset($id_others_order_array[$i]);
                                        else
                                            unset($id_others_order_array);
                                    }

                                    //On propose le zip au téléchargement
                                    $tnt_zip->close();
                                    if ($TNTCheck) $this->confirmations[] = '&nbsp;<a target="_blank" href="'._MODULE_DIR_.$tnt_zip_path.'" alt="zip_file">'.$this->l('Download zip archive which contents all labels for TNT shipment').'<br/></a>';
                                }
                            }
                            else
                                    $this->errors[] = Tools::displayError($this->l('Class ZipArchive does not exist !').'<br/>');

                            //END Initialisations for TNT
                    }
                    
                    // affichage des commandes non traitées : idothers
                    if(isset($id_others_order_array))
                        if(count($id_others_order_array) == 1)
                        {
                            //var_dump($id_others_order_array);die();
                            if(is_array($id_others_order_array))
                            {
                                $id_others_order_array = $id_others_order_array[1];
                            }
                            $this->errors[] = Tools::displayError($this->l('The following order has not been processed : order #').$id_others_order_array.'. '.$this->l('Please make sure that the carrier is either TNT, ExpeditorInet, or MondialRelay and that the order fits the carrier requirements.'));
                        }
                        elseif (count($id_others_order_array) > 1)
                        {
                            $this->errors[] = Tools::displayError($this->l('The following orders have not been processed : orders #').implode(", ", $id_others_order_array).'. '.$this->l('Please make sure that the carrier is either TNT, ExpeditorInet, or MondialRelay and that the orders fit the carrier requirements.'));
                        }
		}

		return  parent::renderList();
	}
	/*
	* getStock of order
	*/
	public function getStock($stock, $data)
	{
		// On récupère ($stock) une string contenant "id_product-id_attribut_product-quantiy-id_commande"
		// séparés par des "-", chaque produit sont sépraés par des epaces

		//On récupère la liste des status actifs
		$status_actifs = array();
		$order_states = OrderState::getOrderStates((int)$this->context->language->id);
				foreach ($order_states as $state)
						if (Configuration::get('ERP_STATUS_WARNING_STOCK_'.$state['id_order_state']) == 'on')
								$status_actifs[] = $state['id_order_state'];

		$stock_level = array ('img' => '../img/admin/status_green.png', 'alt' => '1');

		// On explode et range tout dans un tableau
		$produits = explode(" ", $stock);
		foreach ($produits AS &$prod)
			$prod = explode('-', $prod);

		$objCurrOrder = new ErpOrder($produits[0][3]);

		if (array_search($objCurrOrder->current_state, $status_actifs) === false)
			$stock_level = array ('img' => '../img/admin/blank.gif', 'alt' => '0');
		else
		{
			$stock_level_out_of_stock = false;

			foreach ($produits AS &$prod) // Premier parcours pour voir si un produit est hors stock car c'est le plus important
			{
				$stock_physique = StockAvailable::getQuantityAvailableByProduct($prod[0], $prod[1]); // Gestion non avancée par defaut

				if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) // Si la gestion avancée est activée
				{
					$manager = StockManagerFactory::getManager();
					$stock_physique = $manager->getProductPhysicalQuantities($prod[0], $prod[1]);
				}

				// Si un produit est hors stock
				if (($stock_physique - $prod[2]) < 0)
				{
					$stock_level = array ('img' => '../img/admin/status_red.png', 'alt' => '3');
					$stock_level_out_of_stock = true;
				}
			}

			if (!$stock_level_out_of_stock)
			{
				foreach ($produits AS &$prod) // Second parcours pour voir si une commande a un produti en commun
				{
					//On vérifie si il est commandé ailleur
					$tabOrders = ErpOrder::getOrdersByProductAndAttribute($prod[0], $prod[1]);
					foreach ($tabOrders AS &$order)
					{
						$objOrder = new ErpOrder($order['id_order']);

						// Si la commande n'est ni envoyée, ni annulée ni la commande courante
						if (array_search($objOrder->current_state, $status_actifs) !== false && $order['id_order'] != $prod[3])
							$stock_level = array ('img' => '../img/admin/status_orange.png', 'alt' => '2');
					}
				}
			}
		}

		$stock_level_title = '';

		if ($stock_level['alt'] == '2')
			$stock_level_title = $this->l('Orders with the same product(s)');
		elseif ($stock_level['alt'] == '3')
			$stock_level_title = $this->l('One or several product(s) are out of stock');
		elseif ($stock_level['alt'] == '1')
			$stock_level_title = $this->l('All products are in stock');

		$token = Tools::getAdminToken('AdminAdvancedOrder'.(int)(Tab::getIdFromClassName('AdminAdvancedOrder')).(int)$this->context->employee->id);

				$html = '<a href="#" id="info-stock" class="info-orders" title="'.$stock_level_title.'"
		   rel="index.php?controller=AdminAdvancedOrder&ajax=1&id_order='.$data['id_order'].'&task=getOrdersWithSameProduct&token='.$token.'">
			<img style="width: 20px; height: 20px;" alt="'.$stock_level['alt'].'" src="'.$stock_level['img'].'" />
		</a>';

		return $html;
	}


	/*
	*	Get image of carrier
	*/
	public function getCarrierImage($carrier_id, $data)
	{
		// if carrier image exist else we take a default image
		$tabCarrier = array ('src' => ((file_exists(_PS_SHIP_IMG_DIR_.$carrier_id.'.jpg')) ? _THEME_SHIP_DIR_.$carrier_id.'.jpg' : '../img/admin/delivery.gif'), 'alt' => 'logo');
                
		// allows to get a carrier with Javascript
		if (ErpOrder::isMROrder($data['id_order']))
			$tabCarrier['alt'] = 'MR';

		else if (ErpOrder::isExpeditorCarrier($carrier_id))
			$tabCarrier['alt'] = 'Expeditor';

		else
			$tabCarrier['alt'] = 'unsupportedCarrier';

		// add weight in hidden field (for shipping)
		$tabCarrier['weight'] = $data['poid'] * 1000;

		$html = '<input type="hidden" name="weight-carrier_id" value="'.$tabCarrier['weight'].'" />';

		$html .= '<img style="height:32px;" alt="'.$tabCarrier['alt'].'" src="'.$tabCarrier['src'].'" class="carrier_image" data-carrier="'.$tabCarrier['alt'].'"/>';

		return $html;
	}

	/*
	* get carrier list
	*/
	public function getListCarrier()
	{
		$carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, 5);
		$retour = array ();
		foreach ($carriers as $carrier)
			$retour[$carrier['id_carrier']] = $carrier['name'];
		return $retour;
	}

	public function getLastDocument($id_order)
	{
		$order = new ErpOrder($id_order);
		$documents = $order->getDocuments();

		if (count($documents) == 0) return '';
		$document = $documents[0];

		$num_doc = $document->number;

		while(Tools::strlen($num_doc) < 6)
			$num_doc = '0'.$num_doc;

		switch (get_class($documents[0])) 
		{
			case 'OrderInvoice' :
				$num_doc = '#'.Configuration::get('PS_INVOICE_PREFIX', $this->context->language->id) . $num_doc;
			break;
			case 'OrderQuotationDetail' :
				$num_doc = '#'.Configuration::get('PS_QUOTATION_PREFIX', $this->context->language->id) . $num_doc;
			break;
			default:
				$num_doc = '#'. $num_doc;
			break;
		}

		return $num_doc;
	}

	public function processGenerateInvoicesPDF3()
	{
		require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrderInvoice.php';

		$order_invoice_collection = array();
		foreach (explode (",", Tools::getValue('id_orders')) as $id_order)
                {
                        if (is_array($order_invoices = ErpOrderInvoice::getByOrder($id_order)))
                        {
                                $order_invoice_collection = array_merge($order_invoices, $order_invoice_collection);
                        }
                }

		if (!count($order_invoice_collection))
			return false;

		$pdf = new AdminPdfController();
		$pdf->generatePDF($order_invoice_collection, PDF::TEMPLATE_INVOICE);
	}

	public function processGenerateDeliverySlipsPDF2()
	{
		require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrderInvoice.php';

		$order_delivery_collection = array();
		foreach (explode (",", Tools::getValue('id_orders')) as $id_order)
				{
					if (is_array($order_delivery = ErpOrderInvoice::getByOrder($id_order)))
					{
						$order_delivery_collection = array_merge($order_delivery, $order_delivery_collection);
					}
				}

		if (!count($order_delivery_collection))
			return false;

		$pdf = new AdminPdfController();
		$pdf->generatePDF($order_delivery_collection, PDF::TEMPLATE_DELIVERY_SLIP);
	}

	/*
	*	get status list
	*/
	public function getStatutsListe($statut, $data)
	{
		$statuts = $this->fields_list['osname']['list'];
		$indice = array_search ($statut , $statuts);
		if ($indice != false)
			$statuts['curr'] = $indice;

		$html = '<select style="max-width: 220px;" class="selectUpdateOrderState-'.$data['id_order'].'">';

		$html .= '<option class="selectedOrderState-'.$statuts['curr'].'" value ="'.$statuts[$statuts['curr']].'">'.$statuts[$statuts['curr']].'</option>';

			foreach ( $statuts as $indice => $statut)
				if ($indice != 'curr' && $indice != $statuts['curr'])
					$html .= '<option class="selectedOrderState-'.$indice.'" value ="'.$statut.'">'.$statut.'</option>';

		$html .= '</select>';

		return $html;
	}

	public function renderQuickViewColumn($id_order)
	{
		$token = Tools::getAdminToken('AdminAdvancedOrder'.(int)(Tab::getIdFromClassName('AdminAdvancedOrder')).(int)$this->context->employee->id);

                $html = '<a href=\'#\' class=\'info-orders\' title=\' '.$this->l('Products list').'\' rel=\'index.php?controller=AdminAdvancedOrder&ajax=1&id_order='.(int)$id_order.'&task=getProducts&token='.$token.'\'>
			<img style=\'width: 16px; height: 16px;\' alt=\'products\' src=\'../img/admin/search.gif\' />
		</a> ';

		return $html;
	}

	public function gestockLevelColor($real_quantity)
	{
		return '<span class="stock_level_color" style="background-color:'.ErpSupplyOrderClasses::getStockLevelColor($real_quantity).'"></span> ';
	}

	public function getProductLinkTag($product_name, $param)
	{
		$token = Tools::getAdminToken('AdminProducts'.(int)(Tab::getIdFromClassName('AdminProducts')).(int)$this->context->employee->id);
		return '<a target="_blank" href="?controller=AdminProducts&id_product='.$param['product_id'].'&updateproduct&token='.$token.'" target="_blank">'.$product_name.'</a>';
	}

		/* RJMA
	 * Rajout pour la traduction du controller AdminAdvancedOrder
	*/
	protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = false)
	{
            if (!empty($class))
            {
                $str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminAdvancedOrder');
                $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
                return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
            }
	}

		/**/
		public function ajaxGetOrdersWithSameProduct()
		{
			if (Tools::isSubmit('id_order'))
			{

			$action_type = Tools::isSubmit('type') ? Tools::getValue('type') : 'customer';

			require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrder.php';

			/*On récupère la liste des status actifs */
			$status_actifs = array();
			$order_states = OrderState::getOrderStates((int)$this->context->language->id);
			foreach ($order_states as $state)
					if (Configuration::get('ERP_STATUS_WARNING_STOCK_'.$state['id_order_state']) == 'on')
                                                $status_actifs[] = $state['id_order_state'];

			$objOrder = new ErpOrder( (int)Tools::getValue('id_order'));
			$produits = $objOrder->getListOfProducts($action_type);
			$message = '<table id=\'erp_commande_pastille_orange_table\'>
						<tr id="entete_pastille_orange">
                                                    <th align="center">'.$this->l('Order id').'</th>
                                                    <th align="center">'.$this->l('Order reference').'</th>
                                                    <th align="center">'.$this->l('Date').'</th>
                                                    <th align="center">'.$this->l('Total te').'</th>
                                                    <th align="center">'.$this->l('Details').'</th>
						</tr>';

			$tabOrdersDejaPasses = array ();

			foreach ($produits as &$prod)
			{
				/*On vérifie si il est commandé ailleur*/
				$tabOrders = ErpOrder::getOrdersByProductAndAttribute($prod['product_id'], $prod['product_attribute_id']);
				foreach ($tabOrders as &$order)
				{
					$objOrderCorespondant = new ErpOrder($order['id_order']);

					if (array_search($objOrderCorespondant->current_state, $status_actifs) !== false && $order['id_order'] != Tools::getValue('id_order') && array_search($order['id_order'], $tabOrdersDejaPasses) === false)
					{
						$order_token = Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)$this->context->employee->id);
						$advanced_order_token = Tools::getAdminToken('AdminAdvancedOrder'.(int)(Tab::getIdFromClassName('AdminAdvancedOrder')).(int)$this->context->employee->id);

						$message .= '<tr>';
						$message .= '<td align="center" class="row_pastille_orange">'.(int)$objOrderCorespondant->id.'</td>';
						$message .= '<td align="center" class="row_pastille_orange"><a target="_blank" href=\'?controller=AdminOrders&id_order='.(int)$objOrderCorespondant->id.'&vieworder&token='.$order_token.'\' target=\'_blank\'>'.$objOrderCorespondant->reference.'</td>';
						$message .= '<td align="center" class="row_pastille_orange">'.Tools::displayDate($objOrderCorespondant->date_add).'</td>';
						$message .= '<td align="center" class="row_pastille_orange">'.Tools::displayPrice($objOrderCorespondant->total_paid).'</td>';
						$message .= '<td align="center" class="row_pastille_orange">
										<a class="pointer" id="details_detailsAjax_'.(int)$objOrderCorespondant->id.'" title="Details"
											href="javascript:void(0)" onclick="display_action_details(
													\''.(int)$objOrderCorespondant->id.'\',
													\'AdminAdvancedOrder\',
													\''.$advanced_order_token.'\',
													\'detailsAjax\',
													{
														&quot;action&quot;:&quot;detailsAjax&quot;
													});
													return false">
											<img src="../img/admin/more.png" alt="Details">
										</a>
									</td>';
						$message .= '</tr>';

						$tabOrdersDejaPasses[] = $order['id_order'];
					}
				}
			}
			$message .= '</table>';

			print $message;
			}
		}

		public function ajaxGetProducts()
		{
                    if (Tools::isSubmit('id_order'))
                    {
                        $product_return_template = array();
                        require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrder.php';

                        $objOrder = new ErpOrder( (int)Tools::getValue('id_order'));
                        $produits = $objOrder->getListOfProductsWithQuantity();

                        if (!empty($produits))
                        {
                            foreach ($produits as $key => &$prod)
                            {
                                $objProd = new Product($prod['product_id']);

                                // Si la commande n'est ni envoyée, ni annulée ni la commande courante 
                                $product_return_template[$key]['reference'] = $objProd->reference;
                                $product_return_template[$key]['name'] = $objProd->getProductName($prod['product_id'], $prod['product_attribute_id']);
                                $product_return_template[$key]['quantity'] = $prod['product_quantity'];

                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) /* Si la gestion avancée est activée */
                                {
                                    $manager = StockManagerFactory::getManager();							
                                    $product_return_template[$key]['physical_stock'] = $manager->getProductPhysicalQuantities($prod['product_id'], $prod['product_attribute_id']);
                                    $product_return_template[$key]['usable_stock'] = $manager->getProductPhysicalQuantities($prod['product_id'], $prod['product_attribute_id'], null, true);
                                    $product_return_template[$key]['real_stock'] = $manager->getProductRealQuantities($prod['product_id'], $prod['product_attribute_id']);
                                }
                                else
                                    $product_return_template[$key]['stock'] = StockAvailable::getQuantityAvailableByProduct($prod['product_id'], $prod['product_attribute_id']);
                            }
                        }
                                               
                        $this->context->smarty->assign(array(
                                'products' => $product_return_template,
                        ));
                        
                        echo $this->context->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/advanced_order/quick_view.tpl');
                        die();
                    }
		}
                
                public function renderView()
                {
                    self::$currentIndex = $this->context->link->getAdminLink('AdminOrders');
                    Tools::redirectAdmin(self::$currentIndex.'&vieworder&id_order='.(int)Tools::getValue('id_order'));
                }
}