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
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStock.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpSupplyOrderClasses.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrderCustomer.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminGenerateSupplyOrdersController  extends IPAdminController {

	public function __construct()
	{
			$this->bootstrap = true;
			$this->table = 'order';
			$this->className = 'Order';
			$this->lang = false;
			$this->addRowAction('view');
			$this->context = Context::getContext();
                        

			$this->list_no_link = true;

                        $this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0;
                        
			// status à appliquer
			$this->generate_order_state = Configuration::get('ERP_GENERATE_ORDER_STATE');
			$this->generate_order_state_to = Configuration::get('ERP_GENERATE_ORDER_STATE_TO');

                        // template path
                        $this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';
                        $this->override_folder = $this->template_path;

			// build query
			$this->_select = 'a.id_order as checkbox,a.id_order as action, CONCAT(c.`firstname`, \' \', c.`lastname`) AS `customer`, c.`email`';
			$this->_where = 'AND a.current_state = '.(int)$this->generate_order_state;
			$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`) ';
			$this->_orderBy = 'id_order';
			$this->_orderWay = 'DESC';

			$this->fields_list = array(
				'checkbox' => array(
						'title' => $this->l('Choice'),
						'align' => 'center',
						'width' => 25,
						'havingFilter' => 'false',
						'orderby' => false,
						'search' => false,
						'callback' => 'isOrderSelected',
				),
				'id_order' => array(
						'title' => $this->l('ID'),
						'align' => 'center',
						'width' => 25
				),
				'customer' => array(
						'title' => $this->l('Customer name'),
						'havingFilter' => true,
				),
				'email' => array(
						'title' => $this->l('Email'),
						'havingFilter' => true,
				),
				'total_products' => array(
						'title' => $this->l('Total TE without shipping'),
						'align' => 'right',
						'prefix' => '<b>',
						'suffix' => '</b>',
						'type' => 'price',
						'currency' => true
				),
				'total_shipping_tax_incl' => array(
						'title' => $this->l('Shipping'),
						'align' => 'right',
						'prefix' => '<b>',
						'suffix' => '</b>',
						'type' => 'price',
						'currency' => true
				),
			);

			parent::__construct();
                        
            $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
            
            require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
            
            // send var to template
            $this->context->smarty->assign(array(
                'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code)
            ));

            // get controller status
           	$this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedOrder'));
            
	}

	public function isOrderSelected($id_order)
        {
            /*
            *  true = chechekd
            *  false = unchechekd
           */

           $poutput = '<input type="checkbox" name="selected_orders[]" class="selected_orders" checked="checked" value="'.(int)$id_order.'">';

           if (!empty( $this->context->cookie->unselected_orders))
           {
                     $unselected_orders_array = Tools::unSerialize( $this->context->cookie->unselected_orders);

                     if (is_array($unselected_orders_array) && !empty($unselected_orders_array))
                             if (in_array( $id_order, $unselected_orders_array))
                                           $poutput = '<input type="checkbox" name="selected_orders[]" class="selected_orders" value="'.(int)$id_order.'">';
           }

           return $poutput;
        }

	public function initContent()
	{
            
            if( $this->controller_status == STATUS3)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Activate additional features in your TIME SAVER module in the Module section of your back-office! Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            }
            if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
            {
                $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management prior to using this feature. (Preferences/Products/Products Stock)');
                return false;
            }
            else
            {
                // Manage simulate
		if (Tools::isSubmit('submitSimulate'))
		{
			$this->saveUnselectedOrders();
			$this->initSimulateContent();
		}
		else if (Tools::isSubmit('submitOrdering'))
			$this->initOrderingContent();
		else
			parent::initContent();
            }
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJS(_MODULE_DIR_.'/erpillicopresta/js/generate_supply_orders.js');
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/style.css');
                
                if(version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
                    $this->addJqueryPlugin('ui.datepicker.min', '/js/jquery/ui/');

		$this->addJqueryUI('ui.datepicker');
	}

	public function initOrderingContent()
	{
                // list order id by created providers
		$supply_order_created = array();

		$this->show_toolbar = true;

		$this->display = 'ordering';

                if ($this->is_1_6)
                    $this->initPageHeaderToolbar();
            
		$this->initToolbar();
		                
		$datas = $this->getDataGeneration();
                
		if (!empty($datas['data_return']))
		{
			//get default currencie
			$id_default_currency = Configuration::get('PS_CURRENCY_DEFAULT');

			// get default id lang
			$id_default_lang = Configuration::get('PS_LANG_DEFAULT');
                        
			foreach ($datas['data_return'] as $id_supplier => $products_info)
			{

                                // Get provider datas
				$supplier = new Supplier ( (int)$id_supplier);

                                // get warehouse datas, delivery date and tax for the provider order
				$id_warehouse_data = Tools::getValue('id_warehouse');
				$date_delivery_expected_data = Tools::getValue('date_delivery_expected');
				$tax_rate_data = Tools::getValue('tax_rate');
                                $tax_rate_data = $tax_rate_data[$id_supplier];

				// id warehouse
				$id_warehouse = $id_warehouse_data[ $id_supplier ];

				// delivery date
				$date_delivery_expected = $date_delivery_expected_data[ $id_supplier ];

                                // create the provider order
				$supply_order = new SupplyOrder();
				$supply_order->reference = ErpSupplyOrderClasses::getNextSupplyOrderReference();
				$supply_order->id_supplier = $id_supplier;
				$supply_order->supplier_name = $supplier->name;
				$supply_order->id_warehouse = $id_warehouse;
				$supply_order->id_currency = $id_default_currency;
				$supply_order->id_lang = $id_default_lang;
				$supply_order->id_supply_order_state = 1;
				$supply_order->id_ref_currency = (int)Currency::getDefaultCurrency()->id;
				$supply_order->date_delivery_expected  = $date_delivery_expected;
                                
                                // if recording is ok, create the order lines
				if ($supply_order->add())
				{
                                        // get the provider id order
					$id_supply_order = $this->getLastIdSupplyOrder();

					$supply_order_created[] = $id_supply_order;

					// Ajout de son historique
                                        // add historical
					$history = new SupplyOrderHistory();
					$history->id_supply_order = $id_supply_order;
					$history->id_state = 3;
					$history->id_employee = (int)$this->context->employee->id;
					$history->employee_firstname = pSQL($this->context->employee->firstname);
					$history->employee_lastname = pSQL($this->context->employee->lastname);
					$history->save();

                                        // Create entries for provider order
					if (!empty($products_info))
					{
                                            $i = 0;
                                            foreach ($products_info as $item)
                                            {
                                                    if (!isset($item['product_id']))
                                                            continue;
                                                    $supply_order_detail = new SupplyOrderDetail();
                                                    $supply_order_detail->id_supply_order = $id_supply_order;
                                                    $supply_order_detail->id_currency = (int)Currency::getDefaultCurrency()->id;
                                                    $supply_order_detail->id_product = $item['product_id'];
                                                    $supply_order_detail->id_product_attribute = $item['product_attribute_id'];
                                                    $supply_order_detail->reference = $item['product_reference'];
                                                    $supply_order_detail->supplier_reference = $item['product_supplier_reference'];
                                                    $supply_order_detail->name = $item['product_name'];
                                                    $supply_order_detail->ean13 = $item['product_ean13'];
                                                    $supply_order_detail->upc = $item['product_upc'];
                                                    $supply_order_detail->quantity_expected =  $item['total_product_quantity'];
                                                    $supply_order_detail->exchange_rate = 1;
                                                    $supply_order_detail->unit_price_te = $item['unit_price_tax_excl'];
                                                    $supply_order_detail->tax_rate = $tax_rate_data[$i];
                                                    $supply_order_detail->save();

                                                    // Get the supply order created
                                                    $id_supply_order_detail = $this->getLastIdSupplyOrderDetail();

                                                    // Record the relation between provider order and customer order
                                                    if (!empty($item))
                                                    {
                                                            foreach ($item['concerned_id_order_detail'] as $customer_link)
                                                            {
                                                                    $supply_order_customer = new ErpSupplyOrderCustomer();
                                                                    $supply_order_customer->id_customer = $customer_link['id_customer'];
                                                                    $supply_order_customer->id_order_detail = $customer_link['id_order_detail'];
                                                                    $supply_order_customer->id_supply_order_detail = $id_supply_order_detail;
                                                                    $supply_order_customer->id_supply_order = $id_supply_order;
                                                                    $supply_order_customer->save();
                                                            }
                                                    }
                                                    $i++;
                                            }

                                            // Rerecording provider order data to update totals
                                            $supply_order->save();
					}
				}
			}

                        // update provider order status
			if (!empty($datas['order_to_change_state']))
			{
                            foreach ($datas['order_to_change_state'] as $id_order)
                            {
                                $order_change_state = new Order( (int)$id_order);
                                $order_change_state->setCurrentState( $this->generate_order_state_to , (int)$this->context->employee->id);
                            }
			}

			$this->confirmations[] = $this->l('Order saved successfully !');

                        // remove treated order in cookies
			$this->context->cookie->__unset('unselected_orders');

		}
		else {
			$this->errors[] = Tools::displayError($this->l('No data available for ordering ! You must select at least one order.'));
		}

		$this->context->smarty->assign(array(
				'content' =>  '',
				'show_toolbar ' => 'true',
				'show_toolbar' => true,
				'toolbar_btn' => $this->toolbar_btn,
				'title' => $this->l('Supply Order : order screen'),
				'toolbar_scroll' => $this->toolbar_scroll,
				'token' => $this->token,
				'url_post' => self::$currentIndex.'&token='.$this->token,
				'supply_order_created' => !empty($supply_order_created) ? implode(',',$supply_order_created) : ''
		));

                $this->createTemplate('ordering.tpl');
                $this->template = 'ordering.tpl';
	}

	public function initSimulateContent()
	{
            $this->show_toolbar = false;

            // change the display type in order to add specific actions to
            $this->display = 'simulate';

            if ($this->is_1_6)
                $this->initPageHeaderToolbar();

            $this->initToolbar();

            $result = $this->renderProductSimulationList();

            $this->context->smarty->assign(array(
                'content' =>  $result['content'],
                'nbr_commande_genere' => $result['nbr_commande_genere'],
                'show_toolbar ' => 'true',
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'title' => $this->l('1 Click ERP ILLICOPRESTA'),
                'toolbar_scroll' => $this->toolbar_scroll,
                'token' => $this->token,
                'warehouses' => Warehouse::getWarehouses(true),
                'id_default_currency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'current_date' => date('Y-m-d'),
                'url_post' => self::$currentIndex.'&token='.$this->token,
                'url_post_ordering' => $this->context->link->getAdminLink('AdminGenerateSupplyOrders').'&submitOrdering',
            ));

           $this->createTemplate('simulate.tpl');
           $this->template = 'simulate.tpl';
	}

	public function getTax($tax, $data)
	{
		$tax = round($tax, 1);
                $html = '<input type="text" class="tax" value="'.$tax.'" data-id_supplier="'.$data['id_supplier'].'" '
                        . 'size="5" style="display:inline; max-width:80px;"/>';
                $html .= '<span class=""> % </span>';
                return $html;
	}

	public function displayTTCPrice($price)
	{
		return "<span class='price_ttc'>".$price."</span>";
	}

	public function displayHTPrice($price)
	{
		return "<span class='price_ht'>".$price."</span>";
	}
        
        public function renderList()
        {
            $this->toolbar_title = $this->l('Auto restock');
            return parent::renderList();
        }

	protected function renderProductSimulationList()
	{
            $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
            
		$content = array();
		$datas = $this->getDataGeneration();

		if (empty($datas))
			$this->errors[] = Tools::displayError($this->l('No data available for simulation ! Please select an order.'));

		foreach ( $datas as $id_supplier => $products_info)
		{
			$supplier_price_total_tax_excl = 0;
			foreach ( $products_info as &$product_info)
			{
				//$product_info['tax_rate'] = (($product_info['unit_price_tax_incl'] - $product_info['unit_price_tax_excl']) / $product_info['unit_price_tax_excl']) * 100;
				$supplier_price_total_tax_excl += $product_info['unit_price_tax_excl'] * $product_info['total_product_quantity'];
				$product_info['unit_price_tax_excl'] = Tools::displayPrice($product_info['unit_price_tax_excl']);
                                $product_info['id_supplier'] = $id_supplier;
			}

			$this->fields_list_simulate = array(
                            'product_supplier_reference' => array( 'title' => $this->l('Supplier reference')),
                            'product_name' => array( 'title' => $this->l('Product Name')),
                            'total_product_quantity' => array('title' => $this->l('Ordered quantity')),
                            'unit_price_tax_excl' => array('title' => $this->l('Unit Price TE')),
                            'total_price_tax_excl' => array('title' => $this->l('Total price TE'), 'callback' => 'displayHTPrice'),
                            'tax_rate' => array('title' => $this->l('Tax'),'callback' => 'getTax'),
                            'total_price_tax_incl' => array('title' => $this->l('Total price TI'), 'callback' => 'displayTTCPrice'),
                            'customer_concerned' => array( 'title' => $this->l('Customer')),
			);

			// Render list
			$helper = new HelperList();
			$helper->shopLinkType = '';
			$helper->no_link = true;
			$helper->currentIndex = '';
			$helper->identifier = 'id_order';
			$helper->toolbar_scroll = false;
			$helper->simple_header = true;
			$helper->show_toolbar = false;
			$helper->bulk_actions = $this->bulk_actions;
			$list = $helper->generateList( $products_info , $this->fields_list_simulate);

			$supplier = new Supplier($id_supplier);

			$content[] = array(
                            'supplier_name' => $supplier->name,
                            'supplier_id' => $supplier->id,
                            'supplier_price_total_text_excl' => $supplier_price_total_tax_excl,
                            'list' => $list
                        );
		}
                                
		return array( 'content' => $content, 'nbr_commande_genere' =>  count($datas));
	}

	public function getDataGeneration()
	{
		$datas = array();
		$order_to_change_state = array();
		$saved_product = array();
		$datas_quantity = array();
		$customer_concerned = array();
		$concerned_id_order_detail = array();

		$orders = Db::getInstance()->executeS('
		SELECT a.id_order
		FROM `'._DB_PREFIX_.'orders` a
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
		WHERE a.current_state = '.(int)$this->generate_order_state.'
		ORDER BY a.`id_order` DESC');

		$unselected_orders_array = array();
		/* if (isset( $this->context->cookie->unselected_orders) && !empty( $this->context->cookie->unselected_orders))
		{
			  $unselected_orders_array = Tools::unSerialize( $this->context->cookie->unselected_orders);
		} */

		if (Tools::getValue('unselected_orders_list', false))
		{
			$unselected_orders_array = explode(',', Tools::getValue('unselected_orders_list'));
			$unselected_orders_array = array_map('trim', $unselected_orders_array);
		}

		if (!empty($orders))
		{
                    foreach ($orders as $order)
                    {
                        $id_order = (int)$order['id_order'];

                        if (!in_array( $id_order, $unselected_orders_array))
                        {
                            $order = new Order( (int)$id_order);

                            if (!Validate::isLoadedObject($order))
                                         throw new PrestaShopException('object oder simulate can\'t be loaded');

                            $order_details = $order->getOrderDetailList();

                            if (!empty($order_details))
                            {
                                $order_to_change_state[] = (int)$id_order;
                                foreach ($order_details as $order_detail)
                                {
                                    $id_supplier = 0;
                                    $product_key = $order_detail['product_id'].'_'.$order_detail['product_attribute_id'];

                                    $product = new Product($order_detail['product_id'], $order_detail['product_attribute_id']);

                                    // update selling price to purchase price
                                    $order_detail['unit_price_tax_excl'] = ErpStock::getWholesalePrice( $order_detail['product_id'] , $order_detail['product_attribute_id']);
                                    $order_detail['tax_rate'] = Tax::getProductTaxRate( $order_detail['product_id']);

                                    $order_detail['unit_price_tax_incl'] = $order_detail['unit_price_tax_excl'] * ( 1 + (float)$order_detail['tax_rate'] /100);

                                    if (empty( $product->id_supplier))
                                    {
                                           // Get already associated suppliers
                                           $associated_suppliers = new Collection('ProductSupplierCore');
                                           $associated_suppliers->where('id_product', '=', (int)$product->id);
                                           $associated_suppliers->groupBy('id_supplier');

                                           foreach ($associated_suppliers as $associated_supplier)
                                                           $id_supplier = $associated_supplier->id_supplier;
                                   }
                                    else
                                                    $id_supplier = $product->id_supplier;
                                    
                                    

                                    if (isset( $saved_product[$product_key]))
                                    {
                                                    $datas_quantity[$product_key] += $order_detail['product_quantity'];
                                                    $customer_concerned[$product_key][] = $order->id_customer;
                                                    $concerned_id_order_detail[$product_key][] = array( 'id_order_detail' => $order_detail['id_order_detail'], 'id_customer' => $order->id_customer);

                                                    $order_detail['total_product_quantity'] = $datas_quantity[ $product_key ];
                                                    $order_detail['customer_concerned'] = $customer_concerned[ $product_key ];
                                                    $order_detail['concerned_id_order_detail'] = $concerned_id_order_detail[ $product_key ];

                                                    $datas[ $id_supplier ][ $product_key ] = $order_detail;
                                   }
                                    else {
                                                    $product_quantity = $order_detail['product_quantity'];

                                                    $order_detail['total_product_quantity'] = $product_quantity;
                                                    $order_detail['customer_concerned'][] = $order->id_customer;
                                                    $order_detail['concerned_id_order_detail'][] = array( 'id_order_detail' => $order_detail['id_order_detail'], 'id_customer' => $order->id_customer);
                                                    $datas[ $id_supplier ][$product_key] = $order_detail;

                                                    $datas_quantity[ $product_key] = $product_quantity;
                                                    $customer_concerned[$product_key][] = $order->id_customer;
                                                    $concerned_id_order_detail[$product_key][] = array( 'id_order_detail' => $order_detail['id_order_detail'], 'id_customer' => $order->id_customer);

                                                    $saved_product[ $product_key ] = true;
                                   }
                                   
                                }
                         }
                      }
                    }
		}

		$data_return = array();
		foreach ( $datas as $id_supplier => $data)
		{
                    if($id_supplier > 0)
                    {
			$product_list = array();

			foreach ($data as $product_key => $product_info)
			{
				$customer_concerned = '';
				$customer_concerned_arr = array_unique( $product_info['customer_concerned']);
				foreach ( $customer_concerned_arr  as $id_customer)
				{
					$customer = new Customer( $id_customer);
					$customer_concerned .= $customer->lastname.' '.$customer->firstname.', ';
				}

				$total_te = $product_info['unit_price_tax_excl'] * $product_info['total_product_quantity'];
				$total_ti = $product_info['unit_price_tax_incl'] * $product_info['total_product_quantity'];

				$product_info['total_price_tax_excl'] = Tools::displayPrice($total_te);
				$product_info['total_price_tax_incl'] = Tools::displayPrice($total_ti);
				$product_info['unit_price_tax_excl'] = $product_info['unit_price_tax_excl'];
				$product_info['customer_concerned'] = Tools::substr( $customer_concerned, 0 , Tools::strlen($customer_concerned) -2);
				$product_info['customer_id'] = $customer_concerned_arr;

				$product_list[] = $product_info;
			}

			$data_return[$id_supplier] = $product_list;
                    }
		}
               
                // for the order, get the orders list that the statuts should be update in provider order
		if ($this->display == 'ordering')
				return array('data_return' => $data_return, 'order_to_change_state' => $order_to_change_state);
		else
				return $data_return;
	}

	public function InitToolbar ()
	{
            if ($this->display == '') // only for lsit (display == '')
            {
                if (!$this->is_1_6)
                {
                    $this->toolbar_btn['simulate'] = array(
                        'href' => 'javascript:void(0)',
                        'desc' => $this->l('Simulate'),
                        'class' => 'process-icon-preview'
                    );
                }
            }

            if ($this->display == 'simulate')
            {
               $this->toolbar_btn[ $this->is_1_6 ? 'refresh' : 'generate-supply-orders' ] = array(
                    'short' => 'Generate',
                    'href' => '#',
                    'desc' => $this->l('Generate'),
                    'class' => 'process-icon-refresh-index'
                );
            }

            if ($this->display == 'simulate' || $this->display == 'ordering')
            {                
                $this->toolbar_btn['back_order_list'] = array(
                    'short' => $this->l('Return to orders list'),
                    'href' => $this->context->link->getAdminLink('AdminGenerateSupplyOrders'),
                    'desc' => $this->l('Return to orders list'),
                    'class' => 'process-icon-back'
                );   
            }
	
            parent::initToolbar();
            
            unset($this->toolbar_btn['new']);
	}

	public function initPageHeaderToolbar()
	{
                if ($this->is_1_6)
                    parent::initPageHeaderToolbar();
                
		if ($this->display == '') // only for lsit (then when display == '')
                {
                        $this->page_header_toolbar_btn['simulate'] = array(
                                'href' => 'javascript:void(0)',
                                'desc' => $this->l('Simulate'),
                                'class' => 'process-icon-preview'
                        );
                }

                if ($this->display == 'simulate')
                {
                    $this->page_header_toolbar_btn['generate-supply-orders'] = array(

                                'href' => 'javascript:void(0)',
                                'desc' => $this->l('Generate'),
                                'class' => 'process-icon-refresh-index'
                     );
                }

                if ($this->display == 'simulate' || $this->display == 'ordering')
                {
                        $this->page_header_toolbar_btn['back'] = array(
                                'href' => $this->context->link->getAdminLink('AdminGenerateSupplyOrders'),
                                'desc' => $this->l('Return to orders list')
                        );
                }
	}


        // Get the customer order list to generate the provider order
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
            parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
            
            $nb_items = count($this->_list);
            
            // Alert if there are no orders in the status
            if($nb_items == 0)
               $this->errors[] = $this->l('No orders found with the given status');
	}


	public function postProcess()
	{
                // record all order unselected
		$this->saveUnselectedOrders();

				// Export PDF of supply order
		if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'generateSupplyOrderFormPDF')
			$this->processGenerateSupplyOrderFormPDF();

				require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
				$this->context->smarty->assign(array(
			'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
			'template_path' => $this->template_path,
				));

		parent::postProcess();
	}

	/*
         * backup unselected orders in cookies
	 *
	*/
	public function saveUnselectedOrders()
	{
                 // Get the POST datas of the unselected orders
		 $unselected_orders_post = Tools::getValue('unselected_orders_list');

                 // Si page load with unselect in progress
		 if (!Tools::isSubmit('submitSimulate') && !empty( $this->context->cookie->unselected_orders))
		 {
			 $_POST['unselected_orders_list'] = implode(',', Tools::jsonDecode( $this->context->cookie->unselected_orders));
		 }
		 else
		 {
			$unselected_orders_array = explode(',', $unselected_orders_post);
			$unselected_orders_array = array_map('trim', $unselected_orders_array);
			//$this->context->cookie->__set('unselected_orders', json_encode( $unselected_orders_array));
			$this->context->cookie->__set('unselected_orders', Tools::jsonEncode( $unselected_orders_array));
		}
	}

	public function getLastIdSupplyOrder()
	{
		$query = new DbQuery();
		$query->select('id_supply_order');
		$query->from('supply_order', 'so');
		$query->orderBy('so.id_supply_order DESC');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	public function getLastIdSupplyOrderDetail()
	{
		$query = new DbQuery();
		$query->select('id_supply_order_detail');
		$query->from('supply_order_detail', 'sod');
		$query->orderBy('sod.id_supply_order_detail DESC');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	public function processGenerateSupplyOrderFormPDF()
	{
		require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/pdf/HTMLTemplateErpSupplyOrderForm.php');

		// generate multiple supply order PDF
		if (Tools::isSubmit('print_pdf_bulk'))
		{
				$supply_order_collection = array();
				foreach (explode(',', Tools::getValue('supply_order_created')) as $id_supply_order)
					if (is_array($supply_order = ErpSupplyOrderClasses::getSupplyOrderCollection( (int)$id_supply_order)))
						$supply_order_collection = array_merge( $supply_order, $supply_order_collection);

				if (!count( $supply_order_collection))
					die($this->errors[] = Tools::displayError('No supply order was found.'));

				//$this->generatePDF( $supply_order_collection , PDF::TEMPLATE_SUPPLY_ORDER_FORM);
				$pdf = new PDF($supply_order_collection, 'ErpSupplyOrderForm', Context::getContext()->smarty);
				$pdf->render();
		}
	}
        
        public function renderView()
	{
            $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
            
            $id_order = (int)Tools::getValue('id_order');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&id_order='.$id_order.'&vieworder');
        }
        
        // open detail order in blank
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
}