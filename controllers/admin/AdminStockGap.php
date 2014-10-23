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
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminStockGapController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap = true;
		$this->table = 'erpip_inventory_product';
		$this->className = 'InventoryProduct';
		$this->list_no_link = true;

                // template path
		$this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';
                
                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');

		parent::__construct();

		$this->fields_list = array(
			'id_warehouse' => array(
                            'title' => $this->l('Warehouse'),
                            'filter_key' => 'a!id_warehouse'
                        ),
                        'name' => array(
                            'title' => $this->l('Name'),
                            'filter_key' => 'pl!name'
                        ),
			'reference' => array(
                            'title' => $this->l('SKU')
                        ),
			'first_supplier_ref' => array(
                            'title' => $this->l('Supplier references'),
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
			'sign' => array(
				'title' => $this->l('Sign'),
				'width' => 100,
				'search' => false,
				'align' => 'center',
				'type' => 'select',
				'filter_key' => 'a!sign',
				'list' => array(
					'1' => $this->l('Increase'),
					'-1' => $this->l('Decrease'),
				),
				'icon' => array(
					-1 => 'remove_stock.png',
					1 => 'add_stock.png'
				)
						),
			'gap' => array(
				'title' => $this->l('Stock Gap') ,
				'search' => false
                        )
		);
	}

	// Ajout des colonnes hors table inventory_product
	public function renderList()
	{
                $this->toolbar_title = $this->l('Inventory reports');
                
                if (Tools::isSubmit('id_container') && Tools::getValue('id_container') > 0)
                        self::$currentIndex .= '&id_container='.(int)Tools::getValue('id_container');

		// Détermination de l'id container. Si aucun sélectionné, on prend le premier
		if (($id_container = $this->getCurrentValue('id_container')) == false)
		{
			$id_container = (int)Inventory::getFirstId();
			$this->tpl_list_vars['id_container'] = $id_container;
		}
                
                // get total stock gap of inventory
                $total_stock_gap = InventoryProduct::getTotalStockGap($id_container);
                
		 $this->tpl_list_vars['total_gap'] = Tools::displayPrice($total_stock_gap);
                 
		// Query
		$this->_select = '
                                        IF(a.id_warehouse = -1, "'. html_entity_decode($this->l('No warehouse')) .'",a.id_warehouse) as id_warehouse,
					pl.name as name,
					p.reference,
					smrl.name as reason,
					smr.sign, (
					a.qte_after - a.qte_before) as gap,
					(
						SELECT ps.product_supplier_reference
						FROM '._DB_PREFIX_.'product_supplier ps
						WHERE ps.id_product = a.id_product
						AND ps.id_product_attribute = 0
						LIMIT 1
					)as first_supplier_ref ';
		$this->_join = 'INNER JOIN '._DB_PREFIX_.'product_lang pl ON (a.id_product = pl.id_product AND pl.id_lang = '.(int)$this->context->language->id.')
				INNER JOIN '._DB_PREFIX_.'product p ON a.id_product = p.id_product
				INNER JOIN '._DB_PREFIX_.'stock_mvt_reason_lang smrl ON (a.id_mvt_reason = smrl.id_stock_mvt_reason AND smrl.id_lang = '.(int)$this->context->language->id.')
				INNER JOIN '._DB_PREFIX_.'stock_mvt_reason smr ON a.id_mvt_reason = smr.id_stock_mvt_reason
				INNER JOIN '._DB_PREFIX_.'erpip_inventory i ON a.id_erpip_inventory = i.id_erpip_inventory';
		$this->_where = 'AND i.id_erpip_inventory = '.$id_container;
		$this->_order = 'a.id_erpip_inventory_product DESC';
		$this->_group = 'GROUP BY a.id_erpip_inventory_product';

		// Envoi valeurs à la vue
		$this->tpl_list_vars['containers'] = Inventory::getContainers();

		$list = parent::renderList();
		return $list;
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		// order by sign
		if (Tools::isSubmit('inventory_productOrderby') && Tools::getValue('inventory_productOrderby') == 'sign')
			$this->context->cookie->stockgapinventory_productOrderby = 'srm.sign';

		parent::getList($id_lang, $order_by , $order_way , $start , $limit , $id_lang_shop);
	}

	// Retourne une valeur en get/post
	protected function getCurrentValue($var)
	{
            if (Tools::isSubmit($var))
            {
                    $value = Tools::getValue($var);

                    $this->tpl_list_vars[$var] = $value;
                    return ($value == -1) ? false : $value;
            }
	}

	// Aucun bouton
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
	}

	public function initPageHeaderToolbar()
	{
		parent::initPageHeaderToolbar();
		$token = Tools::getAdminToken('AdminInventory'.(int)(Tab::getIdFromClassName('AdminInventory')).(int)$this->context->employee->id);
		$back = 'index.php?controller=AdminInventory&token='.$token;

		$this->page_header_toolbar_btn['back'] = array(
					'href' => $back,
					'desc' => $this->l('Back to inventory'),
		);
	}

	/* JMA */
	/* Rajout pour la traduction du controller AdminStockGap */
	protected function l($string, $class = 'AdminStockGap', $addslashes = FALSE, $htmlentities = TRUE)
	{
            if (!empty($class))
            {
                    // Envoie du nom du controller a la methode statique de notre module
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

                parent::postProcess();
        }
}