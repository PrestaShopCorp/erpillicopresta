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

class StockImageContent extends ObjectModel
{
	/**/
	public $id_stock_image_content;

	/**/
	public $id_product;

	/**/
	public $id_product_attribute;

	/**/
	public $id_stock_image;

	/**/
	public $wholesale_price;

	/**/
	public $price_te;

	/**/
	public $valuation;

	/**/
	public $quantity;

	/**/
	public $physical_quantity;

	/**/
	public $usable_quantity;

	/**/
	public $real_quantity;

	/**/
	public $location;

	/* ORM */
	public static $definition = array
	(
		'table' => 'stock_image_content',
		'primary' => 'id_stock_image_content',
		'multilang' => false,
		'fields' => array
		(
			'id_stock_image_content' => array('type' => ObjectModel::TYPE_INT),
			'id_product' => array('type' => ObjectModel::TYPE_INT),
			'id_product_attribute' => array('type' => ObjectModel::TYPE_INT),
			'id_stock_image' => array('type' => ObjectModel::TYPE_INT),
			'wholesale_price' => array('type' => ObjectModel::TYPE_INT),
			'price_te' => array('type' => ObjectModel::TYPE_INT),
			'valuation' => array('type' => ObjectModel::TYPE_INT),
			'quantity' => array('type' => ObjectModel::TYPE_INT),
			'physical_quantity' => array('type' => ObjectModel::TYPE_INT),
			'usable_quantity' => array('type' => ObjectModel::TYPE_INT),
			'real_quantity' => array('type' => ObjectModel::TYPE_INT),
			'location' => array('type' => ObjectModel::TYPE_STRING)
		)
	);

	/* Retourne la liste des images de stocks enregistrées */
	public static function getImageContentIdsByIdImage($id_image)
	{
		// build query
		$query = new DbQuery();
		$query->select('id_stock_image_content');
		$query->from('stock_image_content', 'sic');
		$query->where('id_stock_image='.(int)$id_image);

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		return $result;
	}
}