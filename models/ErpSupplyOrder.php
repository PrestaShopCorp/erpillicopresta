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

class ErpSupplyOrder extends ObjectModel
{

	public $id_erpip_supply_order;
	public $id_supply_order;
	public $escompte;
	public $invoice_number;
	public $date_to_invoice;
	public $global_discount_amount;
	public $global_discount_type;
	public $shipping_amount;
	public $description;

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_supply_order',
		'primary' => 'id_erpip_supply_order',
		'multilang' => false,
		'fields' => array
		(
			'id_supply_order' => array('type' => ObjectModel::TYPE_INT),
			'escompte' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
			'invoice_number' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
			'date_to_invoice' => array('type' => ObjectModel::TYPE_DATE, 'required' => false),
			'global_discount_amount' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
			'global_discount_type' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
			'shipping_amount' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
			'description' => array('type' => ObjectModel::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml'),
		)
	);

	/**
	 * Returns id_erpip_supplier_order for a given id_supplier_order
	 * @param int $id_supplier_order
	 * @return int $id_erpip_supplier_order
	 */
	public static function getErpSupplierOrderIdBySupplierOrderId($id_supplier_order)
	{
		$query = new DbQuery();
		$query->select('id_erpip_supply_order');
		$query->from('erpip_supply_order');
		$query->where('id_supply_order = '.(int)$id_supplier_order);
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

}