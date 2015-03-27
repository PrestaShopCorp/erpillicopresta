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
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStock.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStockMvt.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStockMvtReason.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpWarehouseProductLocation.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpZone.php');
//require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminInventoryController extends IPAdminController
{
	private $advanced_stock_management = false;
	private $controller_status = 0;
	private $advanced_stock_token = null;

	public $id_erpip_inventory;
	public $name;
	public $inventory_values;
	public $id_warehouse;
	public $id_employee;
	public $firstname;
	public $lastname;

        public static $id_erpip_inventory_static = -1;
	private static $local_store = array();

        // Step 1 : Build and array of products
	public function __construct()
	{
		$this->bootstrap = true;
		$this->table = 'product';
		$this->className = 'Product';
		$this->list_no_link = true;

		// template path
		$this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';

		parent::__construct();

                // Get the stock manager type and send it to template
		$this->advanced_stock_management = $this->tpl_list_vars['advanced_stock_management'] = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

		// get controller status
		$this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminInventory'));

		$this->advanced_stock_token = Tools::getAdminToken('AdminAdvancedStock'.(int)(Tab::getIdFromClassName('AdminAdvancedStock')).(int)$this->context->employee->id);

		$this->product_token = Tools::getAdminToken('AdminProducts'.(int)(Tab::getIdFromClassName('AdminProducts')).(int)$this->context->employee->id);

		$this->mvt_stock_reason =  ErpStockMvtReason::getStockMvtReasons((int)$this->context->language->id);

                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
                
		// JMA
                // Record get variables cookies id_warehouse, areaFilter and subareaFilter for Ajax
		if (!Tools::isSubmit('ajax'))
		{
                    $this->setCookie('id_warehouse', (Tools::isSubmit('id_warehouse')) ? Tools::getValue('id_warehouse') : self::getFirstWarehouse());
                    $this->setCookie('areaFilter', Tools::getValue('areaFilter'));
                    $this->setCookie('subareaFilter', Tools::getValue('subareaFilter'));
		}

                // Buidl product array
		$global = array
		(
                    'id_product' => array(
                            'title' => 'ID',
                            'width' => 10,
                            'search' => false,
                            'class' => 'id_product'
                    ),
                    'picture' => array(
                            'title' => $this->l('Picture'),
                            'align' => 'center',
                            'image' => 'p',
                            'width' => 70,
                            'orderby' => false,
                            'filter' => false,
                            'search' => false
                    ),
                    'category_name' => array(
                            'title' => $this->l('Category'),
                            'search' => false,
                            'callback' => 'renderCategoryNameColumn'
                    ),
                    'reference' => array(
                            'title' => $this->l('SKU'),
                            'search' => false
                    ),
                    'first_supplier_ref' => array(
                            'title' => $this->l('Supplier reference'),
                            'search' => false,
                            'callback' => 'renderFirstSupplierRefColumn'
                    ),
                    'product_name' => array(
                                'title' => $this->l('Label'),
                                'search' => false,
                                'callback' => 'renderNameColumn'
                    )
		);

                // if advanced stock manager is inactif, only show the usable quantity in shop
		if (!$this->advanced_stock_management)
		{
                    $quantity = array (
                            'quantity' => array(
                                        'title' => $this->l('Quantity'),
                                        'width' => 50,
                                        'search' => false,
                                        'class' => 'quantity',
                                        'orderby' => false
                        )
                    );
                    $global = array_merge((array)$global, (array)$quantity);
		}
		else // else show the pysical stock
		{
                    $quantity = array
                    (
                            'physical_quantity' => array(
                                    'title' => $this->l('Physical quantity'),
                                    'width' => 50,
                                    'search' => false,
                                    'class' => 'physical_quantity',
                                    'orderby' => false
                            ),
                            // 'usable_quantity' => array('title' => $this->l('Usable quantity'), 'width' => 50, 'search' => false, 'orderby' => false),
                            //'real_quantity' => array('title' => $this->l('Real quantity'), 'width' => 50, 'hint' => $this->l('Physical quantity (usable) - Customer orders + supplier orders'), 'search' => false, 'orderby' => false),
                            'location' => array(
                                'title' => $this->l('Location'), 
                                'width' => 200, 
                                'search' => false, 
                                'orderby' => false,
                                'callback' => 'renderLocationColumn'
                            )
                    );
                    $global = array_merge((array)$global, (array)$quantity);
		}

		$edit = array
		(
			'mvt_reason' => array(
                                'title' => $this->l('Movement reason'),
                                'width' => 50,
                                'search' => false,
                                'orderby' => false,
                                'callback' => 'renderMvtReasonColumn'
                        ),
			'new_quantity' => array(
                                'title' => $this->l('Found quantity'),
                                'width' => 50,
                                'hint' => $this->l('What you actually have'),
                                'search' => false,
                                'orderby' => false,
                                'callback' => 'renderColumnNewQuantity'
                        ),
		);

		$this->fields_list = array_merge((array)$global, (array)$edit);

                // if we already have inventory values recorded, send them (pagination & filters)
		if (Tools::isSubmit('inventory_values') && Tools::getValue('inventory_values') != '')
			$this->context->smarty->assign(array('inventory_values' => Tools::getValue('inventory_values')));
		else
			$this->context->smarty->assign(array('inventory_values' => ''));

                // if we get stock gap values already recorded, send them (pagination & filters)
		if (Tools::isSubmit('gap_values') && Tools::getValue('gap_values') != '')
			$this->context->smarty->assign(array('gap_values' => Tools::getValue('gap_values')));
		else
			$this->context->smarty->assign(array('gap_values' => ''));
	}
        
        public function initContent()
	{
           if( $this->controller_status == STATUS1)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Do not limit yourself to a batch of 10 products, take advantage of the Light version of the Stock Management area for €79.99 before tax or €8.00/month before tax. Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            } else if( $this->controller_status == STATUS2)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Run your inventory by zone and/or in offline mode and perform a visual export of your stock for just €20.00 before tax or €1.00/month before tax. Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            } else if( $this->controller_status == STATUS3)
            {
                $this->informations[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Activate additional features in your TIME SAVER module in the Module section of your back-office! Go to your back-office, under the module tab, page 1-Click ERP!').'</a>';
            }
	
            parent::initContent();
            
	}

        // Step 2 : override render
	public function renderList()
	{
            $this->toolbar_title = $this->l('Products list');
        
                if (Tools::isSubmit('id_display'))
                       self::$currentIndex .= '&id_display='.(int)Tools::getValue('id_display');

               if (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != '-1')
                       self::$currentIndex .= '&id_warehouse='.(int)Tools::getValue('id_warehouse');

               if (Tools::isSubmit('areaFilter'))
                       self::$currentIndex .= '&areaFilter='.Tools::getValue('areaFilter');

               if (Tools::isSubmit('subareaFilter'))
                       self::$currentIndex .= '&subareaFilter='.Tools::getValue('subareaFilter');

               if (Tools::isSubmit('id_category') && Tools::getValue('id_category') != '-1')
                       self::$currentIndex .= '&id_category='.(int)Tools::getValue('id_category');

               if (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != '-1')
                       self::$currentIndex .= '&id_supplier='.(int)Tools::getValue('id_supplier');

               if (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != '-1')
                       self::$currentIndex .= '&id_manufacturer='.(int)Tools::getValue('id_manufacturer');

                // Get display type
		$id_display = $this->getCurrentValue('id_display');

                // qubquery : return the first provider reference for principal product
		$this->_select = '
				cl.name as category_name,
				i.id_image,
				a.id_product as mvt_reason,
                                area.name as area_name, 
                                sub_area.name as sub_area_name, 
                                wpl.location as location,
				a.id_product as new_quantity,
				(
					SELECT ps.product_supplier_reference
					FROM '._DB_PREFIX_.'product_supplier ps
					WHERE ps.id_product = a.id_product
					AND ps.id_product_attribute = 0
					LIMIT 1
				)as first_supplier_ref,
                                (
                                    EXISTS(SELECT pa.id_product FROM '._DB_PREFIX_.'product_attribute pa WHERE pa.id_product = a.id_product LIMIT 1)
                                ) 
                                as have_attribute,
                                ';
                
		$this->_join = ' LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.id_product = a.id_product)'
                             . 'INNER JOIN '._DB_PREFIX_.'product_lang pl ON (a.id_product = pl.id_product AND pl.id_lang = '.(int)$this->context->language->id.')
                                 INNER JOIN '._DB_PREFIX_.'category_lang cl ON (a.id_category_default = cl.id_category AND cl.id_lang = '.(int)$this->context->language->id.')
                                 LEFT JOIN '._DB_PREFIX_.'image i ON a.id_product = i.id_product ';
                
                $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON (wpl.id_product = a.id_product AND wpl.id_product_attribute = IFNULL(pa.id_product_attribute, 0))';
                $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'erpip_warehouse_product_location ewpl ON wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location ';
                $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'erpip_zone area ON area.id_erpip_zone = ewpl.id_zone_parent ';
                $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'erpip_zone sub_area ON sub_area.id_erpip_zone = ewpl.id_zone ';
                
                // Render 1 : mix products and declination to sort by area
		if ($id_display == 1)
                {
                    $this->_select .= 'IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as product_name,
                                        IFNULL((CONCAT(a.id_product, ";", pa.id_product_attribute)), a.id_product) as id_product,';
                    $this->_join .='
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)
                            LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)
                            LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.id_attribute = pac.id_attribute AND al.id_lang = '.(int)$this->context->language->id.')
                            LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.(int)$this->context->language->id.')
                            ';
                }
                // Render 2 : ONLY products, with details by declensions by ajaxProcess
                else
                {
                    $this->_select .= 'pl.name as product_name,';   
                }
			

                $this->tpl_list_vars['advanced_stock_token'] = $this->advanced_stock_token;

		// FILTERS

                // Init filters variables
		$this->tpl_list_vars['id_category'] = -1;
		$this->tpl_list_vars['id_supplier'] = -1;
		$this->tpl_list_vars['id_manufacturer'] = -1;
		$this->tpl_list_vars['id_warehouse'] = -1;
		$this->tpl_list_vars['areaFilter'] = -1;
		$this->tpl_list_vars['subareaFilter'] = -1;
		$this->tpl_list_vars['id_display'] = 0;

                // Add additional filters
		$this->tpl_list_vars['warehouses'] = Warehouse::getWarehouses();
		$this->tpl_list_vars['categories'] = Category::getSimpleCategories((int)$this->context->language->id);
		$this->tpl_list_vars['suppliers'] = Supplier::getSuppliers();
		$this->tpl_list_vars['manufacturers'] = Manufacturer::getManufacturers();
		$this->tpl_list_vars['controller_status'] = $this->controller_status;

                // Get inventory containers
		$this->tpl_list_vars['containers'] = ErpInventory::getContainers();

                // get conf stock gap
		$this->tpl_list_vars['gap_stock'] = Configuration::getGlobalValue('ERP_GAP_STOCK');

                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
                $this->tpl_list_vars['erp_feature'] = ErpFeature::getFeaturesWithToken($this->context->language->iso_code);
                $this->tpl_list_vars['template_path'] = $this->template_path;

                // get default inventory reasons
		if ($this->context->language->iso_code == 'fr')
		{
                    $this->tpl_list_vars['reason_increase'] = ErpStockMvtReason::existsByName('Augmentation d\'inventaire');
                    $this->tpl_list_vars['reason_decrease'] = ErpStockMvtReason::existsByName('Diminution d\'inventaire');
		}
		else
		{
                    $this->tpl_list_vars['reason_increase'] = ErpStockMvtReason::existsByName('Increase of inventory');
                    $this->tpl_list_vars['reason_decrease'] = ErpStockMvtReason::existsByName('Decrease of inventory');
		}

                // specify advanced stock manager or not
		if ($this->advanced_stock_management)
		{
                        // Get the selected warehouse, if there's not, get the first one 
                        if (($id_warehouse = $this->getCurrentValue('id_warehouse')) == false)
                        {
                            $id_warehouse = $this->getCookie('id_warehouse');
                            $this->tpl_list_vars['id_warehouse'] = $id_warehouse;
                        }
                        
                        // filters warehouse location
			$area = $this->getCurrentValue('areaFilter');
			$subarea = $this->getCurrentValue('subareaFilter');

                        $this->tpl_list_vars['areas'] = ErpZone::getZonesName($id_warehouse);
			$this->tpl_list_vars['sub_areas'] = $area ? ErpZone::getZonesName($id_warehouse, 'sub_area', $area) : array();
                        
                        // if an area AND an under area are specified, we filter the area and the under area for the specified area
			if ($area != false && $subarea != false)
			{
                            $this->_where .= ' AND wpl.id_warehouse = '.(int)$id_warehouse.'
                                                AND area.id_erpip_zone = "'.(int)$area.'" AND sub_area.id_erpip_zone = '.(int)$subarea; 
                             
                            $this->_group = 'GROUP BY a.id_product';
			}
                        // if we just specify one area, we filter for the warehouse the specified area
			elseif ($area != false)
			{
                            $this->_where .= ' AND wpl.id_warehouse='.(int)$id_warehouse.' AND area.id_erpip_zone = '.(int)$area; 
                            
				//$this->_where .= ' AND area.id_erpip_zone = '.(int)$area;
				
                                if ($id_display == 1)
                                    $this->_group = 'GROUP BY a.id_product, pa.id_product_attribute';
                                else
                                    $this->_group = 'GROUP BY a.id_product';
			}

                        // Else we filter by the warehouse
			else
			{
                            $this->_where .= ' AND wpl.id_warehouse = '.(int)$id_warehouse;
                            
                            if ($id_display == 1)
                                $this->_group = 'GROUP BY a.id_product, pa.id_product_attribute';
                            else
                                $this->_group = 'GROUP BY a.id_product';
                            
			}
		}
		else
			if ($id_display == 1)
                                $this->_group = 'GROUP BY a.id_product, pa.id_product_attribute';
                            else
                                $this->_group = 'GROUP BY a.id_product';

                // filter the query with applied filters


		//category filter
		if (($id_category = $this->getCurrentValue('id_category')) != false)
		{
			$this->_where .= ' AND a.id_product IN (
                                    SELECT cp.id_product
                                    FROM '._DB_PREFIX_.'category_product cp
                                    WHERE cp.id_category = '.(int)$id_category.'
                            )';
		}

		// provider filter
                if (($id_supplier = $this->getCurrentValue('id_supplier')) != false)
		{
			$this->_where .= ' AND a.id_product IN (
                                SELECT ps.id_product
                                FROM '._DB_PREFIX_.'product_supplier ps
                                WHERE ps.id_supplier = '.(int)$id_supplier.'
                        )';
		}

		// Brand filter
		if (($id_manufacturer = $this->getCurrentValue('id_manufacturer')) != false)
			$this->_where .= ' AND a.id_manufacturer = '.(int)$id_manufacturer;

		$this->displayInformation($this->l('Be careful, if you are using advanced [respectively simple] stock management, only products using advanced [respectively simple] stock management will be exported.'));
                $this->displayInformation($this->l('In advanced sotck managment, products that are not stocked in a warehouse will not appear.'));

                // Show information or confirm message / error at the end of the inventory
		/*switch(Tools::getValue('submitFilterproduct'))
		{
			case 0:
				$this->displayInformation($this->l('New inventory'));
			break;
			case 1:
				$this->confirmations[] = $this->l('Inventory completed');
			break;
			case 2:
				$this->errors[] = Tools::displayError('There has been a problem while handling products');
			break;
			default:
				$this->displayInformation($this->l('New inventory'));
			break;
		}*/

                // Add plugin simple tooltip
		$this->addJqueryPlugin('cluetip', _MODULE_DIR_.'erpillicopresta/js/cluetip/');

		// add jquery dialog
		$this->addJqueryUI('ui.dialog');

		// add plugin validity
		$this->addJqueryPlugin('validity.min', _MODULE_DIR_.'erpillicopresta/js/validity/');

		// Load JS
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/inventory_tools.js');
		$this->addJS(_MODULE_DIR_.'erpillicopresta/js/inventory.js');

		// Load CSS
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/jquery.validity.css');
		$this->addCSS(_MODULE_DIR_.'erpillicopresta/css/jquery.cluetip.css');

		$list = parent::renderList();

		return $list;
	}

        // Step 3 : Last possibility to update render (add field according of those already exists)
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
            if( $this->controller_status == STATUS1)
            {
                $limit = ERP_IVTFR;
                $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits the display to %d products'), $limit);
            }
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

                // Get the render type
		$id_display = $this->getCurrentValue('id_display');

		// Get the current warehouse
		$id_warehouse = $this->getCurrentValue('id_warehouse');
		if ($id_warehouse == '')
			$id_warehouse = $this->getCookie('id_warehouse');


                // Send number products to template to show/hide div-popup
		$nb_items = count($this->_list);
		$this->tpl_list_vars['nb_items'] = $nb_items;

		for ($i = 0; $i < $nb_items; ++$i)
		{
			$item = &$this->_list[$i];
                        
                        if (!isset($item['product_name']) && isset($item['name']))
                            $item['product_name'] = $item['name'];
                        
                        // set Ids depends of the current render
			if (strrpos($item['id_product'], ';') > 0)
			{
				$ids = explode(";", $item['id_product']);
				$id_product = $ids[0];
				$id_product_attribute = $ids[1];
			}
			else
			{
				$id_product = $item['id_product'];
				$id_product_attribute = 0;
			}

			$query = new DbQuery();

                        // Products declensions (if render is mixed, force on an empty array)
			$attributes_ids = ($id_display == 1) ? array() : Product::getProductAttributesIds((int)$id_product);

                        $item['have_attribute'] = false;

                        // add additional column only if we re on a product without declension
                        // else they will be shown on declension
			if (count($attributes_ids) == 0)
			{
                                // add quantity noted

                                // If advanced stock manager is inactif, only show quantity
				if (!$this->advanced_stock_management)
				{
                                        // get the quantity
                                        $query->select('quantity');
                                        $query->from('stock_available');
                                        $query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute);
                                        $query->orderBy('id_stock_available DESC');

                                        // execute query
                                        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                                        // Add column to array
                                        $item['quantity'] = $res['quantity'];
                                        
				}
				else
				{
                                    // get the physical and usable quantity
                                        $query->select('physical_quantity');
                                        $query->select('usable_quantity');
                                        $query->from('stock');
                                        $query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute.
                                                                        ' AND id_warehouse ='.(int)$id_warehouse);

                                        // Execute query
                                        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                                        // Add column to array
                                        $item['physical_quantity'] = $res['physical_quantity'];
                                        $item['usable_quantity'] = $res['usable_quantity'];


                                        // the real quantity depends of the warehouse
                                        $manager = StockManagerFactory::getManager();
                                        $item['real_quantity'] = $manager->getProductRealQuantities($id_product,
                                                                $id_product_attribute,
                                                                ($this->getCookie('id_warehouse') == -1 ? null : array($this->getCookie('id_warehouse'))),
                                                                true);

                                        // add location
                                        $location = ErpWarehouseProductLocationClass::getCompleteLocation($id_product, $id_product_attribute,
                                                                        ($this->getCookie('id_warehouse') == -1 ? 1 : $this->getCookie('id_warehouse')));

                                        $item['location'] = $location;
				}
                                
                                
			}
                        else
                        {
                            $item['have_attribute'] = true;
                        }
                            
		}
               
                // display 0: add detail column +-
		if ($id_display == 0)
			$this->addRowAction('details');

		// display 1 : sort products by location
		/*else
                   usort($this->_list, array('AdminInventoryController', 'cmp'));*/
	}

        // Step 4: Build declension array for a chosen product
	public function ajaxProcess()
	{
                // if a product is selected
		if (Tools::isSubmit('id'))
		{
			$id_product = (int)Tools::getValue('id');
			$token = Tools::getValue('token');

                        // Get declensions
			$datas = $this->getCombinations($id_product);

			$i = 0;

                        // get html of new quantity column accorging to advanced stock statut
                        $html_new_quantity = $this->renderColumnNewQuantity($id_product, array('have_attribute' => true));

                        // Add class and js action on displayed datas
			foreach ($datas as $ligne)
			{
				// foreach product
				foreach ($ligne as $key => $data)
				{
                                        // Add a class on each column and add a link on the provider reference for the tooltip
					if ($key == 'product_name' || $key == 'name')
						$datas[$i]['product_name'] = '<span class="product_name">'.$data.'</span>';

					if ($key == 'first_supplier_ref')
						if (!empty($data))
                                                {
                                                    
                                                    $datas[$i]['first_supplier_ref'] = '<a href="#" class="supplier_ref" rel="index.php?controller=AdminInventory&ajax=1';
                                                    $datas[$i]['first_supplier_ref'] .= 'id_product='.$datas[$i]['id_product'].'&task=getSupplierReference';
                                                    $datas[$i]['first_supplier_ref'] .= '&token='.$token.'">'.$data.'&nbsp';
                                                    $datas[$i]['first_supplier_ref'] .= '<img src="themes/default/img/icon-search.png" /></a>';
                                                }
						else
                                                    $datas[$i]['first_supplier_ref'] = '--';

					if ($key == 'id_product')
						$datas[$i]['id_product'] = '<span class="id_product">'.$data.'</span>';
				}

				$query = new DbQuery();

                                // get id_product and id_product_attribute
				$ids = explode(';', $ligne['id_product']);
				$id_product = (int)$ids[0];
				$id_product_attribute = (int)$ids[1];

				$is_selected = '';

                                // get the current warehouse
				$id_warehouse = $this->getCookie('id_warehouse');

                                // add quantity columns
                                // if advanced stock manager inactive, only return the usable quantity in shop
				if (!$this->advanced_stock_management)
				{
					$query->select('quantity');
					$query->from('stock_available');
					$query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute);

					// execute query
					$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

					// add columns to array
					$datas[$i]['quantity'] = '<span class="quantity">'.(int)$res['quantity'].'</span>';
				}
				else
				{
                                        // get the physical and usable quantity
					$query = new DbQuery();
					$query->select('physical_quantity');
					$query->select('usable_quantity ');
					$query->from('stock');
					$query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute.
							' AND id_warehouse ='.(int)$id_warehouse);

					// Execute query
					$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                                        // Add columns to array if stock exists
					if ($res != false)
					{
						$datas[$i]['physical_quantity'] = '<span class="physical_quantity">'.$res['physical_quantity'].'</span>';
						$datas[$i]['usable_quantity'] = $res['usable_quantity'];

						// La quantité réelle dépend de l'entrepot
                                                // the real quantity depends of the warehouse
						$manager = StockManagerFactory::getManager();
						$datas[$i]['real_quantity'] = $manager->getProductRealQuantities($id_product,
												$id_product_attribute,
												($this->getCookie('id_warehouse') == -1 ? null : array($this->getCookie('id_warehouse'))),
												true);
					}
					else // else stock 0
						$datas[$i]['physical_quantity'] = $datas[$i]['usable_quantity'] = $datas[$i]['real_quantity'] = 0;

					// JMA
                                        // use warehouse cookie to get locations
					if ($this->getCookie('id_warehouse') != '')
					{
                                            $location = ErpWarehouseProductLocationClass::getCompleteLocation($id_product, $id_product_attribute,
								($this->getCookie('id_warehouse') == -1 ? 1 : $this->getCookie('id_warehouse')));
					}
					else
					{
                                            $location = ErpWarehouseProductLocationClass::getCompleteLocation($id_product, $id_product_attribute,
								($this->getCookie('id_warehouse') == -1 ? 1 : $this->getCookie('id_warehouse')));
					}
                                        
                                        if (!empty($location['CompleteArea']))
                                        {
                                            // split to get area, sub area and location
                                            $location = explode(';', $location['CompleteArea']);

                                            if (count($location)>0)
                                            {
                                                // Area, sub area, location
                                                $datas[$i]['location'] = '<span>'.$this->l('Area').' : </span>'.$location[0];			
                                                $datas[$i]['location'] .= '<br/><span>'.$this->l('Subarea').' : </span>'.$location[1];				
                                                $datas[$i]['location'] .= '<br/><span>'.$this->l('Location').' : </span>'.$location[2];
                                            }
                                            else
                                                $datas[$i]['location'] = '--';
                                        }
                                        else
                                            $datas[$i]['location'] = '--';                                        
				}

                                // add ajustment reason
				$array_reason = ErpStockMvtReason::getStockMvtReasons((int)$this->context->language->id);
				$datas[$i]['mvt_reason'] = '<select name="reason" class="table_select">';
				$datas[$i]['mvt_reason'] .= '<option value="-1" '.$is_selected.'>--</option>';
				foreach ($array_reason as $reason)
				{
					$id_reason = $reason['id_stock_mvt_reason'];
					$name_reason = $reason['name'];

					$datas[$i]['mvt_reason'] .= '<option value="'.(int)$id_reason.'">'.$name_reason.'</option>';
				}
				$datas[$i]['mvt_reason'] .= '</select>';

                                // get the found quantity
				$datas[$i]['new_quantity'] = $html_new_quantity;
				$i++;
			}
                        
			$attributes = array(
				'data' => $datas,
				'fields_display' => $this->fields_list
			);

			$json = Tools::jsonEncode($attributes);

			echo $json;
			die();
		}
                 elseif(Tools::isSubmit('task') && Tools::getValue('task') == 'getSupplierReference')
                        include_once(_PS_MODULE_DIR_.'erpillicopresta/ajax/ajax.php');
	}

	/*  get the current warehouse */
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

        // Get a value in get/post
	protected function getCurrentValue($var)
	{
            if (Tools::isSubmit($var))
            {
                $value = Tools::getValue($var);

                $this->tpl_list_vars[$var] = $value;
                return ($value == -1) ? false : $value;
            }
	}

	/* Complète le tableau avec les déclinaisons.
	 * Si on a un id_product : la fonction est appelé pour afficher les déclinaisons d'un produit
	 * Sinon c'est pour toutes les afficher
	 */
	private function getCombinations($id_product = null)
	{
            // Get declensions
            $query = new DbQuery();

            // qubquery : get the first provider reference for declensions
            $query->select(
            'IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
            if (i.id_image = 0, ii.id_image, i.id_image) as id_image,
            cl.name as category_name,
            (
                    SELECT ps.product_supplier_reference
                    FROM '._DB_PREFIX_.'product_supplier ps
                    WHERE ps.id_product = a.id_product
                    AND ps.id_product_attribute = a.id_product_attribute
                    LIMIT 1
            )
            as first_supplier_ref, 
            CONCAT(a.id_product , ";" , a.id_product_attribute) as id_product ');

            $query->from('product_attribute', 'a');
            $query->join( ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (a.id_product = pl.id_product AND pl.id_lang = '.(int)$this->context->language->id.')
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)
                            LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)
                            LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.id_attribute = pac.id_attribute AND al.id_lang = '.(int)$this->context->language->id.')
                            LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.(int)$this->context->language->id.')
                            LEFT JOIN '._DB_PREFIX_.'product_attribute_image i ON a.id_product_attribute = i.id_product_attribute
                            LEFT JOIN '._DB_PREFIX_.'image ii ON a.id_product = ii.id_product
                            LEFT JOIN '._DB_PREFIX_.'product p ON a.id_product = p.id_product
                            INNER JOIN '._DB_PREFIX_.'category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '.(int)$this->context->language->id.')  
                            LEFT JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON wpl.id_product = a.id_product AND wpl.id_product_attribute = a.id_product_attribute
                            LEFT JOIN '._DB_PREFIX_.'erpip_warehouse_product_location ewpl ON wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location 
                            LEFT JOIN '._DB_PREFIX_.'erpip_zone area ON area.id_erpip_zone = ewpl.id_zone_parent
                            LEFT JOIN '._DB_PREFIX_.'erpip_zone sub_area ON sub_area.id_erpip_zone = ewpl.id_zone');

            // if advanced stock manager, filter by warehouse
            if ($this->advanced_stock_management)
                           $query = $this->filterByWarehouse($query);

            // If we just call for a declensions product : display 0
            if ($id_product != null)
                            $query->where('a.id_product = '.(int)$id_product);

            // if we call for all declensions: display 1, apply filters
            else
            {
                // query filter by applied filters

                // category filter
                if (Tools::isSubmit('id_category') && Tools::getValue('id_category') != -1)
                                $query->where('a.id_product IN (
                                                                        SELECT cp.id_product
                                                                        FROM '._DB_PREFIX_.'category_product cp
                                                                        WHERE cp.id_category = '.intval(Tools::getValue('id_category')).')');

                // Provider filter
                if (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != -1)
                                $query->where('(a.id_product, a.id_product_attribute) IN (
                                                                        SELECT ps.id_product, ps.id_product_attribute
                                                                        FROM '._DB_PREFIX_.'product_supplier ps
                                                                        WHERE ps.id_supplier = '.intval(Tools::getValue('id_supplier')).')');

                // Brand filter
                if (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != -1)
                                $query->where('p.id_manufacturer = '.intval(Tools::getValue('id_manufacturer')));

            }
            $query->groupBy('a.id_product, a.id_product_attribute');

            // Re-construct LIMIT
            $product_pagination = (int)$this->context->cookie->product_pagination;
            $currentPage = ($this->context->cookie->submitFilterproduct == false) ? 1 : $this->context->cookie->submitFilterproduct;
            $max = $product_pagination * $currentPage;
            $min = $max - $product_pagination;

            if ($this->getCurrentValue('id_display') == 1)
                            $query->orderBy("a.id_product, a.id_product_attribute ASC LIMIT $min, $max");

            // Execute query
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            
            
            
	}

        // Filter query to only show warehouse products, area and sub area selected
	public function filterByWarehouse($query)
        {
                // JMA
                // Try to get url warehouse id, else get in the cookie
                if (Tools::isSubmit('id_warehouse') == false)
                                $id_warehouse = $this->getCookie('id_warehouse');
                else
                                $id_warehouse = Tools::getValue('id_warehouse');

                // JMA
                // try to get area filter in url, else get in the cookie
                if (Tools::isSubmit('areaFilter') == false)
                {
                        if ($this->getCookie('areaFilter') != "-1" && $this->getCookie('areaFilter') != '')
                            $area = $this->getCookie('areaFilter');
                        else // if first time on page
                            $area = false;
                }
                else
                        $area = $this->getCurrentValue('areaFilter');

                // JMA
                // try to get subarea filter in url, else get in the cookie
                if (Tools::isSubmit('subareaFilter') == false)
                {
                        if ($this->getCookie('subareaFilter') != "-1" && $this->getCookie('subareaFilter') != '')
                            $subarea = $this->getCookie('subareaFilter');
                        else // if first time on page
                            $subarea = false;
                }
                else
                        $subarea = $this->getCurrentValue('subareaFilter');

                // if an area and a sub area are specified, filters on the warehouse on area and sub area
                if ($area != false && $subarea != false)
                {
                    $query->where('wpl.id_warehouse = '.(int)$id_warehouse.' 
                                    AND area.id_erpip_zone = '.(int)$area.' AND sub_area.id_erpip_zone = '.(int)$subarea );        
                }
                
                // if we only specified and area, filter on warehouse and area
                elseif ($area != false) {
                    $query->where('wpl.id_warehouse = '.(int)$id_warehouse.' AND area.id_erpip_zone = '.(int)$area);
                }
                // else filter on warehouse
                else
                        $query->where('wpl.id_warehouse = '.(int)$id_warehouse);
                
                return $query;
	}

	/*  content of the toolbar */
	public function initToolbar()
	{
                // build link export grid PDF : get filters
		$link = (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != -1) ? '&id_warehouse='.Tools::getValue('id_warehouse') : '';
		$link .= (Tools::isSubmit('id_category') && Tools::getValue('id_category') !=-1) ? '&id_category='.Tools::getValue('id_category') : '';
		$link .= (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != -1) ? '&id_supplier='.Tools::getValue('id_supplier') : '';
		$link .= (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != -1) ? '&id_manufacturer='.Tools::getValue('id_manufacturer') : '';
		$link .= (Tools::isSubmit('areaFilter') && Tools::getValue('areaFilter') != -1) ? '&area='.Tools::getValue('areaFilter') : '';
		$link .= (Tools::isSubmit('subareaFilter') && Tools::getValue('subareaFilter') != -1) ? '&subarea='.Tools::getValue('subareaFilter') : '';

		if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
		{
			// stock gap for 1.5
                        if ($this->controller_status)
                        {
                                $this->toolbar_btn['stats'] = array(
					'short' => $this->l('Display the stock gap'),
					'href' => $this->context->link->getAdminLink('AdminStockGap'),
					'desc' => $this->l('Reports'),
				);
                        }

                        // export grid pdf only if area/sub area are shown
			if ($this->getCurrentValue('id_display') == '1')
			{
						$this->toolbar_btn['save-calendar'] = array(
							'short' => $this->l('Print the advanced inventory grid'),
							//'href' => $this->context->link->getAdminLink('AdminInventory').'&submitAction=generateInventoryPDF&advanced=true'.$link,
                            'href' => 'javascript:jAlert(\'Cette fonctionnalité sera activée prochainnement\');',
							'target' => 'blank',
							'desc' => $this->l('Export advanced grid'),
						);
			}

			

			if ($this->controller_status)
			{
                           if($this->controller_status == STATUS3){
				// Application quantity in mass
				$this->toolbar_btn['duplicate'] = array(
											'short' => $this->l('Apply the found quantity as the physical quantity'),
											'href' => 'javascript:void(0)',
											'desc' => $this->l('Copy quantities'),
							);

				// EXPORT CSV
				$this->toolbar_btn['export-csv-orders'] = array(
											'short' => $this->l('Export CSV file'),
											'href' => $this->context->link->getAdminLink('AdminInventory').'&export_csv'.$link,
											'desc' => $this->l('Offline inventory (CSV file)'),
							);

				// IMPORT CSV
				$this->toolbar_btn['save-and-stay'] = array(
											'short' => $this->l('New inventory with file'),
											'href' => 'javascript:void(0)',
											'desc' => $this->l('Import CSV file'),
							);
                                // Export PDF grid
                                $this->toolbar_btn['preview'] = array(
						'short' => $this->l('Print the simple inventory grid'),
						'href' => $this->context->link->getAdminLink('AdminInventory').'&submitAction=generateInventoryPDF&advanced=false'.$link,
						'target' => 'blank',
						'desc' => $this->l('Export simple grid'),
					);
                           }
                           else
                           {
                               $text = addslashes($this->l('Tu use this functionnality please switch to the PRO Version.'));
                               // Application quantity in mass
				$this->toolbar_btn['duplicate'] = array(
											'short' => $this->l('Apply the found quantity as the physical quantity'),
											'js' => 'cancelBubble(event, \''.$text.'\');',
											'desc' => $this->l('Copy quantities'),
							);

				// EXPORT CSV
				$this->toolbar_btn['export-csv-orders'] = array(
											'short' => $this->l('Export CSV file'),
											'js' => 'cancelBubble(event, \''.$text.'\');',
											'desc' => $this->l('Offline inventory (CSV file)'),
							);

				// IMPORT CSV
				$this->toolbar_btn['save-and-stay'] = array(
											'short' => $this->l('New inventory with file'),
											'js' => 'cancelBubble(event, \''.$text.'\');',
											'desc' => $this->l('Import CSV file'),
							);
                                // Export PDF grid
                                $this->toolbar_btn['preview'] = array(
						'short' => $this->l('Print the simple inventory grid'),
						'js' => 'cancelBubble(event, \''.$text.'\');',
						'desc' => $this->l('Export simple grid'),
					);
                           }
			}

                        // select an inventory container and record
			$this->toolbar_btn['save'] = array(
							'short' => $this->l('Save inventory'),
							'href' => 'javascript:void(0)',
							'desc' => $this->l('Save'),
					);
		}


	}

	public function initPageHeaderToolbar()
	{
                // build export link grid PDF: get filters
		$link = (Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != -1) ? '&id_warehouse='.Tools::getValue('id_warehouse') : '';
		$link .= (Tools::isSubmit('id_category') && Tools::getValue('id_category') !=-1) ? '&id_category='.Tools::getValue('id_category') : '';
		$link .= (Tools::isSubmit('id_supplier') && Tools::getValue('id_supplier') != -1) ? '&id_supplier='.Tools::getValue('id_supplier') : '';
		$link .= (Tools::isSubmit('id_manufacturer') && Tools::getValue('id_manufacturer') != -1) ? '&id_manufacturer='.Tools::getValue('id_manufacturer') : '';
		$link .= (Tools::isSubmit('areaFilter') && Tools::getValue('areaFilter') != -1) ? '&area='.Tools::getValue('areaFilter') : '';
		$link .= (Tools::isSubmit('subareaFilter') && Tools::getValue('subareaFilter') != -1) ? '&subarea='.Tools::getValue('subareaFilter') : '';

		parent::initPageHeaderToolbar();
		// stocks gap
                
                if ($this->controller_status)
                {
                    $this->page_header_toolbar_btn['stats'] = array(
                            'short' => $this->l('Display the stock gap'),
                            'href' => $this->context->link->getAdminLink('AdminStockGap'),
                            'desc' => $this->l('Reports'),
                    );
                }


		// Export PDF grid
		$this->page_header_toolbar_btn['preview'] = array(
                    'short' => $this->l('Print the simple inventory grid'),
                    'href' => $this->context->link->getAdminLink('AdminInventory').'&submitAction=generateInventoryPDF&advanced=false'.$link,
                    'target' => 'blank',
                    'desc' => $this->l('Export simple grid'),
                );

		if ($this->controller_status)
		{

                    if($this->controller_status == STATUS3){
                        // Apply mass quantity
			$this->page_header_toolbar_btn['duplicate'] = array(
										'short' => $this->l('Apply the same quantity as the physical quantity'),
										'href' => 'javascript:void(0)',
										'desc' => $this->l('Copy quantities'),
						);
                        // EXPORT CSV
			$this->page_header_toolbar_btn['download'] = array(
										'short' => $this->l('Export CSV file'),
										'href' => $this->context->link->getAdminLink('AdminInventory').'&export_csv'.$link,
										'desc' => $this->l('Offline inventory (CSV file)'),
						);

			// IMPORT CSV
			$this->page_header_toolbar_btn['save-and-stay'] = array(
										'short' => $this->l('New inventory with file'),
										'href' => 'javascript:void(0)',
										'desc' => $this->l('Import CSV file'),
						);
                        // export grid pdf only if display area / sub area
                        if ($this->getCurrentValue('id_display') == '1')
                        {
                            $this->page_header_toolbar_btn['save-calendar'] = array(
                                    'short' => $this->l('Print the advanced inventory grid'),
                                    //'href' => $this->context->link->getAdminLink('AdminInventory').'&submitAction=generateInventoryPDF&advanced=true'.$link,
                                    'href' => 'javascript:jAlert("'.$this->l('This functionnality would be released soon.').'");',	
                                    'target' => 'blank',
                                    'desc' => $this->l('Export advanced grid'),
                            );
                        }
                     }
                     else
                     {
                         $text = addslashes($this->l('To use this functionnality switch to PRO Version.'));
                        // Apply mass quantity
			$this->page_header_toolbar_btn['duplicate'] = array(
										'short' => $this->l('Apply the same quantity as the physical quantity'),
										'js' => 'cancelBubble(event, \''.$text.'\');',
										'desc' => $this->l('Copy quantities'),
						);
                        // EXPORT CSV
			$this->page_header_toolbar_btn['download'] = array(
										'short' => $this->l('Export CSV file'),
										'js' => 'cancelBubble(event, \''.$text.'\');',
										'desc' => $this->l('Offline inventory (CSV file)'),
						);

			// IMPORT CSV
			$this->page_header_toolbar_btn['save-and-stay'] = array(
										'short' => $this->l('New inventory with file'),
										'js' => 'cancelBubble(event, \''.$text.'\');',
										'desc' => $this->l('Import CSV file'),
						);  
                        // export grid pdf only if display area / sub area
                        if ($this->getCurrentValue('id_display') == '1')
                        {
                            $this->page_header_toolbar_btn['save-calendar'] = array(
                                    'short' => $this->l('Print the advanced inventory grid'),
                                    //'js' => 'cancelBubble(event, \''.$text.'\');',
                                    'href' => 'javascript:jAlert("'.$this->l('This functionnality would be released soon.').'");',	
                                    'desc' => $this->l('Export advanced grid'),
                            );
                        }
                     }
		}

                // Select inventory container and record
		$this->page_header_toolbar_btn['save'] = array(
						'short' => $this->l('New inventory'),
						'href' => 'javascript:void(0)',
						'desc' => $this->l('Save'),
				);

	}

	private static function cmp($a, $b)
	{
            return strcmp($a['location']['CompleteArea'], $b['location']['CompleteArea']);
	}

	/*  Treatment display controller */
	public function postProcess()
	{
				//create manual inventory
				if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'submitCreateInventory')
					$this->processCreateInventory();

		// Import CSV
		else if (isset($_FILES['file']) && Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'submitCreateInventoryFromCsv')
		{
                       
                        // ASA - Security Audit
                        $_FILES['file']['name'] = str_replace("\0","",$_FILES['file']['name']);
                        
			// get extention file
			$file_extension = strrchr($_FILES['file']['name'], '.');

                        // allowed exention
                        $allowed_extensions = array('.csv');

                        $file_name = basename($_FILES['file']['name']);

                        if (!in_array($file_extension, $allowed_extensions))
                        {
                                $this->errors[] = Tools::displayError($this->l('The uploaded file is not a CSV !'));
                                return;
                        }

                        // max file size
                        $max_file_size = 1048576; //1 Mo

                        //file size
                        $file_size = filesize($_FILES['file']['tmp_name']);

                        if ($file_size > $max_file_size)
                        {
                                $this->errors[] = Tools::displayError($this->l('Your CSV file should not weight more than 1Mo !'));
                                return;
                        }

			// if error
			if (!empty($_FILES['file']['error']))
			{
                            switch ($_FILES['file']['error'])
                            {
                                    case UPLOAD_ERR_INI_SIZE:
                                                    $this->errors[] = Tools::displayError($this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess.'));
                                                    break;
                                    case UPLOAD_ERR_FORM_SIZE:
                                                    $this->errors[] = Tools::displayError($this->l('The uploaded file exceeds the post_max_size directive in php.ini.
                                                                                    If your server configuration allows it, you may add a directive in your .htaccess, for example:'))
                                                    .'<br/><a target="_blank" href="'.$this->context->link->getAdminLink('AdminMeta').'" >
                                                    <code>php_value post_max_size 20M</code> '.
                                                    $this->errors[] = Tools::displayError($this->l('(click to open "Generators" page)')).'</a>';
                                    break;
                                    case UPLOAD_ERR_PARTIAL:
                                                    $this->errors[] = Tools::displayError($this->l('The uploaded file was only partially uploaded. Please try again.'));
                                    break;
                                    case UPLOAD_ERR_NO_FILE:
                                                    $this->errors[] = Tools::displayError($this->l('No file was uploaded'));
                                    break;
                            }
			}

                        // if no error
                        if (count($this->errors) == 0 )
                        {

                                //we format file name
                                // $file_name = strtr($file_name,
                                         // 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
                                         // 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
										 
								$file_name = Tools::replaceAccentedChars($file_name);

                                $file_name = preg_replace('/([^.a-z0-9]+)/i', '-', $file_name);
                                $file_name = str_replace('.csv', '-'.date('Y_m_d_his').'.csv', $file_name);

                                // try to upload
                                if (@move_uploaded_file($_FILES['file']['tmp_name'], _PS_MODULE_DIR_.'erpillicopresta/imports/'.$file_name))
                                {
                                    $directory = explode("_", Tools::getValue('id_inventory'));
                                    $id_erpip_inventory = $directory[0];

                                    // if directory has only one element, mean it s a new directory and we get the name in the dedicated field
                                    if (count($directory) > 1)
                                                    $name = $directory[1];
                                    else
                                                    $name = Tools::getValue('new_inventory');

                                    $id_warehouse = Tools::getValue('selected_warehouse');
                                    $id_employee = $this->context->employee->id;
                                    $firstname = $this->context->employee->firstname;
                                    $lastname = $this->context->employee->lastname;

                                    $inventory_values = '';

                                    /* ------------------ TREATMENT CSV ------------*/

                                    // 2 - open file
                                    $handle = $this->openCsvFile($file_name);

                                    // 3 - Browse file
                                    $i = 0;
                                    while (($data = fgetcsv($handle, 0, ";")))
                                    {
                                            // 4 - reconstruct inventory_values
                                            /*
                                            $id_product = $data[0];
                                            $id_product_attribute = $data[1];
                                            $area = $data[5];
                                            $subarea = $data[6];
                                            $location = $data[7];
                                            $physical_quantity = $data[8];
                                            $found_quantity = $data[9];
                                            */
                                            //idproduct==6|idproductattribute==0|idreason==109|area==null|subarea==null|location==|physicalquantity==61|foundquantity==20_

                                            // don't use the first line : headers
                                            if ($i > 0)
                                            {
                                                    $inventory_values .= 'idproduct=='.$data[0].'|';
                                                    $inventory_values .= 'idproductattribute=='.$data[1].'|';
                                                    //$inventory_values .= 'idreason==|area=='.$data[-1].'|';
                                                    $inventory_values .= 'area=='.$data[5].'|';
                                                    $inventory_values .= 'subarea=='.$data[6].'|';
                                                    $inventory_values .= 'location=='.$data[7].'|';

                                                    if (isset($data[8]))
                                                                    $inventory_values .= 'physicalquantity=='.$data[8].'|';

                                                    if (isset($data[9]))
                                                                    $inventory_values .= 'foundquantity=='.(int)$data[9].'_';
                                            }

                                            $i++;
                                    }

                                    // 5 - prepare inventory
                                    $this->id_erpip_inventory = $id_erpip_inventory;
                                    $this->name = $name;
                                    $this->id_warehouse = $id_warehouse;
                                    $this->id_employee = $id_employee;
                                    $this->firstname = $firstname;
                                    $this->lastname = $lastname;
                                    $this->advanced_stock_management = $this->advanced_stock_management;
                                    $this->inventory_values = $inventory_values;
                                    $this->createContainer();
                                }
                                else
                                        $this->errors[] = $this->l('An error occurred while uploading and copying the file. Please try again or contact the customer service.');
                        }
		}

		// Export CSV
		if (Tools::isSubmit('export_csv'))
			$this->renderCSV();

		// Export PDF
		if (Tools::isSubmit('submitAction') && Tools::getValue('submitAction') == 'generateInventoryPDF')
			$this->processGenerateInventoryPDF();

		parent::postProcess();
	}

	/*  Export CSV */
	protected function renderCSV()
	{
		if (Tools::isSubmit('export_csv'))
		{
			/* FILTRES */

			// Category filter
			$id_category = (Tools::isSubmit('id_category')) ? intval(Tools::getValue('id_category')) : -1;
			$query = new DbQuery();
			$query->select('id_product');
			$query->from('category_product');
			$query->where("id_category = $id_category");
			$categories = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

			$i = 0;
			foreach ($categories as $category)
			{
				$categories[$i] = $category['id_product'];
				$i++;
			}

			$categories = implode(',', $categories);
			$query = null;

			// Provider filter
			$id_supplier = (Tools::isSubmit('id_supplier')) ? Tools::getValue('id_supplier') : -1;

			// Brand filter
			$id_manufacturer = (Tools::isSubmit('id_manufacturer')) ? Tools::getValue('id_manufacturer') : -1;

			// Location filter
			//$area = (Tools::isSubmit('area')) ? Tools::getValue('area') : -1;
			//$subarea = (Tools::isSubmit('subarea')) ? Tools::getValue('subarea') : -1;


			/* GENERATION CSV */

			// header
			header('Content-type: text/csv; charset=utf-8');
			header('Cache-Control: no-store, no-cache');
			header('Content-disposition: attachment; filename="inventory_grid.csv"');

                        // Get product list
			$query = null;
			$query = new DbQuery();
			$query->select(
						'p.id_product,
						IF(pa.id_product_attribute, pa.reference, p.reference) as reference,
						p.ean13,
						IFNULL(pa.id_product_attribute, 0) as id_product_attribute,
			IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
			p.id_product, IFNULL(pa.id_product_attribute, 0) as id_product_attribute');

			$query->from('product', 'p');

			$query->leftjoin('product_attribute', 'pa', 'p.id_product= pa.id_product');
			$query->leftjoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
			$query->leftjoin('attribute', 'atr', 'atr.id_attribute= pac.id_attribute');
			$query->leftjoin('attribute_lang', 'al', '(al.id_attribute= pac.id_attribute AND al.id_lang='.(int)$this->context->language->id.')');
			$query->leftjoin('attribute_group_lang', 'agl', '(agl.id_attribute_group= atr.id_attribute_group AND agl.id_lang='.(int)$this->context->language->id.')');
			$query->leftjoin('product_lang', 'pl', '(p.id_product = pl.id_product AND pl.id_lang ='.(int)$this->context->language->id.')');

			// Apply filters
			if ($id_category != -1)
				$query->where('p.id_product IN('.pSQL ($categories).')');
			if ($id_supplier != -1)
				$query->where('p.id_supplier = '.(int)$id_supplier);
			if ($id_manufacturer != -1)
				$query->where('p.id_manufacturer = '.(int)$id_manufacturer);

			$id_warehouse = $this->getCookie('id_warehouse');

                        // Apply warehouse filter, area, sub area only in active stock manager
			if ($this->advanced_stock_management && $id_warehouse != -1)
			{
                            $query->select('wpl.location, wpl.id_warehouse, z.name as area, sz.name as subarea');
                            $query->leftjoin('warehouse_product_location', 'wpl', '(p.id_product = wpl.id_product AND wpl.id_product_attribute = IFNULL(pa.id_product_attribute, 0))');
                            $query->leftjoin('erpip_warehouse_product_location', 'ewpl', '(wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location)');
                            $query->leftjoin('erpip_zone', 'z', '(z.id_erpip_zone = ewpl.id_zone_parent)');
                            $query->leftjoin('erpip_zone', 'sz', '(sz.id_erpip_zone = ewpl.id_zone)');
                            
                            
                            $area = (Tools::getValue('area') == null) ? -1 : Tools::getValue('area');
                            $subarea = (Tools::getValue('subarea') == null) ? -1 : Tools::getValue('subarea');
                            
                            // Warehouse filter
                            $query->where('wpl.id_warehouse = '.(int)$id_warehouse);

                            // Area filter
                            if ($area != -1 && $subarea == -1)
                                $query->where('z.id_erpip_zone = '. (int)$area);
                            
                            
                            // area and sub area filter
                            if ($area != -1 && $subarea != -1)
                            {
                                $query->where('z.id_erpip_zone = '. (int)$area);
                                $query->where('sz.id_erpip_zone = '. (int)$subarea);
                            }
			}

			$query->groupBy('pa.id_product_attribute, p.id_product');
			$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

			// Get the physical quantity
			$nb_items = count($products);

			for ($i = 0; $i < $nb_items; ++$i)
			{
                                $item = &$products;
                                if ($this->advanced_stock_management)
                                {
                                            $query = new DbQuery();
                                            $query->select('physical_quantity');
                                            $query->from('stock');
                                            $query->where('id_product = '.(int)$item[$i]['id_product'].' AND id_product_attribute = '.(int)$item[$i]['id_product_attribute'].
                                                                            ' AND id_warehouse ='.(int)$item[$i]['id_warehouse']);

                                            // Execute query
                                            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
                               }
                                    else
                                            $res['physical_quantity'] = (int)Product::getQuantity($item[$i]['id_product'], (int)$item[$i]['id_product_attribute']);

                               // add column to array
                               $item[$i]['physical_quantity'] = $res['physical_quantity'];
			}

			// write headers column
			$keys = array(
                                'id_product',
                                'id_product_attribute',
                                'reference',
                                'ean',
                                'name',
                                'area',
                                'subarea',
                                'location',
                                'physical_quantity',
                                'found_quantity'
                        );

			echo sprintf("%s\n", implode(';', $keys));

			// write datas
			foreach ($products as $product)
			{
                            if ($this->advanced_stock_management)
                            {
                                // Location
                                $product['zone'] = $product['area'];
                                $product['sous_zone'] = $product['subarea'];
                                $product['location'] = $product['location'];

                                    if (!StockAvailable::dependsOnStock((int)$product['id_product']))
                                            continue;
                            }
                            else
                            {
                                    if (StockAvailable::dependsOnStock((int)$product['id_product']))
                                            continue;
                            }

                            // Still not have quantity, set 0
                            $physical_quantity = ($product['physical_quantity'] == '') ? 0 : (int)$product['physical_quantity'];

                            $content_csv = array( 
                                    $product['id_product'],
                                    $product['id_product_attribute'],
                                    $product['reference'],
                                    $product['ean13'],
                                    self::transformText($product['name'])
                                );
                            // define optionnal array to set specific field to the advanced stock manager
                            $optional = array();
                            if($this->advanced_stock_management)
                            {
                                $optional = array(
                                    self::transformText($product['zone']),
                                    self::transformText($product['sous_zone']),
                                    self::transformText($product['location'])
                                );
                            }
                            
                            $end = array(
                                $physical_quantity.'; '.PHP_EOL
                            );
                            
                            // Merge $content_csv with advanced stock manager array
                            $content_csv = array_merge($content_csv, $optional, $end);
                            
                            echo implode(';', $content_csv);
			}
			die();
		}
	}

	protected function openCsvFile($file)
	{
	   $handle = fopen(_PS_MODULE_DIR_.'erpillicopresta/imports/'.(string)preg_replace('/\.{2,}/', '.', $file), 'r');

	   if (!$handle)
				$this->errors[] = Tools::displayError('Cannot read the .CSV file. Please choose a new CSV file.');

	   for ($i = 0; $i < (int)Tools::getValue('skip'); ++$i)
				fgetcsv($handle, MAX_LINE_SIZE, $this->separator);

	   return $handle;
	}

	/* JMA */
        // add translation for AdminInventory controller
	protected function l($string, $class = 'AdminInventory', $addslashes = false, $htmlentities = false)
	{
            if (!empty($class))
            {
                    // Send controller name to static method of our module
                    $str = erpillicopresta::findTranslation('erpillicopresta', $string, 'AdminInventory');
                    $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
                    return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
            }
	}

	/*  create cookie */
	private function setCookie($key, $val)
	{
            $this->context->cookie->{$key} = $val;
	}

	/*  get cookie */
	private function getCookie($key)
	{
            return $this->context->cookie->{$key};
	}


	/*  generate PDF of inventory grid */
	public function processGenerateInventoryPDF()
	{
            require_once _PS_MODULE_DIR_.'erpillicopresta/models/InventoryProduct.php';

            $id_warehouse = (int)Tools::getValue('id_warehouse');
            $id_category = (int)Tools::getValue('id_category');
            $id_supplier = (int)Tools::getValue('id_supplier');
            $id_manufacturer = (int)Tools::getValue('id_manufacturer');
            $area = Tools::getValue('area');
            $subarea = Tools::getValue('subarea');

            // get products
            $inventoryProduct = new InventoryProduct();
            $inventoryGrid = $inventoryProduct->getInventoryGrid($id_warehouse, $id_category, $id_supplier, $id_manufacturer, $area, $subarea);

            // Generate PDF --> Go HTMLTemplateInventory.php (landscape or portrait, depends of the template to display
            if (Tools::getValue('advanced') == 'true')
                    $orientation = 'L';
            else
                    $orientation = 'P';

            require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/pdf/HTMLTemplateInventory.php');
            
             if( $this->controller_status == STATUS1)
            {
                $inventoryGrid = array_splice($inventoryGrid,0,ERP_IVTFR);
                $inventoryGrid[] = array(
                    'name' => sprintf($this->l('You are using the free version of 1-Click ERP which limits the display to %d products'),ERP_IVTFR) ,
                    'reference' => '', 
                    'ean' => '',
                    'id_product' => '',
                    'id_warehouse' => '',
                    'id_product_attribute' => '',
                    'manufacturer_name' => '',
                    'zone' => '',
                    'sous_zone' => '', 
                    'location' =>  '',
                    'quantity' => '',
                    'image' => ''
                );
            }

            $pdf = new PDF(array($inventoryGrid) , 'Inventory', Context::getContext()->smarty);
            $pdf->render(true, $orientation);
	}

	/*
        * JMA
         * Static function to get the first warehouse non deleted
        */
	public static function getFirstWarehouse()
	{
            $query = new DbQuery();
            $query->select('id_warehouse');
            $query->from('warehouse');
            $query->where('deleted = 0');
            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

		/*   */
	public function processCreateInventory()
	{
            if (Tools::isSubmit('id_inventory') && Tools::isSubmit('name') && Tools::isSubmit('inventory_values') && Tools::isSubmit('advanced_stock_management')
            && Tools::isSubmit('id_warehouse') && Tools::isSubmit('id_employee') && Tools::isSubmit('firstname') && Tools::isSubmit('lastname'))
            {
                    require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpInventory.php');
                    require_once(_PS_MODULE_DIR_.'erpillicopresta/models/InventoryProduct.php');

                    $this->id_erpip_inventory = (int)Tools::getValue('id_inventory');
                    $this->name = Tools::getValue('name');
                    $this->inventory_values = Tools::getValue('inventory_values');
                    $this->advanced_stock_management = Tools::getValue('advanced_stock_management');
                    $this->id_warehouse = (int)Tools::getValue('id_warehouse');
                    $this->id_employee = (int)Tools::getValue('id_employee');
                    $this->firstname = Tools::getValue('firstname');
                    $this->lastname = Tools::getValue('lastname');
                    $this->createContainer();
            }
            else
                    $this->errors[] = Tools::displayError($this->l('Parameters are missing to complete the inventory. Make sure you filled out a name for the inventory, that you are in adavanced stock management, and that the identity of the employee is completed (first name and last name).'));

            if (!count($this->errors))
            {
                // display confirm message
                $token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
                $redirect = self::$currentIndex.'&token='.$token;
                $this->redirect_after = $redirect.'&conf=4';
            }
        }

        public function renderColumnNewQuantity($id_product, $data)
        {
            if (!$this->advanced_stock_management)
            {
                    if (!StockAvailable::dependsOnStock((int)$id_product))
                    {
                            if (!$data['have_attribute'] || Tools::isSubmit('ajax'))
                                
                                    return '<input type="text" class="filled_quantity" name="filled_quantity" size="5" />';
                    }
                    else
                            return  '<img src="../img/admin/warning.gif" class="cluetip" title="'.$this->l('Product IS USED with advanced stock management').'"/>';
            }
            else
            {
                if (StockAvailable::dependsOnStock((int)$id_product))
                {
                    if (!$data['have_attribute'] || Tools::isSubmit('ajax'))
                    {
                         return  '<input type="text" class="filled_quantity" name="filled_quantity" size="5" />';
                    }       
                }
                else
                    return '<img src="../img/admin/warning.gif" class="cluetip" title="'.$this->l('Product IS NOT USED with advanced stock management and/or you chose to specify available quantities manually. (Catalog > YourProduct > Quantities)').'"/>';
                    
            }
        }


        /*
	* Create the container
	*/
	public function createContainer()
	{
                // only if we get all the necessary values to the inventory
		if ($this->id_erpip_inventory != '' 
                        && $this->name != '' 
                        && $this->advanced_stock_management != '' 
                        && $this->inventory_values != ''
			&& $this->id_employee != '' 
                        && $this->firstname != '' 
                        && $this->lastname !=''
                        && ($this->id_warehouse != '' || $this->advanced_order_management == false)
                        )
		{
			$create_container = false;

						// we create a new inventory
			if ($this->id_erpip_inventory == '-1')
			{
                                $inventory = new ErpInventory();
                                $inventory->id_erpip_inventory = '';
                                $inventory->name = $this->name;
                                

                                $create_container = $inventory->add(true);
                                self::$id_erpip_inventory_static  = $inventory->getLastId();
			}
			else
				self::$id_erpip_inventory_static  = $this->id_erpip_inventory;

			// create inventory contanier
			if ($create_container || $this->id_erpip_inventory != '-1')
			{
                            // Split to get products
                            $products = explode('_', $this->inventory_values);

                            // reverse array read to get last values
                            $products = array_reverse($products);
                            $array_products = array();

                            foreach ($products as $key => $product)
                            {
                                    // Split to get product values
                                    $produc_line = explode('|', $product);
                                    if (count($produc_line) > 1)
                                    {
                                        foreach ($produc_line as $element)
                                        {
                                                // Split to get key and value
                                                $element = explode('==', $element);
                                                if (isset($element[1]))
                                                                $array_products[$key][$element[0]] = $element[1];
                                        }

                                        $ids = $array_products[$key]['idproduct'].';'.$array_products[$key]['idproductattribute'];

                                        // if already treat by update more recent, continue
                                        foreach (self::$local_store as $local_product)
                                                        if ($local_product == $ids)
                                                                        continue 2;

                                        // Record product
                                        $this->productHandler($array_products[$key]);

                                        // Store treated product, that only treat the LAST update
                                        array_push(self::$local_store, $ids);
                                    }
                            }

                            // if no error
                            if (count($this->errors) == 0)
                                $this->confirmations[] = $this->l('Inventory has been completed successfully');
			}
			else
                            $this->errors[] = Tools::displayError($this->l('Error while creating a new directory. Please contact the customer service.'));
		}
		else
                    $this->errors[] = Tools::displayError($this->l('Error : missing data. Please try again.'));
	}

	/*
         * Treat one inventory line
	*/
	public function productHandler($product)
	{
                if (empty($product['idproduct']))
                {
                        $this->errors[] = Tools::displayError($this->l('Error : the id of the product is missing !'));
                        return false;
                }

		$inventory_product = new InventoryProduct();
		$inventory_product->id_erpip_inventory = self::$id_erpip_inventory_static;
		$inventory_product->id_product = $product['idproduct'];
		$inventory_product->id_product_attribute = $product['idproductattribute'];
		$inventory_product->qte_before = (!isset($product['physicalquantity']) || $product['physicalquantity'] == '') ? 0 : $product['physicalquantity'];
		$inventory_product->qte_after = (!isset($product['foundquantity']) || $product['foundquantity'] == '') ? 0 : (int)$product['foundquantity'];
                $inventory_product->id_warehouse = Tools::isSubmit('id_warehouse') ? (int)Tools::getValue('id_warehouse') : -1;

                // if not mvt reason chosen => depend of quanities, select increate or decrease
		if (!isset($product['idreason']) || $product['idreason'] == '')
		{
                    if ($inventory_product->qte_before <= $inventory_product->qte_after)
                                    $inventory_product->id_mvt_reason = 1;
                    else
                                    $inventory_product->id_mvt_reason = 2;
		}
		else
                    $inventory_product->id_mvt_reason = $product['idreason'];
                
                // Gireg:
                // only treat products that match to stock manager selected :
                // Products in advanced stock manager if we re in advanced stock manager
                // Or product in non advanced stock manager, if we are in non advanced stock manager                
                if ($this->advanced_stock_management == StockAvailable::dependsOnStock((int)$product['idproduct']))
                {
                
                    // If record inventory line --> update stock
                    if ($inventory_product->add())
                    {
                            // if advanced stock manager
                            if ($this->advanced_stock_management == 1)
                            {
                                    $stock = new ErpStock();
                                    $stock->id_product = $product['idproduct'];
                                    $stock->id_product_attribute = $product['idproductattribute'];
                                    $stock->id_warehouse = $this->id_warehouse;
                                    $stock->physical_quantity = (!isset($product['foundquantity']) || $product['foundquantity'] == '') ? 0 : (int)$product['foundquantity'];
                                    $stock->usable_quantity = (!isset($product['foundquantity']) || $product['foundquantity'] == '') ? 0 : (int)$product['foundquantity'];

                                    // get reference price
                                    $price = $stock->getPriceTe();

                                    // if $price is false, we dont have stock for this product. si quantity is lower than stock, set price to 0
                                    if ((isset($product['foundquantity']) && (int)$product['foundquantity'] < $product['physicalquantity']) || $price = false)
                                                    $price = 0;

                                    $stock->price_te = $price;

                                    // if we already have a stock line for this production, we update else we insert
                                    if (($stock->id = $stock->getStockId()))
                                                    $maj_stock = $stock->update();
                                    else
                                                    $maj_stock = $stock->add();
                            }
                            else
                            {
                                    $maj_stock = StockAvailable::setQuantity($product['idproduct'], $product['idproductattribute'], (int)$product['foundquantity']);

                                    if (is_null($maj_stock))
                                            $maj_stock = true;
                            }

                            // if update stock ok --> update location
                            if ($maj_stock)
                            {

                                    // no stock change if advanced stock inactive
                                    if ($this->advanced_stock_management == 1)
                                    {
                                            require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpWarehouseProductLocation.php');
                                            $wpl_id = ErpWarehouseProductLocationClass::getWarehouseProductLocationId($product['idproduct'], $product['idproductattribute']);
                                            $warehouse_location = new ErpWarehouseProductLocationClass($wpl_id);
                                            $warehouse_location->id_product = $product['idproduct'];
                                            $warehouse_location->id_product_attribute = $product['idproductattribute'];
                                            $warehouse_location->id_warehouse = $this->id_warehouse;

                                            /*if ($product['area'] != '--')
                                                    $warehouse_location->zone = $product['area'];
                                            if ($product['subarea'] != '--')
                                                    $warehouse_location->sous_zone = $product['subarea'];*/

                                            $warehouse_location->location = $product['location'];
                                            $warehouse_location->id_warehouse_product_location = $wpl_id;

                                            // if update location ok & advanced stock active -> generate stock movement
                                            if ($warehouse_location->update())
                                            {
                                                    //echo 'Maj location OK';

                                                    // No stock movement if advanced stock inactive
                                                    if ($this->advanced_stock_management == 1)
                                                    {
                                                            $stock_mvt = new ErpStockMvt();
                                                            $stock_mvt->id_stock = $stock->id;
                                                            $stock_mvt->id_order = 0;
                                                            $stock_mvt->id_supply_order = 0;

                                                            // if not mvt reason selected => depend of quantity to increase or decrease
                                                            if (!isset($product['idreason']) || $product['idreason'] == '')
                                                            {
                                                                    if ($inventory_product->qte_before <= $inventory_product->qte_after)
                                                                            $stock_mvt->id_stock_mvt_reason = 1;
                                                                    else
                                                                            $stock_mvt->id_stock_mvt_reason = 2;
                                                            }
                                                            else
                                                                    $stock_mvt->id_stock_mvt_reason = $product['idreason'];

                                                            $stock_mvt->id_employee = $this->id_employee;
                                                            $stock_mvt->employee_firstname = $this->firstname;
                                                            $stock_mvt->employee_lastname = $this->lastname;
                                                            $stock_mvt->price_te = $price;
                                                            $stock_mvt->current_wa = $price;

                                                            // Récupération du sign (flèche up / down)
                                                            // Get sign (arrow up/down)
                                                            if (isset($product['foundquantity'])
                                                                            && (((int)$product['foundquantity'] >= (int)$product['physicalquantity']) || (int)$product['foundquantity'] == 0))
                                                                    $stock_mvt->sign = 1;
                                                            else
                                                                    $stock_mvt->sign = -1;

                                                            // calculate the quantity movement of stock
                                                            $foundquantity = (!isset($product['foundquantity']) || $product['foundquantity'] == '') ? 0 : (int)$product['foundquantity'];
                                                            $physicalquantity = (!isset($product['physicalquantity']) || $product['physicalquantity'] == '') ? 0 : (int)$product['physicalquantity'];

                                                            $mvt = abs($foundquantity - $physicalquantity);
                                                            $stock_mvt->physical_quantity = $mvt;

                                                            // Synchronise available stock 
                                                            if ($stock_mvt->add(true))
                                                                StockAvailable::synchronize($product['idproduct']);
                                                            else
                                                                    $this->errors[] = Tools::displayError($this->l('Error while inserting stock movement. Please try again.'));
                                                    }
                                                    else
                                                            $this->errors[] = Tools::displayError($this->l('No stock movement. You need to activate the advanced stock management in Preference > Products'));
                                            }
                                            else
                                                    $this->errors[] = Tools::displayError($this->l('Error while updating product location'));
                                    }
                            }
                            else
                                    $this->errors[] = Tools::displayError($this->l('Error while updating stock'));
                    }
                    else
                            $this->errors[] = Tools::displayError($this->l('Error while inserting product inventory row'));
                }
	}


        public function renderCategoryNameColumn($category_name, $data)
        {

                $html = '<a href="#" class="category" title="'.$this->l('Category').'" rel="index.php?controller=AdminAdvancedStock&ajax=1&id_product='.(int)$data['id_product'].'&task=getCategories&token='.$this->advanced_stock_token.'">
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
                        $html = '<a href="#" class="supplier_ref" title="'.$this->l('Suppliers references').'" rel="index.php?controller=AdminAdvancedStock&id_product='.(int)$data['id_product'].'&task=getSupplierReference&token='.$this->token.'">
                                        <img style="width: 16px; height: 16px;" alt="products" src="../img/admin/search.gif" class="icon-search" />
                                        '.$first_supplier_ref.'
                        </a> ';
                }
                return $html;
        }

        public function renderNameColumn($name, $data)
        {
                $html = '<a class="product_name" target="_blank" href="index.php?controller=AdminProducts&id_product='.(int)$data['id_product'].'&updateproduct&token='.$this->product_token.'">
                                '.$name.'
                </a> ';

                return $html;
        }

        public function renderMvtReasonColumn($mvm_stock_reason)
        {
                $html = '--';
                if (!empty($this->mvt_stock_reason) && !empty($mvm_stock_reason))
                {
                        $html = '<select name="reason" class="table_select">
                                        <option value="-1">--</option>';

                        foreach ($this->mvt_stock_reason as $reason)
                        {
                                $html .= '<option value="'.$reason['id_stock_mvt_reason'].'">'.$reason['name'].'</option>';
                        }

                        $html .= '</select>';
                }

                return $html;
        }
                
        public function renderLocationColumn($id_product,$data)
        {
            $html = '--';
            
            // Optim presta validator useless test
            if (!empty($id_product))
            {
                //if product has attribute we dont dispay location 
                if (isset($data['location']['CompleteArea']) && !empty($data['location']['CompleteArea']))
                {
                    $location = explode(';', $data['location']['CompleteArea']);

                    $html = '<span><b>'.$this->l('Area').' :</b> </span>'.(empty($location[0]) ? '' : $location[0]);
                    $html .= '<br/><span><b>'.$this->l('Subarea').' :</b> </span>'.(empty($location[1]) ? '' : $location[1]);                    
                    $html .= '<br/><span><b>'.$this->l('Location').' :</b> </span>'.(empty($location[2]) ? '' : $location[2]);
                }
            }
            
            return $html;
        }
}