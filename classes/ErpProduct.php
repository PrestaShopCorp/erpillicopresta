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

class ErpProduct extends Product
{

	/**
	 * Gets the name of a given product, in the given lang
	 * HAI : override method to record product name with sort
         * 
	 * @since 1.5.0
	 * @param int $id_product
	 * @param int $id_product_attribute Optional
	 * @param int $id_lang Optional
	 * @return string
	 */
	public static function getProductName ($id_product, $id_product_attribute = null, $id_lang = null)
	{
		// use the lang in the context if $id_lang is not defined
		if (!$id_lang)
			$id_lang = (int)Context::getContext()->language->id;

		// creates the query object
		$query = new DbQuery();

		// selects different names, if it is a combination
		if ($id_product_attribute)
			$query->select('IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name ORDER BY agl.`name`, \' - \', al.name ASC SEPARATOR \', \')),pl.name) as name');
		else
			$query->select('DISTINCT pl.name as name');

		// adds joins & where clauses for combinations
		if ($id_product_attribute)
		{
			$query->from('product_attribute', 'pa');
			$query->join(Shop::addSqlAssociation('product_attribute', 'pa'));
			$query->innerJoin('product_lang', 'pl', 'pl.id_product = pa.id_product AND pl.id_lang = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl'));
			$query->leftJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
			$query->leftJoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
			$query->leftJoin('attribute_lang', 'al', 'al.id_attribute = atr.id_attribute AND al.id_lang = '.(int)$id_lang);
			$query->leftJoin('attribute_group_lang', 'agl', 'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.(int)$id_lang);
			$query->where('pa.id_product = '.(int)$id_product.' AND pa.id_product_attribute = '.(int)$id_product_attribute);
		}
		else // or just adds a 'where' clause for a simple product
		{
			$query->from('product_lang', 'pl');
			$query->where('pl.id_product = '.(int)$id_product);
			$query->where('pl.id_lang = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl'));
		}

		return Db::getInstance()->getValue($query);
	}


	/**
	 * Gets the data in common of a given product and potentiat product_attribute
	 *
	 * @since 1.5.0
	 * @param int $id_product
	 * @param int $id_product_attribute Optional
	 * @return string
	 */
	public static function getProductsInfo ($id_product, $id_product_attribute = null)
	{
		// creates the query object
		$query = new DbQuery();

		// selects different names, if it is a combination
		if ($id_product_attribute)
		{
			$query->select('pa.ean13 AS ean13, pa.reference AS reference');
			// adds joins & where clauses for combinations
			$query->from('product_attribute', 'pa');
			$query->where('pa.id_product = \''.pSQL($id_product).'\' AND pa.id_product_attribute = '.pSQL($id_product_attribute));
		}
		else
		{
			$query->select('p.ean13 AS ean13, p.reference AS reference');
			// or just adds a 'where' clause for a simple product
			$query->from('product', 'p');
			$query->where('p.id_product = \''.pSQL($id_product).'\'');
		}

		$data = Db::getInstance()->executeS ($query);

		return (count ($data) == 1) ? $data [0] : false;
	}
}