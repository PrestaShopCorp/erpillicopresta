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
*  @copyright 2007-2015 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once _PS_MODULE_DIR_.'erpillicopresta/controllers/admin/IPAdminController.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpSupplyOrderClasses.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrder.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrderDetail.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminAdvancedSupplyOrderController extends IPAdminController
{

	private $controller_status = 0;
	private $advanced_stock_management = false;
        private $nbcmdfou = 0;
	/*
	 * @var array List of warehouses
	 */
	protected $warehouses;

	public function __construct()
	{
		$this->bootstrap = true;
		$this->context = Context::getContext();
		$this->table = 'supply_order';

		$this->className = 'SupplyOrder';
		$this->identifier = 'id_supply_order';
		$this->lang = false;
		$this->is_template_list = false;
		$this->multishop_context = Shop::CONTEXT_ALL;

                // get controller status
		$this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedSupplyOrder'));
                if($this->controller_status == STATUS1)
                {
                    $sql = 'SELECT count(*) from '._DB_PREFIX_.'erpip_supply_order';
                    $query = new DbQuery();
			$query->select('count(distinct so.id_supply_order)');
			$query->from('erpip_supply_order', 'so');
                        $query->innerJoin('supply_order','s','so.id_supply_order = s.id_supply_order');
                        $query->where("s.date_add >= '".pSQL(Configuration::get('ERP_FIRST_INSTALL_DATE'))."'");
                        $this->nbcmdfou = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
               }

                // template path for avdanced supply ordr
                $this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';
                
                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');

                //get if advanced stock enabled
                $this->advanced_stock_management = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

                $this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0;
                
                $this->token = Tools::getAdminToken('AdminAdvancedSupplyOrder'.(int)(Tab::getIdFromClassName('AdminAdvancedSupplyOrder')).(int)$this->context->employee->id);

		$this->addRowAction('updatereceipt');
		$this->addRowAction('changestate');
		$this->addRowAction('edit');
		$this->addRowAction('view');
		$this->addRowAction('details');
		$this->list_no_link = true;

                $statuses_array = array();
		$statuses = SupplyOrderState::getSupplyOrderStates((int)$this->context->language->id);

		foreach ($statuses as $status)
			$statuses_array[$status['id_supply_order_state']] = $status['name'];

		$this->fields_list = array(
						'id_supply_order' => array(
							'title' => 'ID',
							'width' => 100,
							'search' => false,
							'havingFilter' => false,
							'orderby' => true,
							'callback' => 'renderIdSupplyOrderColumn'
						),
			'reference' => array(
							'title' => $this->l('Reference'),
							'width' => 100,
							'havingFilter' => true,
							'callback' => 'renderReferenceColumn'
						),
						'with_description' => array(
							'title' => $this->l('Description'),
							'width' => 25,
							'align' => 'center',
							'class' => 'view_description',
							'callback' => 'renderWithDescriptionColumn',
							'search' => false,
							'orderby' => false
						),
				);

				if ($this->controller_status)
				{
					$this->fields_list = array_merge($this->fields_list, array(
						'invoice_number' => array(
							'title' => $this->l('Invoice Number'),
							'width' => 100,
							'havingFilter' => true
						),
						'date_to_invoice' => array(
							'title' => $this->l('Invoice Date'),
							'width' => 100,
							'type' => 'date',
							'havingFilter' => true
						)));
				}

				$this->fields_list = array_merge($this->fields_list, array(

			'supplier' => array(
							'title' => $this->l('Supplier'),
							'width' => 100,
							'filter_key' => 's!name'
			),
			'warehouse' => array(
							'title' => $this->l('Warehouse'),
							'width' => 100,
							'filter_key' => 'w!name'
			),
			'state' => array(
							'title' => $this->l('Status'),
							'color' => 'color',
							'width' => 150,
							'type' => 'select',
							'list' =>  $statuses_array,
							'filter_key' => 'stl!name',
							'filter_type' => 'int',
							'color' => 'color',
							'callback' => 'renderSupplyOrderStatesColumn'
			),
			'date_add' => array(
                                'title' => $this->l('Creation'),
                                'width' => 150,
                                'align' => 'left',
                                'type' => 'date',
                                'havingFilter' => true,
                                'filter_key' => 'a!date_add'
			),
			'date_upd' => array(
							'title' => $this->l('Last modification'),
							'width' => 150,
							'align' => 'left',
							'type' => 'date',
							'havingFilter' => true,
							'filter_key' => 'a!date_upd'
			),
			'date_delivery_expected' => array(
							'title' => $this->l('Expected delivery date'),
							'width' => 150,
							'align' => 'left',
							'type' => 'date',
							'havingFilter' => true,
							'filter_key' => 'a!date_delivery_expected'
			),
			'id_export' => array(
							'title' => $this->l('Export'),
							'width' => 80,
							'callback' => 'printExportIcons',
							'orderby' => false,
							'search' => false
			),
		));

		// gets the list of warehouses available
		$this->warehouses = Warehouse::getWarehouses(true);
		// gets the final list of warehouses
		array_unshift($this->warehouses, array('id_warehouse' => -1, 'name' => $this->l('All warehouses')));

                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
                                
                // send var to template
                $this->context->smarty->assign(array(
                    'is_1_6' => $this->is_1_6,
                    'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
                    'inheritance_merge_compiled_includes' => false
                ));

        // get controller status
  	 	$this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedOrder'));
                                                    
		parent::__construct();
	}

	/**
	 * AdminController::init() override
	 * @see AdminController::init()
	 */
	public function init()
	{
                if( $this->controller_status == STATUS1)
                    {
                        $this->informations[] = '<b><a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Do not limit yourself to 1 trial order, take advantage of the Light version of the Supplier area for €79.99 before tax or €8.00/month before tax. Go to your back-office, under the module tab, page 1-Click ERP!').'</b></a><br/><br/>';
                    } else if( $this->controller_status == STATUS2)
                    {
                        $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Optimise Supplier management with accounting reconciliation, multiple product selection and a decision-making assistant tool for just €20.00 before tax or €1.00/month before tax. Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
                    } else if( $this->controller_status == STATUS3)
                    {
                        $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Activate additional features in your TIME SAVER module in the Module section of your back-office! Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
                    }
		parent::init();

		if (Tools::isSubmit('addsupply_order') ||
			Tools::isSubmit('submitAddsupply_order') ||
			(Tools::isSubmit('updatesupply_order') && Tools::isSubmit('id_supply_order')))
		{
			// override table, lang, className and identifier for the current controller
			$this->table = 'supply_order';
			$this->className = 'SupplyOrder';
			$this->identifier = 'id_supply_order';
			$this->lang = false;

			$this->action = 'new';
			$this->display = 'add';

			if (Tools::isSubmit('updatesupply_order'))
				if ($this->tabAccess['edit'] === '1')
					$this->display = 'edit';
				else
					$this->errors[] = Tools::displayError($this->l('You do not have permission to edit this.'));
		}

		if (Tools::isSubmit('update_receipt') && Tools::isSubmit('id_supply_order'))
		{
			// change the display type in order to add specific actions to
			$this->display = 'update_receipt';

			// display correct toolBar
			$this->initToolbar();
		}
	}

	/**
	 * AdminController::renderForm() override
	 * @see AdminController::renderForm()
	 */
	public function renderForm()
	{
		// loads current warehouse
		if (!($obj = $this->loadObject(true)))
			return;

		if (Tools::isSubmit('addsupply_order') ||
			Tools::isSubmit('updatesupply_order') ||
			Tools::isSubmit('submitAddsupply_order') ||
			Tools::isSubmit('submitUpdatesupply_order'))
		{

			if (Tools::isSubmit('addsupply_order') ||	Tools::isSubmit('submitAddsupply_order'))
				$this->toolbar_title = $this->l('Create a new supply order');

			if (Tools::isSubmit('updatesupply_order') || Tools::isSubmit('submitUpdatesupply_order'))
				$this->toolbar_title = $this->l('Manage supply orders');

			if (Tools::isSubmit('mod') && Tools::getValue('mod') === 'template' || $this->object->is_template)
				$this->toolbar_title .= ' ('.$this->l('template').')';

			//get warehouses list
			$warehouses = Warehouse::getWarehouses(true);

			// displays warning if there are no warehouses
			if (!$warehouses)
				$this->displayWarning($this->l('You must have at least one warehouse. See Stock/Warehouses'));

			//get currencies list
			$currencies = Currency::getCurrencies();
			$id_default_currency = Configuration::get('PS_CURRENCY_DEFAULT');
			$default_currency = Currency::getCurrency($id_default_currency);
			if ($default_currency)
				$currencies = array_merge(array($default_currency, '-'), $currencies);

			//get suppliers list
			$suppliers = Supplier::getSuppliers();

			//get languages list
			$languages = Language::getLanguages(true);
			$id_default_lang = Configuration::get('PS_LANG_DEFAULT');
			$default_lang = Language::getLanguage($id_default_lang);
			if ($default_lang)
				$languages = array_merge(array($default_lang, '-'), $languages);

			$this->fields_form = array(
				'legend' => array(
					'title' => $this->l('Order information'),
                                        ($this->is_1_6 ? 'icon' : 'image') => ($this->is_1_6 ? 'icon-pencil' : '../img/admin/edit.gif')
				),
				'input' => array(

					array(
					'type' => 'hidden',
					'name' => 'id_erpip_supply_order',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Reference:'),
						'name' => 'reference',
						'size' => 50,
						'required' => true,
												'readonly' => true,
						'desc' => $this->l('The reference number of your order.'),
					),
										array(
											'type' => 'textarea',
											'label' => $this->l('Description:'),
											'name' => 'description',
											'cols' => 60,
											'rows' => 10,
											'lang' => false,
											'hint' => $this->l('Invalid characters :').' <>;=#{}',
											'desc' => $this->l('Will appear in the suppliers list'),
											'autoload_rte' => 'rte' //Enable TinyMCE editor for description
										),
					array(
						'type' => 'select',
						'label' => $this->l('Supplier:'),
						'name' => 'id_supplier',
						'required' => true,
						'options' => array(
							'query' => $suppliers,
							'id' => 'id_supplier',
							'name' => 'name'
						),
						'desc' => $this->l('Select the supplier you\'ll be purchasing from.'),
						'hint' => $this->l('Warning: All products already added to the order will be removed.')
					),
					array(
						'type' => 'select',
						'label' => $this->l('Warehouse:'),
						'name' => 'id_warehouse',
						'required' => true,
						'options' => array(
							'query' => $warehouses,
							'id' => 'id_warehouse',
							'name' => 'name'
						),
						'desc' => $this->l('Which warehouse will the order be sent to?'),
					),
					array(
						'type' => 'select',
						'label' => $this->l('Currency:'),
						'name' => 'id_currency',
						'required' => true,
						'options' => array(
							'query' => $currencies,
							'id' => 'id_currency',
							'name' => 'name'
						),
						'desc' => $this->l('The currency of the order.'),
						'hint' => $this->l('Warning: All products already added to the order will be removed.')
					),
					array(
						'type' => 'select',
						'label' => $this->l('Order Language:'),
						'name' => 'id_lang',
						'required' => true,
						'options' => array(
							'query' => $languages,
							'id' => 'id_lang',
							'name' => 'name'
						),
						'desc' => $this->l('The language of the order.')
					),
					array(
						'type' => 'radio',
						'label' => $this->l('Global discount type:'),
						'name' => 'global_discount_type',
						'required' => true,
						'br' => true,
						'class' => 't',
						'default_value' => 'amount',
						'values' => array(
							array(
									'id' => 'global_discount_type'._PS_SMARTY_NO_COMPILE_,
									'value' => 'rate',
									'label' => $this->l('Ratio')
							),
							array(
									'id' => 'global_discount_type'._PS_SMARTY_CHECK_COMPILE_,
									'value' => 'amount',
									'label' => $this->l('Amount')
						)
						),
						'desc' => $this->l('Please select the discount type which will be applied to this order : ratio or amount')
					),
					array(
						'type' => 'text',
						'label' => $this->l('Global discount rate (%):'),
						'name' => 'discount_rate',
						'size' => 10,
						'required' => true,
						'desc' => $this->l('This is the global discount rate in percent for the order.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Discount total amount:'),
						'name' => 'global_discount_amount',
						'size' => 10,
						'required' => false,
						'desc' => $this->l('Please indicate the discount total amount'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Discount (%)'),
						'name' => 'escompte',
						'size' => 10,
						'required' => false,
						'desc' => $this->l('Percentage discount impacting the order total.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Shipping amount'),
						'name' => 'shipping_amount',
						'size' => 10,
						'required' => false,
						'desc' => $this->l('Shipping amount that will be added to the total order (tax excl.).'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Automatically load products:'),
						'name' => 'load_products',
						'size' => 10,
						'required' => false,
						'hint' => $this->l('This will reset the order'),
						'desc' => $this->l('If specified, each product quantity inferior or equal to this value will be loaded.'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save order'),
				)
			);

			if (Tools::isSubmit('mod') && Tools::getValue('mod') === 'template' ||
				$this->object->is_template)
			{

				$this->fields_form['input'][] = array(
					'type' => 'hidden',
					'name' => 'is_template'
				);

				$this->fields_form['input'][] = array(
					'type' => 'hidden',
					'name' => 'date_delivery_expected',
				);
			}
			else
			{
				$this->fields_form['input'][] = array(
					'type' => 'date',
					'label' => $this->l('Expected delivery date:'),
					'name' => 'date_delivery_expected',
					'size' => 10,
					'required' => true,
					'desc' => $this->l('The expected delivery date for this order is...'),
				);
			}

			//specific discount display
			if (isset($this->object->discount_rate))
				$this->object->discount_rate = Tools::ps_round($this->object->discount_rate, 4);

			//specific date display

			if (isset($this->object->date_delivery_expected))
			{
				$date = explode(' ', $this->object->date_delivery_expected);
				if ($date)
					$this->object->date_delivery_expected = $date[0];
			}

			$this->displayInformation(
				$this->l('If you wish to order products, they have to be available for the specified supplier/warehouse.')
				.' '.
				$this->l('See Catalog/Products/Your Product/Suppliers & Warehouses')
				.'<br />'.
				$this->l('Changing the currency or the supplier will reset the order.')
				.'<br />'
				.'<br />'.
				$this->l('Please note that you can only order from one supplier at a time.')
			);

			//-- ERP informations
			// add additional information
			$erp_filed_value = array();
						$erp_filed_value['id_erpip_supply_order'] = 0;
						$franco_amount = 0;
						//if we are in add form
			if (Tools::isSubmit('addsupply_order') || Tools::isSubmit('submitAddsupply_order'))
			{
				$erp_filed_value['reference'] = ErpSupplyOrderClasses::getNextSupplyOrderReference();

				//get value of the first supplier : the default selected
				if (!empty($suppliers))
				{
					// get additional information for the first supplier
					$id_erpip_supplier = ErpSupplier::getErpSupplierIdBySupplierId((int)$suppliers[0]['id_supplier']);
					$erp_supplier = null;
					if ($id_erpip_supplier > 0)
						   $erp_supplier = new ErpSupplier( (int)$id_erpip_supplier);

					if ($erp_supplier != null)
					{
						$franco_amount = $erp_supplier->franco_amount;
						$erp_filed_value['escompte'] = $erp_supplier->escompte;
						$erp_filed_value['shipping_amount'] = $erp_supplier->shipping_amount;
						$erp_filed_value['global_discount_amount'] = $erp_supplier->discount_amount;

						if ((int)$erp_supplier->delivery_time > 0)
							   $erp_filed_value['date_delivery_expected'] = date('Y-m-d', strtotime('+ '.(int)$erp_supplier->delivery_time.' days'));

						// the additional information not existe then the id_erpip_supply_order is 0
						$erp_filed_value['id_erpip_supply_order'] = 0;
					}
				}
			}

						//if we are in update form
			else {

				// set franco amount value
				if (isset($this->object->id_supplier))
				{
					  $supplier = new ErpSupplier($this->object->id_supplier);
					  $franco_amount = $supplier->franco_amount;
				}

				// loads current erp_supplier_order information for this supplier order - if possible
				$erp_supplier_order = null;
				if (isset($obj->id))
				{
					$id_erpip_supply_order = ErpSupplyOrder::getErpSupplierOrderIdBySupplierOrderId($obj->id);
					if ($id_erpip_supply_order > 0)
						$erp_supplier_order = new ErpSupplyOrder( (int)$id_erpip_supply_order);
				}

				// force specific fields values (erp_supplier_order)
				if ($erp_supplier_order != null)
				{
					$erp_filed_value['id_erpip_supply_order'] = $erp_supplier_order->id_erpip_supply_order;
					$erp_filed_value['escompte'] = $erp_supplier_order->escompte;
					$erp_filed_value['shipping_amount'] = $erp_supplier_order->shipping_amount;
					$erp_filed_value['global_discount_amount'] = $erp_supplier_order->global_discount_amount;
					$erp_filed_value['description'] = $erp_supplier_order->description;
				}
			}
			//Set form value
			$this->fields_value = $erp_filed_value;

			$this->context->smarty->assign(array(
					'franco_amount' => $franco_amount,
                                        'shipping_amount' => $erp_filed_value['shipping_amount'],
					'supply_order_total_te' => number_format($this->object->total_te, 2, ',', ' '),
					'amount_to_franco_with_produc_discount' => number_format(((float)$franco_amount - $this->object->total_te), 2, ',', ' '),
					'template_path' => $this->template_path,
					'controller_status' => $this->controller_status,
					'stock_management_active' => $this->advanced_stock_management,
			));

			$this->getFilters();

			if (Tools::isSubmit('export_csv'))
				$this->renderCSV();
                                                
			return parent::renderForm();
		}
	}

		/**
	 * AdminController::getList() override
	 * @see AdminController::getList()
	 */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		if (Tools::isSubmit('csv_orders') || Tools::isSubmit('csv_orders_details') || Tools::isSubmit('csv_order_details') || Tools::isSubmit('export_history'))
			$limit = false;

		// defines button specific for non-template supply orders
		if (!$this->is_template_list)
		{
			// adds export csv buttons
			$this->toolbar_btn['export-csv-orders'] = array(
					'short' => 'Export Orders',
					'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&csv_orders&id_warehouse='.$this->getCurrentWarehouse().'&id_supplier='.$this->getCurrentSupplier(),
					'desc' => $this->l('Export Orders (CSV)'),
					'class' => 'process-icon-export'
			);

			$this->toolbar_btn['export-csv-details'] = array(
					'short' => 'Export Orders Details',
					'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&csv_orders_details&id_warehouse='.$this->getCurrentWarehouse().'&id_supplier='.$this->getCurrentSupplier(),
					'desc' => $this->l('Export Orders Details (CSV)'),
					'class' => 'process-icon-export'
			);

			unset($this->toolbar_btn['new']);
			if ($this->tabAccess['add'] === '1')
			{
                            if($this->controller_status == STATUS1 && $this->nbcmdfou >= 1)
                            {
                                $text = addslashes($this->l('Only one order is allowed in FREE version. Switch to superior version to kick off the limit.'));        
                                $this->toolbar_btn['new'] = array(
                                                        'js' => 'cancelBubble(event, \''.$text.'\');',
							'href' => '#',
							'desc' => $this->l('Add New')
					);
                            }
                            else
                            {
                                $this->toolbar_btn['new'] = array(
							'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
							'desc' => $this->l('Add New')
					);
                            }
					
			}
		}
                

		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

		// adds colors depending on the receipt state
                
		if ($order_by == 'quantity_expected')
		{
			$nb_items = count($this->_list);
			for ($i = 0; $i < $nb_items; ++$i)
			{
                            $item = &$this->_list[$i];
                            if ((int)$item['quantity_received'] == (int)$item['quantity_expected'])
                            {
                                    $item['class'] = 'received_equal_expected';
                                    $item['color'] = '#00bb35';
                            }
                            else if ( (int)$item['quantity_received'] > (int)$item['quantity_expected'])
                            {
                                    $item['class'] = 'received_sup_expected';
                                    $item['color'] = '#fb0008';
                            }
			}
		}

		// actions filters on supply orders list
		if ($this->table == 'supply_order')
		{
			$nb_items = count($this->_list);

			for ($i = 0; $i < $nb_items; $i++)
			{
				// if the current state doesn't allow order edit, skip the edit action
				if ($this->_list[$i]['editable'] == 0)
					$this->addRowActionSkipList('edit', $this->_list[$i]['id_supply_order']);
				if ($this->_list[$i]['enclosed'] == 1 && $this->_list[$i]['receipt_state'] == 0)
					$this->addRowActionSkipList('changestate', $this->_list[$i]['id_supply_order']);
				if (1 != $this->_list[$i]['pending_receipt'])
					$this->addRowActionSkipList('updatereceipt', $this->_list[$i]['id_supply_order']);
			}
		}

                        // only on home screen
			if ($this->display == 'update_receipt')
			{
                                // Send the number of products to the template to hide or show the div-popup
				$nb_items = count($this->_list);
				$this->tpl_list_vars['nb_items'] = $nb_items;

				$ids = array();
				for ($i = 0; $i < $nb_items; ++$i)
				{
					$item = &$this->_list[$i];
					$id = $item['id_product'].'_'.$item['id_product_attribute'];
					array_push($ids, $id);
				}

				$ids = implode('|', $ids);

				// assigns var
				$this->context->smarty->assign(array(
						'ids' => $ids,
				));
			}
	}

	/**
	 * AdminController::renderList() override
	 * @see AdminController::renderList()
	 */
	public function renderList()
	{$this->toolbar_title = $this->l('Supply orders');
            

		$this->displayInformation($this->l('This interface allows you to manage supply orders.').'<br />');
		$this->displayInformation($this->l('You can create templates to generate actual orders.').'<br />');

		if (count($this->warehouses) <= 1)
			$this->displayWarning($this->l('You must choose at least one warehouse before creating supply orders. For more information, see Stock/Warehouses.'));

		// assigns warehouses
		$this->tpl_list_vars['warehouses'] = $this->warehouses;
		$this->tpl_list_vars['current_warehouse'] = $this->getCurrentWarehouse();
		$this->tpl_list_vars['filter_status'] = $this->getFilterStatus();

                // assigns supplier
                $this->tpl_list_vars['suppliers'] = Supplier::getSuppliers();

                $this->tpl_list_vars['current_supplier'] = $this->getCurrentSupplier();

		// overrides query
		$this->_select = '
			s.name AS supplier,
			w.name AS warehouse,
			stl.name AS state,
			st.delivery_note,
			st.editable,
			st.enclosed,
			st.receipt_state,
			st.pending_receipt,
			st.color AS color,
			a.id_supply_order as id_export,
						IF (eso.invoice_number, eso.invoice_number, NULL) as invoice_number,
			IF (eso.date_to_invoice, eso.date_to_invoice, NULL) as date_to_invoice,
						( CASE WHEN description IS NULL OR TRIM(description) = \'\' THEN 0 ELSE 1 END) as with_description';

		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'supply_order_state_lang` stl ON
								(
										a.id_supply_order_state = stl.id_supply_order_state
										AND stl.id_lang = '.(int)$this->context->language->id.'
								)
								LEFT JOIN `'._DB_PREFIX_.'supply_order_state` st ON a.id_supply_order_state = st.id_supply_order_state
								LEFT JOIN `'._DB_PREFIX_.'supplier` s ON a.id_supplier = s.id_supplier
								LEFT JOIN `'._DB_PREFIX_.'warehouse` w ON (w.id_warehouse = a.id_warehouse)
								LEFT JOIN `'._DB_PREFIX_.'erpip_supply_order` eso ON (eso.id_supply_order = a.id_supply_order)';

		$this->_where = ' AND a.is_template = 0';

		if ($this->getCurrentWarehouse() != -1)
		{
			$this->_where .= ' AND a.id_warehouse = '.$this->getCurrentWarehouse();
			self::$currentIndex .= '&id_warehouse='.(int)$this->getCurrentWarehouse();
		}

				if ($this->getCurrentSupplier() != -1)
		{
			$this->_where .= ' AND s.id_supplier = '.$this->getCurrentSupplier();
			self::$currentIndex .= '&id_supplier='.(int)$this->getCurrentSupplier();
		}

		if ($this->getFilterStatus() != 0)
		{
			$this->_where .= ' AND st.enclosed != 1';
			self::$currentIndex .= '&filter_status=on';
		}

		$this->_group = 'GROUP BY id_supply_order';
		$this->_orderBy = 'id_supply_order';
		$this->_orderWay = 'DESC';


		$this->list_id = 'orders';

				$this->tpl_list_vars['is_template_list'] = false;

		$first_list = parent::renderList();

		if (Tools::isSubmit('csv_orders') || Tools::isSubmit('csv_orders_details') || Tools::isSubmit('csv_order_details') || Tools::isSubmit('export_history'))
		{
			if (count($this->_list) > 0)
			{
                            if($this->controller_status == STATUS1)
                                $first_list = array_splice($first_list,0,ERP_STCKMGTFR);
                            
                            $this->renderCSV();
                            die;
			}
			else
				$this->displayWarning($this->l('There is nothing to export as a CSV.'));
		}

		// second list : templates
		$second_list = null;
		$this->is_template_list = true;
				$this->tpl_list_vars['is_template_list'] = true;
		unset($this->tpl_list_vars['warehouses']);
		unset($this->tpl_list_vars['current_warehouse']);
		unset($this->tpl_list_vars['filter_status']);

		// unsets actions
		$this->actions = array();
		unset($this->toolbar_btn['export-csv-orders']);
		unset($this->toolbar_btn['export-csv-details']);
		// adds actions
		$this->addRowAction('view');
		$this->addRowAction('edit');
		$this->addRowAction('createsupplyorder');
		$this->addRowAction('delete');
		// unsets some fields
		unset($this->fields_list['state'],
			  $this->fields_list['date_upd'],
			  $this->fields_list['id_pdf'],
			  $this->fields_list['date_delivery_expected'],
			  $this->fields_list['id_export']);

		// $this->fields_list['date_add']['align'] = 'left';

		// adds filter, to gets only templates
		unset($this->_where);
		$this->_where = ' AND a.is_template = 1';
		if ($this->getCurrentWarehouse() != -1)
			$this->_where .= ' AND a.id_warehouse = '.$this->getCurrentWarehouse();

		// re-defines toolbar & buttons
		$this->toolbar_title = $this->l('Supply order templates');
		$this->initToolbar();
		unset($this->toolbar_btn['new']);
		$this->toolbar_btn['new'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&mod=template&token='.$this->token,
			'desc' => $this->l('Add a new template')
		);

		$this->list_id = 'templates';
		// inits list
		$second_list = parent::renderList();

		return $first_list.$second_list;
	}

	/**
	 * AdminController::postProcess() override
	 * @see AdminController::postProcess()
	 */
	public function postProcess()
	{
		$this->is_editing_order = false;

		// Checks access
		if (Tools::isSubmit('submitAddsupply_order') && !($this->tabAccess['add'] === '1'))
			$this->errors[] = Tools::displayError($this->l('You do not have permission to add a supply order.'));
		if (Tools::isSubmit('submitBulkUpdatesupply_order_detail') && !($this->tabAccess['edit'] === '1'))
			$this->errors[] = Tools::displayError($this->l('You do not have permission to edit an order.')); 

		// Trick to use both Supply Order as template and actual orders
		if (Tools::isSubmit('is_template'))
			$_GET['mod'] = 'template';

		// checks if supply order reference is unique
		if (Tools::isSubmit('reference'))
		{
			// gets the reference
			$ref = pSQL(Tools::getValue('reference'));

			if (Tools::getValue('id_supply_order') != 0 && SupplyOrder::getReferenceById((int)Tools::getValue('id_supply_order')) != $ref)
			{
				if ((int)SupplyOrder::exists($ref) != 0)
					$this->errors[] = Tools::displayError($this->l('The reference has to be unique.'));
			}
			else if (Tools::getValue('id_supply_order') == 0 && (int)SupplyOrder::exists($ref) != 0)
				$this->errors[] = Tools::displayError($this->l('The reference has to be unique.'));
		}

		if ($this->errors)
			return;

		// Global checks when add / update a supply order
		if (Tools::isSubmit('submitAddsupply_order') || Tools::isSubmit('submitAddsupply_orderAndStay'))
		{
			$this->action = 'save';
			$this->is_editing_order = true;

			// get supplier ID
			$id_supplier = (int)Tools::getValue('id_supplier', 0);
			if ($id_supplier <= 0 || !Supplier::supplierExists($id_supplier))
				$this->errors[] = Tools::displayError($this->l('The selected supplier is not valid.'));

			// get warehouse id
			$id_warehouse = (int)Tools::getValue('id_warehouse', 0);
			if ($id_warehouse <= 0 || !Warehouse::exists($id_warehouse))
				$this->errors[] = Tools::displayError($this->l('The selected warehouse is not valid.'));

			// get currency id
			$id_currency = (int)Tools::getValue('id_currency', 0);
			if ($id_currency <= 0 || ( !($result = Currency::getCurrency($id_currency)) || empty($result)))
				$this->errors[] = Tools::displayError($this->l('The selected currency is not valid.'));
			// get delivery date
			$delivery_expected = new DateTime(pSQL(Tools::getValue('date_delivery_expected')));
			// converts date to timestamp
			if ($delivery_expected <= (new DateTime('yesterday')))
				$this->errors[] = Tools::displayError($this->l('The date you specified cannot be in the past.'));

			// gets threshold
			$quantity_threshold = Tools::getValue('load_products');

			if (is_numeric($quantity_threshold))
				$quantity_threshold = (int)$quantity_threshold;
			else
				$quantity_threshold = null;

			if (!count($this->errors))
			{
				// forces date for templates
				if (Tools::isSubmit('is_template') && !Tools::getValue('date_delivery_expected'))
					$_POST['date_delivery_expected'] = date('Y-m-d h:i:s');

				// specify initial state
				$_POST['id_supply_order_state'] = 1; //defaut creation state

				// specify global reference currency
				$_POST['id_ref_currency'] = Currency::getDefaultCurrency()->id;

				// specify supplier name
				$_POST['supplier_name'] = Supplier::getNameById($id_supplier);

				//specific discount check
				$_POST['discount_rate'] = (float)str_replace(array(' ', ','), array('', '.'), Tools::getValue('discount_rate', 0));
			}

			// manage each associated product
			$this->manageOrderProducts();

			// if the threshold is defined and we are saving the order
			if (Tools::isSubmit('submitAddsupply_order') && Validate::isInt($quantity_threshold))
				$this->loadProducts((int)$quantity_threshold);

			//--ERP informations

			// updates/creates erp_supplier_order if it does not exist
			if (Tools::isSubmit('id_erpip_supply_order') && (int)Tools::getValue('id_erpip_supply_order') > 0)
				$erp_supplier_order = new ErpSupplyOrder((int)Tools::getValue('id_erpip_supply_order')); // updates erp_supplier_order
			else
				$erp_supplier_order = new ErpSupplyOrder(); // creates erp_supplier_order

			$erp_supplier_order->escompte = Tools::getValue('escompte', null);
			$erp_supplier_order->global_discount_amount = Tools::getValue('global_discount_type', null);
			$erp_supplier_order->global_discount_type = Tools::getValue('global_discount_type', null);
			$erp_supplier_order->shipping_amount = Tools::getValue('shipping_amount', null);
			$erp_supplier_order->description = Tools::getValue('description', null);

			$validation = $erp_supplier_order->validateController();

			// checks erp_supplier_order validity
			if (count($validation) > 0)
			{
				foreach ($validation as $item)
					$this->errors[] = $item;
				$this->errors[] = Tools::displayError('The ErpIllicopresta Supplier Order is not correct. Please make sure all of the required fields are completed.');
			}
			else
			{
				if (Tools::isSubmit('id_erpip_supply_order') && Tools::getValue('id_erpip_supply_order') > 0)
					$erp_supplier_order->update();
				else
				{
					$erp_supplier_order->save();
					$_POST['id_erpip_supply_order'] = $erp_supplier_order->id;
				}
			}

		}

		// Manage state change
		if (Tools::isSubmit('submitChangestate')
			&& Tools::isSubmit('id_supply_order')
			&& Tools::isSubmit('id_supply_order_state'))
		{
			if ($this->tabAccess['edit'] != '1')
				$this->errors[] = Tools::displayError($this->l('You do not have permission to change the order status.'));

			// get state ID
			$id_state = (int)Tools::getValue('id_supply_order_state', 0);
			if ($id_state <= 0)
				$this->errors[] = Tools::displayError($this->l('The selected supply order status is not valid.'));

			// get supply order ID
			$id_supply_order = (int)Tools::getValue('id_supply_order', 0);
			if ($id_supply_order <= 0)
				$this->errors[] = Tools::displayError($this->l('The supply order ID is not valid.'));

			if (!count($this->errors))
			{
				// try to load supply order
				$supply_order = new SupplyOrder($id_supply_order);

				if (Validate::isLoadedObject($supply_order))
				{
					// get valid available possible states for this order
					$states = SupplyOrderState::getSupplyOrderStates($supply_order->id_supply_order_state);

					foreach ($states as $state)
					{
						// if state is valid, change it in the order
						if ($id_state == $state['id_supply_order_state'])
						{

							$new_state = new SupplyOrderState($id_state);
							$old_state = new SupplyOrderState($supply_order->id_supply_order_state);

							// special case of validate state - check if there are products in the order and the required state is not an enclosed state
							if ($supply_order->isEditable() && !$supply_order->hasEntries() && !$new_state->enclosed)
								$this->errors[] = Tools::displayError(
									$this->l('It is not possible to change the status of this order because you did not order any product.')
								);

							if (!count($this->errors))
							{
															// send mail to supplier with supply order
															if ($this->sendMailOnValidateSupplyOrder($supply_order))
															{
								$supply_order->id_supply_order_state = $state['id_supply_order_state'];
								if ($supply_order->save())
								{

																		//-ERP information
																		// save erp_supply_order additionale information
																		// loads current erp_supplier` informationfor this supplier - if possible
																		$erp_supply_order = null;
																		if (isset($supply_order->id))
																		{
																			$id_erpip_supply_order = ErpSupplyOrder::getErpSupplierOrderIdBySupplierOrderId((int)$supply_order->id);
																			if ($id_erpip_supply_order > 0)
																					$erp_supply_order = new ErpSupplyOrder( (int)$id_erpip_supply_order);
																			else
																					$erp_supply_order = new ErpSupplyOrder();
																		}

																		if ($erp_supply_order != null)
																		{
																			if (Tools::isSubmit('date_to_invoice'))
																				$erp_supply_order->date_to_invoice = Tools::getValue('date_to_invoice', '000-00-00');

																			 if (Tools::isSubmit('invoice_number'))
																				$erp_supply_order->invoice_number = Tools::getValue('invoice_number', '');

																			 $erp_supply_order->id_supply_order = $supply_order->id;
																			 $erp_supply_order->save();
																		}

									// if pending_receipt,
									// or if the order is being canceled,
									// synchronizes StockAvailable
									if (($new_state->pending_receipt && !$new_state->receipt_state) ||
										($old_state->receipt_state && $new_state->enclosed && !$new_state->receipt_state))
									{
																			$supply_order_details = $supply_order->getEntries();
																			$products_done = array();
																			foreach ($supply_order_details as $supply_order_detail)
																			{
																				if (!in_array($supply_order_detail['id_product'], $products_done))
																				{
																						StockAvailable::synchronize($supply_order_detail['id_product']);
																						$products_done[] = $supply_order_detail['id_product'];
																				}
																			}
									}

									$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
									$redirect = self::$currentIndex.'&token='.$token;
									$this->redirect_after = $redirect.'&conf=5';
								}
															}
							}
						}
					}
				}
				else
					$this->errors[] = Tools::displayError($this->l('The selected supplier is not valid.'));
			}
		}

				// get id_supplier
				$id_supply_order = (int)Tools::getValue('id_supply_order', null);

				$supply_order = new SupplyOrder($id_supply_order);
				$erp_supplier_order = new ErpSupplyOrder($id_supply_order);
                                
				$this->context->smarty->assign(array(
                                                'supplier_id' => $supply_order->id_supplier,
						'currency' => new CurrencyCore((int)$supply_order->id_currency),
						'random' => rand(99999,150000),
						'template_path' => $this->template_path,
						'controller_status' => $this->controller_status,
						'supply_order_description' => $erp_supplier_order->description,
                                ));

				$this->getFilters();

				//-ErpIllicopresta
				// Fixed bug : different variable available according Prestashop version
				//      1.5.4.1 => submitFiltersupply_order_detail
				//      1.5.5.0 => submitFiltersupply_order
				// Add an OR to take into account this two version

		// updates receipt
		if (Tools::isSubmit('submitBulkUpdatesupply_order_detail') && Tools::isSubmit('id_supply_order') && ( Tools::isSubmit('submitFiltersupply_order') || Tools::isSubmit('submitFiltersupply_order_detail')))
					$this->postProcessUpdateReceipt();

		// use template to create a supply order
		if (Tools::isSubmit('create_supply_order') && Tools::isSubmit('id_supply_order'))
			$this->postProcessCopyFromTemplate();

				 // Export PDF of supply order
		if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'generateSupplyOrderFormPDF')
			$this->processGenerateSupplyOrderFormPDF();

				// Export PDF of receiving slip
		if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'generateSupplyReceivingSlipFormPDF')
			$this->processGenerateSupplyReceivingSlipFormPDF();

		if ((!count($this->errors) && $this->is_editing_order) || !$this->is_editing_order)
			parent::postProcess();
	}

	/**
	 * Exports CSV
	 */
	protected function renderCSV()
	{
		// exports orders
		if (Tools::isSubmit('csv_orders'))
		{
			// header
			header('Content-type: text/csv; charset=utf-8');
			header('Cache-Control: no-store, no-cache');
			header('Content-disposition: attachment; filename="supply_orders.csv"');


			// write headers column
			$keys = array(
                    'id_supplier',
                    'supplier_name',
                    'id_lang',
                    'id_warehouse',
                    'id_supply_order_state',
                    'id_currency',
                    'reference',
                    'date_add',
                    'date_upd',
                    'date_delivery_expected',
                    'total_te',
                    'total_with_discount_te',
                    'total_ti',
                    'total_tax',
                    'discount_rate',
                    'discount_value_te',
                    'is_template',
                    'escompte',
                    'invoice_number',
                    'date_to_invoice',
                    'global_discount_amount',
                    'global_discount_type',
                    'shipping_amount',
                    'description'
            );

			echo sprintf("%s\n", implode(';', $keys));


			$query = null;
			$query = new DbQuery();
			$query->select(
						'so.*, ipso.*');

			$query->from('supply_order', 'so');
			$query->leftjoin('erpip_supply_order', 'ipso', 'ipso.id_supply_order = so.id_supply_order');
                        if($this->controller_status == STATUS1)
                            $query->limit(ERP_STCKMGTFR);
                        
			// FILTERS SUPPLIER & WAREHOUSE
			$id_warehouse = $this->getCurrentWarehouse();
			if ($id_warehouse != -1)
				$query->where('so.id_warehouse = '.(int)$id_warehouse);

			$id_supplier = $this->getCurrentSupplier();
			if ($id_supplier != -1)
				$query->where('so.id_supplier = '.(int)$id_supplier);
			
			// Execute query
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);


			// write datas
			foreach ($res as $order)
			{
                $content_csv = array( 
                    $order['id_supplier'],
                    self::transformText($order['supplier_name']),
                    $order['id_lang'],
                    $order['id_warehouse'],
                    $order['id_supply_order_state'],
                    $order['id_currency'],
                    $order['reference'],
                    $order['date_add'],
                    $order['date_upd'],
                    $order['date_delivery_expected'],
                    $order['total_te'],
                    $order['total_with_discount_te'],
                    $order['total_ti'],
                    $order['total_tax'],
                    $order['discount_rate'],
                    $order['discount_value_te'],
                    $order['is_template'],
                    $order['escompte'],
                    $order['invoice_number'],
                    ($order['date_to_invoice'] == '0000-00-00') ? '' : $order['date_to_invoice'],
                    $order['global_discount_amount'],
                    $order['global_discount_type'],
                    $order['shipping_amount'],
                    self::transformText($order['description']),
                    PHP_EOL
                );
                 echo implode(';', $content_csv);
			}
                        if($this->controller_status == STATUS1)
                            echo sprintf($this->l('Your are using a free version of 1-Click ERP which limits the export to %d lines.'),ERP_STCKMGTFR);
			die();

		}
		// exports details for all orders
		else if (Tools::isSubmit('csv_orders_details'))
		{
			// header
			header('Content-type: text/csv');
			header('Content-Type: application/force-download; charset=UTF-8');
			header('Cache-Control: no-store, no-cache');
			header('Content-disposition: attachment; filename="'.$this->l('supply_orders_details').'.csv"');

			// echoes details
			$ids = array();
			foreach ($this->_list as $entry)
				$ids[] = $entry['id_supply_order'];
                        
                        if($this->controller_status == STATUS1)
                            $ids = array_splice($ids,0,ERP_STCKMGTFR);
                        
			if (count($ids) <= 0)
				return;

			// for each supply order
			$keys = array('id_product', 'id_product_attribute', 'reference', 'supplier_reference', 'ean13', 'upc', 'name',
						  'unit_price_te', 'quantity_expected', 'quantity_received', 'price_te', 'discount_rate', 'discount_value_te',
						  'price_with_discount_te', 'tax_rate', 'tax_value', 'price_ti', 'tax_value_with_order_discount',
						  'price_with_order_discount_te', 'id_supply_order', 'comment');
                        
			echo sprintf("%s\n", implode(';', array_map(array('CSVCore', 'wrap'), $keys)));

			// overrides keys (in order to add FORMAT calls)
			$keys = array('sod.id_product', 'sod.id_product_attribute', 'sod.reference', 'sod.supplier_reference', 'sod.ean13',
						  'sod.upc', 'sod.name',
						  'FORMAT(sod.unit_price_te, 2)', 'sod.quantity_expected', 'sod.quantity_received', 'FORMAT(sod.price_te, 2)',
						  'FORMAT(sod.discount_rate, 2)', 'FORMAT(sod.discount_value_te, 2)',
						  'FORMAT(sod.price_with_discount_te, 2)', 'FORMAT(sod.tax_rate, 2)', 'FORMAT(sod.tax_value, 2)',
						  'FORMAT(sod.price_ti, 2)', 'FORMAT(sod.tax_value_with_order_discount, 2)',
						  'FORMAT(sod.price_with_order_discount_te, 2)', 'sod.id_supply_order', 'ipsod.comment');
			foreach ($ids as $id)
			{
				$query = new DbQuery();
				$query->select(implode(', ', $keys));
				$query->from('supply_order_detail', 'sod');
				$query->leftJoin('supply_order', 'so', 'so.id_supply_order = sod.id_supply_order');
				$query->leftJoin('erpip_supply_order_detail', 'ipsod', 'ipsod.id_supply_order_detail = sod.id_supply_order_detail');
				
				// FILTERS SUPPLIER & WAREHOUSE
				$id_warehouse = $this->getCurrentWarehouse();
				if ($id_warehouse != -1)
					$query->where('so.id_warehouse = '.(int)$id_warehouse);

				$id_supplier = $this->getCurrentSupplier();
				if ($id_supplier != -1)
					$query->where('so.id_supplier = '.(int)$id_supplier);


				$query->where('sod.id_supply_order = '.(int)$id);
				$query->orderBy('sod.id_supply_order_detail DESC');
				$resource = Db::getInstance()->query($query);
				// gets details
				while ($row = Db::getInstance()->nextRow($resource))
                                {
                                    $row = array_map(array('CSVCore', 'wrap'), $row);
                                    $row['name'] = self::transformText($row['name']);
                                    $row['reference'] = self::transformText($row['reference']);
                                    $row['supplier_reference'] = self::transformText($row['supplier_reference']);
                                    echo sprintf("%s\n", implode(';', $row));
                                }
			}
                        if($this->controller_status == STATUS1)
                            echo sprintf($this->l('Your are using a free version of 1-Click ERP which limits the export to %d lines.'),ERP_STCKMGTFR);

		}
		// exports details for the given order
		else if (Tools::isSubmit('csv_order_details') && Tools::getValue('id_supply_order'))
		{
			$supply_order = new SupplyOrder((int)Tools::getValue('id_supply_order'));
			if (Validate::isLoadedObject($supply_order))
			{
				$details = $supply_order->getEntriesCollection();
				$details->getAll();
				$csv = new CSV($details, $this->l('supply_order').'_'.$supply_order->reference.'_details');
				$csv->export();
			}
		}
		else if (Tools::isSubmit('export_csv'))
		{
			// get id lang
			$id_lang = Context::getContext()->language->id;

			// header
			header('Content-type: text/csv');
			header('Cache-Control: no-store, no-cache');
			header('Content-disposition: attachment; filename="Supply order detail.csv"');

			// puts hearder of CSV
			$keys = array('supplier_reference', 'quantity_expected');
			echo sprintf("%s\n", implode(';', $keys));

			// gets global order information
			$supply_order = new SupplyOrder((int)  Tools::getValue( 'id_supply_order'));

			// get supply order detail
			$supply_order_detail = $supply_order->getEntries($id_lang);

			// puts data
			foreach ($supply_order_detail as $product)
			{
					$row_csv = array($product['supplier_reference'], $product['quantity_expected']);

					// puts one row
					echo sprintf("%s\n", implode(';', array_map(array('CSVCore', 'wrap'), $row_csv)));
			}
                        if($this->controller_status == STATUS1)
                            echo sprintf($this->l('Your are using a free version of 1-Click ERP which limits the export to %d lines.'),ERP_STCKMGTFR);

			die();
		}
		else if (Tools::isSubmit('export_history'))
		{
			// header
			header('Content-type: text/csv; charset=utf-8');
			header('Cache-Control: no-store, no-cache');
			header('Content-disposition: attachment; filename="supply_orders_history.csv"');


			// write headers column
			$keys = array(
                    'id_supply_order_history',
                    'id_supply_order',
                    'id_employee',
                    'employee_lastname',
                    'employee_firstname',
                    'id_state',
                    'state',
                    'unit_price',
                    'discount_rate',
                    'is_canceled'
            );

			echo sprintf("%s\n", implode(';', $keys));


			$query = null;
			$query = new DbQuery();
			$query->select(
						'sorh.*, ipsorh.*, "state" as state');

			$query->from('supply_order_receipt_history', 'sorh');
			$query->leftjoin('erpip_supply_order_receipt_history', 'ipsorh', 'ipsorh.id_supply_order_receipt_history = sorh.id_supply_order_receipt_history');
			$query->leftjoin('supply_order_detail', 'sod', 'sod.id_supply_order_detail = sorh.id_supply_order_detail');
			$query->where('sod.id_supply_order = '.(int)Tools::getValue('id_supply_order'));
			
                        if($this->controller_status == STATUS1)
                            $query->limit(ERP_STCKMGTFR);
			
			// Execute query
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

			// write datas
			foreach ($res as $history)
			{
                $content_csv = array( 
                    $history['id_supply_order_history'],
                    $history['id_supply_order'],
                    $history['id_employee'],
                    $history['employee_lastname'],
                    $history['employee_firstname'],
                    $history['id_state'],
                    $history['state'],
                    $history['unit_price'],
                    $history['discount_rate'],
                    $history['is_canceled'],
                    PHP_EOL
                );
                 echo implode(';', $content_csv);
			}
                        if($this->controller_status == STATUS1)
                            echo sprintf($this->l('Your are using a free version of 1-Click ERP which limits the export to %d lines.'),ERP_STCKMGTFR);
			die();
		}
	}

	/**
	 * Helper function for AdminAdvancedSupplyOrderController::postProcess()
	 *
	 * @see AdminAdvancedSupplyOrderController::postProcess()
	 */
	protected function postProcessUpdateReceipt()
	{
		// gets all box selected
		$rows = Tools::getValue('supply_order_detailBox');
		if (!$rows)
		{
			$this->errors[] = Tools::displayError($this->l('You did not select any product to update.'));
			return;
		}

		// final array with id_supply_order_detail and value to update
		$to_update = array();
				$comment = array();

		// gets quantity for each id_order_detail
				//--ERP information add new product
		foreach ($rows as $row)
		{
                                        // If _new, it mean new product, then we explode to get the number
					if (strpos($row, "_") !== false)
					{
						$row = explode('_', $row);
						$row = $row[1];

						// Commande courante
						$supply_order = new SupplyOrder((int)Tools::getValue('id_supply_order'));

						// Currency courant
						$currency = new Currency($supply_order->id_ref_currency);

						// Objet supply order detail
						$supply_order_detail = new SupplyOrderDetail();
						$supply_order_detail->id = 0;
						$supply_order_detail->reference = Tools::getValue("input_reference_$row");
						$supply_order_detail->name = Tools::getValue("input_name_displayed_$row");
						$supply_order_detail->ean13 = Tools::getValue("input_ean13_$row");
						$supply_order_detail->upc = Tools::getValue("input_upc_$row");
						$supply_order_detail->unit_price_te = Tools::getValue("input_unit_price_te_$row");
						$supply_order_detail->discount_rate = Tools::getValue("input_discount_rate_$row");
						$supply_order_detail->tax_rate = Tools::getValue("input_tax_rate_$row");
						$supply_order_detail->quantity_expected = 0;
						$supply_order_detail->exchange_rate = $currency->conversion_rate;
						$supply_order_detail->id_currency = $currency->id;
						$supply_order_detail->id_supply_order = $supply_order->id;
						$supply_order_detail->supplier_reference = (Tools::getValue("input_supplier_reference_$row") == null) ? '' :
						Tools::getValue("input_supplier_reference_$row");
						$supply_order_detail->name_displayed = Tools::getValue('input_name_displayed_'.$row);
                                                


						$ids = Tools::getValue("input_id_product_$row");

						// If declension we explode
						if (strrpos($ids, "_"))
						{
							$ids = explode('_', $ids);
							$supply_order_detail->id_product = $ids[0];
							$supply_order_detail->id_product_attribute = $ids[1];
						}
						// else id decl = 0
						else
						{
							$supply_order_detail->id_product = $ids;
							$supply_order_detail->id_product_attribute = 0;
						}

						// Name
						$supply_order_detail->name = Product::getProductName($supply_order_detail->id_product,
						$supply_order_detail->id_product_attribute, $supply_order->id_lang);

						$errors = $supply_order_detail->validateController();

						// if there is a problem, handle error for the current product
						// error > 1 only because quantity_expected is always in error
						// Then errors are displayed if there is more than 1 error
						if (count($errors) > 1)
						{
								// add the product to error array => display again product line
								$this->order_products_errors[] = array(
										'id_product' =>	$supply_order_detail->id_product,
										'id_product_attribute' => $supply_order_detail->id_product_attribute,
										'unit_price_te' =>	$supply_order_detail->unit_price_te,
										'quantity_expected' => $supply_order_detail->quantity_expected,
										'discount_rate' =>	$supply_order_detail->discount_rate,
										'tax_rate' => $supply_order_detail->tax_rate,
										'name' => $supply_order_detail->name,
										'name_displayed' => $supply_order_detail->name_displayed,
										'reference' => $supply_order_detail->reference,
										'supplier_reference' => $supply_order_detail->supplier_reference,
										'ean13' => $supply_order_detail->ean13,
										'upc' => $supply_order_detail->upc,
								);

								$error_str = '<ul>';
								foreach ($errors as $e)
										$error_str .= '<li>'.$this->l('field').' '.$e.'</li>';
								$error_str .= '</ul>';

								$this->errors[] = Tools::displayError($this->l('Please check the product information:').$supply_order_detail->name.' '.$error_str);
						}
						else
						{
							$supply_order_detail->save();
							$to_update[Db::getInstance()->Insert_ID()] = (int)Tools::getValue('quantity_received_today_'.$row);
							$comment[Db::getInstance()->Insert_ID()] = Tools::getValue('input_comment_'.$row);

							//--ERP information
							// creates erp_supplier_order_detail for new product
							if ((int)$supply_order_detail->id > 0)
							{
								$erp_supply_order_detail = new ErpSupplyOrderDetail();
								$erp_supply_order_detail->id_supply_order_detail = (int)$supply_order_detail->id;
								$erp_supply_order_detail->comment = Tools::getValue('input_comment_'.$row, '');
								$validation_esod = $erp_supply_order_detail->validateController();

								// checks erp_supplier_order_detail validity
								if (count($validation_esod) > 0)
								{
									foreach ($validation_esod as $item)
											$this->errors[] = $item;
									$this->errors[] = Tools::displayError('The ErpIllicopresta Supplier Order Detail is not correct. Please make sure all of the required fields are completed.');
								}
								else
									$erp_supply_order_detail->save();
							}
						}
					}
					else {
						if (Tools::isSubmit('quantity_received_today_'.$row))
                                                {
                                                    if (Tools::isSubmit('input_comment_'.$row)){
                                                        $comment[Db::getInstance()->Insert_ID()] = Tools::getValue('input_comment_'.$row);
                                                    }
                                                    $to_update[$row] = (int)Tools::getValue('quantity_received_today_'.$row);
                                                }
					}
		}

		// checks if there is something to update
		if (!count($to_update))
		{
			$this->errors[] = Tools::displayError($this->l('You did not select any product to update.'));
			return;
		}

		foreach ($to_update as $id_supply_order_detail => $quantity)
		{
			$supply_order_detail = new SupplyOrderDetail($id_supply_order_detail);
			$supply_order = new SupplyOrder((int)Tools::getValue('id_supply_order'));

			if (Validate::isLoadedObject($supply_order_detail) && Validate::isLoadedObject($supply_order))
			{
				// checks if quantity is valid
				// It's possible to receive more quantity than expected in case of a shipping error from the supplier
				if (!Validate::isInt($quantity) || $quantity <= 0)
					$this->errors[] = sprintf(Tools::displayError($this->l('Quantity (%d) for product #%d is not valid')), (int)$quantity, (int)$id_supply_order_detail);
				else // everything is valid :  updates
				{

					// creates the history
					$supplier_receipt_history = new SupplyOrderReceiptHistory();
					$supplier_receipt_history->id_supply_order_detail = (int)$id_supply_order_detail;
					$supplier_receipt_history->id_employee = (int)$this->context->employee->id;
					$supplier_receipt_history->employee_firstname = pSQL($this->context->employee->firstname);
					$supplier_receipt_history->employee_lastname = pSQL($this->context->employee->lastname);
					$supplier_receipt_history->id_supply_order_state = (int)$supply_order->id_supply_order_state;
					$supplier_receipt_history->quantity = (int)$quantity;

					// updates quantity received
					$supply_order_detail->quantity_received += (int)$quantity;

					// if current state is "Pending receipt", then we sets it to "Order received in part"
					if (3 == $supply_order->id_supply_order_state)
						$supply_order->id_supply_order_state = 4;

					// Adds to stock
					$warehouse = new Warehouse($supply_order->id_warehouse);
					if (!Validate::isLoadedObject($warehouse))
					{
						$this->errors[] = Tools::displayError($this->l('The warehouse could not be loaded.'));
						return;
					}

					$price = $supply_order_detail->unit_price_te;
					// converts the unit price to the warehouse currency if needed
					if ($supply_order->id_currency != $warehouse->id_currency)
					{
						// first, converts the price to the default currency
						$price_converted_to_default_currency = Tools::convertPrice($supply_order_detail->unit_price_te, $supply_order->id_currency, false);

						// then, converts the newly calculated pri-ce from the default currency to the needed currency
						$price = Tools::ps_round(Tools::convertPrice($price_converted_to_default_currency,
																	 $warehouse->id_currency,
																	 true),
												 6);
					}

					$manager = StockManagerFactory::getManager();
					$res = $manager->addProduct($supply_order_detail->id_product,
												$supply_order_detail->id_product_attribute,
												$warehouse,
												(int)$quantity,
												Configuration::get('PS_STOCK_MVT_SUPPLY_ORDER'),
												$price,
												true,
												$supply_order->id);
					if ($res)
						StockAvailable::synchronize($supply_order_detail->id_product);
					else
						$this->errors[] = Tools::displayError($this->l('Error while adding products to the warehouse.'));

					$location = Warehouse::getProductLocation($supply_order_detail->id_product,
															  $supply_order_detail->id_product_attribute,
															  $warehouse->id);

					$res = Warehouse::setProductlocation($supply_order_detail->id_product,
														 $supply_order_detail->id_product_attribute,
														 $warehouse->id,
														 $location ? $location : '');

										//-ERP information
										//
					if ($res)
					{
						$supplier_receipt_history->add();
						$supply_order_detail->save();
						$supply_order->save();
					}
					else
						$this->errors[] = Tools::displayError($this->l('Error while setting warehouse on product record'));
				}
			}
		}

		if (!count($this->errors))
		{
			// display confirm message
			$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
			$redirect = self::$currentIndex.'&token='.$token;
			$this->redirect_after = $redirect.'&conf=4';
		}
	}

	/**
	 * Display state action link
	 * @param string $token the token to add to the link
	 * @param int $id the identifier to add to the link
	 * @return string
	 */
	public function displayUpdateReceiptLink($token = null, $id)
	{
		if (!array_key_exists('Receipt', self::$cache_lang))
			self::$cache_lang['Receipt'] = html_entity_decode ($this->l('Receipt'));

		$this->context->smarty->assign(array(
			'href' => self::$currentIndex.
				'&'.$this->identifier.'='.$id.
				'&update_receipt&token='.($token != null ? $token : $this->token),
			'action' => self::$cache_lang['Receipt'],
		));

		return $this->context->smarty->fetch('helpers/list/list_action_supply_order_receipt.tpl');
	}

	/**
	 * Display receipt action link
	 * @param string $token the token to add to the link
	 * @param int $id the identifier to add to the link
	 * @return string
	 */
	public function displayChangestateLink($token = null, $id)
	{
		if (!array_key_exists('State', self::$cache_lang))
			self::$cache_lang['State'] = html_entity_decode ($this->l('Change state'));

		$this->context->smarty->assign(array(
			'href' => self::$currentIndex.
				'&'.$this->identifier.'='.$id.
				'&changestate&token='.($token != null ? $token : $this->token),
			'action' => self::$cache_lang['State'],
		));

		return $this->context->smarty->fetch('helpers/list/list_action_supply_order_change_state.tpl');
	}

	/**
	 * Display state action link
	 * @param string $token the token to add to the link
	 * @param int $id the identifier to add to the link
	 * @return string
	 */
	public function displayCreateSupplyOrderLink($token = null, $id)
	{
		if (!array_key_exists('CreateSupplyOrder', self::$cache_lang))
			self::$cache_lang['CreateSupplyOrder'] = html_entity_decode ($this->l('Use this template to create a supply order.'));

		if (!array_key_exists('CreateSupplyOrderConfirm', self::$cache_lang))
			self::$cache_lang['CreateSupplyOrderConfirm'] = html_entity_decode ($this->l('Are you sure you want to use this template?'));

		$this->context->smarty->assign(array(
			'href' => self::$currentIndex.
				'&'.$this->identifier.'='.$id.
				'&create_supply_order&token='.($token != null ? $token : $this->token),
			'confirm' => self::$cache_lang['CreateSupplyOrderConfirm'],
			'action' => self::$cache_lang['CreateSupplyOrder'],
                        'controller_status' => $this->controller_status,
		));

		return $this->context->smarty->fetch('helpers/list/list_action_supply_order_create_from_template.tpl');
	}
        
        public function renderDetails()
	{
		// tests if an id is submit
		if (Tools::isSubmit('id_supply_order') && !Tools::isSubmit('display_product_history'))
		{
			// overrides attributes
			$this->identifier = 'id_supply_order_history';
			$this->table = 'supply_order_history';
			$this->lang = false;
			$this->actions = array();
			$this->toolbar_btn = array();
			$this->list_simple_header = true;
			// gets current lang id
			$lang_id = (int)$this->context->language->id;
			// gets supply order id
			$id_supply_order = (int)Tools::getValue('id_supply_order');

			// creates new fields_list
			$this->fields_list = array(
				'history_date' => array(
					'title' => $this->l('Last update'),
					'align' => 'left',
					'type' => 'datetime',
					'havingFilter' => true
				),
				'history_employee' => array(
					'title' => $this->l('Employee'),
					'align' => 'left',
					'havingFilter' => true
				),
				'history_state_name' => array(
					'title' => $this->l('Status'),
					'align' => 'left',
					'color' => 'color',
					'havingFilter' => true
				),
			);
			// loads history of the given order
			unset($this->_select, $this->_join, $this->_where, $this->_orderBy, $this->_orderWay, $this->_group, $this->_filterHaving, $this->_filter);
			$this->_select = '
			a.`date_add` as history_date,
			CONCAT(a.`employee_lastname`, \' \', a.`employee_firstname`) as history_employee,
			sosl.`name` as history_state_name,
			sos.`color` as color';

			$this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'supply_order_state` sos ON (a.`id_state` = sos.`id_supply_order_state`)
			LEFT JOIN `'._DB_PREFIX_.'supply_order_state_lang` sosl ON
			(
				a.`id_state` = sosl.`id_supply_order_state`
				AND sosl.`id_lang` = '.(int)$lang_id.'
			)';

			$this->_where = 'AND a.`id_supply_order` = '.(int)$id_supply_order;
			$this->_orderBy = 'a.date_add';
			$this->_orderWay = 'DESC';
                        

			return parent::renderList();
		}
		else if (Tools::isSubmit('id_supply_order') && Tools::isSubmit('display_product_history'))
		{
			$this->identifier = 'id_supply_order_receipt_history';
			$this->table = 'supply_order_receipt_history';
			$this->actions = array();
			$this->toolbar_btn = array();
			$this->list_simple_header = true;
			$this->lang = false;
			$lang_id = (int)$this->context->language->id;
			$id_supply_order_detail = (int)Tools::getValue('id_supply_order');

			unset($this->fields_list);
			$this->fields_list = array(
				'date_add' => array(
					'title' => $this->l('Last update'),
					'align' => 'left',
					'type' => 'datetime',
					'havingFilter' => true
				),
				'employee' => array(
					'title' => $this->l('Employee'),
					'align' => 'left',
					'havingFilter' => true
				),
				'quantity' => array(
					'title' => $this->l('Quantity received'),
					'align' => 'left',
					'havingFilter' => true
				),
			);

			// loads history of the given order
			unset($this->_select, $this->_join, $this->_where, $this->_orderBy, $this->_orderWay, $this->_group, $this->_filterHaving, $this->_filter);
			$this->_select = 'CONCAT(a.`employee_lastname`, \' \', a.`employee_firstname`) as employee';
			$this->_where = 'AND a.`id_supply_order_detail` = '.(int)$id_supply_order_detail;
			$this->_orderBy = 'a.date_add';
			$this->_orderWay = 'DESC';

			return parent::renderList();
		}
	}

	/**
	 * method call when ajax request is made with the details row action
	 * @see AdminController::postProcess()
	 */
	public function ajaxProcess()
	{
		// tests if an id is submit
		if (Tools::isSubmit('id') && !Tools::isSubmit('display_product_history'))
		{
			// overrides attributes
			$this->identifier = 'id_supply_order_history';
			$this->table = 'supply_order_history';

			$this->display = 'list';
			$this->lang = false;
                        $this->actions = array();
			$this->toolbar_btn = array();
			$this->list_simple_header = true;
                        $this->list_footer = true;
			// gets current lang id
			$lang_id = (int)$this->context->language->id;
			// gets supply order id
			$id_supply_order = (int)Tools::getValue('id');

			// creates new fields_list
			unset($this->fields_list);
			$this->fields_list = array(
				'history_date' => array(
					'title' => $this->l('Last update'),
					'width' => 50,
					'align' => 'left',
					'type' => 'datetime',
					'havingFilter' => true
				),
				'history_employee' => array(
					'title' => $this->l('Employee'),
					'width' => 100,
					'align' => 'left',
					'havingFilter' => true
				),
				'history_state_name' => array(
					'title' => $this->l('Status'),
					'width' => 100,
					'align' => 'left',
					'color' => 'color',
					'havingFilter' => true
				),
			);
			// loads history of the given order
			unset($this->_select, $this->_join, $this->_where, $this->_orderBy, $this->_orderWay, $this->_group, $this->_filterHaving, $this->_filter);
			$this->_select = '
			a.`date_add` as history_date,
			CONCAT(a.`employee_lastname`, \' \', a.`employee_firstname`) as history_employee,
			sosl.`name` as history_state_name,
			sos.`color` as color';

			$this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'supply_order_state` sos ON (a.`id_state` = sos.`id_supply_order_state`)
			LEFT JOIN `'._DB_PREFIX_.'supply_order_state_lang` sosl ON
			(
				a.`id_state` = sosl.`id_supply_order_state`
				AND sosl.`id_lang` = '.(int)$lang_id.'
			)';

			$this->_where = ' AND a.`id_supply_order` = '.(int)$id_supply_order;
			$this->_orderBy = 'a.`date_add`';
			$this->_orderWay = 'DESC';

			// gets list and forces no limit clause in the request
			$this->getList($lang_id, 'date_add', 'DESC', 0, false, false);

			// renders list
			$helper = new HelperList();
			$helper->no_link = true;
			$helper->show_toolbar = false;
			$helper->toolbar_scroll = false;
			$helper->shopLinkType = '';
			$helper->identifier = $this->identifier;
			//$helper->colorOnBackground = true;
			$helper->simple_header = true;
			$content = $helper->generateList($this->_list, $this->fields_list);

			echo Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content));
		}
		else if (Tools::isSubmit('id') && Tools::isSubmit('display_product_history'))
		{
			$this->identifier = 'id_supply_order_receipt_history';
			$this->table = 'supply_order_receipt_history';
			$this->display = 'list';
			$this->lang = false;
			$lang_id = (int)$this->context->language->id;
			$id_supply_order_detail = (int)Tools::getValue('id');

			unset($this->fields_list);
			$this->fields_list = array(
                                        'ids' => array(
                                                        'title' => '#',
                                                        'class' => 'ids',
                                                        'width' => 5
                                        ),
				'date_add' => array(
					'title' => $this->l('Last update'),
					'width' => 50,
					'align' => 'left',
					'type' => 'datetime',
				),
				'employee' => array(
					'title' => $this->l('Employee'),
					'width' => 100,
					'align' => 'left',
				),
				'quantity' => array(
					'title' => $this->l('Quantity received'),
					'width' => 100,
					'align' => 'left',
										'callback' => 'renderQuantityReceivedColumn'

				),
								'wholesale_price' => array(
					'title' => $this->l('Supplier price without discount'),
					'width' => 100,
										'callback' => 'renderWholesalePriceReceivedColumn'
				),
								'discount_rate' => array(
					'title' => $this->l('Discount rate'),
					'width' => 100,
										'callback' => 'renderDiscountRateReceivedColumn'
				),
								'wholesale_price_net' => array(
					'title' => $this->l('Supplier price with discount'),
					'width' => 100,
										'type' => 'price'
				),
								'total_price' => array(
					'title' => $this->l('TotalPrice'),
					'width' => 50,
					'align' => 'center',
										'callback' => 'renderTotalPriceReceivedColumn'
				),
				'action' => array(
					'title' => $this->l('Actions'),
					'width' => 50,
										'callback' => 'renderActionReceivedColumn'
				)
			);

			// loads history of the given order
			unset($this->_select, $this->_join, $this->_where, $this->_orderBy, $this->_orderWay, $this->_group, $this->_filterHaving, $this->_filter);

						$this->_select = '
						CONCAT(a.`employee_lastname`, \' \', a.`employee_firstname`) as employee,
			COALESCE(ROUND(esorh.unit_price, 2), ROUND(sod.unit_price_te, 2)) as wholesale_price,
			a.quantity as quantity,
						sod.id_currency,
						sm.id_stock_mvt,
						sod.id_supply_order,
			IFNULL(CONCAT(p.id_product, ";", pa.id_product_attribute), CONCAT(p.id_product, ";", "0")) as ids,
			COALESCE(ROUND(esorh.discount_rate, 2), ROUND(sod.discount_rate, 2)) as discount_rate,
			COALESCE(ROUND(a.quantity * (esorh.unit_price - (esorh.unit_price * esorh.discount_rate / 100)), 2), ROUND(a.quantity * (sod.unit_price_te - (sod.unit_price_te * sod.discount_rate / 100)), 2)) as total_price,
			COALESCE(ROUND((esorh.unit_price - (esorh.unit_price * esorh.discount_rate / 100)), 2), ROUND((sod.unit_price_te - (sod.unit_price_te * sod.discount_rate / 100)), 2)) as wholesale_price_net,
			IFNULL(CONCAT(p.id_product, ";", pa.id_product_attribute), CONCAT(p.id_product, ";", "0")) as ids,
			IF (esorh.is_canceled,1,0) as action,
						IF (id_erpip_supply_order_receipt_history, id_erpip_supply_order_receipt_history, 0) as id_erpip_supply_order_receipt_history';

			$this->_where = 'AND a.`id_supply_order_detail` = '.(int)$id_supply_order_detail;

						// get the purchasing price
						$this->_join = ' INNER JOIN '._DB_PREFIX_.'supply_order_detail sod ON a.id_supply_order_detail = sod.id_supply_order_detail';
						$this->_join .= ' INNER JOIN '._DB_PREFIX_.'stock s ON (s.id_product = sod.id_product AND s.id_product_attribute = sod.id_product_attribute)';
						$this->_join .= ' INNER JOIN '._DB_PREFIX_.'stock_mvt sm ON (sm.id_stock = s.id_stock AND sm.id_supply_order = sod.id_supply_order)';
						$this->_join .= ' INNER JOIN '._DB_PREFIX_.'product p ON sod.id_product = p.id_product';
						$this->_join .= ' LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON pa.id_product_attribute = sod.id_product_attribute';
						$this->_join .= ' LEFT JOIN '._DB_PREFIX_.'erpip_supply_order_receipt_history esorh ON esorh.id_supply_order_receipt_history = a.id_supply_order_receipt_history';
						$this->_group = 'GROUP BY a.id_supply_order_receipt_history';

			// gets list and forces no limit clause in the request
			$this->getList($lang_id, 'date_add', 'DESC', 0, false, false);

                        // assign var to template list
                       $this->tpl_list_vars['template_path'] = $this->template_path;

			// renders list
			$helper = new HelperList();
			$helper->no_link = true;
			$helper->show_toolbar = false;
			$helper->toolbar_scroll = false;
			$helper->shopLinkType = '';
			$helper->identifier = $this->identifier;
			$helper->colorOnBackground = true;
			$helper->simple_header = true;
			$content = $helper->generateList($this->_list, $this->fields_list);

			echo Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content));
		}
				elseif (Tools::isSubmit('task') && Tools::getValue('task') == 'getProductsForSupplyOrder')
					$this->ajaxGetProductsForSupplyOrder();

				elseif (Tools::isSubmit('task') && Tools::getValue('task') == 'getSupplyOrderDetail')
					$this->ajaxGetSupplyOrderDetail();
                                elseif(Tools::isSubmit('task') && Tools::getValue('task') == 'supplier')
                                     include_once(_PS_MODULE_DIR_.'erpillicopresta/ajax/ajax.php');

		die;
	}

	/**
	 * method call when ajax request is made for search product to add to the order
	 * @TODO - Update this method to retreive the reference, ean13, upc corresponding to a product attribute
	 */
	public function	ajaxProcessSearchProduct()
	{
		// Get the search pattern
		$pattern = pSQL(Tools::getValue('q', false));

		if (!$pattern || $pattern == '' || Tools::strlen($pattern) < 1)
			die();

		// get supplier id
		$id_supplier = (int)Tools::getValue('id_supplier', false);

		// gets the currency
		$id_currency = (int)Tools::getValue('id_currency', false);

		// get lang from context
		$id_lang = (int)Context::getContext()->language->id;

		$query = new DbQuery();
		$query->select('
			CONCAT(p.id_product, \'_\', IFNULL(pa.id_product_attribute, \'0\')) as id,
			ps.product_supplier_reference as supplier_reference,
			IFNULL(pa.reference, IFNULL(p.reference, \'\')) as reference,
			IFNULL(pa.ean13, IFNULL(p.ean13, \'\')) as ean13,
			IFNULL(pa.upc, IFNULL(p.upc, \'\')) as upc,
			md5(CONCAT(\''._COOKIE_KEY_.'\', p.id_product, \'_\', IFNULL(pa.id_product_attribute, \'0\'))) as checksum,
			IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.name, \' - \', al.name SEPARATOR \', \')), pl.name) as name
		');

		$query->from('product', 'p');

		$query->innerJoin('product_lang', 'pl', 'pl.id_product = p.id_product AND pl.id_lang = '.$id_lang);
		$query->leftJoin('product_attribute', 'pa', 'pa.id_product = p.id_product');
		$query->leftJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
		$query->leftJoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
		$query->leftJoin('attribute_lang', 'al', 'al.id_attribute = atr.id_attribute AND al.id_lang = '.$id_lang);
		$query->leftJoin('attribute_group_lang', 'agl', 'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.$id_lang);
		$query->leftJoin('product_supplier', 'ps', 'ps.id_product = p.id_product AND ps.id_product_attribute = IFNULL(pa.id_product_attribute, 0)');

		$query->where('(pl.name LIKE \'%'.$pattern.'%\' OR p.reference LIKE \'%'.$pattern.'%\' OR ps.product_supplier_reference LIKE \'%'.$pattern.'%\')');
		$query->where('p.id_product NOT IN (SELECT pd.id_product FROM `'._DB_PREFIX_.'product_download` pd WHERE (pd.id_product = p.id_product))');
		$query->where('p.is_virtual = 0 AND p.cache_is_pack = 0');

		if ($id_supplier)
			$query->where('ps.id_supplier = '.$id_supplier.' OR p.id_supplier = '.$id_supplier);

		$query->groupBy('p.id_product, pa.id_product_attribute');


		$items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		foreach ($items as &$item)
		{
			$ids = explode('_', $item['id']);
			$prices = ProductSupplier::getProductSupplierPrice($ids[0], $ids[1], $id_supplier, true);
			if (count($prices))
                            $item['unit_price_te'] = $item['unit_price_te'] = Tools::convertPriceFull($prices['product_supplier_price_te'],
                                new Currency((int)$prices['id_currency']),
                                new Currency($id_currency));
		}
		if ($items)
			die(Tools::jsonEncode($items));

		die(1);
	}

	/**
	 * @see AdminController::renderView()
	 */
	public function renderView()
	{
                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrderCustomer.php';

		$this->show_toolbar = true;
		$this->toolbar_scroll = false;
		$this->table = 'supply_order_detail';
		$this->identifier = 'id_supply_order_detail';
		$this->className = 'SupplyOrderDetail';
		$this->colorOnBackground = false;
		$this->lang = false;
		$this->list_simple_header = true;
		$this->list_no_link = true;

		// gets the id supplier to view
		$id_supply_order = (int)Tools::getValue('id_supply_order');

		// gets global order information
		$supply_order = new SupplyOrder((int)$id_supply_order);

		if (Validate::isLoadedObject($supply_order))
		{
			if (!$supply_order->is_template)
				$this->displayInformation($this->l('This interface allows you to display detailed information about your order.').'<br />');
			else
				$this->displayInformation($this->l('This interface allows you to display detailed information about your order template.').'<br />');

			$lang_id = (int)$supply_order->id_lang;

			// just in case..
			unset($this->_select, $this->_join, $this->_where, $this->_orderBy, $this->_orderWay, $this->_group, $this->_filterHaving, $this->_filter);

                        $this->_select = 'CONCAT("'.$this->l('Ref').' : ", reference, " ", "'.$this->l('Supplier').' : ", supplier_reference) as refs';
                        $this->_select .= ',CONCAT("EAN13 :", ean13," UPC : ",upc) as bare_codes ';
                        
			// gets all information on the products ordered
			$this->_where = 'AND a.`id_supply_order` = '.(int)$id_supply_order;

			// gets the list ordered by price desc, without limit
			$this->getList($lang_id, 'price_te', 'DESC', 0, false, false);

			// gets the currency used in this order
			$currency = new Currency($supply_order->id_currency);

			// gets the warehouse where products will be received
			$warehouse = new Warehouse($supply_order->id_warehouse);

			// sets toolbar title with order reference
			if (!$supply_order->is_template)
				$this->toolbar_title = sprintf($this->l('Details on supply order #%s'), $supply_order->reference);
			else
				$this->toolbar_title = sprintf($this->l('Details on supply order template #%s'), $supply_order->reference);
			// re-defines fields_list
			$this->fields_list = array(
				'refs' => array(
					'title' => $this->l('Refs.'),
					'align' => 'center',
					'width' => 120,
					'orderby' => false,
					'filter' => false,
					'search' => false,
				),
				'bare_codes' => array(
					'title' => $this->l('Bare codes'),
					'align' => 'center',
					'width' => 100,
					'orderby' => false,
					'filter' => false,
					'search' => false,
				),
				'name' => array(
					'title' => $this->l('Name'),
					'orderby' => false,
					'filter' => false,
					'search' => false,
				),
				'unit_price_te' => array(
					'title' => $this->l('Unit price (tax excl.)'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'type' => 'price',
					'currency' => true,
				),
				'quantity_expected' => array(
					'title' => $this->l('Quantity'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
				),
				'price_te' => array(
					'title' => $this->l('Price (tax excl.)'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'type' => 'price',
					'currency' => true,
				),
				'discount_rate' => array(
					'title' => $this->l('Discount rate'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'suffix' => '%',
				),
				'discount_value_te' => array(
					'title' => $this->l('Discount value (tax excl.)'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'type' => 'price',
					'currency' => true,
				),
				'price_with_discount_te' => array(
					'title' => $this->l('Price with product discount (tax excl.)'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'type' => 'price',
					'currency' => true,
				),
				'tax_rate' => array(
					'title' => $this->l('Tax rate'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'suffix' => '%',
				),
				'tax_value' => array(
					'title' => $this->l('Tax value'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'type' => 'price',
					'currency' => true,
				),
				'price_ti' => array(
					'title' => $this->l('Price (tax incl.)'),
					'align' => 'right',
					'width' => 80,
					'orderby' => false,
					'filter' => false,
					'search' => false,
					'type' => 'price',
					'currency' => true,
				),
			);

			//some staff before render list
			foreach ($this->_list as &$item)
			{
				$item['discount_rate'] = Tools::ps_round($item['discount_rate'], 4);
				$item['tax_rate'] = Tools::ps_round($item['tax_rate'], 4);
				$item['id_currency'] = $currency->id;
			}

			// unsets some buttons
			unset($this->toolbar_btn['export-csv-orders']);
			unset($this->toolbar_btn['export-csv-details']);
			unset($this->toolbar_btn['new']);

			// renders list
			$helper = new HelperList();
			$this->setHelperDisplay($helper);
			$helper->actions = array();
			$helper->show_toolbar = false;
			$helper->toolbar_btn = $this->toolbar_btn;

			$content = $helper->generateList($this->_list, $this->fields_list);

			//-ERP information
			//get information in erp_supply_order

			$erp_shipping_amount = 0;
			$erp_escompte = 0;
			$erp_date_to_invoice = null;
			$erp_global_discount_amount = 0;
			$erp_invoice_number = 0;
			$erp_supply_order_description = '';

			$d_erp_supply_order = ErpSupplyOrder::getErpSupplierOrderIdBySupplierOrderId((int)$id_supply_order);
			if ((int)$d_erp_supply_order > 0)
			{
				$erp_supply_order = new ErpSupplyOrder((int)$d_erp_supply_order);
				$erp_shipping_amount = $erp_supply_order->shipping_amount;
				$erp_escompte = $erp_supply_order->escompte;
				$erp_date_to_invoice = $erp_supply_order->date_to_invoice;
				$erp_global_discount_amount = $erp_supply_order->global_discount_amount;
				$erp_invoice_number = $erp_supply_order->invoice_number;
				$erp_supply_order_description = $erp_supply_order->description;
			}

			// calculated values
			$total_shipping = $supply_order->total_with_discount_te + $erp_shipping_amount;
			$escompte_amount = ($total_shipping * $erp_escompte) / 100;
			$total_escompte = $total_shipping - $escompte_amount;
			$total_to_pay = $total_escompte + $supply_order->total_tax;

                        // remove id_lang attribute for version >= 1.5.5
                        // id_lang parameter in displayDate() is deprecated
			if (version_compare(_PS_VERSION_,'1.5.5','>='))
			{
				$supply_order_creation_date =  Tools::displayDate($supply_order->date_add, null, false);
				$supply_order_last_update = Tools::displayDate($supply_order->date_upd, null, false);
				$supply_order_expected = Tools::displayDate($supply_order->date_delivery_expected, null, false);
				//$supply_order_date_to_invoice = Tools::displayDate($erp_date_to_invoice, null , false);
			}
			else {
				$supply_order_creation_date =  Tools::displayDate($supply_order->date_add, $lang_id, false);
				$supply_order_last_update = Tools::displayDate($supply_order->date_upd, $lang_id, false);
				$supply_order_expected = Tools::displayDate($supply_order->date_delivery_expected, $lang_id, false);
				//$supply_order_date_to_invoice = Tools::displayDate($erp_date_to_invoice, $lang_id , false);
			}

			// display these global order informations
			$this->tpl_view_vars = array(
				'supply_order_detail_content' => $content,
				'supply_order_warehouse' => (Validate::isLoadedObject($warehouse) ? $warehouse->name : ''),
				'supply_order_reference' => $supply_order->reference,
				'supply_order_supplier_name' => $supply_order->supplier_name,
				'supply_order_creation_date' => $supply_order_creation_date,
				'supply_order_last_update' => $supply_order_last_update,
				'supply_order_expected' => $supply_order_expected,
				'supply_order_discount_rate' => Tools::ps_round($supply_order->discount_rate, 2),
				'supply_order_total_te' => Tools::displayPrice($supply_order->total_te, $currency),
				'supply_order_discount_value_te' => Tools::displayPrice($supply_order->discount_value_te, $currency),
				'supply_order_total_with_discount_te' => Tools::displayPrice($supply_order->total_with_discount_te, $currency),
				'supply_order_total_tax' => Tools::displayPrice($supply_order->total_tax, $currency),
				'supply_order_total_ti' => Tools::displayPrice($supply_order->total_ti, $currency),
				'supply_order_currency' => $currency,
				'is_template' => $supply_order->is_template,

				//-ERP additional information
				'supply_order_discount_amount' => Tools::ps_round($erp_global_discount_amount, 2),
				'supply_order_escompte' => Tools::ps_round($erp_escompte, 2),
				'supply_order_shipping_amount' => Tools::ps_round($erp_shipping_amount, 2),
				'supply_order_invoice_number' => $erp_invoice_number,
				'supply_order_date_to_invoice' => $erp_date_to_invoice,
				'total_shipping' => Tools::displayPrice($total_shipping,$currency),
				'escompte_amount' => Tools::displayPrice($escompte_amount,$currency),
				'total_escompte' => Tools::displayPrice($total_escompte,$currency),
				'total_to_pay' => Tools::displayPrice($total_to_pay,$currency),
				'supply_order_description' => $erp_supply_order_description,
				'concerned_customer' => ErpSupplyOrderCustomer::getSupplyOrdersConcernedCustomer( (int)$id_supply_order)
			);
		}

		return parent::renderView();
	}

	/**
	 * Callback used to display custom content for a given field
	 * @param int $id_supply_order
	 * @param string $tr
	 * @return string $content
	 */
	public function printExportIcons($id_supply_order)
	{
		$supply_order = new SupplyOrder((int)$id_supply_order);

		if (!Validate::isLoadedObject($supply_order))
			return;

		$supply_order_state = new SupplyOrderState($supply_order->id_supply_order_state);
		if (!Validate::isLoadedObject($supply_order_state))
			return;

		$content = '<span style="width:20px; margin-right:5px;">';
		if ($supply_order_state->editable == false)
			$content .= '<a href="'.$this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&submitAction=generateSupplyOrderFormPDF&id_supply_order='.(int)$supply_order->id.'" title="'.$this->l('Export as PDF').'"><img src="../img/admin/pdf.gif" alt=""/></a>';
		else
			$content .= '-';
		$content .= '</span>';

		// receiving slip
		$content .= '<span style="width:20px; margin-right:5px;">';
		if ($supply_order_state->receipt_state || $supply_order_state->enclosed)
				$content .= '<a href="'.$this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&submitAction=generateSupplyReceivingSlipFormPDF&id_supply_order='.(int)$supply_order->id.'" title="'.$this->l('Export receipt slip').'"><img src="../img/admin/pdf.gif" alt=""/></a>';
		else
				$content .= '-';
		$content .= '</span>';

		$content .= '<span style="width:20px">';
		if ($supply_order_state->enclosed == true && $supply_order_state->receipt_state == true)
			$content .= '<a href="'.$this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&id_supply_order='.(int)$supply_order->id.'
						 &csv_order_details" title='.$this->l('Export as CSV').'">
						 <img src="../img/admin/excel_file.png" alt=""/></a>';
		else
			$content .= '-';
		$content .= '</span>';


		return $content;
	}

	/**
	 * Assigns default actions in toolbar_btn smarty var, if they are not set.
	 * uses override to specifically add, modify or remove items
	 * @see AdminSupplier::initToolbar()
	 */
	public function initToolbar()
	{
		$id_supply_order = (int)Tools::getValue('id_supply_order');

		unset($this->toolbar_btn['duplicate']);
		unset($this->toolbar_btn['generate-supply-orders']);
                
		switch ($this->display)
		{
                        case null :
                                if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
                                {
                                        if ($this->controller_status && !$this->is_template_list)
                                        {
                                                /*$this->toolbar_btn['duplicate'] = array(
                                                                'short' => 'Billing with many orders',
                                                                'href' => 'javascript:void(0)',
                                                                'desc' => $this->l('Billing with several orders'));*/

                                                 $this->toolbar_btn['generate-supply-orders'] = array(
                                                                'short' => $this->l('Generate Supply Orders'),
                                                                'href' => $this->context->link->getAdminLink('AdminGenerateSupplyOrders'),
                                                                'desc' => html_entity_decode($this->l('Generate Supply Orders')),
                                                );

                                                 $this->toolbar_btn['save'] = array(
                                                                'short' => $this->l('Export orders'),
                                                                'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&csv_orders&id_warehouse='.$this->getCurrentWarehouse().'&id_supplier='.$this->getCurrentSupplier(),
                                                                'desc' => html_entity_decode($this->l('Export orders')),
                                                );

                                                 $this->toolbar_btn['save-and-stay'] = array(
                                                                'short' => $this->l('Export orders details'),
                                                                'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&csv_orders_details&id_warehouse='.$this->getCurrentWarehouse().'&id_supplier='.$this->getCurrentSupplier(),
                                                                'desc' => html_entity_decode($this->l('Export orders details')),
                                                );



                                        }
                                }

                        break;

			case 'update_order_state':
				$this->toolbar_btn['save'] = array(
					'href' => '#',
					'desc' => $this->l('Save')
				);
                            

			case 'update_receipt':
				// Default cancel button - like old back link
				if (!isset($this->no_back) || $this->no_back == false)
				{
					$back = Tools::safeOutput(Tools::getValue('back', ''));
					if (empty($back))
						$back = self::$currentIndex.'&token='.$this->token;

					$this->toolbar_btn['cancel'] = array(
						'href' => $back,
						'desc' => $this->l('Cancel')
					);

					$this->toolbar_btn['save'] = array(
							'short' => $this->l('Export history'),
							'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&export_history&id_supply_order='.Tools::getValue('id_supply_order'),
							'desc' => html_entity_decode($this->l('Export history')),
					);
				}
			break;

                                case 'view':

                                $back = Tools::safeOutput(Tools::getValue('back', ''));
				if (empty($back))
					$back = self::$currentIndex.'&token='.$this->token;
				if (!Validate::isCleanHtml($back))
					die(Tools::displayError());
				if (!$this->lite_display)
					$this->toolbar_btn['back'] = array(
						'href' => $back,
						'desc' => $this->l('Back to list')
								);

						break;

			case 'add':
			case 'edit':
			if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
			{
				$this->toolbar_btn['export-csv-orders'] = array(
				  'short' => 'Export current stock',
				  'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&id_supply_order='.$id_supply_order.'&updatesupply_order&export_csv',
				  'desc' => $this->l('Export supply orders products'),
				);
			}

				$this->toolbar_btn['save-and-stay'] = array(
					'href' => '#',
					'desc' => $this->l('Save and stay')
				);



			default:
				parent::initToolbar();
		}

                parent::initToolbar();
                
                if ( $this->display == 'update_order_state')
                     unset($this->toolbar_btn['new']);
	}

	public function initPageHeaderToolbar()
	{
		if ($this->display == 'details')
			$this->page_header_toolbar_btn['back_to_index'] = array(
				'href' => Context::getContext()->link->getAdminLink('AdminAdvancedSupplyOrder'),
				'desc' => $this->l('Back to list'),
				'icon' => 'process-icon-back'
			);
                
                
                elseif ($this->display == 'update_receipt')
                    $this->page_header_toolbar_btn['back_to_index'] = array(
                            'href' => Context::getContext()->link->getAdminLink('AdminAdvancedSupplyOrder'),
                            'desc' => $this->l('Back to list'),
                            'icon' => 'process-icon-back'
                    );

        if($this->display == 'update_receipt')
        {
        	$this->page_header_toolbar_btn['save'] = array(
					'short' => $this->l('Export history'),
					'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&export_history&id_supply_order='.Tools::getValue('id_supply_order'),
					'desc' => html_entity_decode($this->l('Export history')),
			);
        }
                
		elseif (empty($this->display))
		{
			if ($this->controller_status && !$this->is_template_list)
			{
				/*$this->page_header_toolbar_btn['duplicate'] = array(
						'short' => $this->l('Billing with several orders'),
						'href' => 'javascript:void(0)',
						'desc' => $this->l('Billing with several orders'));*/
                                if($this->controller_status == STATUS3)
                                {
                                    $this->page_header_toolbar_btn['refresh'] = array(
                                                   'short' => $this->l('Generate Supply Orders'),
                                                   'href' => $this->context->link->getAdminLink('AdminGenerateSupplyOrders'),
                                                   'desc' => html_entity_decode($this->l('Generate Supply Orders')),
                                   );
                                }
                                else
                                {
                                    $text = addslashes($this->l('To use this feature, switch to the PRO offer'));                              
                                    $this->page_header_toolbar_btn['refresh'] = array(
						'short' => $this->l('Generate Supply Orders'),
						'js' => 'cancelBubble(event, \''.$text.'\');',
                                                'href' => '#',
						'desc' => html_entity_decode($this->l('Generate Supply Orders')),
                                    );
                                }

				 $this->page_header_toolbar_btn['save'] = array(
						'short' => $this->l('Export orders'),
						'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&csv_orders&id_warehouse='.$this->getCurrentWarehouse().'&id_supplier='.$this->getCurrentSupplier(),
						'desc' => html_entity_decode($this->l('Export orders')),
				);

				 $this->page_header_toolbar_btn['save-and-stay'] = array(
						'short' => $this->l('Export orders details'),
						'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder').'&csv_orders_details&id_warehouse='.$this->getCurrentWarehouse().'&id_supplier='.$this->getCurrentSupplier(),
						'desc' => html_entity_decode($this->l('Export orders details')),
				);
			}
                        
                        if($this->controller_status == STATUS1 && $this->nbcmdfou > ERP_CMFOFR)
                        {
                            $text = addslashes($this->l('Only 1 order is allowed in the free version. Switch to a higher version to eliminate this limit.'));
                            $this->page_header_toolbar_btn['new_supply_order'] = array(
                                    'js' => 'cancelBubble(event, \''.$text.'\');',
                                    'desc' => html_entity_decode($this->l('Add a new supply order')),
                                    'href' => '#',
                                    'icon' => 'process-icon-new'
                            );
                        }
                        else
                        {
                            $this->page_header_toolbar_btn['new_supply_order'] = array(
                                    'href' => self::$currentIndex.'&addsupply_order&token='.$this->token,
                                    'desc' => html_entity_decode($this->l('Add a new supply order')),
                                    'icon' => 'process-icon-new'
                            );
                        }
                        
			$this->page_header_toolbar_btn['new_supply_order_template'] = array(
				'href' => self::$currentIndex.'&addsupply_order&mod=template&token='.$this->token,
				'desc' => html_entity_decode($this->l('Add a new supply order template')),
				'icon' => 'process-icon-new'
			);
		}

                if ($this->is_1_6)
                    parent::initPageHeaderToolbar();
	}


	/**
	 * Helper function for AdminAdvancedSupplyOrderController::postProcess()
	 * @see AdminAdvancedSupplyOrderController::postProcess()
	 */
	protected function postProcessCopyFromTemplate()
	{
		// gets SupplyOrder and checks if it is valid
		$id_supply_order = (int)Tools::getValue('id_supply_order');
		$supply_order = new SupplyOrder($id_supply_order);
		if (!Validate::isLoadedObject($supply_order))
			$this->errors[] = Tools::displayError($this->l('This template could not be copied.'));

		// gets SupplyOrderDetail
		$entries = $supply_order->getEntriesCollection($supply_order->id_lang);

		// updates SupplyOrder so that it is not a template anymore
		$language = new Language($supply_order->id_lang);
		$ref = $supply_order->reference;
		$ref .= ' ('.date($language->date_format_full).')';
		$supply_order->reference = $ref;
		$supply_order->is_template = 0;
		$supply_order->id = (int)0;
		$supply_order->save();

		// copies SupplyOrderDetail
		foreach ($entries as $entry)
		{
			$entry->id_supply_order = $supply_order->id;
			$entry->id = (int)0;
			$entry->save();
		}

		// redirect when done
		$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
		$redirect = self::$currentIndex.'&token='.$token;
		$this->redirect_after = $redirect.'&conf=19';
	}


	/**
	 * Gets the current warehouse used
	 *
	 * @return int id_warehouse
	 */
	protected function getCurrentWarehouse()
	{
		static $warehouse = 0;

		if ($warehouse == 0)
		{
			$warehouse = -1; // all warehouses
			if ((int)Tools::getValue('id_warehouse'))
				$warehouse = (int)Tools::getValue('id_warehouse');
		}
		return $warehouse;
	}

	/**
	 * Gets the current filter used
	 *
	 * @return int status
	 */
	protected function getFilterStatus()
	{
		static $status = 0;

		$status = 0;
		if (Tools::getValue('filter_status') === 'on')
			$status = 1;

		return $status;
	}

	/**
	 * Overrides AdminController::afterAdd()
	 * @see AdminController::afterAdd()
	 * @param ObjectModel $object
	 * @return bool
	 */
	protected function afterAdd($object)
	{
		if (is_numeric(Tools::getValue('load_products')))
			$this->loadProducts((int)Tools::getValue('load_products'));

		//--ERP informations
		// bind erp_supplier_order to supply_order
				if (Tools::isSubmit('id_erpip_supply_order') && Tools::getValue('id_erpip_supply_order') > 0)
		{
					$id_erpip_supply_order = (int)Tools::getValue('id_erpip_supply_order');
					$erp_supplier_order = new ErpSupplyOrder($id_erpip_supply_order);
					if (Validate::isLoadedObject($erp_supplier_order))
					{
							$erp_supplier_order->id_supply_order = $object->id;
							$erp_supplier_order->save();
					}
				}

		$this->object = $object;
		return true;
	}


		/*
		 * After update supply order we bind
		*/
		protected function afterUpdate($object)
		{
			//--ERP informations
			// bind erp_supplier_order_detail to supply_order_detail
			if (Tools::isSubmit('id_erpip_supply_order_detail'))
			{
				$ids_erp_supply_order_detail = Tools::getValue('id_erpip_supply_order_detail');

				foreach ($ids_erp_supply_order_detail as $id_erpip_supply_order_detail => $id_supply_order_detail)
				{
					if ($id_supply_order_detail > 0 && $id_erpip_supply_order_detail > 0)
					{
						$erp_supply_order_detail = new ErpSupplyOrderDetail( (int)$id_erpip_supply_order_detail);
						if (Validate::isLoadedObject($erp_supply_order_detail))
						{
							$erp_supply_order_detail->id_supply_order_detail = (int)$id_supply_order_detail;
							$erp_supply_order_detail->save();
						}
					}
				}
			}

			return parent::afterUpdate($object);
		}

	/**
	 * Loads products which quantity (hysical quantity) is equal or less than $threshold
	 * @param int $threshold
	 */
	protected function loadProducts($threshold)
	{
		// if there is already an order
		if (Tools::getValue('id_supply_order'))
			$supply_order = new SupplyOrder((int)Tools::getValue('id_supply_order'));
		else // else, we just created a new order
			$supply_order = $this->object;

		// if order is not valid, return;
		if (!Validate::isLoadedObject($supply_order))
			return;

		// resets products if needed
		if (Tools::getValue('id_supply_order'))
			$supply_order->resetProducts();

		// gets products
		$query = new DbQuery();
		$query->select('ps.id_product,
						ps.id_product_attribute,
						ps.product_supplier_reference as supplier_reference,
						ps.product_supplier_price_te as unit_price_te,
						ps.id_currency,
						IFNULL(pa.reference, IFNULL(p.reference, \'\')) as reference,
						IFNULL(pa.ean13, IFNULL(p.ean13, \'\')) as ean13,
						IFNULL(pa.upc, IFNULL(p.upc, \'\')) as upc');
		$query->from('product_supplier', 'ps');
		$query->leftJoin('stock', 's', '
			s.id_product = ps.id_product
			AND s.id_product_attribute = ps.id_product_attribute
			AND s.id_warehouse = '.(int)$supply_order->id_warehouse);
		$query->innerJoin('warehouse_product_location', 'wpl', '
			wpl.id_product = ps.id_product
			AND wpl.id_product_attribute = ps.id_product_attribute
			AND wpl.id_warehouse = '.(int)$supply_order->id_warehouse.'
		');
		$query->leftJoin('product', 'p', 'p.id_product = ps.id_product');
		$query->leftJoin('product_attribute', 'pa', '
			pa.id_product_attribute = ps.id_product_attribute
			AND p.id_product = ps.id_product
		');
		$query->where('ps.id_supplier = '.(int)$supply_order->id_supplier);

		// gets items
		$items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		// loads order currency
		$order_currency = new Currency($supply_order->id_currency);
		if (!Validate::isLoadedObject($order_currency))
			return;

		$manager = StockManagerFactory::getManager();
		foreach ($items as $item)
		{
			$diff = (int)$threshold;

			if ($supply_order->is_template != 1)
			{
				$real_quantity = (int)$manager->getProductRealQuantities($item['id_product'], $item['id_product_attribute'], $supply_order->id_warehouse, true);
				$diff = (int)$threshold - (int)$real_quantity;
			}

			if ($diff >= 0)
			{
				// sets supply_order_detail
				$supply_order_detail = new SupplyOrderDetail();
				$supply_order_detail->id_supply_order = $supply_order->id;
				$supply_order_detail->id_currency = $order_currency->id;
				$supply_order_detail->id_product = $item['id_product'];
				$supply_order_detail->id_product_attribute = $item['id_product_attribute'];
				$supply_order_detail->reference = $item['reference'];
				$supply_order_detail->supplier_reference = $item['supplier_reference'];
				$supply_order_detail->name = Product::getProductName($item['id_product'], $item['id_product_attribute'], $supply_order->id_lang);
				$supply_order_detail->ean13 = $item['ean13'];
				$supply_order_detail->upc = $item['upc'];
				$supply_order_detail->quantity_expected = ((int)$diff == 0) ? 1 : (int)$diff;
				$supply_order_detail->exchange_rate = $order_currency->conversion_rate;

				$product_currency = new Currency($item['id_currency']);
				if (Validate::isLoadedObject($product_currency))
					$supply_order_detail->unit_price_te = Tools::convertPriceFull($item['unit_price_te'], $product_currency, $order_currency);
				else
					$supply_order_detail->unit_price_te = 0;
				$supply_order_detail->save();
				unset($product_currency);
			}
		}

		// updates supply order
		$supply_order->update();

	}

	/**
	 * Init the content of change state action
	 */
	public function initChangeStateContent()
	{
		$id_supply_order = (int)Tools::getValue('id_supply_order', 0);

		if ($id_supply_order <= 0)
		{
			$this->errors[] = Tools::displayError($this->l('The specified supply order is not valid'));
			return parent::initContent();
		}

		$supply_order = new SupplyOrder($id_supply_order);
		$supply_order_state = new SupplyOrderState($supply_order->id_supply_order_state);

		if (!Validate::isLoadedObject($supply_order) || !Validate::isLoadedObject($supply_order_state))
		{
			$this->errors[] = Tools::displayError($this->l('The specified supply order is not valid'));
			return parent::initContent();
		}

		// change the display type in order to add specific actions to
		$this->display = 'update_order_state';
		// overrides parent::initContent();
		$this->initToolbar();
                
                if ($this->is_1_6)
                    $this->initPageHeaderToolbar();

                $states = $this->getSupplyOrderStats($supply_order->id_supply_order_state);

		// loads languages
		$this->getlanguages();

                
		// defines the fields of the form to display
                if ($this->is_1_6)
                {
                    // works for 1.6 
                    $this->fields_form[0]['form'] = array(
                            'legend' => array(
                                    'title' => $this->l('Supply order status'),
                                    'icon' => 'icon-pencil'
                            ),
                            'input' => array(),
                            'submit' => array(
                                    'title' => $this->l('Save')
                            )
                    );
                }
                else {
                    
                    $this->fields_form[]['form'] = array(
			'legend' => array(
				'title' => $this->l('Supply order status'),
				'image' => '../img/admin/cms.gif',
                            
			),
                    );
                }
                
		$this->displayInformation($this->l('Be careful when changing status. Some of those changes cannot be cancelled.'));

		// sets up the helper
		$helper = new HelperForm();
		$helper->submit_action = 'submitChangestate';
		$helper->currentIndex = self::$currentIndex;
		$helper->toolbar_btn = $this->toolbar_btn;
		$helper->toolbar_scroll = false;
		$helper->token = $this->token;
		$helper->id = null; // no display standard hidden field in the form
		$helper->languages = $this->_languages;
		$helper->default_form_language = $this->default_form_language;
		$helper->allow_employee_form_lang = $this->allow_employee_form_lang;
		$helper->title = sprintf($this->l('Stock: Change supply order status #%s'), $supply_order->reference);

		$helper->override_folder = 'advanced_supply_orders_change_state/';

		// assigns our content
		$helper->tpl_vars['show_change_state_form'] = true;
		$helper->tpl_vars['supply_order_state'] = $supply_order_state;
		$helper->tpl_vars['supply_order'] = $supply_order;
		$helper->tpl_vars['supply_order_states'] = $states;
		$helper->tpl_vars['controller_status'] = $this->controller_status;
                
		// generates the form to display
		$content = $helper->generateForm($this->fields_form);
                
                $smarty_vars = array(
                    'content' => $content,
                    'url_post' => self::$currentIndex.'&token='.$this->token,
                );
                
                if ($this->is_1_6) {
                    $smarty_vars = array_merge($smarty_vars, array(
                        'show_page_header_toolbar' => $this->show_page_header_toolbar,
                        'page_header_toolbar_title' => $this->page_header_toolbar_title,
                        'page_header_toolbar_btn' => $this->page_header_toolbar_btn
                    ));
                }
                
                $this->context->smarty->assign($smarty_vars);
	}


		public function getSupplyOrderStats($id_supply_order_state)
		{
			// given the current state, loads available states
			$states = SupplyOrderState::getSupplyOrderStates($id_supply_order_state);

			// gets the state that are not allowed
			$allowed_states = array();
			foreach ($states as &$state)
			{
					$allowed_states[] = $state['id_supply_order_state'];
					$state['allowed'] = 1;
			}
			$not_allowed_states = SupplyOrderState::getStates($allowed_states);

			// generates the final list of states
			$index = count($allowed_states);
			foreach ($not_allowed_states as &$not_allowed_state)
			{
					$not_allowed_state['allowed'] = 0;
					$states[$index] = $not_allowed_state;
					++$index;
			}

			return $states;
		}
	/**
	 * Init the content of change state action
	 */
	public function initUpdateSupplyOrderContent()
	{
		$this->addJqueryPlugin('autocomplete');

		// load supply order
		$id_supply_order = (int)Tools::getValue('id_supply_order', null);

		if ($id_supply_order != null)
		{
			$supply_order = new SupplyOrder($id_supply_order);

			$currency = new Currency($supply_order->id_currency);

			if (Validate::isLoadedObject($supply_order))
			{
				// load products of this order
				$products = $supply_order->getEntries();
				$product_ids = array();

				if (isset($this->order_products_errors) && is_array($this->order_products_errors))
				{
					//for each product in error array, check if it is in products array, and remove it to conserve last user values
					foreach ($this->order_products_errors as $pe)
						foreach ($products as $index_p => $p)
							if (($p['id_product'] == $pe['id_product']) && ($p['id_product_attribute'] == $pe['id_product_attribute']))
								unset($products[$index_p]);

					// then merge arrays
					$products = array_merge($this->order_products_errors, $products);
				}

				foreach ($products as &$item)
				{

					// calculate md5 checksum on each product for use in tpl
					$item['checksum'] = md5(_COOKIE_KEY_.$item['id_product'].'_'.$item['id_product_attribute']);
					$item['unit_price_te'] = Tools::ps_round($item['unit_price_te'], 2);

										// add real quantity
										if ($this->advanced_stock_management)
										{
											$manager = StockManagerFactory::getManager();
											$item['stock'] = $manager->getProductRealQuantities($item['id_product'], $item['id_product_attribute'], null, true);
										}
										else
										{
											$query = 'SELECT quantity FROM '._DB_PREFIX_.'stock_available
													  WHERE id_product = '.intval($item['id_product']).'
													  AND id_product_attribute = '. intval($item['id_product_attribute']);

											$item['stock'] = DB::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
										}

										// add id defaut supplier of the product
										$obProduct = new Product( (int) $item['id_product']);
										$item['id_default_supplier'] = $obProduct->id_supplier;

					// add id to ids list
					$product_ids[] = $item['id_product'].'_'.$item['id_product_attribute'];

										//-ERP information

										//get erp_supply_order_detail information if exist
										$erp_supply_order_detail = null;
										$id_erpip_supply_order_detail = ErpSupplyOrderDetail::getErpSupplierOrderDetailIdBySupplierOrderDetailId( (int)$item['id_supply_order_detail']);
										if ($id_erpip_supply_order_detail > 0)
											$erp_supply_order_detail = new ErpSupplyOrderDetail((int)$id_erpip_supply_order_detail);

										if ($erp_supply_order_detail != null)
										{
											$item['comment'] = $erp_supply_order_detail->comment;
											$item['id_erpip_supply_order_detail'] = $erp_supply_order_detail->id;
										}
										else
										{
											$item['comment'] = '';
											$item['id_erpip_supply_order_detail'] = 0;
										}
				}

				$this->tpl_form_vars['products_list'] = $products;
				$this->tpl_form_vars['product_ids'] = implode($product_ids, '|');
				$this->tpl_form_vars['product_ids_to_delete'] = '';
				$this->tpl_form_vars['supplier_id'] = $supply_order->id_supplier;
				$this->tpl_form_vars['currency'] = $currency;
			}
		}

		$this->tpl_form_vars['content'] = $this->content;
		$this->tpl_form_vars['token'] = $this->token;
		$this->tpl_form_vars['show_product_management_form'] = true;

		// call parent initcontent to render standard form content
		parent::initContent();
	}

	/**
	 * Inits the content of 'update_receipt' action
	 * Called in initContent()
	 * @see AdminSuppliersOrders::initContent()
	 */
	public function initUpdateReceiptContent()
	{
                // autocomplete to add new product in receipt page
                $this->addJqueryPlugin('autocomplete');

		$id_supply_order = (int)Tools::getValue('id_supply_order', null);

		// if there is no order to fetch
		if (null == $id_supply_order)
			return parent::initContent();

		$supply_order = new SupplyOrder($id_supply_order);

		// if it's not a valid order
		if (!Validate::isLoadedObject($supply_order))
			return parent::initContent();
                
                if ($this->is_1_6)
                    $this->initPageHeaderToolbar();

		// re-defines fields_list
		$this->fields_list = array(
			'refs' => array(
				'title' => $this->l('Ref.'),
				'align' => 'left',
				'width' => 50,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
			'bar_codes' => array(
				'title' => $this->l('Bar codes'),
				'align' => 'left',
				'width' => 30,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
			'name' => array(
				'title' => $this->l('Name'),
				'align' => 'left',
				'width' => 300,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
			'quantity_received_today' => array(
				'title' => $this->l('Quantity received today?'),
				'align' => 'left',
				'width' => 20,
				'type' => 'editable',
				'orderby' => false,
				'filter' => false,
				'search' => false,
				'hint' => $this->l('Enter here the quantity you received today'),
			),
			'quantity_received' => array(
				'title' => $this->l('Quantity received'),
				'align' => 'left',
				'width' => 20,
				'orderby' => false,
				'filter' => false,
				'search' => false,
				'hint' => $this->l('Note that you can see details on the receptions - per products'),
			),
			'quantity_expected' => array(
				'title' => $this->l('Quantity expected'),
				'align' => 'left',
				'width' => 40,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
			'quantity_left' => array(
				'title' => $this->l('Remaining quantity'),
				'align' => 'left',
				'width' => 20,
				'orderby' => false,
				'filter' => false,
				'search' => false,
				'hint' => $this->l('This is the remaining quantity to receive'),
			),
						'unit_price_te' => array(
				'title' => $this->l('Unit Price (tax excl.)'),
				'align' => 'right',
				'width' => 20,
								'type' => 'price',
								'currency' => true,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
			'discount_rate' => array(
				'title' => $this->l('Discount rate'),
				'align' => 'right',
				'width' => 20,
				'orderby' => false,
				'filter' => false,
				'search' => false,
								'type' => 'decimal',
			),
			'tax_rate' => array(
				'title' => $this->l('Tax rate'),
				'align' => 'right',
				'width' => 20,
				'orderby' => false,
				'filter' => false,
				'search' => false,
                                'type' => 'decimal'
			),
                        'comment' => array(
                                'title' => $this->l('Comment'),
                                'align' => 'right',
                                'width' => 100,
                                'orderby' => false,
                                'filter' => false,
                                'search' => false,
                                'callback' => 'renderCommentReceiptColumn'
                        )
		);


		// attributes override
		unset($this->_select, $this->_join, $this->_where, $this->_orderBy, $this->_orderWay, $this->_group, $this->_filterHaving, $this->_filter);
		$this->table = 'supply_order_detail';
		$this->identifier = 'id_supply_order_detail';
		$this->className = 'SupplyOrderDetail';
		$this->list_simple_header = false;
		$this->list_no_link = true;
		$this->colorOnBackground = true;
		$this->row_hover = false;
		$this->bulk_actions = array('Update' => array('text' => $this->l('Update receipt of selected orders'), 'confirm' => $this->l('Update receipt of selected orders?')));
                
		$this->addRowAction('details');

		// sets toolbar title with order reference
		$this->toolbar_title = sprintf($this->l('Receipt of products for supply order #%s'), $supply_order->reference);

		$this->lang = false;
		$lang_id = (int)$this->context->language->id; //employee lang

		// gets values corresponding to fields_list
		$this->_select = '
                        CONCAT(reference, " ", supplier_reference) as refs,
                        CONCAT(ean13, " ", upc) as bar_codes,
			a.id_supply_order_detail as id,
			a.quantity_received as quantity_received,
			a.quantity_expected as quantity_expected,
			IF (a.quantity_expected < a.quantity_received, 0, a.quantity_expected - a.quantity_received) as quantity_left,
			IF (a.quantity_expected < a.quantity_received, 0, a.quantity_expected - a.quantity_received) as quantity_received_today,
                        IF (esod.comment,esod.comment, \'\') as comment, id_erpip_supply_order_detail ';

		$this->_where = 'AND a.`id_supply_order` = '.(int)$id_supply_order;

		$this->_group = 'GROUP BY a.id_supply_order_detail';

                $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'erpip_supply_order_detail` esod ON esod.id_supply_order_detail = a.id_supply_order_detail';

		// gets the list ordered by price desc, without limit
		$this->getList($lang_id, 'quantity_expected', 'DESC', 0, false, false);

		// defines action for POST
		$action = '&id_supply_order='.$id_supply_order;

		// unsets some buttons
		unset($this->toolbar_btn['export-csv-orders']);
		unset($this->toolbar_btn['export-csv-details']);
		unset($this->toolbar_btn['new']);

		$this->toolbar_btn['back'] = array(
			'desc' => $this->l('Back'),
			'href' => $this->context->link->getAdminLink('AdminAdvancedSupplyOrder')
		);
                
		// renders list
		$helper = new HelperList();
		$this->setHelperDisplay($helper);
		$helper->actions = array('details');
                $helper->force_show_bulk_actions = true;
		
                if ($this->is_1_6)
                    $helper->override_folder = 'supply_orders_receipt_history/helpers_16/';
                else
                     $helper->override_folder = 'supply_orders_receipt_history/helpers_15/';
                
		$helper->toolbar_btn = $this->toolbar_btn;
                $helper->list_id = 'supply_order_detail';

		$helper->ajax_params = array(
			'display_product_history' => 1,
		);

		$helper->currentIndex = self::$currentIndex.$action;

		// display these global order informations
		$this->displayInformation($this->l('This interface allows you to update the quantities of this ongoing order.').'<br />');
		$this->displayInformation($this->l('Be careful! Once you\'ve updated, you cannot go back unless you add new negative stock movements.').'<br />');
		$this->displayInformation($this->l('A green line means that you\'ve received what you expected. A red line means that you\'ve received more than expected.').'<br />');

		// generates content
		$content = $helper->generateList($this->_list, $this->fields_list);
		
                $smarty_vars = array('content' => $content);
                
                if( $this->is_1_6)
                {
                    $smarty_vars = array_merge($smarty_vars, array(
                        'show_page_header_toolbar' => $this->show_page_header_toolbar,
                        'page_header_toolbar_title' => $this->page_header_toolbar_title,
                        'page_header_toolbar_btn' => $this->page_header_toolbar_btn,
                    ));
                }
                
                // assigns var
		$this->context->smarty->assign($smarty_vars);   
	}

	/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
		$this->addJqueryUI('ui.dialog');                
                $this->addjqueryPlugin('cluetip');
		$this->addJqueryUI('ui.datepicker');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_supply_order.js');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_supply_order_tools.js');

		if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
		{
			$this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management prior to using this feature. (Preferences/Products/Products Stock)');
			return false;
		}
                
                // displays warning if there are no warehouses
                if (!Warehouse::getWarehouses(true))
                {
                        $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You must have at least one warehouse. See Stock/Warehouses');
                        return false;
                }
                
		// Manage the add stock form
		if (Tools::isSubmit('changestate'))
			$this->initChangeStateContent();
		elseif (Tools::isSubmit('update_receipt') && Tools::isSubmit('id_supply_order'))
			$this->initUpdateReceiptContent();
		elseif (Tools::isSubmit('viewsupply_order') && Tools::isSubmit('id_supply_order'))
		{
			$this->action = 'view';
			$this->display = 'view';
			parent::initContent();
		}
		elseif (Tools::isSubmit('updatesupply_order'))
			$this->initUpdateSupplyOrderContent();
		else
			parent::initContent();
	}


	/**
	 * Ths method manage associated products to the order when updating it
	 */
	public function manageOrderProducts()
	{
		// load supply order
		$id_supply_order = (int)Tools::getValue('id_supply_order', null);
		$products_already_in_order = array();

		if ($id_supply_order != null)
		{
			$supply_order = new SupplyOrder($id_supply_order);

			if (Validate::isLoadedObject($supply_order))
			{
				// tests if the supplier or currency have changed in the supply order
				$new_supplier_id = (int)Tools::getValue('id_supplier');
				$new_currency_id = (int)Tools::getValue('id_currency');

				if (($new_supplier_id != $supply_order->id_supplier) ||
					($new_currency_id != $supply_order->id_currency))
				{
					// resets all products in this order
					$supply_order->resetProducts();
				}
				else
				{
									$products_already_in_order = $supply_order->getEntries();
									$currency = new Currency($supply_order->id_ref_currency);

									// gets all product ids to manage
									$product_ids_str = Tools::getValue('product_ids', null);
									$product_ids = explode('|', $product_ids_str);
									$product_ids_to_delete_str = Tools::getValue('product_ids_to_delete', null);
									$product_ids_to_delete = array_unique(explode('|', $product_ids_to_delete_str));

									//delete products that are not managed anymore
									foreach ($products_already_in_order as $paio)
									{
											$product_ok = false;

											foreach ($product_ids_to_delete as $id)
											{
													$id_check = $paio['id_product'].'_'.$paio['id_product_attribute'];
													if ($id_check == $id)
															$product_ok = true;
											}

											if ($product_ok === true)
											{
													$entry = new SupplyOrderDetail($paio['id_supply_order_detail']);
													$entry->delete();

																											//--ERP information
																											// delete bind in erp_supply_order_detail table
																											$id_erpip_supply_order_detail = ErpSupplyOrderDetail::getErpSupplierOrderDetailIdBySupplierOrderDetailId((int)$paio['id_supply_order_detail']);
																											if ((int)$id_erpip_supply_order_detail > 0)
																											{
																													$erp_supply_order_detail = new ErpSupplyOrderDetail((int)$id_erpip_supply_order_detail);
																													$erp_supply_order_detail->delete();
																											}
																							}
									}

									// manage each product
									foreach ($product_ids as $id)
									{
																							// if quantity is null, we dont save product in supply order
																							if (Tools::getValue('input_quantity_expected_'.$id, 0) == '0')
																									continue;

											$errors = array();

											// check if a checksum is available for this product and test it
											$check = Tools::getValue('input_check_'.$id, '');
											$check_valid = md5(_COOKIE_KEY_.$id);

											if ($check_valid != $check)
													continue;

											$pos = strpos($id, '_');
											if ($pos === false)
													continue;

											// Load / Create supply order detail
											$entry = new SupplyOrderDetail();
											$id_supply_order_detail = (int)Tools::getValue('input_id_'.$id, 0);
											if ($id_supply_order_detail > 0)
											{
													$existing_entry = new SupplyOrderDetail($id_supply_order_detail);
													if (Validate::isLoadedObject($supply_order))
															$entry = &$existing_entry;
											}

											// get product informations
											$entry->id_product = Tools::substr($id, 0, $pos);
											$entry->id_product_attribute = Tools::substr($id, $pos + 1);
											$entry->unit_price_te = (float)str_replace(array(' ', ','), array('', '.'), Tools::getValue('input_unit_price_te_'.$id, 0));
											$entry->quantity_expected = (int)str_replace(array(' ', ','), array('', '.'), Tools::getValue('input_quantity_expected_'.$id, 0));
											$entry->discount_rate = (float)str_replace(array(' ', ','), array('', '.'), Tools::getValue('input_discount_rate_'.$id, 0));
											$entry->tax_rate = (float)str_replace(array(' ', ','), array('', '.'), Tools::getValue('input_tax_rate_'.$id, 0));
											$entry->reference = Tools::getValue('input_reference_'.$id, '');
											$entry->supplier_reference = Tools::getValue('input_supplier_reference_'.$id, '');
											$entry->ean13 = Tools::getValue('input_ean13_'.$id, '');
											$entry->upc = Tools::getValue('input_upc_'.$id, '');

																							// fixed bug of discount_value_te field while % is null
																							if ($entry->discount_rate == '0.000000')
																									$entry->discount_value_te = '0.000000';

											//get the product name in the order language
											$entry->name = Product::getProductName($entry->id_product, $entry->id_product_attribute, $supply_order->id_lang);

											if (empty($entry->name))
													$entry->name = '';

											if ($entry->supplier_reference == null)
													$entry->supplier_reference = '';

											$entry->exchange_rate = $currency->conversion_rate;
											$entry->id_currency = $currency->id;
											$entry->id_supply_order = $supply_order->id;

											$errors = $entry->validateController();

											//get the product name displayed in the backoffice according to the employee language
											$entry->name_displayed = Tools::getValue('input_name_displayed_'.$id, '');

											// if there is a problem, handle error for the current product
											if (count($errors) > 0)
											{
													// add the product to error array => display again product line
													$this->order_products_errors[] = array(
															'id_product' =>	$entry->id_product,
															'id_product_attribute' => $entry->id_product_attribute,
															'unit_price_te' =>	$entry->unit_price_te,
															'quantity_expected' => $entry->quantity_expected,
															'discount_rate' =>	$entry->discount_rate,
															'tax_rate' => $entry->tax_rate,
															'name' => $entry->name,
															'name_displayed' => $entry->name_displayed,
															'reference' => $entry->reference,
															'supplier_reference' => $entry->supplier_reference,
															'ean13' => $entry->ean13,
															'upc' => $entry->upc,
													);

													$error_str = '<ul>';
													foreach ($errors as $e)
															$error_str .= '<li>'.$this->l('Field').$e.'</li>';
													$error_str .= '</ul>';

													$this->errors[] = Tools::displayError($this->l('Please check the product information:').$entry->name.' '.$error_str);
											}
											else
													$entry->save();

													//-ERP information
													// updates/creates erp_supplier_order_detail if it does not exist
													if (Tools::isSubmit('id_erpip_supply_order_detail_'.$id) && (int)Tools::getValue('id_erpip_supply_order_detail_'.$id) > 0)
																	$erp_supply_order_detail = new ErpSupplyOrderDetail((int)Tools::getValue('id_erpip_supply_order_detail_'.$id)); // updates erp_supplier_detail
													else
																	$erp_supply_order_detail = new ErpSupplyOrderDetail(); // creates erp_supplier_order_detail

													$erp_supply_order_detail->comment = Tools::getValue('input_comment_'.$id, '');

													$validation_esod = $erp_supply_order_detail->validateController();

													// checks erp_supplier_order_detail validity
													if (count($validation_esod) > 0)
													{
																	foreach ($validation_esod as $item)
																					$this->errors[] = $item;
																	$this->errors[] = Tools::displayError('The ErpIllicopresta Supplier Order Detail is not correct. Please make sure all of the required fields are completed.');
													}
													else
													{
																	if (Tools::isSubmit('id_erpip_supply_order_detail_'.$id) && Tools::getValue('id_erpip_supply_order_detail_'.$id) > 0)
																					$erp_supply_order_detail->update();
																	else
																	{
																					$erp_supply_order_detail->save();
																					$_POST['id_erpip_supply_order_detail'][$erp_supply_order_detail->id] = $entry->id;
																	}
													}
									}
				}
			}
		}
	}

	/**
	 * Overrides AdminController::beforeAdd()
	 * @see AdminController::beforeAdd()
	 * @param ObjectModel $object
	 */
	public function beforeAdd($object)
	{
			if (Tools::isSubmit('is_template'))
					$object->is_template = 1;

			return true;
	}

	public function initProcess()
	{
			if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
			{
					$this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management prior to using this feature. (See Preferences > Products)');
					return false;
			}
			parent::initProcess();
	}

	private function getFilters()
	{

	   // get list of categories to filter
	   $categories_where = ' AND c.`id_category` != '.Configuration::get('PS_ROOT_CATEGORY');
	   $categories = Category::getCategories((int)$this->context->language->id, true, false,$categories_where);
	   $finale_categories = array();
	   foreach ($categories as $category)
			   $finale_categories[] = array( 'id_category' => $category['id_category'], 'name' => $category['name']);

                        $all_categories = html_entity_decode( $this->l('All categories') );
                        
			// gets the final list of product categories
			array_unshift($finale_categories, array('id_category' => '', 'name' => $all_categories));

			//get manufacturers
			$manufacturers = Manufacturer::getManufacturers();

			// gets the final list of manufacturers
			array_unshift($manufacturers, array('id_manufacturer' => '', 'name' => $this->l('All manufacturers')));

			$this->context->smarty->assign(array('categories' => $finale_categories));
			$this->context->smarty->assign(array('manufacturers' => $manufacturers));
	}


	public function renderIdSupplyOrderColumn($id_supply_order, $data)
	{
			$output = (int)$id_supply_order;

			// if supply order is in status 4 and status controller is ok
			if ((int)$data['id_supply_order_state'] == 4 && $this->controller_status)
					   $output = '<input type="checkbox" name="orderSelected" class="orderSelected" />';

			$output .= '<input type="hidden" name="id" class="id" value="'.(int)$id_supply_order.'" />';

			return $output;
	}

	public function renderReferenceColumn($reference, $data)
	{
                $output = '';
                $output .= '<a href="#" rel="index.php?controller=AdminAdvancedSupplyOrder&ajax=1&id_supplier_order='.(int)$data['id_supply_order'].'&task=getSupplyOrderDetail&token='.$this->token.'"
                                 class="cluetip" title="'.$this->l('Product list').'">
                                <img src="themes/default/img/icon-search.png" class="saveNbProducts '.(int)$data['id_supply_order'].'"/>
                </a>';

                $output .= '<a href="index.php?controller=AdminAdvancedSupplyOrder&id_supply_order='.(int)$data['id_supply_order'].'&viewsupply_order&token='.$this->token.'"
                   class="cluetip" title="'.$this->l('Product list').'">
                <span>'.pSQL($reference).'</span>
                </a>';

                return $output;
	}

	public function renderCommentReceiptColumn($comment, $data)
	{
			$output = '<input type="text" name="input_comment_'.$data['id_supply_order_detail'].'" class="id" value="'.pSQL($comment).'" style="width:100px" />';
			$output .= '<input type="hidden" name="id_erpip_supply_order_detail_'.$data['id_supply_order_detail'].'" class="id" value="'.pSQL($data['id_erpip_supply_order_detail']).'" style="width:100px" />';
			return $output;
	}

	public function renderWithDescriptionColumn($with_description, $data)
	{
			$output = '';

			if ($with_description)
					$output .= '<a href="#" class="cluetip-min" title="'.$this->l('Supply order description').'" '
											. 'rel="AdminAdvancedSupplyOrder&ajax=1&id_supplier_order='.(int)$data['id_supply_order'].'&task=supplier&action=getSupplyOrderDescription&token='.$this->token.'">'
													.'<img src="../img/admin/note.png" />'
											.'</a>';

			return $output;
	}

	public function renderQuantityReceivedColumn($quantity_received)
	{
			$output = '<input type="hidden" class="last_quantity" type="text" size="5" value="'.(int)$quantity_received.'" />';
			$output .= '<input class="quantity" type="text" size="3" value="'.(int)$quantity_received.'" disabled/>';
			return $output;
	}

	public function renderWholesalePriceReceivedColumn($wholesale_price, $data)
	{
			// if this field is editable : is_canceled == 1
			$disabled = $data['action'] == '1' ? 'disabled' : '';

			// get currency object
			$currency = new CurrencyCore((int)$data['id_currency']);

			$output = '<input class="input_price" style="width:100px" type="text" size="5" value="'.pSQL($wholesale_price).'" '.$disabled.' /> '.$currency->sign;

                        // allow the price to be show on the product only if recept is not cancel
                        
			if ($data['action'] == '0')
			   $output .= '<a href="#" title="'.$this->l('Updating supplier price').'" ><img class="wholesale_update" src="../img/admin/export.gif" /></a>';

			return $output;
	}

	public function renderActionReceivedColumn($action)
	{
			 $output = '';

                        // Show the delete button only if is_cancel equal "0"
			if ($action == '0')
			{
					$output .= '<a href="#" title="'.$this->l('Updating receipt').'" ><img class="receipt_update" src="../img/admin/edit.gif" /></a>';
					$output .= '<a href="#" title="'.$this->l('Cancelling receipt').'"><img class="receipt_cancel" src="../img/admin/cross.png" /></a>';
			}
			else
					$output .= '<p><i>'.$this->l('Cancelled').'</i></p>';

			return $output;
	}

	public function renderDiscountRateReceivedColumn($discount_rate, $data)
	{
			// if this field is editable : is_canceled == 1
			$disabled = $data['action'] == '1' ? 'disabled' : '';

			$output = '<input class="discount_rate_change" type="text" size="5" value="'.pSQL($discount_rate).'" '.$disabled.' /> %';
			return $output;
	}

	public function renderTotalPriceReceivedColumn($total_price, $data)
	{
		// hidden field by line of received product hystory
		$output = '<input type="hidden" class="last_price" value="'.(float)$total_price.'" />';
		$output .= '<input type="hidden" class="id_employee" value="'.(int)$data['id_employee'].'" />';
		$output .= '<input type="hidden" class="employee_lastname" value="'.pSQL($data['employee_lastname']).'" />';
		$output .= '<input type="hidden" class="employee_firstname" value="'.pSQL($data['employee_firstname']).'" />';
		$output .= '<input type="hidden" class="id_supply_order_state" value="'.(int)$data['id_supply_order_state'].'" />';
		$output .= '<input type="hidden" class="id_supply_order_receipt_history" value="'.(int)$data['id_supply_order_receipt_history'].'" />';
		$output .= '<input type="hidden" class="id_erpip_supply_order_receipt_history" value="'.(int)$data['id_erpip_supply_order_receipt_history'].'" />';
		$output .= '<input type="hidden" class="id_supply_order_detail" value="'.(int)$data['id_supply_order_detail'].'" />';
		$output .= '<input type="hidden" class="id_stock_mvt" value="'.(int)$data['id_stock_mvt'].'" />';

		 // get currency object
		$currency = new CurrencyCore((int)$data['id_currency']);

		$output .= pSQL($total_price).' '.$currency->sign;

		return $output;
	}

	/*
	*	get status list
	*/
	public function renderSupplyOrderStatesColumn($statut, $data)
	{
			if ((int)$data['id_supply_order_state'] != 6 && !empty($statut))
			{
					$states = $this->getSupplyOrderStats((int)$data['id_supply_order_state']);

					$html = '<select style="max-width: 220px;" class="selectUpdateSupplyOrderState">';

					foreach ($states as $state)
					{
							$selected = ( $state['id_supply_order_state'] == $data['id_supply_order_state']) ? 'selected="selected"' : '';
							$disabled = ( $state['allowed']) ? '' : 'disabled="disabled"';

							$html .= '<option class="selectedOrderState-'.$state['id_supply_order_state'].'" value ="'.$state['id_supply_order_state'].'" '.$selected.'  '.$disabled.'>'
											 .$state['name'].
											'</option>';
					}

					$html .= '</select>';
					$html .= '<input type="hidden" id="id_supply_order" value="'.(int)$data['id_supply_order'].'" />';
			}
			else
					$html = pSQL($data['state']);

			return $html;
	}

	/**
	* Gets the current supplier used
	*
	* @return int id_supplier
	*/
	protected function getCurrentSupplier()
	{
					static $supplier = 0;

					if ($supplier == 0)
					{
							$supplier = -1; // all supplier
							if ((int)Tools::getValue('id_supplier'))
											$supplier = (int)Tools::getValue('id_supplier');
					}
					return $supplier;
	}

        // generate the purchase receipt
	public function processGenerateSupplyReceivingSlipFormPDF()
	{
		if (!Tools::isSubmit('id_supply_order'))
						die ($this->errors[] = Tools::displayError('Missing supply order ID'));

		$id_supply_order = (int)Tools::getValue('id_supply_order');
		$supply_order = new SupplyOrder($id_supply_order);
	

		if (!Validate::isLoadedObject($supply_order))
						die($this->errors[] = Tools::displayError('Cannot find this supply order in the database'));

		require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/pdf/HTMLTemplateErpSupplyOrderForm.php');

		$pdf = new PDF(array($supply_order) , 'ErpSupplyOrderForm', Context::getContext()->smarty);
		 
		$pdf->render(true);
	}

	public function processGenerateSupplyOrderFormPDF()
	{
            require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/pdf/HTMLTemplateErpSupplyOrderForm.php');

            // generate multiple supply order PDF
            if (!Tools::isSubmit('print_pdf_bulk'))
            {
                if (!Tools::isSubmit('id_supply_order'))
                                die ($this->errors[] = Tools::displayError('The supply order ID is missing.'));

                $id_supply_order = (int)Tools::getValue('id_supply_order');
                $supply_order = new SupplyOrder($id_supply_order);

                if (!Validate::isLoadedObject($supply_order))
                                                die($this->errors[] = Tools::displayError('The supply order cannot be found within your database.'));

                //$this->generatePDF($supply_order, PDF::TEMPLATE_SUPPLY_ORDER_FORM);
                $pdf = new PDF(array($supply_order) , 'ErpSupplyOrderForm', Context::getContext()->smarty);
                $pdf->render(true);
            }
	}

	/*
	 * Send mail to supplier in specifique status
	 *
	 * @supply_order object supplier order
	 *
	 * @return void()
	*/
	public function sendMailOnValidateSupplyOrder($supply_order)
	{
                        // Verify that the provider mail is activate
			$enable_sending_mail_supplier = Configuration::get('ERP_ENABLE_SENDING_MAIL_SUPPLIER');

			if (!empty($enable_sending_mail_supplier) && $enable_sending_mail_supplier == '1')
			{

                                        // if status is configured to send mail
					$supply_order_state_to_send_mail = Configuration::get('ERP_SO_STATE_TO_SEND_MAIL');

					$id_state_to = (int)Tools::getValue('id_supply_order_state', 0);

                                        // send mail to provider if validation step
					if ($id_state_to == $supply_order_state_to_send_mail)
					{

                                                        // Get the order provider
							$supplier = new Supplier( $supply_order->id_supplier);

							//If supplier is valid
							if (Validate::isLoadedObject($supplier))
							{

									//--ERP information
									// get additional information from erp supplier
									$erp_supplier = null;
									if (isset($supplier->id))
									{
											$id_erpip_supplier = ErpSupplier::getErpSupplierIdBySupplierId((int)$supplier->id);
											if ($id_erpip_supplier > 0)
															$erp_supplier = new ErpSupplier( (int)$id_erpip_supplier);
									}

									// if ERP supplier existe
									if ($erp_supplier != null)
									{
											require_once _PS_MODULE_DIR_.'erpillicopresta/classes/ErpSupplierClass.php';

											//get supplier mail or emails sseparated by ";"
											$supplier_emails  = $erp_supplier->email;

											//explode if caractère ";" find
											if (strpos($supplier_emails, ';') !== false)
													$to = explode(';', $supplier_emails);
											 else
													$to = $supplier_emails;

											 if (empty($to))
											 {
													 $this->errors[] = Tools::displayError($this->l('Error while sending validation email to the suppplier : email not informed  ! '));
													 return false;
											}

											 //Shop email
											 $from = Configuration::get('PS_SHOP_EMAIL');

											 //shop name
											 $from_name = Configuration::get('PS_SHOP_NAME');

											 //Id order_lang

                                                                                         // if the provider come from a francophone country France, DOM, TOM, id lang = fr (5)
											 if (ErpSupplierClass::isSupplierFrench((int)$supplier->id))
											 {
													 $supplier_id_lang = Language::getIdByIso('fr');

													 //subject
													 $subject = 'Commande fournisseur valide';
											}
                                                                                        // if other counter look for the first active language
											 else {

                                                                                                $languages = Language::getLanguages();
                                                                                                $supplier_id_lang = $languages[0]['id_lang'];

                                                                                                //subject
                                                                                                $subject = $this->l('Order validated');
											}
                                                                                       
											 //Template path
											 $template_path = _PS_MODULE_DIR_.'erpillicopresta/mails/';

                                                                                         // Get the purchased products
											 $supply_order_details = $supply_order->getEntries();

											 $html_order_details = '';
											 $txt_order_details  = '';

											 // Build html and text code for email
											 if (!empty($supply_order_details))
											 {
													 foreach ($supply_order_details as $product)
													 {

															 $html_order_details .= '<tr>
                                                                                                                                        <td>'.$product['reference'].'</td>
                                                                                                                                        <td>'.$product['supplier_reference'].'</td>
                                                                                                                                        <td>'.$product['name'].'</td>
                                                                                                                                        <td>'.@$product['comment'].'</td>
                                                                                                                                        <td>'.Tools::displayPrice($product['unit_price_te']).'</td>
                                                                                                                                        <td>'.$product['quantity_expected'].'</td>
                                                                                                                        </tr>';

															 $txt_order_details .=   $product['reference'].' -
                                                                                                                        '.$product['supplier_reference'].' -
                                                                                                                        '.$product['name'].' -
                                                                                                                        '.@$product['comment'].' -
                                                                                                                        '.Tools::displayPrice($product['unit_price_te']).' -
                                                                                                                        '.$product['quantity_expected'].' -
                                                                                                                        '."\n\r";
													}
											}

											//template_vars
											$template_vars = array();
											$template_vars['{id_supply_order}'] = $supply_order->id;
											$template_vars['{html_order_details}'] = $html_order_details;
											$template_vars['{txt_order_details}'] = $txt_order_details;

											 //Send mail
											 return Mail::send($supplier_id_lang, 'supply_order_validated',  $subject , $template_vars,  $to, null , $from, $from_name, null, null, $template_path);
									}

							}
					}
			}

			return true;
	}

	public function ajaxGetSupplyOrderDetail()
	{
		if (Tools::isSubmit('id_supplier_order'))
		{
				$no_result = true;

				$id_supplier_order = (int)pSQL(Tools::getValue('id_supplier_order'));

				/*  try to load supply order */
				$supply_order = new SupplyOrder($id_supplier_order);
				$enteries = $supply_order->getEntriesCollection();
				if (!empty($enteries))
				{
						$i = 0;
						$j = 0;

						$nb_enteries = count($enteries);

						foreach ($enteries as $enterie)
						{
								if ($no_result == true)
								{
												echo '<table class="table" style="float: left;width:'.($nb_enteries > 10 ? '400px' : '100%').';">
												<tr>
														<th width="60%">'.$this->l('Name').'</th>
														<th width="20%">'.$this->l('Quantity').'</th>
														<th width="20%">'.$this->l('Price').'</th>
												</tr>';
												$j = $j + 1;
								}

								/*  fields name :name, quantity_expected,name_displayed, price_te,price_ti
								 * ,price_with_order_discount_te, tax_rate , quantity_received
								 * , reference ,supplier_reference */
								?>
								<tr height="40">
										<td ><?php echo $enterie->name; ?></td>
										<td class="right"><?php echo (int)$enterie->quantity_expected; ?></td>
										<td  class="right"><?php echo Tools::displayPrice($enterie->price_te, (int)$supply_order->id_currency)?></td>
								</tr>
								<?php

								if ($i % 9 == 0 && $i != 0)
								{
										if ($j % 4 == 0)
												echo '<br />';
										echo '</table>
												<table class="table" style="float: right;width: 400px;">
												<tr>
														<th>'.$this->l('Name').'</th>
														<th>'.$this->l('Quantity').'</th>
														<th>'.$this->l('Price').'</th>
												</tr>';
										$j = $j + 1;
										$i = 0;
								}
								else
										$i = $i + 1;
								$no_result = false;
						}
						echo '</table>';
				}
		}
		else
				echo $this->l('Error: no supply order found !');
	}

	public function ajaxGetProductsForSupplyOrder()
	{
		require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpSupplyOrderClasses.php';
		require_once _PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php';

		/* manage advanced stock */
		$stock_management_active    = Configuration::get ('PS_ADVANCED_STOCK_MANAGEMENT');
		$sales_forecast_type        = Configuration::get ('ERP_SALES_FORECAST_CHOICE');

		$id_supplier 		= (int)Tools::getValue('id_supplier', false);
		$id_currency 		= (int)Tools::getValue('id_currency', false);
		$id_categorie 		= (int)Tools::getValue('id_categorie', false);
		$id_manufacturer        = (int)Tools::getValue('id_manufacturer', false);
		$id_warehouse 		= (int)Tools::getValue('id_warehouse', null);
		$existing_ids 		= Tools::getValue('ids');
		//$token_get_product      = Tools::getValue('token');

		$products = ErpSupplyOrderClasses::searchProduct ($id_supplier, $id_categorie,$id_manufacturer, $id_currency);

		if (!empty($products))
		{
			$advanced_stock_token = Tools::getAdminToken('AdminAdvancedStock'.(int)(Tab::getIdFromClassName('AdminAdvancedStock')).(int)$this->context->employee->id);

			foreach ($products as $product)
			{
                                // If product already in destination array, continue
				if (strrpos($existing_ids, $product['id']) !== false)
								continue;

				$ids = explode('_', $product['id']);
				$id_product = $ids[0];
				$id_product_attribute = $ids[1];

                                // If the advanced stock manager is activated
				if ($stock_management_active == '1')
				{
                                    // Get the physical and usable quantities
                                    $query = new DbQuery();
                                    $query->select('physical_quantity');
                                    $query->select('usable_quantity');
                                    $query->from('stock');
                                    $query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute);
                                    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                                    /* the two quantities */
                                    $physical_quantity = (int)$res['physical_quantity'];
                                    $usable_quantity   = (int)$res['usable_quantity'];

                                    // The real quantity depends of the warehouse
                                    $manager = StockManagerFactory::getManager();
                                    $product['stock'] = $real_quantity = (int)$manager->getProductRealQuantities($id_product,$id_product_attribute, $id_warehouse , true);
				}
				else
				{
                                    // get the free quantities
                                    $product['stock'] = $usable_quantity = (int)Product::getQuantity($id_product,$id_product_attribute);
				}

				/*  TAX */
				/*  Get the current tax */
				$query = new DbQuery();
				$query->select('rate');
				$query->from('tax', 't');
				$query->innerJoin('tax_rule', 'tr', 'tr.id_tax = t.id_tax');
				$query->innerJoin('product', 'p', 'p.id_tax_rules_group = tr.id_tax_rules_group');
				$query->where('p.id_product = '.(int)$id_product);
				$query->where('tr.id_country IN (SELECT id_country
                                                FROM '._DB_PREFIX_.'address
                                                WHERE id_supplier = '.(int)$id_supplier.')');

				$product['tax_rate'] = round(Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query), 1);

				$prices = ErpSupplyOrderClasses::getWholesalePrice((int)$id_product, (int)$id_product_attribute , (int)$id_supplier);
				$product['unit_price_te'] = Tools::convertPriceFull($prices, new Currency((int)$id_currency));

                                // sales quantity for X rolling month
				$quantity_sales = ErpSupplyOrderClasses::getQuantitySales((int)$id_product, (int)$id_product_attribute);

                                // if sale forecast is activ
				if ($sales_forecast_type != 0)
				{
                                    // if we use the 6 month rolling method
                                    if ($sales_forecast_type == 1)
                                            $sales_forecasts = round(ErpSupplyOrderClasses::getProductSalesForecasts($id_product, $id_product_attribute), 1);

                                    // if we use last year statistics on same period
                                    else
                                            $sales_forecasts = round(ErpSupplyOrderClasses::getProductSalesForecastsByPeriod($id_product, $id_product_attribute), 1);
				} else {
                                    $sales_forecasts = 'NA';
                                }

                                // Sales gain
				$sales_gains = ErpSupplyOrderClasses::getProductSalesGains($id_product, $id_product_attribute);

                                // Prepare the hidden json foreach line
				$product['comment'] = '';
				$product_json = Tools::jsonEncode($product);

				echo '<tr>
                                        <td class="product_json hide">'.$product_json.'</td>
                                        <td><input type="checkbox" class="select_product" name="select_product"/></td>
                                        <td>'.$product['supplier_reference'].'</td>
                                        <td>'.$product['reference'].'</td>
                                        <td>
                                                         <a href="#" class="cluetip-supply-price" title="'.$this->l('Supplier Price').'"
                                                                                        rel="index.php?controller=AdminAdvancedStock&ajax=1&id_product='.$id_product.'&id_product_attribute='.$id_product_attribute.'&id_currency='.$id_currency.'&task=getProductSupplierPrice&token='.$advanced_stock_token.'" >
                                                                                                                           <img src="themes/default/img/icon-search.png">
                                                        </a>
                                                        '.$product['name'].'
                                        </td>';
                                        if (Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedSupplyOrder')))
                                            echo '<td align="center"> <p style="background-color:'.ErpSupplyOrderClasses::getStockLevelColor($real_quantity).'; width:16px; height:16px"></p> </td>';

                                        echo '<td align="center">'.$usable_quantity.'</td>
                                        '.($stock_management_active == '1'? '
                                                                        <td align="center">'.$physical_quantity.'</td>
                                                                        <td align="center">'.$real_quantity.'</td>' : '').'';

                                        if (Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedSupplyOrder')))
                                        {
                                                echo '<td align="center">'.$quantity_sales.'</td>
                                                <td align="center">'.$sales_gains.'</td>';
												if(Configuration::get('ERP_SALES_FORECAST_CHOICE') != 0)
													echo '<td>'.$sales_forecasts.'</td>';
                                        }
                                        echo '<td align="center"><input type="text" class="quantity_ordered" size="2" /></td>
                                        <td align="center"> <input type="text" name="comment" class="comment"/></td>
                                        
				</tr>';
			}
		}
		else {
                    
                   echo '<div class="list-empty-msg">
				<i class="icon-warning-sign list-empty-icon"></i>
				'.$this->l('No product found !').'
			</div>';
                }
	}

	/* RJMA
         * Added to translate AdminAdvancedSupplyOrder controller
	*/
	protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = true)
	{
	   if (!empty($class))
	   {
                $str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminAdvancedSupplyOrder');
                $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
                return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
	   }
	}
}