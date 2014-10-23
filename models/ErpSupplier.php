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

class ErpSupplier extends ObjectModel
{

	public $id_erpip_supplier;
	public $id_supplier;
	public $email;
	public $fax;
	public $franco_amount;
	public $discount_amount;
	public $shipping_amount;
	public $escompte;
	public $delivery_time;
	public $account_number_accounting;

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_supplier',
		'primary' => 'id_erpip_supplier',
		'multilang' => false,
		'fields' => array
		(
					'id_supplier' => array('type' => ObjectModel::TYPE_INT),
					'email' => array('type' => ObjectModel::TYPE_STRING, 'required' => false, 'validate' => 'isEmail'),
					'fax' => array('type' => ObjectModel::TYPE_STRING, 'required' => false, 'validate' => 'isPhoneNumber'),
					'franco_amount' => array('type' => ObjectModel::TYPE_FLOAT, 'required' => false, 'validate' => 'isUnsignedFloat'),
					'discount_amount' => array('type' => ObjectModel::TYPE_FLOAT, 'required' => false, 'validate' => 'isUnsignedFloat'),
					'shipping_amount' => array('type' => ObjectModel::TYPE_FLOAT, 'required' => false, 'validate' => 'isUnsignedFloat'),
					'escompte' => array('type' => ObjectModel::TYPE_FLOAT, 'required' => false, 'validate' => 'isUnsignedFloat'),
					'delivery_time' => array('type' => ObjectModel::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt'),
					'account_number_accounting' => array('type' => ObjectModel::TYPE_STRING, 'required' => false),
		)
	);

	/**
	 * Returns id_erpip_supplier for a given id_supplier
	 * @param int $id_supplier
	 * @return int $id_erpip_supplier
	 */
	public static function getErpSupplierIdBySupplierId($id_supplier)
	{
		$query = new DbQuery();
		$query->select('id_erpip_supplier');
		$query->from('erpip_supplier');
		$query->where('id_supplier = '.(int)$id_supplier);
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}