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
require_once(_PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpWarehouseProductLocation.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStock.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProductSupplier.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpWarehouseProductLocation.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpZone.php');
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminAdvancedStockController extends IPAdminController
{
	protected $stock_instant_state_warehouses = array();
	private $advanced_stock_management = false;
	private $context_link = '';
	private $controller_status = 0;
	private $product_token = null;

	public function __construct()
	{
            parent::__construct();
            $this->bootstrap = true;

            // template path
            $this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';

            $this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0;

            // Get the type of active stock management and send to tpl
            $this->advanced_stock_management = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
            
            // get controller status
            $this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedStock'));
            $this->product_token = Tools::getAdminToken('AdminProducts'.(int)(Tab::getIdFromClassName('AdminProducts')).(int)$this->context->employee->id);
            $this->erp_zone_token = Tools::getAdminToken('AdminErpZone'.(int)(Tab::getIdFromClassName('AdminErpZone')).(int)$this->context->employee->id);

            // get controller status
            $this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedOrder'));
	}

	/**/
	public function initContent()
	{
            if( $this->controller_status == STATUS1)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Do not limit yourself to a batch of 10 products, take advantage of the Light version of the Stock Management area for €79.99 before tax or €8.00/month before tax. Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            } else if( $this->controller_status == STATUS2)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Optimise the management of your stock with dynamic logs of stock images and the auto-incrementation of SKU for just €20.00 before tax or €1.00/month before tax. Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            } else if( $this->controller_status == STATUS3)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Activate additional features in your TIME SAVER module in the Module section of your back-office! Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            }
                
                $this->displayInformation($this->l('If you want to add an area, a subarea and a location for a product, please select a warehouse in the location filter first.'));
		$this->displayInformation($this->l('A subarea cannot be selected in the location filter if the corresponding area is not selected !'));
                $this->_helper_list = new HelperList();

		$tpl_vars = array();
		$tpl_vars['arrayList'] = array();

		$this->clearListOptions();

		// no id_image we display current stock
		if (!Tools::isSubmit('id_image') || Tools::getValue('id_image') == -1)
			$this->content .= $this->getCustomListInstantStock();

		// if we have an id_image, we display a stock image
		else
                    $this->content .= $this->getCustomListIllicoTimeMachine();

		$this->context->smarty->assign(array(
					'content' => $this->content,
					'url_post' => self::$currentIndex.'&token='.$this->token,
					'erp_zone_token' => $this->erp_zone_token,
		));
                        
		// add plugin tooltip
		$this->addJqueryPlugin('cluetip', _MODULE_DIR_.'erpillicopresta/js/cluetip/');

		// add jquery dialog
		$this->addJqueryUI('ui.dialog');

		// add JS
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_stock.js');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/advanced_stock_tools.js');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/erp_zone.js');
                $this->context->controller->addJqueryPlugin('autocomplete');
                
		// add CSS
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/jquery.cluetip.css');
	}

	/**/
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

	/* list of product in stock at the time t */
	public function getCustomListInstantStock()
	{
		$this->context = Context::getContext();
		$this->lang = false;
		$this->multishop_context = Shop::CONTEXT_ALL;
		$this->list_no_link = true;
		$this->show_toolbar = true;

		// retrieving the type of inventory management and send to template
		$this->advanced_stock_management = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

                $this->context->smarty->assign(array(
                        'advanced_stock_management' => $this->advanced_stock_management,
		));

		// smarty values
		$this->context->smarty->assign(array(
                    'advanced_stock_management' => $this->advanced_stock_management,
                    'warehouses' => Warehouse::getWarehouses(),
                    'categories' => Category::getSimpleCategories((int)$this->context->language->id),
                    'suppliers' => Supplier::getSuppliers(),
                    'manufacturers' => Manufacturer::getManufacturers(),
                    'controller_status' => $this->controller_status,
                    'quantity_filter' => 0,
                    'tokens' => array(
                            array('value' => '=', 'label' => $this->l('Equal')),
                            array('value' => '>', 'label' => $this->l('Strictly greater than')),
                            array('value' => '<', 'label' => $this->l('Strictly less than'))
                    ),
                    'list' => 'first'
		));

		// if advanced stock, we work in stock table
		if ($this->advanced_stock_management)
		{
			$this->table = 'product';
			$this->className = 'Product';
			$this->identifier = 'id_product';
			$this->_orderBy = 'id_product';
                        
                        // determination of the warehouse, if no selected warehouse, select the first
			if (($id_warehouse = $this->getCurrentValue('id_warehouse')) == false)
				$id_warehouse = -1;
                        
			$this->fields_list = array(
					'ids' => array('title' => $this->l('#'), 'search' => false),
					'name' => array(
                                                'title' => $this->l('Name'),
                                                'havingFilter' => true,
                                                'width' => 200,
                                                'callback' => 'renderNameColumn'
					),
                                        'reference' => array(
							'title' => $this->l('Reference'),
							'align' => 'center',
							'width' => 50,
							'havingFilter' => true
					),
					'first_supplier_ref' => array(
                                                'title' => $this->l('Supplier reference'),
                                                'search' => false,
                                                'callback' => 'renderFirstSupplierRefColumn'
                                        ),
					'category_name' => array(
                                                'title' => $this->l('Categorie'),
                                                'search' => false,
                                                'callback' => 'renderCategoryNameColumn'
                                        ),
					'manufacturer' => array(
                                                'title' => $this->l('Manufacturer'),
                                                'search' => false
                                        ),
					'first_supplier_price' => array(
							'title' => $this->l('Supplier price'),
							'width' => 80,
							'orderby' => true,
							'search' => false,
							'align' => 'right',
							'callback' => 'renderSupplierPriceColumn'
					),
					'price' => array(
							'title' => $this->l('Price(te)'),
							'width' => 80,
							'orderby' => true,
							'search' => false,
							'type' => 'price',
							'align' => 'right'
					),
					'price_ttc' => array(
							'title' => $this->l('Price(ti)'),
							'width' => 80,
							'type' => 'price',
							'search' => false,
							'align' => 'right',
							'orderby' => false
					),
					'valuation' => array(
							'title' => $this->l('Valuation'),
							'width' => 150,
							'orderby' => false,
							'search' => false,
							'type' => 'price',
							'currency' => true,
							'hint' => $this->l('Total value of the physical quantity. The sum (for all prices) is not available for all warehouses, please filter by warehouse.')
					),
					'physical_quantity' => array(
                                                'title' => $this->l('Physical quantity'),
                                                'width' => 50,
                                                'search' => false,
                                                'align' =>
                                                'right'
                                        ),
					'usable_quantity' => array(
                                                'title' => $this->l('Usable quantity'),
                                                'width' => 50,
                                                'search' => false,
                                                'align' => 'right'
                                        ),
					'real_quantity' => array(
                                                'title' => $this->l('Real quantity'),
                                                'width' => 50,
                                                'align' => 'right',
                                                'hint' => $this->l('Physical quantity (usable) - Customer orders + suppliers orders'),
                                                'search' => false,
                                                'orderby' => false
                                        ),
					'advanced_stock_management' => array(
                                                'title' => $this->l('Stock management'),
                                                'width' => 70,
                                                'align' => 'center',
                                                'hint' => $this->l('Adv stock mgt ? (Product->Quantities)'),
                                                'search' => false,
                                                'callback' => 'renderAdvancedStockManagementColumn'
                                        ),
			);

                        if ($this->controller_status)
                        {
                            $this->fields_list = array_merge( $this->fields_list, array(
                                    'id_product' => array(
                                            'title' => $this->l('Location'),
                                            'width' => 70,
                                            'align' => 'center',
                                            'search' => false,
                                            'class' => 'location_column',
                                            'callback' => 'renderLocationColumn',
                                            'orderby' => false
                                    )
                            ));
                        }
                                                
			// building query
			$this->_select = '  IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
                                            IFNULL(CONCAT(a.id_product, ";", pa.id_product_attribute), a.id_product) as ids,
                                           ps.product_supplier_reference as first_supplier_ref,
                                           ps.product_supplier_price_te as first_supplier_price,
                                           w.id_currency, 
                                           cl.name as category_name, 
                                           m.name as manufacturer, 
                                           if (pa.wholesale_price = 0.000000, a.wholesale_price, pa.wholesale_price) as wholesale_price,
                                           IFNULL(pa.reference, a.reference) as reference,
                                           pa.price as price_attribute, 
                                           a.price as price_product, 
                                           a.id_tax_rules_group, 
                                           IFNULL(pa.id_product_attribute, 0) as id_product_attribute,
                                           wpl.id_warehouse_product_location, 
                                           ewpl.id_erpip_warehouse_product_location,
                                           area.id_erpip_zone as id_area, 
                                           area.name as area_name, 
                                           sub_area.id_erpip_zone as id_sub_area, 
                                           sub_area.name as sub_area_name, 
                                           wpl.location as location';
                        
                        $this->_group = 'GROUP BY a.id_product, pa.id_product_attribute';
                        
			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON a.id_product = pa.id_product ';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					a.id_product = pl.id_product
					AND pl.id_lang = '.(int)$this->context->language->id.'
			)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
					al.id_attribute = pac.id_attribute
					AND al.id_lang = '.(int)$this->context->language->id.'
			)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
					agl.id_attribute_group = atr.id_attribute_group
					AND agl.id_lang = '.(int)$this->context->language->id.'
			)';

			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'stock s ON (a.id_product = s.id_product AND s.id_product_attribute = IFNULL(pa.id_product_attribute, 0)';
                        //Si le filtre par entrepôt a été sélectionné, alors il ne faut prendre les quantités que dans cet entrepôt
                        if ($id_warehouse != -1)
                            $this->_join .= 'AND s.id_warehouse = '.$id_warehouse;
                        $this->_join .= ') ';
                        
			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON (wpl.id_product = a.id_product AND wpl.id_product_attribute = IFNULL(pa.id_product_attribute, 0)) ';
                        
                        if ($id_warehouse != -1)
                            $this->_join .= ' AND wpl.id_warehouse='.$id_warehouse.' ';
                        
                        $this->_join .= 'LEFT JOIN '._DB_PREFIX_.'erpip_warehouse_product_location ewpl ON wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location ';
                        $this->_join .= 'LEFT JOIN '._DB_PREFIX_.'erpip_zone area ON area.id_erpip_zone = ewpl.id_zone_parent ';
                        $this->_join .= 'LEFT JOIN '._DB_PREFIX_.'erpip_zone sub_area ON sub_area.id_erpip_zone = ewpl.id_zone ';
                             
			$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'warehouse` w ON (w.id_warehouse = s.id_warehouse) ';

			$this->_join .= 'INNER JOIN '._DB_PREFIX_.'category_lang cl ON (a.id_category_default = cl.id_category AND cl.id_lang = '.(int)$this->context->language->id.')' ;
			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'manufacturer m ON m.id_manufacturer = a.id_manufacturer ';
			
                        $this->_join .= 'LEFT JOIN '._DB_PREFIX_.'product_supplier ps ON
                                        ps.id_product = a.id_product 
                                        AND ps.id_product_attribute = IFNULL(pa.id_product_attribute,0) ';

                        $this->context->smarty->assign(array(
                                'sub_title' => $this->l('List of products available in stock'),
                        ));
                        
                                         
                            

			// SPECIFIC FILTER
			$area = $this->getCurrentValue('area');
			$subarea = $this->getCurrentValue('subarea');
                        
                        if ($id_warehouse > 0)
                        {
                            // treatment of filters
                            $this->context->smarty->assign(array(
                                    'areas' => ErpZone::getZonesName($id_warehouse),
                                    'sub_areas' => $area ? ErpZone::getZonesName($id_warehouse, 'sub_area', $area) : array(),
                                    'id_warehouse' => $id_warehouse,
                            ));
                        }

			
			// If area and sub area specified, filter on warehouse, area and subarea
			if ($area != false && $subarea != false)
			{
                            $this->_where .= ' AND (s.id_warehouse = '.$id_warehouse.' OR wpl.id_warehouse='.$id_warehouse.') 
                                                AND area.id_erpip_zone = "'.(int)$area.'" AND sub_area.id_erpip_zone = '.(int)$subarea; 
			}
                        // If juste area then filter on warehouse and area
			elseif ($area != false)
			{
                            $this->_where .= ' AND (s.id_warehouse = '.$id_warehouse.' OR wpl.id_warehouse='.$id_warehouse.') 
                                                AND area.id_erpip_zone = '.(int)$area; 
			}
			// if not, filtered only on the warehouse
			else
			{
                            // if id = -1 :no filter because all warehouse
                            if ($id_warehouse != -1)
                                $this->_where .= ' AND (s.id_warehouse = '.$id_warehouse.' OR wpl.id_warehouse='.$id_warehouse.')';
			}

			// filtering Table quantity
			$table_quantity = 'physical_quantity';
			$table_stock = "s";
		}
		else
		{
			$this->table = 'stock_available';
			$this->className = 'StockAvailable';
			$this->identifier = 'id_stock_available';
			$this->_orderBy = 'id_product';

			$this->fields_list = array(
					'ids' => array('title' => '#', 'search' => false),
					'reference' => array(
							'title' => $this->l('Reference'),
							'align' => 'center',
							'width' => 50,
							'havingFilter' => true
					),
					'first_supplier_ref' => array(
											'title' => $this->l('Supplier reference'),
											'search' => false,
											'callback' => 'renderFirstSupplierRefColumn'
										),
					'name' => array(
												'title' => $this->l('Name'),
												'havingFilter' => true
					),
					'category_name' => array(
											'title' => $this->l('Category'),
											'search' => false,
											'callback' => 'renderCategoryNameColumn'
										),
					'manufacturer' => array(
											'title' => $this->l('Manufacturer'),
											'search' => false
										),

					'wholesale_price' => array(
							'title' => $this->l('Supplier price'),
							'width' => 80,
							'orderby' => true,
							'search' => false,
							'align' => 'right'
					),
					'price' => array(
							'title' => $this->l('Price (te)'),
							'width' => 80,
							'orderby' => true,
							'search' => false,
							'align' => 'right',
							'type' => 'decimal',
							'suffix' => '€',
					),
					'price_ttc' => array(
							'title' => $this->l('Price (ti)'),
							'width' => 80,
							'search' => false,
							'type' => 'price',
							'align' => 'right',
							'orderby' => false
					),

					'quantity' => array(
							'title' => $this->l('Quantity'),
							'width' => 50,
							'align' => 'right',
                                                        'search' => false,
							'filter_key' => 'a!quantity'
						)

			);

			// building query
			$this->_select = '
					IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
					pl.name as id_currency, if (a.id_product_attribute = 0, a.id_product, CONCAT(a.id_product, ";", a.id_product_attribute)) as ids,

							(
								SELECT ps.product_supplier_reference
								FROM '._DB_PREFIX_.'product_supplier ps
								WHERE ps.id_product = a.id_product
								AND ps.id_product_attribute = a.id_product_attribute
								LIMIT 1
							)as first_supplier_ref, cl.name as category_name, m.name as manufacturer, pa.price as price_attribute, p.price as price_product,
							IFNULL((p.price + pa.price), p.price) as price, if (pa.wholesale_price = 0.000000, p.wholesale_price, pa.wholesale_price) as wholesale_price,
							IFNULL(pa.reference, p.reference) as reference, p.id_tax_rules_group';

			$this->_group = 'GROUP BY a.id_product, a.id_product_attribute';

			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					a.id_product = pl.id_product
					AND pl.id_lang = '.(int)$this->context->language->id.'
			)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.id_product_attribute = a.id_product_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
					al.id_attribute = pac.id_attribute
					AND al.id_lang = '.(int)$this->context->language->id.'
			)';
			$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
					agl.id_attribute_group = atr.id_attribute_group
					AND agl.id_lang = '.(int)$this->context->language->id.'
			)';

			$this->_join .= 'INNER JOIN '._DB_PREFIX_.'product p ON a.id_product = p.id_product ';
			$this->_join .= 'INNER JOIN '._DB_PREFIX_.'category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '.(int)$this->context->language->id.')';
			$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'manufacturer m ON m.id_manufacturer = p.id_manufacturer ';

			$this->context->smarty->assign(array(
				'sub_title' => $this->l('List of products available in stock'),
			));

			// filtering Table quantity
			$table_quantity = 'quantity';
			$table_stock = "a";
		}

		// FILTERS

		// category filter
		if (($id_category = $this->getCurrentValue('id_category')) != false)
		{
			$this->_where .= ' AND a.id_product IN (
						SELECT cp.id_product
						FROM '._DB_PREFIX_.'category_product cp
						WHERE cp.id_category = '.$id_category.'
						)';
		}

		// supplier filter
		if (($id_supplier = $this->getCurrentValue('id_supplier')) != false)
		{
			$this->_where .= ' AND a.id_product IN (
						SELECT ps.id_product
						FROM '._DB_PREFIX_.'product_supplier ps
						WHERE ps.id_supplier = '.$id_supplier.'
						)';
		}

		// manufacturer filter
		if (($id_manufacturer = $this->getCurrentValue('id_manufacturer')) != false)
			$this->_where .= ' AND m.id_manufacturer = '.$id_manufacturer;

		// quantity filter
		if (($moreless = $this->getCurrentValue('moreless')) != false)
		{
			// quantity
			$quantity = (int)Tools::getValue('quantity_filter');
                        if ($quantity < 0)
                        {
                            $this->displayWarning($this->l('The quantity filter has not been taken into account because the comparison quantity must be positive or null.'));
                        }
                        elseif (!in_array($moreless, array('=','>','<')))
                        {
                            $this->displayWarning($this->l('To filter by quantity, the allowed operators are : ">", "=" and "<" !'));
                        }
                        else
                        {
                            
                            //If filter by warehouse not used, then we check the addition of quantity of the warehouses
                            //Therefore we modify the selection in the request to calculate its sum
                            //Then we replace where condition by group by having
                            //(In this case no need to force to 0 quantity not definied cause Sum manage it)
                            if(!$this->advanced_stock_management)
                                $id_warehouse = -1;
                            
                            if ($id_warehouse == -1)
                            {
                                $this->_select .=', SUM(distinct '.$table_stock.'.'.$table_quantity.') as sum_quantity ';
                                $this->_group .= ' HAVING sum_quantity '.$moreless.$quantity;
                            }
                            else
                            {
                                $this->_where .= ' AND ('.$table_stock.'.'.$table_quantity.' '.$moreless.' '.$quantity;

                                if ($moreless == '=' && $quantity == 0 || $moreless == "<" && $quantity >0)
                                {
                                    $this->_where .= ' OR  '.$table_stock.'.'.$table_quantity.' IS NULL)';
                                }
                                else
                                {
                                    $this->_where .= ')';
                                }
                            }
                        }
		}

		$this->context->smarty->assign(array(
				'ps_version_sup_1550' => version_compare(_PS_VERSION_, '1.5.5.0', '>='), // if ps_version is >= 1.5.5.0 the template liste_header.tpl change
		));

		$list = $this->renderList();

		return $list;
	}

	/* In stock Product list in function of the image choosen */
	public function getCustomListIllicoTimeMachine()
	{

		$current_currency = Context::getContext()->currency->sign;

				// smarty values
		$this->context->smarty->assign(array(
				'advanced_stock_management' => $this->advanced_stock_management,
				'id_category' => -1,
				'id_supplier' => -1,
				'id_manufacturer' => -1,
				'moreless' => -1,
				'quantity_filter' => 0,
				'controller_status' => $this->controller_status
		));

		$id_image = Tools::getValue('id_image');

		$this->context = Context::getContext();
		$this->lang = false;
		$this->multishop_context = Shop::CONTEXT_ALL;
		$this->list_no_link = true;
		$this->show_toolbar = false;

		$this->table = 'stock_image_content';
		$this->className = 'StockImageContent';
		$this->identifier = 'id_stock_image_content';
		$this->_orderBy = 'id_stock_image_content';

		$base_columns = array(
			'ids' => array(
                                'title' => '#',
                                'search' => false
                        ),
                    'name' => array(
                                                'title' => $this->l('Name'),
                                                'havingFilter' => true,
                                                'width' => 200,
                                                'callback' => 'renderNameColumn'
					),
			'reference' => array(
                                'title' => $this->l('Reference'),
                                'align' => 'center',
                                'width' => 50,
                                'havingFilter' => true
			),
			'first_supplier_ref' => array(
                                'title' => $this->l('Supplier reference'),
                                'search' => false,
                                'callback' => 'renderFirstSupplierRefColumn'
							),
			'category_name' => array(
                                'title' => $this->l('Category'),
                                'search' => false
                        ),
			'manufacturer' => array(
                                'title' => $this->l('Manufacturer'),
                                'search' => false
                        ),
			'wholesale_price' => array(
                                            'title' => $this->l('Supplier price'),
                                            'width' => 80,
                                            'orderby' => true,
                                            'search' => false,
                                            'type' => 'decimal',
                                            'currency' => true,
                                            'type' => 'price',
                                            'align' => 'right'
                            ),
                            'price' => array(
                                            'title' => $this->l('Price (te)'),
                                            'width' => 80,
                                            'orderby' => true,
                                            'search' => false,
                                            'align' => 'right',
                                            'type' => 'decimal',
                                            'type' => 'price'
                            ),
                            'price_ttc' => array(
                                            'title' => $this->l('Price (ti)'),
                                            'width' => 80,
                                            'search' => false,
                                            'type' => 'price',
                                            'align' => 'right',
                                            'orderby' => false
                            ),
		);

		// Column specific to the stock management active
		if ($this->advanced_stock_management)
			$additional_columns = array(
					'valuation' => array(
						'title' => $this->l('Valuation'),
						'width' => 150,
						'orderby' => false,
						'search' => false,
						'type' => 'price',
						'currency' => true,
						'hint' => $this->l('Total value of the physical quantity. The sum (for all prices)
											is not available for all warehouses, please filter by warehouse.')
				),
				'physical_quantity' => array(
									'title' => $this->l('Physical quantity'),
									'width' => 50,
                                                                        'search' => false,
									'align' =>
									'right'
								),
				'usable_quantity' => array(
									'title' => $this->l('Usable quantity'),
									'width' => 50,
                                                                        'search' => false,
									'align' => 'right'
								),
				'real_quantity' => array(
									'title' => $this->l('Real quantity'),
									'width' => 50,
                                                                        'search' => false,
									'align' => 'right',
									'hint' => $this->l('Physical quantity (usable) - Customer orders + suppliers orders')
								),
				'location' => array(
									'title' => $this->l('Location'),
									'width' => 100,
									'hint' => $this->l('Location of the product in the warehouse (Area > sub area> location)'),
									'search' => false
							   )
			);
		else
			$additional_columns = array(
				'quantity' => array(
						'title' => $this->l('Real quantity'),
						'width' => 50,
						'align' => 'right',
						'search' => false
					)
			);

		$this->fields_list = array_merge($base_columns, $additional_columns);


		// building query
		$this->_join = null;
		$this->_where = null;
		$this->_group = null;

		$this->_select = '
		IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
		pl.name as id_currency,
				if (a.id_product_attribute = 0, a.id_product, CONCAT(a.id_product, ";", a.id_product_attribute)) as ids,
				(
						SELECT ps.product_supplier_reference
						FROM '._DB_PREFIX_.'product_supplier ps
						WHERE ps.id_product = a.id_product
						AND ps.id_product_attribute = a.id_product_attribute
						LIMIT 1
				)as first_supplier_ref,
				cl.name as category_name,
				m.name as manufacturer,
				a.price_te as price,
				IFNULL(pa.reference, p.reference) as reference,
				p.id_tax_rules_group,
				pa.price as price_attribute,
				p.price as price_product';

		$this->_group = 'GROUP BY a.id_product, a.id_product_attribute';

		$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
				a.id_product = pl.id_product
				AND pl.id_lang = '.(int)$this->context->language->id.'
		)';
		$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)';
		$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)';
		$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
				al.id_attribute = pac.id_attribute
				AND al.id_lang = '.(int)$this->context->language->id.'
		)';
		$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
				agl.id_attribute_group = atr.id_attribute_group
				AND agl.id_lang = '.(int)$this->context->language->id.'
		)';

		$this->_join .= 'INNER JOIN '._DB_PREFIX_.'product p ON a.id_product = p.id_product ';
		$this->_join .= 'INNER JOIN '._DB_PREFIX_.'category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '.(int)$this->context->language->id.')';
		$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'manufacturer m ON m.id_manufacturer = p.id_manufacturer ';
		$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.id_product_attribute = a.id_product_attribute)';

		$this->_where = 'AND id_stock_image ='.$id_image;

		// we display only one filter bloc
		$this->context->smarty->assign(array(
				'list' => 'image',
				'sub_title' => null, 
		));

		$list = $this->renderList();

		return $list;
	}

	/* Product in stock : add a quantity & location column */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
			if (Tools::isSubmit('csv') && (int)Tools::getValue('id_warehouse') != -1)
					$limit = false;
                        
                        
                        $stckmgtfr = ERP_STCKMGTFR;
                        if( $this->controller_status == STATUS1)
                        {
                            $limit = $stckmgtfr;
                            $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits document editing to %d products'), $limit);
                        }

			$order_by_valuation = false;
			$order_by_real_quantity = false;
			if ($this->context->cookie->{$this->table.'Orderby'} == 'valuation')
			{
                            unset($this->context->cookie->{$this->table.'Orderby'});
                            $order_by_valuation = true;
			}
			else if ($this->context->cookie->{$this->table.'Orderby'} == 'real_quantity')
			{
                            echo '$order_by_real_quantity';
                            unset($this->context->cookie->{$this->table.'Orderby'});
                            $order_by_real_quantity = true;
			}
			else if (Tools::isSubmit('configurationOrderby') && Tools::getValue('configurationOrderby') == 'real_quantity')
			{
                            unset($this->context->cookie->{$this->table.'Orderby'});
                            $order_by_real_quantity = true;
			}

			parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

			// Add columns only for the product in stock list
			if (isset($this->_list[0]['id_product']))
			{
				$nb_items = count($this->_list);
				for ($i = 0; $i < $nb_items; ++$i)
				{
                                    $item = &$this->_list[$i];


                                    $item[$this->identifier] = $item['id_product'];

                                    // gets stock manager
                                    $manager = StockManagerFactory::getManager();

                                    $id_warehouse = (int)$this->getCurrentCoverageWarehouse();

                                    // gets quantities and valuation
                                    $query = new DbQuery();
                                    $query->select('SUM(physical_quantity) as physical_quantity');
                                    $query->select('SUM(usable_quantity) as usable_quantity');
                                    $query->select('SUM(price_te * physical_quantity) as valuation');

                                    $query->from('stock');

                                    $query->where('id_product ='.(int)$item['id_product'].' AND id_product_attribute = '.(int)$item['id_product_attribute']);

                                    // If id = -1, all warehouses
                                    if ($id_warehouse != -1)
                                            $query->where('id_warehouse = '.$id_warehouse);
                                    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                                    $item['physical_quantity'] = ($res['physical_quantity'] == '') ? 0 : $res['physical_quantity'];
                                    $item['usable_quantity'] = ($res['usable_quantity'] == '') ? 0 : $res['usable_quantity'];

                                    // gets real_quantity depending on the warehouse
                                    $item['real_quantity'] = $manager->getProductRealQuantities($item['id_product'],
                                                                                    $item['id_product_attribute'],
                                                                                    ($this->getCurrentCoverageWarehouse() == -1 ? null : array($this->getCurrentCoverageWarehouse())),
                                                                                    true);

                                    // removes the valuation if the filter corresponds to 'all warehouses'
                                    if ($this->getCurrentCoverageWarehouse() == -1)
                                                    $item['valuation'] = 'N/A';
                                    else
                                                    $item['valuation'] = $res['valuation'];
				}

				if ($this->getCurrentCoverageWarehouse() != -1 && $order_by_valuation)
						usort($this->_list, array($this, 'valuationCmp'));
				else if ($order_by_real_quantity)
						usort($this->_list, array($this, 'realQuantityCmp'));
			}

			if (isset($this->_list[0]['id_stock_available']))
			{
                            $nb_items = count($this->_list);
                            for ($i = 0; $i < $nb_items; ++$i)
                            {
                                $item = &$this->_list[$i];

                                if ((int)$item['id_product_attribute'] == 0)
                                {
                                        if (count(Product::getProductAttributesIds((int)$item['id_product'])) > 0)
                                                $item['quantity'] = '--';
                                }
                            }
			}

			// Add TTC price column for  stock, stock available & illicotimemachine
			if (isset($this->_list[0]['id_product']) || isset($this->_list[0]['id_stock_available'])
					|| isset($this->_list[0]['id_stock_image_content']))
			{
				$nb_items = count($this->_list);
				for ($i = 0; $i < $nb_items; ++$i)
				{
					$item = &$this->_list[$i];

					$query = new DbQuery();
					$query->select('rate');
					$query->from('tax', 't');
					$query->innerjoin('tax_rule', 'tr', 'tr.id_tax = t.id_tax');
					$query->where('t.id_tax = '.(int)$item['id_tax_rules_group']);
					$query->where('tr.id_country = '.(int)$this->context->country->id);

					$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);


					// If we are on a product
					if ($item['price_attribute'] == null)
					{
						$item['price'] = $item['price_product'];
						$item['price_ttc'] = ($item['price_product'] * ($res['rate'] / 100)) + $item['price_product'];
					}
					else
					{
						$item['price'] = $price = $item['price_product'] + $item['price_attribute'];
						$item['price_ttc'] = ($price * ($res['rate'] / 100)) + $price;
					}
				}
			}

			// totals
			$this->getTotalPrices();
	}

	/* Return the total purchase prices / supplier and sell prices HT */
	protected function getTotalPrices()
	{
		$array = array();
		$suppliers = Supplier::getSuppliers();
		foreach ($suppliers as $key => $supplier)
		{
			$wholesale_price = 0;
			$price = 0;
			$nb_items = count($this->_list);

			for ($i = 0; $i < $nb_items; ++$i)
			{
				$item = &$this->_list[$i];
				$wholesale_price += ErpProductSupplier::getProductPrice($supplier['id_supplier'], $item['id_product'], $item['id_product_attribute'])*$item['physical_quantity'];
				$price += $item['price']*$item['physical_quantity'];
			}

			$array[$key]['name'] = $supplier['name'];
			$array[$key]['id'] = $supplier['id_supplier'];
			$array[$key]['wholesale_price'] = $wholesale_price;
		}

		// add default purchase price
		$array[$key+1]['name'] = $this->l('Base price');
		$array[$key+1]['id'] = -1;

		$wholesale_price = 0;

		for ($i = 0; $i < $nb_items; ++$i)
		{
			$item = &$this->_list[$i];

			// In case of display stock management desactivated: we do not take in account the products displayed while they have attributes (quantities = --)
			if ($item['quantity'] != '--')
				$wholesale_price += ErpStock::getWholesalePrice($item['id_product'], $item['id_product_attribute']);
		}

		$array[$key+1]['wholesale_price'] = $wholesale_price;
		$this->context->smarty->assign(array(
				'suppliers_prices' => (array)$array,
				'price' => round($price, 2)
		));
	}

	/**
	 * CMP
	 *
	 * @param array $n
	 * @param array $m
	 */
	public function valuationCmp($n, $m)
	{
            if ($this->context->cookie->{$this->table.'Orderway'} == 'desc')
                return $n['valuation'] > $m['valuation'];
            else
                return $n['valuation'] < $m['valuation'];
	}

	/**
	 * CMP
	 *
	 * @param array $n
	 * @param array $m
	 */
	public function realQuantityCmp($n, $m)
	{
            if ($this->context->cookie->{$this->table.'Orderway'} == 'desc')
                return $n['real_quantity'] > $m['real_quantity'];
            else
                return $n['real_quantity'] < $m['real_quantity'];
	}

	/**
	 * Gets the current warehouse used
	 *
	 * @return int id_warehouse
	*/
	protected function getCurrentCoverageWarehouse()
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

	/* return a value in get/post */
	protected function getCurrentValue($var)
	{
            if (Tools::isSubmit($var))
                    $value = Tools::getValue($var);
            else
                    $value = -1;

            $this->context->smarty->assign(array(
                            $var => $value
                    ));

            return ($value == -1) ? false : $value;
	}

	/**/
	public function initToolbar()
	{
		if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
		{
			$link = $this->context_link->getAdminLink('AdminAdvancedStock').'&export_csv';
			$link .= (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != -1) ? '&id_warehouse='.(int)Tools::getValue('id_warehouse') : '';
			$link .= (Tools::isSubmit('id_category') && Tools::getValue('id_category') !=-1) ? '&id_category='.(int)Tools::getValue('id_category') : '';
			$link .= (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != -1) ? '&id_supplier='.(int)Tools::getValue('id_supplier') : '';
			$link .= (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != -1) ? '&id_manufacturer='.(int)Tools::getValue('id_manufacturer') : '';
			$link .= (Tools::isSubmit('area') && Tools::getValue('area') != -1) ? '&area='.Tools::getValue('area') : '';
			$link .= (Tools::isSubmit('subarea') && Tools::getValue('subarea') != -1) ? '&subarea='.Tools::getValue('subarea') : '';
			$link .= (Tools::isSubmit('moreless') && Tools::getValue('moreless') != -1) ? '&moreless='.Tools::getValue('moreless') : '';
			$link .= (Tools::isSubmit('quantity_filter') && Tools::getValue('quantity_filter') != -1) ? '&quantity_filter='.(int)Tools::getValue('quantity_filter') : '';

			$catalog = $this->context_link->getAdminLink('AdminAdvancedStock').'&export_catalog';
			$catalog .= (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != -1) ? '&id_warehouse='.Tools::getValue('id_warehouse') : '';
                        $catalog .= (Tools::isSubmit('area') && Tools::getValue('area') != -1) ? '&area='.Tools::getValue('area') : '';
                        $catalog .= (Tools::isSubmit('subarea') && Tools::getValue('subarea') != -1) ? '&subarea='.Tools::getValue('subarea') : '';
                        
			// Export catalogue
                        $this->toolbar_btn['export-csv-details'] = array(
					'short' => 'Export current catalog',
					'href' => $catalog,
					'desc' => $this->l('Export current catalog'),
				);

			// Export CSV
			$this->toolbar_btn['export-csv-orders'] = array(
					 'short' => 'Export current stock',
					 'href' => $link,
					 'desc' => $this->l('Export current stock'),
			);
                        
                        
                if($this->controller_status == STATUS1)
                {
                    $text = addslashes($this->l('Proceed to superior offer to use this feature.'));        
                    if ($this->controller_status)
                    {
                            // Creation of a stock image
                            $this->page_header_toolbar_btn['save-and-stay'] = array(
                                    'short' => 'New stock image',
                                    'js' => 'cancelBubble(event, \''.$text.'\');',
                                    'href' => '#',
                                    'desc' => $this->l('Make a new stock image'),
                            );
                    }

                    // Update Areas
                    if ($this->advanced_stock_management)
                    {
                        $this->page_header_toolbar_btn['update'] = array(
                                        'short' => 'Update Areas',
                                        'href' => '#',
                                        'js' => 'cancelBubble(event, \''.$text.'\');',
                                        'desc' => $this->l('Update Location'),
                        );
                    }
                }
                else
                {
                     // Update Areas
                        if ($this->advanced_stock_management)
                        {
                            $this->page_header_toolbar_btn['update'] = array(
                                            'short' => 'Update Areas',
                                            'class' => 'update_areas',    
                                            'href' => 'javascript:$(\'.form\').submit();',
                                            'desc' => $this->l('Update location'),
                            );
                        }
                        
                        
			if ($this->controller_status)
			{
				// Creation of a stock image
				$this->page_header_toolbar_btn['save-and-stay'] = array(
					'short' => 'New stock image',
					'href' => '#',
					'desc' => $this->l('Make a new stock image'),
				);
			}
                }
                       
            }
	}

	public function initToolBarTitle()
	{
		//$this->toolbar_title[] = $this->l('Administration');
		//$this->toolbar_title[] = $this->l('Merchant Expertise');
	}

	public function initPageHeaderToolbar()
	{
                if ($this->is_1_6)
                    parent::initPageHeaderToolbar();
                
		$link = $this->context_link->getAdminLink('AdminAdvancedStock').'&export_csv';
		$link .= (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != -1) ? '&id_warehouse='.(int)Tools::getValue('id_warehouse') : '';
                $link .= (Tools::isSubmit('id_category') && Tools::getValue('id_category') !=-1) ? '&id_category='.(int)Tools::getValue('id_category') : '';
                $link .= (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != -1) ? '&id_supplier='.(int)Tools::getValue('id_supplier') : '';
                $link .= (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != -1) ? '&id_manufacturer='.(int)Tools::getValue('id_manufacturer') : '';
                $link .= (Tools::isSubmit('area') && Tools::getValue('area') != -1) ? '&area='.Tools::getValue('area') : '';
                $link .= (Tools::isSubmit('subarea') && Tools::getValue('subarea') != -1) ? '&subarea='.Tools::getValue('subarea') : '';
                $link .= (Tools::isSubmit('moreless') && Tools::getValue('moreless') != -1) ? '&moreless='.Tools::getValue('moreless') : '';
                $link .= (Tools::isSubmit('quantity_filter') && Tools::getValue('quantity_filter') != -1) ? '&quantity_filter='.(int)Tools::getValue('quantity_filter') : '';
                
		$test = $this->context_link->getAdminLink('AdminAdvancedStock').'&export_catalog';
		$test .= (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != -1) ? '&id_warehouse='.Tools::getValue('id_warehouse') : '';

		// Export catalogue
                $this->page_header_toolbar_btn['import'] = array(
				'short' => 'Export current catalog',
				'href' => $test,
				'desc' => $this->l('Export current catalog'),
                );

		// Export CSV
		$this->page_header_toolbar_btn['stats'] = array(
				 'short' => 'Export current stock',
				 'href' => $link,
				 'desc' => $this->l('Export current stock'),
		);

                if($this->controller_status == STATUS1)
                {
                    $text = addslashes($this->l('Proceed to superior offer to use this feature.'));        
                    if ($this->controller_status)
                    {
                            // Creation of a stock image
                            $this->page_header_toolbar_btn['save-and-stay'] = array(
                                    'short' => 'New stock image',
                                    'js' => 'cancelBubble(event, \''.$text.'\');',
                                    'href' => '#',
                                    'desc' => $this->l('Make a new stock image'),
                            );
                    }

                    // Update Areas
                    if ($this->advanced_stock_management)
                    {
                        $this->page_header_toolbar_btn['update'] = array(
                                        'short' => 'Update Areas',
                                        'href' => '#',
                                        'js' => 'cancelBubble(event, \''.$text.'\');',
                                        'desc' => $this->l('Update Location'),
                        );
                    }
                }
                else
                {
                    if ($this->controller_status)
                    {
                            // Creation of a stock image
                            $this->page_header_toolbar_btn['save-and-stay'] = array(
                                    'short' => 'New stock image',
                                    'href' => '#',
                                    'desc' => $this->l('Make a new stock image'),
                            );
                    }

                    // Update Areas
                    if ($this->advanced_stock_management)
                    {
                        $this->page_header_toolbar_btn['update'] = array(
                                        'short' => 'Update Areas',
                                        'href' => '#',
                                        'desc' => $this->l('Update Location'),
                        );
                    }
                }
                
                $var_assign = array();
                
                if ($this->is_1_6)
                   $var_assign = array('show_page_header_toolbar' => $this->show_page_header_toolbar);
                
                $var_assign = array_merge($var_assign, array(
			'page_header_toolbar_title' => '1 Click ERP illicoPresta',
			'page_header_toolbar_btn' => $this->page_header_toolbar_btn,
                    )
                );
                
                $this->context->smarty->assign($var_assign);
	}

	/* Display table*/
	public function renderList()
	{
                $this->toolbar_title = $this->l('Stock');
//                $this->page_header_toolbar_title = $this->l('Advanced Stock Management');
		$this->processFilter();

		if (Tools::isSubmit('submitReset'.$this->table))
		{
			unset($this->context->cookie->stock_availableFilter_name);
		}

		if (Tools::isSubmit('export_csv'))
		{
			$this->renderCSV();
		}

		if (Tools::isSubmit('export_catalog'))
		{
			$this->renderCatalog();
		}

		if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != '-1')
			self::$currentIndex .= '&id_warehouse='.(int)Tools::getValue('id_warehouse');

		if (Tools::isSubmit('id_image') && Tools::getValue('id_image') != '-1')
			self::$currentIndex .= '&id_image='.(int)Tools::getValue('id_image');

		if (Tools::isSubmit('id_category') && Tools::getValue('id_category') != '-1')
			self::$currentIndex .= '&id_category='.(int)Tools::getValue('id_category');

		if (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != '-1')
			self::$currentIndex .= '&id_supplier='.(int)Tools::getValue('id_supplier');

		if (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != '-1')
			self::$currentIndex .= '&id_manufacturer='.(int)Tools::getValue('id_manufacturer');

		if (Tools::isSubmit('moreless') && in_array(Tools::getValue('moreless'), array('=', '>', '<')))
			self::$currentIndex .= '&moreless='.Tools::getValue('moreless');

		if (Tools::isSubmit('quantity_filter'))
			self::$currentIndex .= '&quantity_filter='.(int)Tools::getValue('quantity_filter');

		return parent::renderList();
	}

	/**/
	public function postProcess()
	{
            
                if(Tools::isSubmit('submitFilterconfiguration'))
                {
                    // get an array with first paramettre id_product, second paramettre id_product attribute, third area 
                    // fourth id_wharehouse_product_location, fifth id_erpip_warhouse_product_location
                    $areas = Tools::getValue('data_location');
                                        
                    if (!empty($areas) && is_array($areas))
                    {
                        foreach($areas as $id_product => $product)
                        {
                            foreach($product as $id_attribute => $attribute)
                            {
                                // data already exists in warehouse product location table 
                                if (!empty($attribute['id_warehouse_product_location']) && $attribute['id_warehouse_product_location'] != '0')
                                    $warehouse = new WarehouseProductLocationCore((int)$attribute['id_warehouse_product_location']);
                                
                                // data not exists in warehouse product location table 
                                else {
                                    $warehouse = new WarehouseProductLocationCore();
                                    $warehouse->id_product = (int)$id_product;
                                    $warehouse->id_product_attribute = (int)$id_attribute;
                                    $warehouse->id_warehouse = (int)Tools::getValue('id_warehouse');
                                }
                                
                                //save location
                                $warehouse->location = empty($attribute['location'])? null : $attribute['location'];
                                $warehouse->save();
                                
                                // data not exists in ERP warehouse product location table 
                                if ($attribute['id_erpip_warehouse_product_location'] == '0')
                                    $erp_warehouse = new ErpWarehouseProductLocation();

                                // data already exists in ERP warehouse product location table 
                                else
                                    $erp_warehouse = new ErpWarehouseProductLocation((int)$attribute['id_erpip_warehouse_product_location']);

                                // save area and sub area
                                $erp_warehouse->id_warehouse_product_location = (int)$warehouse->id;
                                $erp_warehouse->id_zone_parent = empty($attribute['area'])? null : (int)$attribute['area'] ;  //id_zone_parent  = area
                                $erp_warehouse->id_zone =  empty($attribute['sub_area'])? null : (int)$attribute['sub_area'] ;  //id_zone = sub_area
                                $erp_warehouse->save();
                            }
                        }
                        
                       $this->confirmations[] = $this->l('Locations updated successfully');       
                    }
                }
                
		if (Tools::isSubmit('createImageStock'))
		{
			// list of stock images
			$images = Tools::getValue('images');

			if (!empty($images) && is_array($images))
			{
				foreach ($images as $image)
				{
					//if isset id_image then this is the selected images stock
					if (isset($image['id_stock_image']))
					{
						$stock_image = new StockImage();
						$stock_image->createImage((int)$image['id_stock_image'],  $image['name_stock_image']);
						break;
					}
				}
			}
		}

		//Display Information or confirmation message / error of end of inventory
		switch(Tools::getValue('submitFilterstock'))
		{
			case 0:
				$this->displayInformation($this->l('You may create a new stock image or select an older one'));
			break;
			case 1:
				$this->confirmations[] = $this->l('New image saved');
			break;
			case 2:
				$this->errors[] = Tools::displayError('Error while handling products');
			break;
			default:
				$this->displayInformation($this->l('You may create a new stock image or select an older one'));
			break;
		}

		// Stock image selection
		$this->context->smarty->assign(array(
                        'images' => StockImage::getStockImages(),
                        'pack' => ERP_SLOT_IPTIMEMACHINE,
                        'id_warehouse' => Tools::getValue('id_warehouse'),
		));

		$this->getCurrentValue('id_image');

		// Get context link and display toolbar
		$this->context_link = $this->context->link;
		$this->initToolbar();
                $this->initPageHeaderToolbar();

                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
                $this->context->smarty->assign(array(
			'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
			'template_path' => $this->template_path,
                ));

                return parent::postProcess();
	}

	/*
	*	CSV export
	*/
	protected function renderCSV()
	{
		if (Tools::isSubmit('export_csv'))
		{
                    $stckmgtfr = ERP_STCKMGTFR;
                    // get all filter
                    //
                    // category filer
                    $id_category = (Tools::isSubmit('id_category')) ? (int)Tools::getValue('id_category') : -1;
                    $query = new DbQuery();
                    $query->select('id_product');
                    $query->from('category_product');
                    $query->where('id_category = '.$id_category);
                    $categories_exec = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
                    
                    $categories = null;
                    foreach ($categories_exec as $category)
                            $categories[] = (int)$category['id_product'];

                    //If no data
                    if($id_category!=-1 && $categories==null)
                    {
                        $this->displayWarning($this->l('No data to export in this category !'));
                        return;
                    }
                    if($categories != null)
                        $categories = array_unique($categories);
                    
                    // supplier filter
                    $id_supplier = (Tools::isSubmit('id_supplier')) ? (int)Tools::getValue('id_supplier') : -1;
                    $query = null;
                    $query = new DbQuery();
                    $query->select('id_product');
                    $query->from('product_supplier');
                    $query->where('id_supplier = '.$id_supplier);
                    $suppliers_exec = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
                    
                    $suppliers = null;
                    foreach ($suppliers_exec as $supplier)
                            $suppliers[] = (int)$supplier['id_product'];

                    //If no data
                    if($id_supplier!=-1 && $suppliers==null)
                    {
                        $this->displayWarning($this->l('No data to export with this supplier !'));
                        return;
                    }
                    
                    if($suppliers != null)
                        $suppliers = array_unique($suppliers);

                    // Filter by manufacturer
                    $id_manufacturer = (Tools::isSubmit('id_manufacturer')) ? (int)Tools::getValue('id_manufacturer') : -1;
                    $query = null;
                    $query = new DbQuery();
                    $query->select('id_product');
                    $query->from('manufacturer', 'm');
                    $query->innerjoin('product', 'p', 'm.id_manufacturer = p.id_manufacturer');
                    $query->where('m.id_manufacturer = '.$id_manufacturer);
                    $manufacturers_exec = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
                    
                    $manufacturers = null;
                    foreach ($manufacturers_exec as $manufacturer)
                            $manufacturers[] = (int)$manufacturer['id_product'];
                    
                    //If no data
                    if($id_manufacturer!=-1 && $manufacturers==null)
                    {
                        $this->displayWarning($this->l('No data to export with this manufacturer !'));
                        return;
                    }
                    
                    if($manufacturers != null)
                        $manufacturers = array_unique($manufacturers);
                    
                    // Quantity filter
                    $table_quantity = 'quantity';
                    $moreless = (Tools::isSubmit('moreless') && in_array(Tools::getValue('moreless'), array('=','>','<'))) ? Tools::getValue('moreless') : -1;
                    $quantity = (Tools::isSubmit('quantity_filter')) ? (int)Tools::getValue('quantity_filter') : -1;
                    
                    if ($this->advanced_stock_management)
                    {
                        $id_warehouse = $this->getCurrentCoverageWarehouse();
                         
                        header('Content-type: text/csv');
                        header('Cache-Control: no-store, no-cache');
                        header('Content-disposition: attachment; filename=stock_'.date('Y-m-d_His').'.csv');
                        header('charset=iso-8859-1');
                        
                        // product attribute
                        $combination = new DbQuery();
                        $combination->select('
                            pa.id_product as id_product, 
                            pa.id_product_attribute as id_product_attribute, 
                            pa.reference as reference,
                            pa.ean13 as ean13, 
                            w.name as warehouse, 
                            wpl.id_warehouse_product_location,
                            wpl.location as location, 
                            area.name as areaname, 
                            subarea.name as subareaname');

                        //if ($id_warehouse != -1)
                                $combination->select('IFNULL(s.physical_quantity, 0) as physical_quantity, IFNULL(s.usable_quantity, 0) as usable_quantity');
                        //else
                                //$combination->select('SUM(IFNULL(s.physical_quantity, 0)) as physical_quantity, SUM(IFNULL(s.usable_quantity, 0)) as usable_quantity');

                        $combination->from('product_attribute', 'pa');
                        $combination->innerjoin('product', 'p', 'pa.id_product = p.id_product');
                        $combination->leftjoin('warehouse_product_location', 
                                                'wpl', 
                                                '(wpl.id_product = p.id_product AND wpl.id_product_attribute = IFNULL(pa.id_product_attribute, 0)'
                                                .($id_warehouse != -1 ? ' AND wpl.id_warehouse = '.(int)$id_warehouse : '')
                                                .')'    
                                            );
                        
                        $combination->leftjoin('stock', 's', '(s.id_product = pa.id_product AND s.id_product_attribute = IFNULL(pa.id_product_attribute, 0))');
                        $combination->leftjoin('warehouse', 'w','s.id_warehouse = w.id_warehouse');
                        $combination->leftjoin('erpip_warehouse_product_location', 'ewpl', '(wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location)');
                        $combination->leftjoin('erpip_zone', 'area', '(area.id_erpip_zone = ewpl.id_zone_parent)');
                        $combination->leftjoin('erpip_zone', 'subarea', '(subarea.id_erpip_zone = ewpl.id_zone)');

                        // apply filters
                        // warehouse
                        if ($id_warehouse != -1)
                        {
                                $combination->where('s.id_warehouse = '.(int)$id_warehouse.' OR wpl.id_warehouse = '.(int)$id_warehouse);
                                // area
                                if(Tools::isSubmit('area'))
                                {
                                    $combination->where('ewpl.id_zone_parent= '. Tools::getValue('area'));
                                    // sub area
                                    if(Tools::isSubmit('subarea'))
                                    $combination->where('ewpl.id_zone= '. Tools::getValue('subarea'));
                                }
                        }
                        if ($id_category != -1)
                                $combination->where('pa.id_product IN ('.implode (',', array_map('intval',$categories)).')');
                        if ($id_supplier != -1)
                                $combination->where('pa.id_product IN ('.implode (',', array_map('intval',$suppliers)).')');
                        if ($id_manufacturer != -1)
                                $combination->where('pa.id_product IN ('.implode (',', array_map('intval',$manufacturers)).')');
                        if ($moreless != -1)
                        {
                            if ($quantity > 0)
                            {
                                $where_quantity_filter = ' physical_quantity '.$moreless.' '.(int)$quantity;
                                if ($moreless == '=' && $quantity == 0 || $moreless == "<" && $quantity >0)
                                {
                                    $where_quantity_filter .= ' OR physical_quantity IS NULL ';
                                }
                            }
                            
                            $combination->where($where_quantity_filter);
                        }

                        $combination->groupBy('pa.id_product, pa.id_product_attribute, w.id_warehouse');
                        $combination->orderBy('pa.id_product, pa.id_product_attribute');
                        
                       

                        if( $this->controller_status == STATUS1)
                        {
                            $combination->limit($stckmgtfr);
                            $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits document editing to %d products'), $stckmgtfr);
                        }
                        
                        
                        $combinations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($combination);

                        
                        
                        // List of product id
                        $ids = array();
                        foreach ($combinations as $combination)
                                $ids[] = $combination['id_product'];

                        $ids = array_unique($ids);

                        // Product without attribute
                        $product = new DbQuery();
                        $product->select('p.id_product as id_product, 
                            0 as id_product_attribute, 
                            p.reference as reference,
                            p.ean13 as ean13, 
                            w.name as warehouse, 
                            wpl.id_warehouse_product_location,
                            wpl.location as location, 
                            area.name as areaname, 
                            subarea.name as subareaname');

                         //if ($id_warehouse != -1)
                             $product->select('IFNULL(s.physical_quantity, 0) as physical_quantity, IFNULL(s.usable_quantity, 0) as usable_quantity');
                        // else
                                //$product->select('SUM(IFNULL(s.physical_quantity, 0)) as physical_quantity, SUM(IFNULL(s.usable_quantity, 0)) as usable_quantity');

                        $product->from('product', 'p');                        
                        $product->leftjoin('warehouse_product_location', 
                                              'wpl', 
                                              '(wpl.id_product = p.id_product AND wpl.id_product_attribute = 0'
                                              .($id_warehouse != -1 ? ' AND wpl.id_warehouse = '.(int)$id_warehouse : '')
                                              .')'    
                                          );
                                
                        $product->leftjoin('stock', 's', '(s.id_product = p.id_product)');
                        $product->leftjoin('erpip_warehouse_product_location', 'ewpl', '(wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location)');
                        $product->leftjoin('warehouse', 'w','s.id_warehouse = w.id_warehouse');
                        $product->leftjoin('erpip_zone', 'area', '(area.id_erpip_zone = ewpl.id_zone_parent)');
                        $product->leftjoin('erpip_zone', 'subarea', '(subarea.id_erpip_zone = ewpl.id_zone)');
                        // apply filters
                        if ($id_warehouse != -1)
                        {
                                $product->where('s.id_warehouse = '.(int)$id_warehouse.' OR wpl.id_warehouse = '.(int)$id_warehouse);
                                // Area
                                if(Tools::isSubmit('area'))
                                {
                                    $product->where('ewpl.id_zone_parent= '. intval(Tools::getValue('area')));
                                    // sub area
                                    if(Tools::isSubmit('subarea'))
                                    $product->where('ewpl.id_zone= '. intval(Tools::getValue('subarea')));
                                }
                        }
                        if ($id_category != -1)
                                $product->where('p.id_product IN ('.implode(', ', array_map('intval', $categories)).')');
                        if ($id_supplier != -1)
                                $product->where('p.id_product IN ('.implode(', ', array_map('intval',$suppliers)).')');
                        if ($id_manufacturer != -1)
                                $product->where('p.id_product IN ('.implode(', ', array_map('intval',$manufacturers)).')');
                        if ($moreless != -1)
                        {
                            if ($quantity > 0)
                            {
                                $where_quantity_filter = ' physical_quantity '.$moreless.' '.(int)$quantity;
                                if ($moreless == '=' && $quantity == 0 || $moreless == "<" && $quantity >0)
                                {
                                    $where_quantity_filter .= ' OR physical_quantity IS NULL';
                                }
                            }
                            
                            $product->where($where_quantity_filter);
                        }
                        
                        if(count($ids) > 0)
                            $product->where("p.id_product NOT IN (".implode(',', array_map('intval', $ids)).")");
                        $product->groupBy('p.id_product, w.id_warehouse');
                        $product->orderBy('p.id_product');
                        

                        if( $this->controller_status == STATUS1)
                        {
                            $product->limit($stckmgtfr);
                            $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits document editing to %d products'), $stckmgtfr);
                        }
                        
                        $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($product);
                     
                        // $query = array_merge($products, $combinations);
                        $query = array_merge($products, $combinations);
                        
                        
                        
                        // we sort by id_product and id_product_attribute
                        usort($query, array($this, "idproductSort"));
                        
                         if( $this->controller_status == STATUS1)
                        {
                            $query = array_splice($query,0,$stckmgtfr);
                        }

                        $csv_header_columns = array(
                            $this->l('EAN13'),
                            $this->l('ID_PRODUCT'),
                            $this->l('ID_PRODUCT_ATTRIBUTE'),
                            $this->l('REFERENCE'),
                            $this->l('PHYSICAL_QUANTITY'),
                            $this->l('USABLE_QUANTITY'),
                            self::transformText($this->l('WAREHOUSE')),
                            $this->l('AREA'),
                            $this->l('SUBAREA'),
                            $this->l('LOCATION'),
                            $this->l('WARNING'),
                        );
                                
                        echo implode(';', $csv_header_columns)."\r\n";

                        // generate csv file
                        foreach ($query as $product)
                        {
                            //alert in the case where the product has stock in warehouse without be located in this warehouse
                            $warning = '';
                            if (is_null($product['id_warehouse_product_location']))
                                $warning = sprintf($this->l('Product has stock in %s warehouse without being registered in this warehouse !'), $product['warehouse']);
                            
                            $csv_value_columns = array(
                                self::transformText($product['ean13']),
                                $product['id_product'],
                                $product['id_product_attribute'],
                                self::transformText($product['reference']),
                                self::transformText($product['physical_quantity']),
                                self::transformText($product['usable_quantity']),
                                self::transformText($product['warehouse']),
                                self::transformText($product['areaname']),
                                self::transformText($product['subareaname']),
                                self::transformText($product['location']),
                                self::transformText($warning)
                            );
                                    
                            echo implode(';', $csv_value_columns)."\r\n";   
                        }
                        echo sprintf($this->l('You are using the free version of 1-Click ERP which limits the export to %d products'),$stckmgtfr);
                    }
                    else
                    {
                        // we work in different stock table while advanced stock is disabled
                        $table_stock = 'StockAvailable';
                        $table_quantity = 'quantity';

                        // create collection width current filter
                        $id_lang = Context::getContext()->language->id;
                        $stock = new Collection($table_stock, $id_lang);
                        
                        if ($id_category != -1)
                                $stock->where('id_product', 'in', $categories);
                        if ($id_supplier != -1)
                                $stock->where('id_product', 'in', $suppliers);
                        if ($id_manufacturer != -1)
                                $stock->where('id_product', 'in', $manufacturers);
                        
                        if ($moreless != -1)
                        {
                            if ($quantity > 0)
                            {
                                $where_quantity_filter = $table_quantity.' '.$moreless.' '.$quantity; 
                                if ($moreless == '=' && $quantity == 0 || $moreless == "<" && $quantity >0)
                                {
                                    $where_quantity_filter .= ' OR '.$table_quantity.' IS NULL';
                                }
                            }
                           
                            $stock->sqlWhere($where_quantity_filter);
                        }
                        
                        $stock->orderBy('id_product');
                        $stock->orderBy('id_product_attribute');
                        $stock->getAll();

                         if( $this->controller_status == STATUS1)
                        {
                            $stock = array_splice($stock,0,$stckmgtfr);
                            $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits document editing to %d products'), $stckmgtfr);
                        }
                        
                        // generation of CSV
                        $csv = new CSV($stock, $this->l('stock').'_'.date('Y-m-d_His'));
                        $csv->export();
                    }
                    die();
		}
	}

	/**/
	public function idproductSort($product1, $product2)
	{
		if ($product1['id_product'] == $product2['id_product'])
		{
			if ($product1['id_product_attribute'] == $product2['id_product_attribute'])
				return 0;
			else if ($product1['id_product_attribute'] < $product2['id_product_attribute'])
				return -1;
			return 1;
		}
		else if ($product1['id_product'] < $product2['id_product'])
			return -1;
		return 1;
	}

	/* */
	protected function renderCatalog()
	{
            $stckmgtfr = ERP_STCKMGTFR;
		if (Tools::isSubmit('export_catalog'))
		{
                    //OUPUT HEADERS
                    header('Pragma: public');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Cache-Control: private',false);
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename=catalog_'.date('Y-m-d_His').'.csv;');
                    header('Content-Transfer-Encoding: binary');

                    $id_warehouse = (int)Tools::getValue('id_warehouse');
                    $area = (int)Tools::getValue('area');
                    $subarea = (int)Tools::getValue('subarea');
                    
                    // GET COMBINATIONS
                    $combination = new DbQuery();

                    $select_combination = 'pa.id_product,
                                    pa.id_product_attribute,
                                    pa.reference,
                                    pa.ean13,
                                    IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
                                    p.price as price_product,
                                    pa.price as price_attribute,
                                    p.id_tax_rules_group,
                                    p.id_manufacturer,
                                    cl.name as category,
                                    CASE pa.wholesale_price WHEN 0.000000 THEN p.wholesale_price ELSE pa.wholesale_price END as wholesale_price,
                                    IFNULL( pa.weight, p.weight) as weight,
                                    pl.description,
                                    pl.description_short ';

                    // get product and product attribute of selected warehouse
                    if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != "-1")
                    {
                        $select_combination .=', wpl.location, z.name as area, sz.name as subarea';
                        $combination->innerjoin('warehouse_product_location', 'wpl', 'wpl.id_warehouse = '.$id_warehouse.' '
                                                . 'AND pa.id_product = wpl.id_product AND wpl.id_product_attribute = IFNULL(pa.id_product_attribute, 0)');
                        
                        $combination->leftjoin('erpip_warehouse_product_location', 'ewpl', '(wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location)');
                        $combination->leftjoin('erpip_zone', 'z', '(z.id_erpip_zone = ewpl.id_zone_parent)');
                        $combination->leftjoin('erpip_zone', 'sz', '(sz.id_erpip_zone = ewpl.id_zone)');
                        
                        // filter on area
                        if ($area != null && $subarea == null)
                            $combination->where('z.id_erpip_zone = '. (int)$area);
                            
                        // filter on area and sub area
                        if ($area != null && $subarea != null)
                        {
                            $combination->where('z.id_erpip_zone = '. (int)$area);
                            $combination->where('sz.id_erpip_zone = '. (int)$subarea);
                        }
                    }

                    $combination->select($select_combination);
                    $combination->from('product_attribute', 'pa');
                    $combination->innerjoin('product', 'p', 'pa.id_product = p.id_product');
                    $combination->innerjoin('product_lang', 'pl', 'pa.id_product = pl.id_product');
                    $combination->innerjoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
                    $combination->innerjoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
                    $combination->innerjoin('attribute_lang', 'al', 'al.id_attribute = pac.id_attribute AND al.id_lang='.(int)$this->context->language->id);
                    $combination->innerjoin('attribute_group_lang', 'agl', 'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang='.(int)$this->context->language->id);
                    $combination->innerjoin('category_lang', 'cl', 'cl.id_category = p.id_category_default AND cl.id_lang ='.(int)$this->context->language->id);
                    $combination->groupBy('pa.id_product, pa.id_product_attribute');
                    
                    if( $this->controller_status == STATUS1)
                        {
                            $combination->limit($stckmgtfr);
                            $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits document editing to %d products'), $order_free_limit);
                        }

                    $combinations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($combination);

		   // list of product ids
		   $ids = array();
		   foreach ($combinations as $combination)
				$ids[] = (int)$combination['id_product'];

			$ids = array_unique ($ids);

		   // GET PRODUCT WITHOUT COMBINATIONS
		   $product = new DbQuery();
                   
                   // Base query
                   $select_product = 'p.id_product,
                                    p.reference,
                                    p.ean13,
                                    pl.name as name,
                                    p.weight,
                                    pl.description,
                                    pl.description_short,
                                    p.price as price_product,
                                    p.id_tax_rules_group,
                                    p.id_manufacturer,
                                    cl.name as category,
                                    p.wholesale_price as wholesale_price';

                    // warehouse query
                    if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != "-1")
                    {
                        $select_product .=', wpl.location, z.name as area, sz.name as subarea';
                        $product->innerjoin('warehouse_product_location', 'wpl', 'wpl.id_warehouse = '.$id_warehouse.' AND p.id_product = wpl.id_product AND wpl.id_product_attribute = 0');
 
                        $product->leftjoin('erpip_warehouse_product_location', 'ewpl', '(wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location)');
                        $product->leftjoin('erpip_zone', 'z', '(z.id_erpip_zone = ewpl.id_zone_parent)');
                        $product->leftjoin('erpip_zone', 'sz', '(sz.id_erpip_zone = ewpl.id_zone)');
                        
                        // filter on area
                        if ($area != null && $subarea == null)
                            $product->where('z.id_erpip_zone = '. (int)$area);
                            

                        // filter on area and sub area
                        if ($area != null && $subarea != null)
                        {
                            $product->where('z.id_erpip_zone = '. (int)$area);
                            $product->where('sz.id_erpip_zone = '. (int)$subarea);
                        }
                    }
                    
                    $product->select($select_product);
                    $product->from('product', 'p');
                    $product->innerjoin('product_lang', 'pl', 'p.id_product = pl.id_product');
                    $product->innerjoin('category_lang', 'cl', 'cl.id_category = p.id_category_default AND cl.id_lang ='.(int)$this->context->language->id);
                   
                   // if we have attributes we filter for not having a product already listed with attributes
                   if(count($ids) > 0)
                        $product->where('p.id_product NOT IN ('.pSQL (implode(',' , array_map('intval', $ids))).') ');
                   
		   $product->groupBy('p.id_product');
                   
                   if( $this->controller_status == STATUS1)
                        {
                            $product->limit($stckmgtfr);
                            $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits document editing to %d products'), $order_free_limit);
                        }
                        
		   $products = Db::getInstance()->executeS($product);
                  
                    // merge product with product attribute
		   $query = array_merge($products, $combinations);
                   
                   if( $this->controller_status == STATUS1)
                        {
                            $query = array_splice($query,0,$stckmgtfr);
                        }

		   $nb_items = count($query);
		   for ($i = 0; $i < $nb_items; ++$i)
		   {
                        $item = &$query[$i];

                        // gets stock manager
                        $manager = StockManagerFactory::getManager();

                        // id_product_attribute pour un produit sans déclinaisons
                        if (!isset($item['id_product_attribute']))
                           $item['id_product_attribute'] = 0;

                        // gets quantities and valuation
                        $stock = new DbQuery();
                        $stock->select('SUM(physical_quantity) as physical_quantity');
                        $stock->select('SUM(usable_quantity) as usable_quantity');
                        $stock->select('SUM(price_te * physical_quantity) as valuation');
                        $stock->from('stock');

                        if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != "-1")
                        $stock->where('id_product ='.(int)$item['id_product'].' AND id_product_attribute = '.(int)$item['id_product_attribute'].' AND id_warehouse = ' .(int)$id_warehouse);
                        else
                        $stock->where('id_product ='.(int)$item['id_product'].' AND id_product_attribute = '.(int)$item['id_product_attribute']);

                        $res_stock = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($stock);

                        $item['physical_quantity'] = $res_stock['physical_quantity'];

                        // real quantity
                        if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != "-1")
                                $item['real_quantity'] = $manager->getProductRealQuantities($item['id_product'], $item['id_product_attribute'], $id_warehouse, true);
                        else
                                $item['real_quantity'] = $manager->getProductRealQuantities($item['id_product'], $item['id_product_attribute'], null, true);

                        // price tax include and tax
                        $price = new DbQuery();
                        $price->select('rate');
                        $price->from('tax', 't');
                        $price->innerjoin('tax_rule', 'tr', 'tr.id_tax = t.id_tax');
                        $price->where('t.id_tax = '.(int)$item['id_tax_rules_group']);
                        $price->where('tr.id_country = '.(int)$this->context->country->id);
                        $res_price = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($price);

                        $item['rate'] = $res_price['rate'];

                        // if we are in product
                        if (!isset($item['price_attribute']))
                           $item['price_ttc'] = ($item['price_product'] * ($res_price['rate'] / 100)) + $item['price_product'];
                        else
                        {
                           $price = $item['price_product'] + $item['price_attribute'];
                           $item['price_ttc'] = ($price * ($res_price['rate'] / 100)) + $price;
                        }

                        // get manufacturer
                        $item['manufacturer'] = Manufacturer::getNameById($item['id_manufacturer']);

                        // get image product id
                        $id_image = Product::getCover((int)$item['id_product']);

                        // there is an image ?
                        if ($id_image != false)
                        {
                                $image = new Image($id_image['id_image']);
                                $item['url_image'] = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
                        }
                        else
                                $item['url_image'] = $this->l('No image');

                        }
                        
                        // BASE CSV HEADER
                        $header = array(
                            $this->l('PRODUCT_ID_'),
                            $this->l('PRODUCT_ATTRIBUTE_ID'),
                            $this->l('SKU'),
                            $this->l('EAN13'),
                            $this->l('MANUFACTURER'),
                            $this->l('CATEGORY'),
                            $this->l('PRODUCT_NAME'),
                            $this->l('PRODUCT_WEIGHT'),
                            $this->l('DESCRIPTION'),
                            $this->l('DESCRIPTION_SHORT'),
                            $this->l('URL_IMAGE'),
                            $this->l('PHYSICAL_QTE'),
                            $this->l('REAL_QTY'),
                            $this->l('PURCHASE_PRICE'),
                            $this->l('PRICES_TAX_EXCL'),
                            $this->l('PRICES_TAX_INCL'),
                            $this->l('VAT_RATE'),
                        );
                        
                        // CSV WITH WAREHOUSE LOCATION
			if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != "-1")
                        {
                            if ($area != null && $subarea == null)
                                array_push($header, $this->l('AREA'));

                            // filter on area and sub area
                            if ($area != null && $subarea != null)
                                array_push($header, $this->l('AREA'), $this->l('SUBAREA'));
                            
                            array_push($header, $this->l('LOCATION'));
                        }
                        
                        // Print header
                        echo implode(';', $header)."\r\n";;

			// generate CSV file
			foreach ($query as $product)
			{
				if (!isset($product['price_attribute']))
					$product['price_attribute'] = 0;
                                
                                $content = array(
                                    $product['id_product'],
                                    $product['id_product_attribute'],
                                    $product['reference'],
                                    $product['ean13'],
                                    $product['manufacturer'],
                                    self::transformText($product['category']),
                                    self::transformText($product['name']),
                                    $product['weight'],
                                    self::transformText($product['description']),
                                    self::transformText($product['description_short']),
                                    self::transformText($product['url_image']),
                                    $product['physical_quantity'],
                                    $product['real_quantity'],
                                    round($product['wholesale_price'], 2),
                                    round($product['price_product'] + $product['price_attribute'], 2),
                                    round($product['price_ttc'], 2),
                                    round($product['rate'], 2)
                                );
                                
				if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != "-1")
				{
                                    if ($area != null && $subarea == null)
                                        array_push($content, $product['area']);
                                    
                                    // filter on area and sub area
                                    if ($area != null && $subarea != null)
                                         array_push($content, $product['area'], $product['subarea']);
                                    
                                    array_push($content, $product['location']);
				}
                                
                                echo implode(';', $content)."\r\n";
                                
			}
                        echo sprintf($this->l('You are using the free version of 1-Click ERP which limits the export to %d products'),$stckmgtfr);
			die();
		}
	}

	public function ajaxProcess()
	{
		if (Tools::isSubmit('task') && Tools::getValue('task') == 'getCategories')
			$this->ajaxGetCategories();

		elseif (Tools::isSubmit('task') && Tools::getValue('task') == 'getProductSupplierPrice')
			$this->ajaxGetProductSupplierPrice();
                elseif(Tools::isSubmit('task') && Tools::getValue('task') == 'getSupplierReference')
                {
                    include_once(_PS_MODULE_DIR_.'erpillicopresta/ajax/ajax.php');
                }
	}

	public function ajaxGetProductSupplierPrice()
	{
		//  If we have called the script with a term to search
		if (Tools::isSubmit('id_product') && Tools::isSubmit('id_product_attribute'))
		{
				require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProductSupplier.php');

				$id_product = Tools::getValue('id_product');
				$id_product_attribute = Tools::getValue('id_product_attribute');
				//$id_currency = Tools::getValue('id_currency', false) ? Tools::getValue('id_currency') : 1;

				/*  Price for all suppliers for the product */
				$supplier_prices = ErpProductSupplier::getAllProductSupplierPrice($id_product, $id_product_attribute, true);

				if (!empty($supplier_prices))
				{
                                    echo '<table class="table">';
                                    foreach ($supplier_prices as $price)
                                    {
                                        /*  If supplier price  = 0 we take the basic one */
                                        if ($price['product_supplier_price_te'] == '0.000000')
                                                $supplier_price = ErpStock::getWholesalePrice($id_product, $id_product_attribute);
                                        else
                                                $supplier_price = $price['product_supplier_price_te'];

                                        /*  writing of the HTML table */
                                        echo  '<tr>
                                                    <td>'.$price['supplier_name'].' : </td>
                                                    <td> &nbsp; '.number_format($supplier_price , 2, '.', ' ').'€</td>
                                                </tr>';
                                    }
                                    echo '</table>';
				}
				else
						echo $this->l('No price found for this product!');
	}

			die();
	}

	public function ajaxGetCategories()
	{
            //  If we have called the script with a term to search
            if (Tools::isSubmit('id_product'))
            {
                    $id_product = (int)Tools::getValue ('id_product');

                    /*  Get product categories */
                    $categories = Product::getProductCategoriesFull($id_product, (int)$this->context->language->id);

                    /* If we have some we return the table content */
                    if (count ($categories) > 0)
                    {
                        $i = 0;

                        /*  determination of the number of column in the table in function of the number of category to display */
                        $maxCells = (count ($categories) < 10) ? 1 : round ((count ($categories) / 10));

                        echo '<table style="text-align:left" class="table" width="100%">';
                        foreach ($categories as $category)
                        {
                                        if ($i == $maxCells)
                                                        $i = 0;

                                        if ($i == 0) echo '<tr>';
                                        echo '<td>'.$category['name'].'</td>';
                                        if ($i == $maxCells) echo '</tr>';

                                        $i++;
                        }
                        echo '</table>';
                    }
                    else
                            echo $this->l('There is no categorie');
            }
		exit();
	}

	/* RJMA
	 * Add traduction for controller AdminAdvancedStock
	*/
	protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = false)
	{
		if (!empty($class))
		{
			$str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminAdvancedStock');
			$str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
			return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
		}
	}


	public function renderNameColumn($name, $data)
	{
		$html = '<a target="_blank" href="index.php?controller=AdminProducts&id_product='.(int)$data['id_product'].'&updateproduct&token='.$this->product_token.'">
				'.$name.'
		</a> ';

		return $html;
	}

	public function renderCategoryNameColumn($category_name, $data)
	{
		$html = '<a href="#" class="category" title="'.$this->l('Categories').'" rel="index.php?controller=AdminAdvancedStock&ajax=1&id_product='.(int)$data['id_product'].'&task=getCategories&token='.$this->token.'">
				<img style="width: 16px; height: 16px;" alt="products" src="../img/admin/search.gif" class="icon-search" />
				'.$category_name.'
		</a> ';

		return $html;
	}

	public function renderFirstSupplierRefColumn($first_supplier_ref, $data)
	{
		$html = '--';
		if (!empty($first_supplier_ref))
		{
			$html = '<a href="#" class="supplier_ref" title="'.$this->l('Suppliers references').'" rel=index.php?controller=AdminAdvancedStock&ajax=1&id_product='.(int)$data['id_product'].'&id_product_attribute='.(int)$data['id_product_attribute'].'&task=getSupplierReference&token='.$this->token.'">
					<img style="width: 16px; height: 16px;" alt="products" src="../img/admin/search.gif" class="icon-search" />
					'.$first_supplier_ref.'
			</a> ';
		}
		return $html;
	}

	public function renderSupplierPriceColumn($supplier_price, $data)
	{
		$rel = 'index.php?controller=AdminAdvancedStock&ajax=1&id_product='.(int)$data['id_product'].'&id_product_attribute='.(int)$data['id_product_attribute'].'&task=getProductSupplierPrice&token='.$this->token;

		if ($this->advanced_stock_management)
		   $rel .= '&id_currency='.(int)$data['id_currency'];

		$html = '<a href="#" class="supplier_price" title="'.$this->l('Supplier price').'" rel="'.$rel.'">
				<img style="width: 16px; height: 16px;" alt="products" src="../img/admin/search.gif" class="icon-search" />
				'.Tools::displayPrice($supplier_price).'
		</a> ';

		return $html;
	}

	public function renderAdvancedStockManagementColumn($advanced_stock_management, $data)
	{
                $warning = '';
                
		if (!$this->advanced_stock_management)
		{
			if ($advanced_stock_management == '1')
			   $warning = '<img src="../img/admin/warning.gif" class="cluetip" title="'.$this->l('Product IS USED with advanced stock management').'"/>';
		}
		else
		{
			if ($advanced_stock_management == '0')
				$warning = '<img src="../img/admin/warning.gif" class="cluetip" title="'.$this->l('Product IS NOT USED with advanced stock management').'"/>';
		}
                
                // in the case where the product has stock in warehouse without be located in this warehouse
                if ((int)Tools::getValue('id_warehouse') > 0 && (int)$data['id_warehouse_product_location'] == '0')
                {
                    $warehouse_name = Warehouse::getWarehouseNameById(Tools::getValue('id_warehouse'));
                    if (!empty($warehouse_name))
                    {
                        $message = sprintf($this->l('Product has stock in "%s" warehouse without being registered in this warehouse !'), $warehouse_name);
                        $warning .= ' &nbsp; <img src="../img/admin/warning.gif" class="cluetip" title="'.$message.'"/>';
                    }
                }
                                
                return $warning;
	}
        
        public function renderLocationColumn($id_product,$data)
        {
            if (Tools::isSubmit('id_warehouse') & (int)Tools::getValue('id_warehouse') > 0)
            {
                $html = '
                    
                    <input placeholder="'.$this->l('Area').'"
                        type="text"
                        class="area"
                        value="'.(empty($data['area_name']) ? '' : $data['area_name']).'"/>
                    
                    <input type="hidden" 
                        class="id_area_hidden"
                        name="data_location['.$id_product.']['.(int)$data['id_product_attribute'].'][area]"
                        value="'.(empty($data['id_area']) ? '' : $data['id_area']).'" />

                    <input placeholder="'.$this->l('Sub area').'"
                        type="text"
                        class="sub_area"
                        value="'.(empty($data['sub_area_name']) ? '' : $data['sub_area_name']).'" />
                    
                    <input type="hidden"
                        class="id_sub_area_hidden"
                        name="data_location['.$id_product.']['.(int)$data['id_product_attribute'].'][sub_area]"
                        value="'.(empty($data['id_sub_area']) ? '' : $data['id_sub_area']).'" />

                    <input placeholder="'.$this->l('Location').'" type="text"
                        class="location"
                        name="data_location['.$id_product.']['.(int)$data['id_product_attribute'].'][location]"
                        value="'.(empty($data['location']) ? '' : $data['location']).'" />
            
                    <input type="hidden" name="data_location['.$id_product.']['.(int)$data['id_product_attribute'].'][id_warehouse_product_location]"
                        value="'.(int)$data['id_warehouse_product_location'].'" >
                            
                    <input type="hidden"
                        name="data_location['.$id_product.']['.(int)$data['id_product_attribute'].'][id_erpip_warehouse_product_location]"
                        value="'.(int)$data['id_erpip_warehouse_product_location'].'" >';
            }
            else {
                $html = $this->l('No warehouse selected');
            }
            return $html;
        }

}