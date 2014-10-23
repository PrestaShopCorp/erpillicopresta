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

class ErpStockMvt extends StockMvt
{

	/**/
	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		//self::$definition['fields']['last_quantity'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true);
		parent::__construct($id, $id_lang, $id_shop);
	}

	/* Retourne l'id du dernier mouvement de stock*/
	public static function getLastId()
	{
		$query = new DbQuery();
		$query->select('id_stock_mvt');
		$query->from('stock_mvt');
		$query->orderBy('id_stock_mvt DESC');

		return Db::getInstance()->getValue($query);
	}

	/* Retourne la liste des mouvements correspondant aux Ids en paramètre --> génération PDF rapport de transfert de stock*/
	public static function getMovementsByIds ($ids)
	{
		// Réformattage des données si plusieurs ID séparés par des '|' et protection
		if (strstr($ids, '|'))
		{
			$idsList = explode ('|', $ids);
			array_walk($idsList, 'intval');
			$ids = implode (',', $idsList);
		}
		else
			$ids = pSQL($ids);


		$query = new DbQuery();
		$query->select('
						IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
						IFNULL(pa.reference, s.reference) as reference, sm.physical_quantity, m.name as manufacturer_name ');
		$query->from('stock_mvt', 'sm');
		$query->join(' INNER JOIN `'._DB_PREFIX_.'stock` s ON s.id_stock = sm.id_stock');
		$query->join('INNER JOIN '._DB_PREFIX_.'product p ON s.id_product = p.id_product ');
		$query->join('LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON s.id_product_attribute = pa.id_product_attribute ');
		$query->join(' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
				s.id_product = pl.id_product
				AND pl.id_lang = '.(int)Context::getContext()->language->id.'
		)');
		$query->join(' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = s.id_product_attribute)');
		$query->join(' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)');
		$query->join(' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
				al.id_attribute = pac.id_attribute
				AND al.id_lang = '.(int)Context::getContext()->language->id.'
		)');
		$query->join('LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
				agl.id_attribute_group = atr.id_attribute_group
				AND agl.id_lang = '.(int)Context::getContext()->language->id.'
		)');
		$query->join('LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.id_manufacturer = p.id_manufacturer');
		$query->where("sm.id_stock_mvt IN ($ids)");

		$query->groupBy('s.id_product, s.id_product_attribute');

		return Db::getInstance()->executeS($query);
	}
}