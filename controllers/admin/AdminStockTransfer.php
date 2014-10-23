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

require_once _PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStockMvt.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminStockTransferController extends ModuleAdminController
{
	private $baseColumns;

	public function __construct()
	{
		parent::__construct();
                
                $this->bootstrap = true;
                
                // Colonnes de base
                $this->baseColumns = array(
                        'ids' => array(
                                'title' => $this->l('#'),
                                'width' => 20,
                                'search' => false,
                                'class' => 'ids'
                        ),
                        'EAN' => array(
                                'title' => 'EAN',
                                'width' => 100,
                                'search' => false,
                                'havingFilter' => true
                        ),
                        'name' => array(
                                'title' => $this->l('Name'),
                                'align' => 'left',
                                'width' => 200,
                                'search' => false,
                                'havingFilter' => true
                        ),
                        'physical_quantity' => array(
                                'title' => $this->l('Physical quantity'),
                                'width' => 50,
                                'align' => 'center',
                                'search' => false,
                                'class' => 'physical_quantity text-center'
                        ),
                        'usable_quantity' => array(
                                'title' => $this->l('Usable quantity'),
                                'width' => 50,
                                'align' => 'center',
                                'search' => false,
                                'class' => 'usable_quantity text-center'
                        )
                );

                // template path
		$this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';

                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
                
                $this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0;
                
		$this->context->smarty->assign(array(
                    'transfers' => ''
		));
                
                // message to downloas file after transfer
                if ( Tools::isSubmit('validate_transfer') && ( Tools::isSubmit('ids_mvt') || Tools::isSubmit('ids_mvt_csv')))
                {
                    $link = new Link();
                    $link = $link->getAdminLink("AdminStockTransfer", true);
                            
                    if (Tools::isSubmit('ids_mvt'))
                    {
                        $ids_mvt = Tools::getValue('ids_mvt');

                        if (!empty($ids_mvt))
                        {
                            $url_get_pdf = $link.'&action=generateTransferPDF&ids_mvt='.$ids_mvt;
                            $url_get_pdf .= '&stockA='.Tools::getValue('id_warehouse_src').'&stockB='.Tools::getValue('id_warehouse_dst');
                            
                             $this->confirmations[] = $this->l('The stock has been transferred successfully.').''
                                    . '<br/>&nbsp;<a href="'.$url_get_pdf.'">'
                                    . '<b>'.$this->l('Download PDF transfer').'</b></a>';
                        }
                    }
                    
                    if (Tools::isSubmit('ids_mvt_csv'))
                    {
                        $ids_mvt_csv = Tools::getValue('ids_mvt_csv');
                        $url_get_csv = $link.'&get_csv_transfer&ids_mvt_csv='.$ids_mvt_csv;
                        $url_get_csv .= '&id_warehouse_src='.Tools::getValue('id_warehouse_src').'&id_warehouse_dst='.Tools::getValue('id_warehouse_dst');
                        
                        if (!empty($ids_mvt_csv))
                            $this->confirmations[] = '&nbsp; - <a target="_blank" href="'.$url_get_csv.'" alt="csv_file"><b>'.$this->l('Download CSV transfer').'</b></a>';
                    }
                }
                
        }

	public function renderList()
	{
            $this->toolbar_title = $this->l('Products list');
            
		$prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));

		if (Tools::isSubmit('stockOrderby'))
		{
			$stockOrderby = Tools::getValue('stockOrderby');
			$_GET [$prefix.'stockOrderby'] 	= $stockOrderby;
			$_GET ['stockOrderby'] 	= $stockOrderby;
		}
		if (Tools::isSubmit('stockOrderway'))
		{
			$stockOrderway = Tools::getValue('stockOrderway');
			$_GET [$prefix.'stockOrderway'] = $stockOrderway;
			$_GET ['stockOrderway'] 	= $stockOrderway;
		}

                $this->processFilter();

                $this->getCurrentValue('stockOrderway');
                $this->getCurrentValue('stockOrderby');

                //sidebar
                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
                $this->tpl_list_vars['erp_feature'] = ErpFeature::getFeaturesWithToken($this->context->language->iso_code);
                $this->tpl_list_vars['template_path'] = $this->template_path;

                $advanced_stock_token = Tools::getAdminToken('AdminAdvancedStock'.(int)(Tab::getIdFromClassName('AdminAdvancedStock')).(int)$this->context->employee->id);

                $this->tpl_list_vars['advanced_stock_token'] = $advanced_stock_token;

                return parent::renderList();
	}

	public function initContent()
	{
		if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
		{
                    $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management prior to using this feature. (Preferences/Products/Products Stock)');
                    return false;
		}

		if (count(Warehouse::getWarehouses(true)) < 2)
		{
                    $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to create at least two warehouses prior to using this feature.');
                    return false;
		}

                $this->displayInformation($this->l('To transfer a stock of product between two warehouses, the product must be registered in both warehouses').
                $this->l(' and the product must have a positive stock in the source warehouse.').'<br />');
                
		$this->display = 'view';

		// Ajout du plugin simple tooltip
		$this->addJqueryPlugin('cluetip', _MODULE_DIR_.'erpillicopresta/js/cluetip/');

		if (version_compare(_PS_VERSION_,'1.5.2','>=') && version_compare(_PS_VERSION_,'1.5.4','<='))
					$this->addJqueryPlugin('custom.min', _MODULE_DIR_.'erpillicopresta/js/');
		else if (version_compare(_PS_VERSION_,'1.6','>='))
		{
                    
                    $this->addJqueryUI('ui.core');
                    $this->addJqueryUI('ui.widget');
                    $this->addJqueryUI('ui.dialog');
                    $this->addJqueryUI('ui.position');
                    $this->addJqueryUI('ui.button');
                }
                else
                {
                    $this->addJqueryPlugin('ui.core.min', '/js/jquery/ui/');
                    $this->addJqueryPlugin('ui.widget.min', '/js/jquery/ui/');
                    $this->addJqueryPlugin('ui.dialog.min', '/js/jquery/ui/');
                    $this->addJqueryPlugin('ui.position.min', '/js/jquery/ui/');
                    $this->addJqueryPlugin('ui.button.min', '/js/jquery/ui/');
                
                }

		// Charge les JS
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_stock.js');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_stock_tools.js');

		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/stock_transfert.js');

		// Charge les CSS
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/jquery.custom.css');
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/jquery.cluetip.css');
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/stock.transfert.css');

		return parent::initContent();
	}


	/*
	* Traitement Filtres
	*/
	public function postProcess()
	{
		// ENTREPOTS
		$warehouses = Warehouse::getWarehouses(true);

		// Si on a déjà choisis un entrepot source et destination, on filtre sur les 2 pour ne pas afficher les sélections de l'autre
		if (Tools::isSubmit('warehouseA') && Tools::getValue('warehouseA') != -1
				&& Tools::isSubmit('warehouseB') && Tools::getValue('warehouseB') != -1)
		{
			$warehouseA = Tools::getValue('warehouseA');
			$warehouseB = Tools::getValue('warehouseB');
			$warehousesA = array();
			$warehousesB = array();
			foreach ($warehouses as $warehouse)
			{
                            if ($warehouse['id_warehouse'] != $warehouseA)
                                            array_push($warehousesB, $warehouse);

                            if ($warehouse['id_warehouse'] != $warehouseB)
                                            array_push($warehousesA, $warehouse);
			}
		}
		// Si on a déjà sélectionné un entrepot source, on filtre la liste de destination pour ne pas afficher celui sélectionné
		elseif (Tools::isSubmit('warehouseA') && Tools::getValue('warehouseA') != -1)
		{
			$warehouseA = Tools::getValue('warehouseA');
			$warehousesB = array();

			foreach ($warehouses as $warehouse)
				if ($warehouse['id_warehouse'] != $warehouseA)
					array_push($warehousesB, $warehouse);

			$warehousesA = $warehouses;

			// Repasse la liste B sur aucune sélection
			$this->context->smarty->assign(array(
				'warehouseB' => -1
			));

		}
		// Si on a déjà sélectionné un entrepot destination, on filtre la liste de source pour ne pas afficher celui sélectionné
		elseif (Tools::isSubmit('warehouseB') && Tools::getValue('warehouseB') != -1)
		{
                    $warehouseB = Tools::getValue('warehouseB');
                    $warehousesA = array();

                    foreach ($warehouses as $warehouse)
                            if ($warehouse['id_warehouse'] != $warehouseB)
                                    array_push($warehousesA, $warehouse);

                    $warehousesB = $warehouses;
		}
		// Sinon on affiche tout
		else
		{
                    $warehousesA = $warehouses;
                    $warehousesB = $warehouses;
		}

		// validate_transfer
		if (Tools::isSubmit('validate_transfer'))
                {
                        $transfer_ok = false;
                    
                        if (Tools::isSubmit('id_stockA') && Tools::isSubmit('id_stockB') && Tools::isSubmit('id_employee') && Tools::isSubmit('firstname')
				&& Tools::isSubmit('lastname') && Tools::isSubmit('values'))
                        {
                                            

                                /*  Après les transferts, on supprime les cookies pour repartir sur un affichage vierge */
                               if (Tools::isSubmit('deleteCookie'))
                               {
                                       $cookie = new Cookie('psAdmin');
                                       $cookie->warehouseA = '';
                                       $cookie->warehouseB = '';
                               }

                               /*  Initialisation */
                               $ids_mvt = array();

                               /* Appel à l'Helper "traducteur" de la chaine de transfert */
                               require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/helpers/StockTransferHelper.php');
                               require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStock.php');
                               $values = StockTransferHelper::getTransfertDataAsArray (Tools::getValue('values'));

                               /* Pour chaque mouvement enregistré */
                               foreach ($values as $value)
                               {
                                               $id_product 		= $value['id_product'];
                                               $id_product_attribute 	= $value['id_product_attribute'];
                                               $transfer_quantity 	= $value['quantity'];
                                               $id_stock_s1            = $value['id_stock_s1'];
                                               $id_stock_s2            = $value['id_stock_s2'];

                                               if (empty($id_stock_s1))
                                               {
                                                   $this->errors[] = 'Error while updating the stock for a product : stock id missing !';
                                                   return true;
                                               }

                                               /*  Mise à jour stock dans l'entrepôt A (source) */
                                               $stock = new ErpStock( (int)$id_stock_s1 );
                                               $stock->physical_quantity -= $transfer_quantity;
                                               $stock->usable_quantity -= $transfer_quantity;

                                               if ($stock->physical_quantity < 0)
                                                               $stock->physical_quantity = 0;

                                               if ($stock->usable_quantity < 0)
                                                               $stock->usable_quantity = 0;

                                               /*  MAJ Stock */
                                               if ($stock->update())
                                               {
                                                               /*  Mouvement A vers B */
                                                               $stock_mvt = new ErpStockMvt();
                                                               $stock_mvt->id_stock = $stock->id;
                                                               $stock_mvt->id_order = 0;
                                                               $stock_mvt->id_supply_order = 0;
                                                               $stock_mvt->id_stock_mvt_reason = 6;
                                                               $stock_mvt->id_employee = Tools::getValue('id_employee');
                                                               $stock_mvt->employee_firstname = Tools::getValue('firstname');
                                                               $stock_mvt->employee_lastname = Tools::getValue('lastname');
                                                               $stock_mvt->price_te = $stock->getPriceTe();
                                                               $stock_mvt->current_wa = $stock->getPriceTe();
                                                               $stock_mvt->sign = -1;
                                                               $stock_mvt->physical_quantity = $transfer_quantity;

                                                               /*  Si mouvement OK, Mise à jour stock dans l'entrepôt B (destination) */
                                                               if ($stock_mvt->add(true))
                                                               {
                                                                               /*  Ajout de l'id mvt stock généré (utile pour la génération du bon inter entrepot) */
                                                                               array_push($ids_mvt, $stock_mvt->getLastId());


                                                                               if ((int)$id_stock_s2 > 0)
                                                                               {
                                                                                   $stock_s2 = new ErpStock((int)$id_stock_s2);
                                                                                   $stock_s2->physical_quantity += $transfer_quantity;
                                                                                   $stock_s2->usable_quantity += $transfer_quantity;
                                                                               }
                                                                               else 
                                                                               {
                                                                                   require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProduct.php');

                                                                                   //get product information
                                                                                   $product_stock = new ProductCore((int)$id_product, (int)$id_product_attribute);

                                                                                   // stock not existe we add row in stock table
                                                                                   $stock_s2 = new ErpStock();
                                                                                   $stock_s2->physical_quantity += $transfer_quantity;
                                                                                   $stock_s2->usable_quantity += $transfer_quantity;
                                                                                   $stock_s2->id_product = (int)$id_product;
                                                                                   $stock_s2->id_product_attribute = (int)$id_product_attribute;
                                                                                   $stock_s2->id_warehouse = (int)Tools::getValue('id_stockB');
                                                                                   $stock_s2->price_te = $product_stock->wholesale_price;
                                                                                   $stock_s2->ean13 = $product_stock->ean13;
                                                                                   $stock_s2->upc = $product_stock->upc;
                                                                               }

                                                                               /*  Si OK, mouvement de B vers A */
                                                                               if ($stock_s2->save())
                                                                               {
                                                                                   $stock_mvt->id_stock = $stock_s2->id;
                                                                                   $stock_mvt->id_stock_mvt_reason = 7;
                                                                                   $stock_mvt->sign = 1;

                                                                                   // Ajout de l'id mvt stock généré (utile pour la génération du bon inter entrepot) 
                                                                                   if ($stock_mvt->add(true))
                                                                                       array_push($ids_mvt, $stock_mvt->getLastId());
                                                                                   else
                                                                                       $this->errors[] = 'Error while updating the stock for a product';
                                                                               }
                                                                               else
                                                                                       $this->errors[] = 'Error while updating the stock for a product';
                                                               }
                                                               else
                                                                       $this->errors[] = 'Error while updating the stock for a product';
                                               }
                                               else
                                                       $this->errors[] = 'Error while updating the stock for a product';

                                               /*  Synchronisation du stock available ::quantity */
                                               StockAvailable::synchronize($id_product);
                               }

                               $ids_mvt =  implode('|', $ids_mvt);
                                                        
                                // we reset values  
                                $transfer_ok = true;   
                        }
                }

                // transfer is ok, we redirect the user to home page of transfer 
                if (Tools::isSubmit('validate_transfer') && isset($transfer_ok) && $transfer_ok )
                {
                    $url_redirect = self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminStockTransfer');
                    $url_redirect .= '&validate_transfer&ids_mvt='.$ids_mvt.'&ids_mvt_csv='.Tools::getValue('ids_mvt_csv');
                    $url_redirect .= '&id_warehouse_src='.Tools::getValue('id_warehouse_src').'&id_warehouse_dst='.Tools::getValue('id_warehouse_dst');
                    Tools::redirectAdmin($url_redirect); 
                }
                    
                if (Tools::isSubmit('ids_mvt_csv') && Tools::isSubmit('get_csv_transfer'))
                    $this->renderCSV();

		// Generate PDF of tranfert
		if (Tools::isSubmit('ids_mvt') && Tools::isSubmit('action') && Tools::getValue('action') == 'generateTransferPDF')
			$this->processGenerateTransferPDF();

                // to get erp feature list
                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';

                // Envois des listes entrepot au tpl
		$this->context->smarty->assign(array(
			'warehousesA' => $warehousesA,
			'warehousesB' => $warehousesB,
			'categories' => Category::getSimpleCategories((int)$this->context->language->id),
			'suppliers' => Supplier::getSuppliers(),
			'manufacturers' => Manufacturer::getManufacturers(),
			'id_category' => -1,
			'id_supplier' => -1,
			'id_manufacturer' => -1,
			'name_or_ean' => '',
                        'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
			'template_path' => $this->template_path,
		));
	}

	public function clearListOptions()
	{
		$this->table = '';
		$this->actions = array();
		$this->lang = false;
		$this->identifier = '';
		$this->_orderBy = '';
		$this->_orderWay = '';
		$this->_filter = '';
		$this->_group = '';
		$this->_where = '';
		$this->list_no_filter = true;
		$this->list_title = $this->l('Product disabled');
		$this->show_toolbar = false;
	}

	/*
		* Affichage des 2 tableaux
		*/
	public function renderView()
	{

		$this->getCurrentValue('warehouseA');
		$this->getCurrentValue('warehouseB');
		//$this->getCurrentValue('name_or_ean', '');

		$this->context->smarty->assign(array(
			'link_pdf' => $this->context->link->getAdminLink('AdminStockTransfer').'&submitAction=generateTransferPDF'
		));

		// Si on a des valeurs de transfert déjà enregistrés on les envoi (pagination & filtre)
		if (Tools::isSubmit('transfers') && Tools::getValue('transfers') != '')
			$this->context->smarty->assign(array('transfers' => Tools::getValue('transfers')));

		$this->_helper_list = new HelperList();

		$this->clearListOptions();
		$this->content = $this->getCustomList();

		return AdminController::renderView();
	}
        
        /*
        * Retourne une valeur en get/post
        */
	protected function getCurrentValue ($var, $defaultValue = -1)
	{

		if (Tools::isSubmit($var))
			$value = Tools::getValue($var);
		else
			$value = $defaultValue;

		$this->context->smarty->assign(array(
				$var => $value
			));

		return ($value == -1) ? false : $value;
	}
	
	/*
	* Generate PDF of tranfert
	*
	*/
	public function processGenerateTransferPDF()
	{
            // Si on a bien des mouvements
            $ids_mvt = Tools::getValue('ids_mvt');

            if (!empty ($ids_mvt))
            {
                    require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/pdf/HTMLTemplateTransfer.php');

                    $movements = new ErpStockMvt();

                    $pdf = new PDF(array ($movements->getMovementsByIds ($ids_mvt)) , 'Transfer', Context::getContext()->smarty);
                    $pdf->render(true);
            }
	}


	/* RJMA
	* Rajout pour la traduction du controller AdminStockTransfer
        *
        */
	protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = true)
	{
            if (!empty($class))
            {
                    $str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminStockTransfer');
                    $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
                    return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
            }
	}

	public function getCustomList()
	{
            // Si on a les id warehouse en get (cas premier affichage)
            if(Tools::isSubmit('warehouseA') && Tools::isSubmit('warehouseB'))
            {
                // Recup valeur warehouses selectionnés ou pas
                $this->context->cookie->warehouseA = Tools::getValue('warehouseA');
                $this->context->cookie->warehouseB = Tools::getValue('warehouseB');
            
                // Si on a un bon id warehouse, on affiche
		if(($this->context->cookie->warehouseA != -1 && $this->context->cookie->warehouseA != '')
                        && ($this->context->cookie->warehouseB != -1 && $this->context->cookie->warehouseB != ''))
                {
			$this->show_toolbar = false;
			$id_warehouseA = (Tools::isSubmit('warehouseA')) ? Tools::getValue('warehouseA') : $this->context->cookie->warehouseA;
			$id_warehouseB = (Tools::isSubmit('warehouseB')) ? Tools::getValue('warehouseB') : $this->context->cookie->warehouseB;

				
			// Ajout de colonnes supplémentaires
			$AColumns = array(
				'qte_transfer' => array(
                                    'title' => $this->l('Transfer'),
                                    'align' => 'center',
                                    'search' => false,
                                    'orderby' => false,
                                    'callback' => 'renderQteTransferColumn'
                                ),
				'quantity_after' => array(
                                        'title' => $this->l('After transfer'),
                                        'align' => 'center',
                                        'search' => false,
                                        'orderby' => false,
                                        'class' => 'quantity_after text-center'
                                ),
				'direction' => array(
                                        'title' => $this->l(''),
                                        'align' => 'center',
                                        'search' => false,
                                        'orderby' => false,
                                        'class' => 'direction text-center',
                                        'callback' => 'renderDirectionColumn'
                                ),
				'physical_quantity2' => array(
                                        'title' => $this->l('Physical quantity'),
                                        'align' => 'center',
                                        'search' => false,
                                        'orderby' => true,
                                        'class' => 'physical_quantity2 text-center'
                                ),
				'usable_quantity2' => array(
                                        'title' => $this->l('Usable quantity'),
                                        'align' => 'center',
                                        'search' => false,
                                        'orderby' => true,
                                        'class' => 'usable_quantity2 text-center'
                                ),
				'new_stock' => array(
                                        'title' => $this->l('New stock'),
                                        'align' => 'center',
                                        'search' => false,
                                        'orderby' => false,
                                        'class' => 'new_stock text-center'
                                )
	);

			// Colonnes affichées
			$this->fields_list = array_merge($this->baseColumns, $AColumns);

			$this->context->smarty->assign(array(
					'warehouse_name' => $this->l('Source warehouse').' : '.Warehouse::getWarehouseNameById($id_warehouseA),
					'warehouse2_name' => $this->l('Destination warehouse').' : '.Warehouse::getWarehouseNameById($id_warehouseB) ,
					'warehouse_id' => 'stockA',
					'warehouse_real_id' =>$id_warehouseA,
					'warehouse2_id' => 'stockB',
					'warehouse2_real_id' =>$id_warehouseB,
					'ps_version_sup_1550' => version_compare(_PS_VERSION_, '1.5.5.0', '>='), // if ps_version is >= 1.5.5.0 the template liste_header.tpl change
                        ));

			$this->table = 'stock';
			$this->className = 'Stock';
			$this->list_no_link = true;

			$this->table = 'stock';
			$this->className = 'Stock';
			$this->identifier = 'id_stock';
			$this->_orderBy = 'name';

			$this->_select = '
                                            "-->" as direction,
                                            IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
                                            a.ean13 as EAN,
                                            w.id_currency,
                                            if (a.id_product_attribute = 0, a.id_product, CONCAT(a.id_product, ";", a.id_product_attribute)) as ids,
                                            0 as qte_transfer,
                                            a.physical_quantity as quantity_after,
                                            s2.physical_quantity as physical_quantity2,
                                            s2.usable_quantity as usable_quantity2,
                                            s2.physical_quantity as new_stock,
                                            a.id_stock as id_stock_s1,
                                            s2.id_stock as id_stock_s2' ;

			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                                        a.id_product = pl.id_product
                                        AND pl.id_lang = '.(int)$this->context->language->id.')';

			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
					al.id_attribute = pac.id_attribute
					AND al.id_lang = '.(int)$this->context->language->id.')';

			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
					agl.id_attribute_group = atr.id_attribute_group
					AND agl.id_lang = '.(int)$this->context->language->id.')';

			$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'warehouse` w ON (w.id_warehouse = a.id_warehouse)';
			$this->_join .= 'INNER JOIN '._DB_PREFIX_.'product p ON a.id_product = p.id_product ';
			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (a.id_product = pa.id_product AND a.id_product_attribute = pa.id_product_attribute)';
			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'manufacturer m ON m.id_manufacturer = p.id_manufacturer ';

			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'stock s2 ON (a.id_product = s2.id_product AND a.id_product_attribute = s2.id_product_attribute AND s2.id_warehouse = \''.$id_warehouseB.'\')';

			$this->_group = "GROUP BY a.id_product, a.id_product_attribute";

			$this->_where .= "AND w.id_warehouse = ".$id_warehouseA;
			// FILTRES

			// Filtre Marque
			if (($id_manufacturer = $this->getCurrentValue('id_manufacturer')) != false)
				$this->_where .= ' AND m.id_manufacturer = '.$id_manufacturer;

			//Filtre catégorie
			if (($id_category = $this->getCurrentValue('id_category')) != false)
			$this->_where .= ' AND a.id_product IN (
											SELECT cp.id_product
											FROM '._DB_PREFIX_.'category_product cp
											WHERE cp.id_category = '.$id_category.'
							)';

			// Filtre fournisseur
			if (($id_supplier = $this->getCurrentValue('id_supplier')) != false)
			$this->_where .= ' AND a.id_product IN (
											SELECT ps.id_product
											FROM '._DB_PREFIX_.'product_supplier ps
											WHERE ps.id_supplier = '.$id_supplier.'
							)';

			$nameOrEan = pSQL ($this->getCurrentValue ('name_or_ean',''));

			if ($nameOrEan != false && $nameOrEan != '')
				$this->_where .= ' AND (a.ean13 LIKE \'%'.$nameOrEan.'%\' OR pl.name LIKE \'%'.$nameOrEan.'%\' OR al.name  LIKE \'%'.$nameOrEan.'%\')';

			$list = $this->renderList();
			return $list;
		}
                else
                {
                    // Vide cookie affichage form : correction if selection warehouse + changement controller
                    $cookie = new Cookie('psAdmin');
                    $cookie->warehouseA = '';
                    $cookie->warehouseB = '';
                }
            }
	}

	protected function renderCSV()
	{
		if (Tools::isSubmit('ids_mvt_csv'))
		{
                    require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProduct.php');

                    $warehouseSrc = new Warehouse(Tools::getValue('id_warehouse_src'));
                    $warehouseDst = new Warehouse(Tools::getValue('id_warehouse_dst'));

                    //csv file name
                    $file_name = 'stock_transfert_'.date('Y-m-d_His').'.csv';

                    // header
                    header('Content-type: text/csv');
                    header('Cache-Control: no-store, no-cache');
                    header('Content-disposition: attachment; filename="'.$file_name);
                    
                    // Ecriture des titres de colonnes
                    $keys = array (
                            $this->l('warehouse_source_id'),
                            $this->l('warehouse_source_name'),
                            $this->l('warehouse_destination_id'),
                            $this->l('warehouse_destination_name'),
                            $this->l('id_product'),
                            $this->l('id_product_attribute'),
                            $this->l('sku'),
                            $this->l('ean'),
                            $this->l('product_name'),
                            $this->l('quantity')
                    );
                    
                    $keys = array_map(array('AdminStockTransferController', 'transformText'), $keys);
                    
                    echo sprintf ("%s\n", implode (';', $keys));

                    // Appel à l'Helper "traducteur" de la chaine de transfert pour récupération des données
                    require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/helpers/StockTransferHelper.php');
                    $data = StockTransferHelper::getTransfertDataAsArray (Tools::getValue ('ids_mvt_csv'));

                    // Ecritures des données
                    foreach ($data as &$product)
                    {
                            // Récupération information complémentaire
                            $product ['product_name']	= ErpProduct::getProductName($product['id_product'], $product['id_product_attribute']);
                            $productInfo 		= ErpProduct::getProductsInfo($product['id_product'], $product['id_product_attribute']);

                            $product ['ean13']          = isset ($productInfo['ean13']) ? $productInfo['ean13'] : '';
                            $product ['reference'] 	= isset ($productInfo['reference']) ? $productInfo['reference'] : '';

                            $file_content = array( 
                                (int)$warehouseSrc->id,
                                self::transformText($warehouseSrc->name),
                                (int)$warehouseDst->id,
                                self::transformText($warehouseDst->name),
                                (int)$product['id_product'],
                                (int)$product['id_product_attribute'],
                                $product['reference'],
                                $product['ean13'],
                                self::transformText($product['product_name']),
                                (int)$product['quantity'].PHP_EOL
                            );
                            
                            echo implode(';', $file_content);
                    }
                    exit();                    
		}
	}

	public function InitToolbar ()
	{
            return parent::initToolbar();
	}

	protected static function transformText($text)
	{
                //delete html tags
		$text = strip_tags($text);
                
                // decode html specialchar 
                $text = html_entity_decode($text);
                $text = utf8_decode($text);
                
		$text = str_replace("\n", '.', $text);
		$text = str_replace("\r", '.', $text);
		$text = str_replace(";", ',', $text);
                
		return $text;
	}
        
	public function renderQteTransferColumn($qte_transfer, $data)
	{
            $html = '<input class="qte_transfer" type="text" value="'.$qte_transfer.'" size="5" />';
            $html .=  '<input class="products_ids" id="products_ids_'.(int)$data['id_product'].'_'.(int)$data['id_product_attribute'].'" type="hidden" value="'.(int)$data['id_product'].'_'.(int)$data['id_product_attribute'].'" />';
            $html .=  '<input class="id_stock_s1" type="hidden" value="'.(int)$data['id_stock_s1'].'" />';
            $html .=  '<input class="id_stock_s2" type="hidden" value="'.(int)$data['id_stock_s2'].'" />';

            return $html;
	}
        
	public function renderDirectionColumn($direction)
	{
            $html = '<div style="padding:4px"> ';
            if ($this->is_1_6)
                $html .= '<i class="icon-arrow-right"></i>';
            else
                $html .= '<img src="../img/admin/arrow-right.png">';
            $html .= '</div>';
            return $html;
	}
}