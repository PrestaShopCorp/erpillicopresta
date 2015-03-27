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

class ErpInventory extends ObjectModel
{
	public $id_erpip_inventory;
	public $name;
	public $date_add;
	public $date_upd;

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_inventory',
		'primary' => 'id_erpip_inventory',
		'multilang' => false,
		'fields' => array
		(
			'id_erpip_inventory' => array('type' => ObjectModel::TYPE_INT),
			'name' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
			'date_add' => array('type' => ObjectModel::TYPE_DATE, 'required' => true),
			'date_upd' => array('type' => ObjectModel::TYPE_DATE, 'required' => true)
	)
	);

	/*
	*	Returns inventory directories list
	*/
	public static function getContainers()
	{
		// build query
		$query = new DbQuery();
		$query->select('id_erpip_inventory, name, date_add, date_upd');
		$query->from('erpip_inventory', 'i');
		$query->orderBy('date_upd DESC');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		// format dates again
		$i = 0;
		foreach ($result as $value)
		{
			$date_1 = explode(' ', $value['date_add']);
			$date_1 = explode('-', $date_1[0]);
			$date_1 = $date_1[2].'-'.$date_1[1].'-'.$date_1[0];

			$date_2 = explode(' ', $value['date_upd']);
			$date_2 = explode('-', $date_2[0]);
			$date_2 = $date_2[2].'-'.$date_2[1].'-'.$date_2[0];

			$result[$i]['date_add'] = 'Le '.$date_1;
			$result[$i]['date_upd'] = 'Le '.$date_2;
			$i++;
		}

		return $result;
	}

        /*
	*   return fisrt id inventory
	*/
	public static function getFirstId()
	{
            // build query
            $query = new DbQuery();
            $query->select('id_erpip_inventory');
            $query->from('erpip_inventory', 'i');
            $query->orderBy('id_erpip_inventory ASC');
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
        
	/*
	*   return last id inventory
	*/
	public function getLastId()
	{
		// build query
		$query = new DbQuery();
		$query->select('id_erpip_inventory');
		$query->from('erpip_inventory', 'i');
		$query->orderBy('id_erpip_inventory DESC');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}