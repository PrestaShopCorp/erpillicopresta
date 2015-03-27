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

class ErpSupplyOrderCustomer extends ObjectModel  {

	public $id_supply_order;
	public $id_supply_order_detail;
	public $id_order_detail;
	public $id_customer;

	public static $definition = array(
		'table' => 'erpip_supply_order_customer',
		'primary' => 'id_erpip_supply_order_customer',
		'multilang' => false,
		'fields' => array(
			'id_supply_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_supply_order_detail' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_order_detail' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
		)
	);

	/*
	*  Displaying customer orders linked to a supplier order from automatic generation
	*/
	public static function getSupplyOrdersConcernedCustomer($supply_order_id)
	{
		// build query
		$query = new DbQuery();
		$query->select(' CONCAT( c.firstname, \' \', c.lastname) as customer_name , od.id_order ');
		$query->from('erpip_supply_order_customer', 'esoc');
		$query->where('esoc.id_supply_order = '.(int)$supply_order_id);
		$query->innerJoin('customer', 'c', 'c.id_customer = esoc.id_customer');
		$query->innerJoin('order_detail', 'od', 'od.id_order_detail = esoc.id_order_detail');
		$query->groupBy('od.id_order');
		$query->orderBy('od.id_order DESC');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		if ($result)
				return $result;
		else
				return array();
	}


	/*
	*  Displaying customer orders linked to a supplier order from automatic generation
	*/
	public static function getSupplyOrdersCustomer($supply_order_id)
	{
		// build query
		$query = new DbQuery();
		$query->select( 'id_erpip_supply_order_customer, id_supply_order, id_supply_order_detail, id_order_detail, c.id_customer,
						CONCAT( c.firstname, \' \', c.lastname) as customer_name ');
		$query->from('erpip_supply_order_customer', 'esoc');
		$query->where('esoc.id_supply_order = '.(int)$supply_order_id);
		$query->innerJoin('customer', 'c', 'c.id_customer = esoc.id_customer');
		$query->groupBy('esoc.id_order_detail, esoc.id_customer ');
		$query->orderBy('esoc.id_erpip_supply_order_customer DESC');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		if ($result)
				return $result;
		else
				return array();
	}
}