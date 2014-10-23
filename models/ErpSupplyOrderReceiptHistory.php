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

class ErpSupplyOrderReceiptHistory extends ObjectModel
{

	public $id_supply_order_receipt_history;
	public $unit_price;
	public $discount_rate;
	public $is_canceled;

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_supply_order_receipt_history',
		'primary' => 'id_erpip_supply_order_receipt_history',
		'multilang' => false,
		'fields' => array
		(
			'id_supply_order_receipt_history' => array('type' => ObjectModel::TYPE_INT),
			'unit_price' => array('type' => ObjectModel::TYPE_FLOAT, 'required' => false),
			'discount_rate' => array('type' => ObjectModel::TYPE_FLOAT, 'required' => false),
			'is_canceled' => array('type' => ObjectModel::TYPE_BOOL, 'required' => false),
		)
	);

	/**
	 * Returns id_erpip_supply_order_receipt_history for a given id_supply_order_receipt_history
	 * @param int $id_supply_order_receipt_history
	 * @return int $id_erpip_supply_order_receipt_history
	*/
	public static function getErpAssociation($id_supply_order_receipt_history)
	{
		$query = new DbQuery();
		$query->select('id_erpip_supply_order_receipt_history');
		$query->from('erpip_supply_order_receipt_history');
		$query->where('id_supply_order_receipt_history = '.(int)$id_supply_order_receipt_history);
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}