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

class ErpWarehouseProductLocationClass extends WarehouseProductLocationCore
{
	/**/
	//public $zone;

	/**/
	//public $sous_zone;

	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		Cache::Clean('objectmodel_def_WarehouseProductLocation');
		self::$definition['fields']['location'] = array('type' => self::TYPE_INT, 'validate' => 'isReference');
		//self::$definition['fields']['zone'] = array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 50);
		//self::$definition['fields']['sous_zone'] = array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 50);

		parent::__construct($id, $id_lang, $id_shop);
	}

	/**/
	static public function getWarehouseProductLocationId($id_product, $id_product_attribute)
	{
		// Récupération valeurs
		$query = new DbQuery();
		$query->select('id_warehouse_product_location');
		$query->from('warehouse_product_location');
		$query->where('id_product = '.(int)$id_product);
		$query->where('id_product_attribute = '.(int)$id_product_attribute);
		return (int)Db::getInstance()->getValue($query);
	}

	/* Retourne la liste des zones */
	/*public static function getZoneByName($zone)
	{
		// build query
		$query = new DbQuery();
		$query->select('id_warehouse_product_location as id, wpl.zone as label');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("wpl.zone LIKE '$zone%'");
				$query->groupBy("wpl.zone");

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}*/

	/* Retourne la liste des sous zones */
	/*public static function getSousZoneByName($zone)
	{
		// build query
		$query = new DbQuery();
		$query->select('id_warehouse_product_location as id, wpl.sous_zone as label');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("wpl.sous_zone LIKE '$zone%'");
				$query->groupBy("wpl.sous_zone");

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}*/

	/* Retourne la zone, sous zone et emplacement d'un produit */
	public static function getCompleteLocation($id_product, $id_product_attribute, $id_warehouse)
	{
		// build query
                $query = new DbQuery();
                $query->select("CONCAT(area.name, ';', IFNULL(sub_area.name, '--'), ';', IF(location='', '--', location)) as CompleteArea");
                $query->from('warehouse_product_location', 'wpl');
                $query->leftJoin('erpip_warehouse_product_location',  'ewpl', 'ewpl.id_warehouse_product_location = wpl.id_warehouse_product_location');
                $query->leftJoin('erpip_zone' , 'area',  'area.id_erpip_zone = ewpl.id_zone_parent');
                $query->leftJoin('erpip_zone', 'sub_area' , 'sub_area.id_erpip_zone = ewpl.id_zone');
                $query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute.' 
                            AND wpl.id_warehouse = '.(int)$id_warehouse
                );
                
                return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
	}

	/* Retourne la liste des zones */
	/*public static function getAreas()
	{
		// build query
		$query = new DbQuery();
		$query->select('id_warehouse_product_location as id, wpl.zone as label');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("zone <> ''");
		$query->groupBy('label');
		$query->orderBy('label');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}*/

	/* Retourne la liste des zones */
	/*public static function getSubAreas()
	{
		// build query
		$query = new DbQuery();
		$query->select('id_warehouse_product_location as id, wpl.sous_zone as label');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("sous_zone <> ''");
		$query->groupBy('label');
		$query->orderBy('label');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}*/

	/* Retourne la liste des zones pour un entrepôt */
	/*public static function getAreasByWarehouseId($id_warehouse)
	{
		// build query
		$query = new DbQuery();
		$query->select('id_warehouse_product_location as id, wpl.zone as label');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("zone <> '' AND id_warehouse = $id_warehouse");
		$query->groupBy('label');
		$query->orderBy('label');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}*/

	/* Retourne la liste des zones pour un entrepôt */
	/*public static function getSubAreasByWarehouseId($id_warehouse)
	{
		// build query
		$query = new DbQuery();
		$query->select('id_warehouse_product_location as id, wpl.sous_zone as label');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("sous_zone <> '' AND id_warehouse = $id_warehouse");
		$query->groupBy('label');
		$query->orderBy('label');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}*/

	/* Retourne le nombre de produit dans une zone complète */
	/*public static function countProductInFullArea($area, $subarea)
	{
		// build query
		$query = new DbQuery();
		$query->select('count(id_warehouse_product_location)');
		$query->from('warehouse_product_location', 'wpl');
		$query->where("zone = '$area' AND sous_zone = '$subarea'");

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}*/

		public static function ifExist($area, $subarea, $location)
		{
			// build query
			$query = new DbQuery();
			$query->select('count(*)');
			$query->from('warehouse_product_location', 'wpl');
			$query->where("zone = '".pSQL($area)."' AND sous_zone = '".pSQL($subarea)."' AND location='".pSQL($location)."'");

			echo Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
		}

		public static function swapProduct($area, $subarea, $location)
		{
			// build query
			if ($location == '')
				$location = '0';

			$query = new DbQuery();
			$query->select('IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
							wpl.id_product, wpl.id_product_attribute');
			$query->from('warehouse_product_location', 'wpl');
			$query->join(' LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = wpl.id_product');
			$query->join(' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (wpl.id_product = pl.id_product AND pl.id_lang = '.(int)Context::getContext()->language->id.')
							LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = wpl.id_product_attribute)
							LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)
							LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.id_attribute = pac.id_attribute AND al.id_lang = '.(int)Context::getContext()->language->id.')
							LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.(int)Context::getContext()->language->id.')');
			$query->where("wpl.zone = '".pSQL($area)."' AND wpl.sous_zone = '".pSQL($subarea)."' AND wpl.location='".pSQL($location)."'");

			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

			if ($row['id_product'] == null)
				echo 'false';
			else
				echo Tools::jsonEncode(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query));
		}
}