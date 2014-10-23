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

class StockImage extends ObjectModel
{
	/**/
	public $id_stock_image;

	/**/
	public $name;

	/**/
	public $date_add;

	/**/
	public $type_stock;

	/* ORM */
	public static $definition = array
	(
		'table' => 'stock_image',
		'primary' => 'id_stock_image',
		'multilang' => false,
		'fields' => array
		(
			'id_stock_image' => array('type' => ObjectModel::TYPE_INT),
			'name' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
			'date_add' => array('type' => ObjectModel::TYPE_DATE, 'required' => true),
			'type_stock' => array('type' => ObjectModel::TYPE_DATE, 'required' => true)
		)
	);

	/* Retourne la liste des images de stocks enregistrées */
	public static function getStockImages()
	{
		// build query
		$query = new DbQuery();
		$query->select('id_stock_image, name, date_add, type_stock');
		$query->from('stock_image', 'si');
		$query->where('type_stock=\''. pSQL(Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')).'\'');
		$query->orderBy('date_add DESC');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		// date format
		$i = 0;
		foreach ($result as $value)
		{
			$date_add_tab = explode(' ',$value['date_add']);
			$date_add_tab = explode('-', $date_add_tab[0]);
			$result[$i]['date_add'] = 'Le '. $date_add_tab[2].'/'.$date_add_tab[1].'/'.$date_add_tab[0];
			$i++;
		}

		return $result;
	}

	/* return the last id saved  */
	public function getLastId()
	{
		// build query
		$query = new DbQuery();
		$query->select('id_stock_image');
		$query->from('stock_image', 'si');
		$query->orderBy('id_stock_image DESC');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	/* Création du container */
	public function createImage($id_stock_image, $name_stock_image)
	{
		// Suppression d'abord de l'image et des ligne de stock liées
		if ($id_stock_image != -1)
		{
			$this->id_stock_image = trim($id_stock_image);
			$this->id = $this->id_stock_image;

			// delete image is Ok, we delete the product lines associated
			if ($this->delete())
			{
				$id_images = StockImageContent::getImageContentIdsByIdImage($this->id);
				foreach ($id_images as $id_image)
				{
					$image_content = new StockImageContent();
					$image_content->id_stock_image_content = $id_image['id_stock_image_content'];
					$image_content->id = $image_content->id_stock_image_content;
					$image_content->delete();
				}
			}
		}

		// Création de la nouvel image
		$this->id_stock_image = '';
		$this->name = $name_stock_image;
		$this->type_stock = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

		//Création du container d'image de stokc
		if ($this->add(true))
		{
			// Pour chaque produit en stock, on récupère les informations nécessaires
			$products = ErpStock::getAllProductInStock($this->type_stock, (int)Tools::getValue('id_warehouse'));

			// Enregistrement
			foreach ($products as $product)
			{
				$product['id_stock_image'] = $this->getLastId();
				$this->createStockImageContent($product);
			}
		}
	}

	/* Enregistrement d'une ligne de stock */
	public function createStockImageContent($product)
	{
		$image_content = new StockImageContent();
		$image_content->id_stock_image_content = '';
		$image_content->id_product = $product['id_product'];
		$image_content->id_product_attribute = $product['id_product_attribute'];
		$image_content->id_stock_image = $product['id_stock_image'];
		//$image_content->location = $product['location']['CompleteArea'];
		$image_content->wholesale_price = $product['wholesale_price'];
		$image_content->price_te = $product['price_te'];
		$image_content->valuation = $product['valuation'];
		$image_content->quantity = ($product['quantity'] == null) ? 0 : $product['quantity'];
		$image_content->physical_quantity = ($product['physical_quantity'] == null) ? 0 : $product['physical_quantity'];
		$image_content->real_quantity = ($product['real_quantity'] == null) ? 0 : $product['real_quantity'];
		$image_content->usable_quantity = ($product['usable_quantity'] == null) ? 0 : $product['usable_quantity'];

		$image_content->add();
	}
}