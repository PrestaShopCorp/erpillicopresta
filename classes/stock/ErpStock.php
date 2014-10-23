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
		// Récupération valeurs
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

			// Si stock avancé on passe par la table ps_stock
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

					// Si gestion de stock avancé, récup quantités & valuation
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

							// Quantités
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

					// PRIX DE VENTE
					// Produit
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

					// Prix achat
					$item['wholesale_price'] = self::getWholesalePrice($item['id_product'], $item['id_product_attribute']);

					// Emplacement
					//$item['location']= ErpWarehouseProductLocationClass::getCompleteLocation($item['id_product'], $item['id_product_attribute'],(int)$warehouse);
			}

			return $stock;
	}

	/* Retourne le prix d'achat d'un produit ou d'une déclinaison */
	public static function getWholesalePrice($id_product, $id_product_attribute = 0, $id_supplier = 0)
	{

            //S'il y a fournisseur
            if (!empty($id_supplier))
            {
                //On récupère tout d'abord le prix du fournisseur
                $prices = ErpProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $id_supplier, true);
                if (isset($prices['product_supplier_price_te']))
                                $price = $prices['product_supplier_price_te'];
            }

            // Si pas de prix pour ce fournisseur, ou prix fournisseur nul, 
            // on cherche le prix du produit ou de la déclinaison
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
                // Prix déclinaison
                else
                {
                    $query = new DbQuery();
                    $query->select('p.wholesale_price as wholesale_price_product, pa.wholesale_price as wholesale_price_product_attribute');
                    $query->from('product_attribute','pa');
                    $query->where('pa.id_product = '.(int)$id_product);
                    $query->where('pa.id_product_attribute = '.(int)$id_product_attribute);
                    $query->innerJoin('product', 'p', ' p.id_product = pa.id_product');
                    $prices = Db::getInstance()->getRow($query);

                    //si la déclinaison à un prix
                    if ($prices['wholesale_price_product_attribute'] == '0.000000')
                                    $price = $prices['wholesale_price_product'];

                    //sinon, on prend le prix du produit
                    elseif ($prices['wholesale_price_product_attribute'] != '0.000000')
                                    $price = $prices['wholesale_price_product_attribute'];

                    //Sinon zero
                    else
                                    $price = '0.00000';
                }
            }

            return $price;
	}

	/* Retourne si un produit est présent dans un entrepot donné */
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