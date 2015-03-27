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

class ErpSupplyOrderDetail extends ObjectModel
{

	public $id_supply_order_detail;
	public $comment;

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_supply_order_detail',
		'primary' => 'id_erpip_supply_order_detail',
		'multilang' => false,
		'fields' => array
		(
			'id_supply_order_detail' => array('type' => ObjectModel::TYPE_INT),
			'comment' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
		)
	);

	/**
	 * Returns id_erpip_supplier_order_detail for a given id_supplier_order_detail
	 * @param int $id_supplier_order_detail
	 * @return int $id_erpip_supplier_order_detail
	 */
	public static function getErpSupplierOrderDetailIdBySupplierOrderDetailId($id_supplier_order_detail)
	{
		$query = new DbQuery();
		$query->select('id_erpip_supply_order_detail');
		$query->from('erpip_supply_order_detail');
		$query->where('id_supply_order_detail = '.(int)$id_supplier_order_detail);
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}