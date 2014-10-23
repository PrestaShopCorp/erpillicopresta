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

class Warehouse extends WarehouseCore
{
	// JMA
	// Fonction statique pour recuperer le premier entrepot non deleted
	/*public static function getFirstWarehouse()
	{
		$query = new DbQuery();

		$query->select('id_warehouse');
        $query->from('warehouse');
        $query->where('deleted = 0');

		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

		if ($data['id_warehouse'] == '')
			$data['id_warehouse'] = 1;
		return $data['id_warehouse'];
	}*/
}