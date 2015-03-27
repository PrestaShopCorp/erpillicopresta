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
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminStockGapController extends IPAdminController
{
	public function __construct()
	{
		$this->bootstrap = true;
		$this->table = 'erpip_inventory_product';
		$this->className = 'InventoryProduct';
		$this->list_no_link = true;

		$this->id_container = (Tools::isSubmit('id_container') ? Tools::getValue('id_container') : (int)ErpInventory::getFirstId());

                // template path
		$this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';
                
                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
                $this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedStock'));
		parent::__construct();

		$this->fields_list = array(
			'warehouse' => array(
                            'title' => $this->l('Warehouse')
                        ),
			'id_product' => array(
                            'title' => $this->l('Id product')
                        ),
			'id_product_attribute' => array(
                            'title' => $this->l('Id product attribute')
                        ),
			'reference' => array(
                            'title' => $this->l('SKU')
                        ),
			'first_supplier_ref' => array(
                            'title' => $this->l('Supplier references'),
                            'search' => false
                        ),
			'product_name' => array(
                            'title' => $this->l('product name'),
                            'search' => false
                        ),
			'qte_before' => array(
                            'title' => $this->l('Quantity before inventory')
                        ),
			'qte_after' => array(
                            'title' => $this->l('Quantity after inventory')
                        ),
			'reason' => array(
                            'title' => $this->l('Movement reason'),
                            'filter_key' => 'smrl!name'
						),
			'gap' => array(
				'title' => $this->l('Stock Gap') ,
				'search' => false
                        )
		);
	}
        
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
	
            parent::initContent();
            
	}

        // Add column out of table inventory_product
	public function renderList()
	{
                $this->toolbar_title = $this->l('Inventory reports');
                
                if (Tools::isSubmit('id_container') && Tools::getValue('id_container') > 0)
                        self::$currentIndex .= '&id_container='.(int)Tools::getValue('id_container');

                // Get id container. if noone selected, take the first one
		if (($id_container = $this->getCurrentValue('id_container')) == false)
		{
			$id_container = (int)ErpInventory::getFirstId();
			$this->tpl_list_vars['id_container'] = $id_container;
		}

                
                // get total stock gap of inventory
                $total_stock_gap = InventoryProduct::getTotalStockGap($id_container);
                
		 $this->tpl_list_vars['total_gap'] = Tools::displayPrice($total_stock_gap);
                 
		// Query
		$this->_select = 'p.id_product,
						IF(pa.id_product_attribute, pa.reference, p.reference) as reference,
						IFNULL(pa.id_product_attribute, 0) as id_product_attribute,
			IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
			p.id_product, IFNULL(pa.id_product_attribute, 0) as id_product_attribute, w.name as warehouse, a.qte_before, a.qte_after, smrl.name as reason, (qte_after - qte_before) as gap,
			(
						SELECT ps.product_supplier_reference
						FROM '._DB_PREFIX_.'product_supplier ps
						WHERE ps.id_product = a.id_product
						AND ps.id_product_attribute = a.id_product_attribute
						LIMIT 1
			)as first_supplier_ref';
		$this->_join = 'INNER JOIN '._DB_PREFIX_.'product_lang pl ON (a.id_product = pl.id_product AND pl.id_lang = '.(int)$this->context->language->id.')
				INNER JOIN '._DB_PREFIX_.'product p ON a.id_product = p.id_product
				INNER JOIN '._DB_PREFIX_.'stock_mvt_reason_lang smrl ON (a.id_mvt_reason = smrl.id_stock_mvt_reason AND smrl.id_lang = '.(int)$this->context->language->id.')
				INNER JOIN '._DB_PREFIX_.'stock_mvt_reason smr ON a.id_mvt_reason = smr.id_stock_mvt_reason
				INNER JOIN '._DB_PREFIX_.'warehouse w ON w.id_warehouse = a.id_warehouse
				INNER JOIN '._DB_PREFIX_.'product_attribute pa ON a.id_product_attribute= pa.id_product_attribute
				INNER JOIN '._DB_PREFIX_.'product_attribute_combination pac ON pac.id_product_attribute = pa.id_product_attribute
				INNER JOIN '._DB_PREFIX_.'attribute atr ON atr.id_attribute= pac.id_attribute
				INNER JOIN '._DB_PREFIX_.'attribute_lang al ON (al.id_attribute= pac.id_attribute AND al.id_lang='.(int)$this->context->language->id.')
				INNER JOIN '._DB_PREFIX_.'attribute_group_lang agl ON (agl.id_attribute_group= atr.id_attribute_group AND agl.id_lang='.(int)$this->context->language->id.')
				INNER JOIN '._DB_PREFIX_.'erpip_inventory i ON a.id_erpip_inventory = i.id_erpip_inventory';

		$this->_where = 'AND i.id_erpip_inventory = '.$id_container;
		$this->_order = 'a.id_erpip_inventory_product DESC  LIMIT 10';
		$this->_group = 'GROUP BY a.id_product_attribute';

        // Send values to view
		$this->tpl_list_vars['containers'] = ErpInventory::getContainers();

                
		$list = parent::renderList();
		return $list;
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
                if( $this->controller_status == STATUS1)
                {
                    $limit = ERP_STCKMGTFR;
                    $this->informations[] = sprintf($this->l('You are using the free version of 1-Click ERP which limits the display to %d products'), $limit);
                }
		parent::getList($id_lang, $order_by , $order_way , $start , $limit , $id_lang_shop);
	}

	// Retourne une valeur en get/post
        // Get value in get/post
	protected function getCurrentValue($var)
	{
            if (Tools::isSubmit($var))
            {
                    $value = Tools::getValue($var);

                    $this->tpl_list_vars[$var] = $value;
                    return ($value == -1) ? false : $value;
            }
	}

	// No button
	public function initToolbar()
	{
		$token = Tools::getAdminToken('AdminInventory'.(int)(Tab::getIdFromClassName('AdminInventory')).(int)$this->context->employee->id);
		$back = 'index.php?controller=AdminInventory&token='.$token;
		if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
		{
			$this->toolbar_btn['back'] = array(
                                'href' => $back,
                                'desc' => $this->l('Back to inventory')
			);
		}

		$this->toolbar_btn['save'] = array(
				'short' => $this->l('Export report'),
				'href' => $this->context->link->getAdminLink('AdminStockGap').'&export_csv&id_container='.$this->id_container,
				'desc' => $this->l('Export report'),
			);
	}

	public function initPageHeaderToolbar()
	{
		parent::initPageHeaderToolbar();
		$token = Tools::getAdminToken('AdminInventory'.(int)(Tab::getIdFromClassName('AdminInventory')).(int)$this->context->employee->id);
		$back = 'index.php?controller=AdminInventory&token='.$token;

		$this->toolbar_btn['back'] = array(
					'href' => $back,
					'desc' => $this->l('Back to inventory'),
		);

		$this->page_header_toolbar_btn['save'] = array(
				'short' => $this->l('Export report'),
				'href' => $this->context->link->getAdminLink('AdminStockGap').'&export_csv&id_container='.$this->id_container,
				'desc' => $this->l('Export report'),
			);
	}

	/* JMA */
	/* add to translate AdminStockGap  controller */
	protected function l($string, $class = 'AdminStockGap', $addslashes = FALSE, $htmlentities = TRUE)
	{
            if (!empty($class))
            {
                    // send controller name to static method of our module
                    $str = erpillicopresta::findTranslation('erpillicopresta', $string, 'AdminStockGap');
                    $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
                    return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
            }
	}

        public function postProcess()
        {
                require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
                $this->context->smarty->assign(array(
                                'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
                                'template_path' => $this->template_path,
                ));

                // Export CSV
				if (Tools::isSubmit('export_csv'))
					$this->renderCSV();

                parent::postProcess();
        }


        /*  Export CSV */
	protected function renderCSV()
	{
		if (Tools::isSubmit('export_csv'))
		{
			/* GENERATION CSV */

			// header
			header('Content-type: text/csv; charset=utf-8');
			header('Cache-Control: no-store, no-cache');
			header('Content-disposition: attachment; filename="inventory_report.csv"');


			// write headers column
			$keys = array(
                    'warehouse',
                    'id_product',
                    'id_product_attribute',
                    'SKU',
                    'supplier_reference',
                    'product_name',
                    'quantity_before',
                    'quantity_after',
                    'movement_reason',
                    'stock_gap'
            );

			echo sprintf("%s\n", implode(';', $keys));


			$query = null;
			$query = new DbQuery();
			$query->select(
						'p.id_product,
						IF(pa.id_product_attribute, pa.reference, p.reference) as reference,
						IFNULL(pa.id_product_attribute, 0) as id_product_attribute,
			IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
			p.id_product, IFNULL(pa.id_product_attribute, 0) as id_product_attribute, w.name as warehouse, ip.qte_before, ip.qte_after, smrl.name as reason, (qte_after - qte_before) as gap,
			(
						SELECT ps.product_supplier_reference
						FROM '._DB_PREFIX_.'product_supplier ps
						WHERE ps.id_product = ip.id_product
						AND ps.id_product_attribute = ip.id_product_attribute
						LIMIT 1
			)as first_supplier_ref');

			$query->from('erpip_inventory_product', 'ip');
			$query->leftjoin('product', 'p', 'ip.id_product= p.id_product');
			$query->leftjoin('product_attribute', 'pa', 'ip.id_product_attribute= pa.id_product_attribute');
			$query->leftjoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
			$query->leftjoin('attribute', 'atr', 'atr.id_attribute= pac.id_attribute');
			$query->leftjoin('attribute_lang', 'al', '(al.id_attribute= pac.id_attribute AND al.id_lang='.(int)$this->context->language->id.')');
			$query->leftjoin('attribute_group_lang', 'agl', '(agl.id_attribute_group= atr.id_attribute_group AND agl.id_lang='.(int)$this->context->language->id.')');
			$query->leftjoin('product_lang', 'pl', '(p.id_product = pl.id_product AND pl.id_lang ='.(int)$this->context->language->id.')');
			$query->leftjoin('warehouse', 'w', 'w.id_warehouse = ip.id_warehouse');
			$query->leftjoin('stock_mvt_reason_lang', 'smrl', '(smrl.id_stock_mvt_reason = ip.id_mvt_reason AND pl.id_lang ='.(int)$this->context->language->id.')');
			$query->where('id_erpip_inventory='.(int)Tools::getValue('id_container'));
			$query->groupBy('ip.id_product_attribute');
                        
                        if( $this->controller_status == STATUS1)
                        {
                            $query->limit(ERP_STCKMGTFR);
                        }
		
			// Execute query
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

			// write datas
			foreach ($res as $product)
			{
                $content_csv = array( 
                    $product['warehouse'],
                    $product['id_product'],
                    $product['id_product_attribute'],
                    $product['reference'],
                    $product['first_supplier_ref'],
                    self::transformText($product['name']),
                    $product['qte_before'],
                    $product['qte_after'],
                    $product['reason'],
                    $product['gap'],
                    PHP_EOL
                );
                 echo implode(';', $content_csv);
			}
                if( $this->controller_status == STATUS1)
                  {
                        echo $this->l('You are using the free version of 1-Click ERP, which limits the display to 10 products. In order to remove the limit, switch to a higher version.');
                  }
			die();
		}
	}
}


