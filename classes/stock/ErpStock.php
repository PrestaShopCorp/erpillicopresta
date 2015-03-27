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

require_once(_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProductSupplier.php');

class ErpStock extends Stock
{

	/**/
	public function getStockId()
	{
		// Récupération valeurs
		$query = new DbQuery();
		$query->select('id_stock');
		$query->from('stock');
		$query->where('id_product = '.(int)$this->id_product);
		$query->where('id_product_attribute = '.(int)$this->id_product_attribute);
		$query->where('id_warehouse = '.(int)$this->id_warehouse);

		return Db::getInstance()->getValue($query);
	}

	/**/
	public function getPriceTe()
	{
		// Get values
		$query = new DbQuery();
		$query->select('price_te');
		$query->from('stock');
		$query->where('id_product = '.(int)$this->id_product);
		$query->where('id_product_attribute = '.(int)$this->id_product_attribute);
		$query->where('id_warehouse = '.(int)$this->id_warehouse);

		return Db::getInstance()->getValue($query);
	}

	/**/
	public static function getAllProductInStock($advanced_stock_management, $warehouse)
	{
			$query = new DbQuery();

			// If Advanced Stock Management go through table ps_stock
			if ($advanced_stock_management)
			{
					$query->select('p.id_product, IFNULL(pa.id_product_attribute, 0) as id_product_attribute');
					$query->from('product', 'p');
					$query->leftJoin('product_attribute', 'pa', 'p.id_product = pa.id_product');

					if ($warehouse != -1)
							$query->where('(
										(
												p.id_product IN (SELECT id_product FROM '._DB_PREFIX_.'stock WHERE id_warehouse = '.(int)$warehouse.')
												AND IFNULL(pa.id_product_attribute, 0)IN(SELECT id_product_attribute FROM '._DB_PREFIX_.'stock WHERE id_warehouse ='.(int)$warehouse.'
										)
								)
								OR (
												p.id_product IN (SELECT id_product FROM '._DB_PREFIX_.'warehouse_product_location WHERE id_warehouse = '.(int)$warehouse.')
												AND IFNULL(pa.id_product_attribute, 0)IN (SELECT id_product_attribute FROM '._DB_PREFIX_.'warehouse_product_location WHERE id_warehouse = '.(int)$warehouse.')
										)
								)'
						);
			}
			else
			{
					$query->select('id_product, id_product_attribute, quantity');
					$query->from('stock_available');
			}

			$stock = Db::getInstance()->ExecuteS($query);

			$nb_items = count($stock);
			for ($i = 0; $i < $nb_items; ++$i)
			{
					$item = &$stock[$i];

					// gets stock manager
					$manager = StockManagerFactory::getManager();

					// If Advanced Stock Management get quantities & valuation
					if ($advanced_stock_management)
					{
							// gets quantities and valuation
							$query = new DbQuery();
							$query->select('SUM(physical_quantity) as physical_quantity');
							$query->select('SUM(usable_quantity) as usable_quantity');
							$query->select('SUM(price_te * physical_quantity) as valuation');
							$query->from('stock');
							$query->where('id_product = '.(int)$item['id_product'].' AND id_product_attribute = '.(int)$item['id_product_attribute']);
							$query->where('id_warehouse = '.(int)$warehouse);

							$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

							// Quantities
							$item['physical_quantity'] = $res['physical_quantity'];
							$item['usable_quantity'] = $res['usable_quantity'];
							$item['quantity'] = 0;

							// gets real_quantity depending on the warehouse
							$item['real_quantity'] = $manager->getProductRealQuantities($item['id_product'],
																			$item['id_product_attribute'], $warehouse, true);
							// Valuation (pump)
							$item['valuation'] = $res['valuation'];
					}
					else
					{
							$item['physical_quantity'] = 0;
							$item['usable_quantity'] = 0;
							$item['real_quantity'] = 0;
							$item['valuation'] = 0;
					}

					// Sale price
					// Product
					if ((int)$item['id_product_attribute'] == 0)
					{
							$query = 'SELECT p.price as price';
							$query .= ' FROM '._DB_PREFIX_.'product p';
							$query .= ' WHERE p.id_product = '.(int)$item['id_product'];
					}
					else
					{
							$query = 'SELECT (p.price + pa.price) as price';
							$query .= ' FROM '._DB_PREFIX_.'product p';
							$query .= ' LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (p.id_product = pa.id_product)';
							$query .= ' WHERE p.id_product = '.(int)$item['id_product'];
							$query .= ' AND pa.id_product_attribute = '.(int)$item['id_product_attribute'];
					}

					$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

					$item['price_te'] = $res['price'];

					// Purchase price
					$item['wholesale_price'] = self::getWholesalePrice($item['id_product'], $item['id_product_attribute']);

					// Location
					//$item['location']= ErpWarehouseProductLocationClass::getCompleteLocation($item['id_product'], $item['id_product_attribute'],(int)$warehouse);
			}

			return $stock;
	}

	/* Returns the purchase price of a product or a variation */
	public static function getWholesalePrice($id_product, $id_product_attribute = 0, $id_supplier = 0)
	{

            // If there is a supplier
            if (!empty($id_supplier))
            {
                //On récupère tout d'abord le prix du fournisseur
                $prices = ErpProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $id_supplier, true);
                if (isset($prices['product_supplier_price_te']))
                                $price = $prices['product_supplier_price_te'];
            }

            // If no price for this supplier, or supplier price null, 
            // get the price of the product or variation
            if (empty($price) || $price == '0.000000')
            {
                // pas de décliaison, on cherche le prix du produit
                if ($id_product_attribute == 0)
                {
                    $query = new DbQuery();
                    $query->select('wholesale_price');
                    $query->from('product');
                    $query->where('id_product = '.(int)$id_product);
                    $price = Db::getInstance()->getValue($query);
                }
                // Variation price
                else
                {
                    $query = new DbQuery();
                    $query->select('p.wholesale_price as wholesale_price_product, pa.wholesale_price as wholesale_price_product_attribute');
                    $query->from('product_attribute','pa');
                    $query->where('pa.id_product = '.(int)$id_product);
                    $query->where('pa.id_product_attribute = '.(int)$id_product_attribute);
                    $query->innerJoin('product', 'p', ' p.id_product = pa.id_product');
                    $prices = Db::getInstance()->getRow($query);

                    //If variation has a price
                    if ($prices['wholesale_price_product_attribute'] == '0.000000')
                                    $price = $prices['wholesale_price_product'];

                    // Else product price
                    elseif ($prices['wholesale_price_product_attribute'] != '0.000000')
                                    $price = $prices['wholesale_price_product_attribute'];

                    // Else ZERO
                    else
                                    $price = '0.00000';
                }
            }

            return $price;
	}

	/* Returns whether a product is present in a specified warehouse */
	public static function getPresenceInStock($id_product, $id_product_attribute, $id_warehouse)
	{
			// build query
			$query = new DbQuery();
			$query->select('count(id_stock)');
			$query->from('stock');
			$query->where(' id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute.' AND id_warehouse = '.(int)$id_warehouse);
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}