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

class InventoryProduct extends ObjectModel
{
	public $id_erpip_inventory_product;
	public $id_erpip_inventory;
	public $id_product;
	public $id_product_attribute;
	public $id_mvt_reason;
	public $qte_before;
	public $qte_after;
        public $id_warehouse;
	public $advanced_stock_management;

	public function __construct()
	{
		$this->advanced_stock_management = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
		parent::__construct();
	}

	/*
	* ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_inventory_product',
		'primary' => 'id_erpip_inventory_product',
		'multilang' => false,
		'fields' => array
		(
			'id_erpip_inventory_product' => array('type' => ObjectModel::TYPE_INT),
			'id_erpip_inventory' => array('type' => ObjectModel::TYPE_INT),
			'id_product' => array('type' => ObjectModel::TYPE_INT, 'required' => true),
			'id_mvt_reason' => array('type' => ObjectModel::TYPE_INT, 'required' => true),
			'qte_before' => array('type' => ObjectModel::TYPE_INT, 'required' => true),
			'qte_after' => array('type' => ObjectModel::TYPE_INT, 'required' => true),
                        'id_warehouse' => array('type' => ObjectModel::TYPE_INT, 'required' => false),
		)
	);

	/*
	* Retourne les produits de la grille d'inventaire prenant en compte les filtres actifs
	*/
	public function getInventoryGrid($id_warehouse, $id_category, $id_supplier, $id_manufacturer, $area, $subarea)
	{
		// Si gestion de stock avancé et pas d'entrepot, on sélectionne le premier (sélection par défaut dans l'IHM)
		if ($this->advanced_stock_management)
			$id_warehouse = ($id_warehouse == '') ? 1 : $id_warehouse;

		// Récupération produits
		if ($this->advanced_stock_management)
		{
			$query = 'SELECT
						pl.name as name,
						p.reference,
						p.ean13 as ean,
						p.id_product,
						wpl.id_warehouse,
						0 as id_product_attribute,
						IFNULL(m.name, \'\') as manufacturer_name,';

			$query .= $this->getSubQuery($id_warehouse, '0', 'all');
		}
		else
		{
			$query  = 'SELECT
						pl.name as name,
						p.reference,
						p.ean13 as ean,
						p.id_product,
						0 as id_warehouse,
						0 as id_product_attribute,
						IFNULL(m.name, \'\') as manufacturer_name ';
		}

		$query .= 'FROM '._DB_PREFIX_.'product p ';
		$query .= ' INNER JOIN '._DB_PREFIX_.'product_lang pl ON
					(p.id_product = pl.id_product AND pl.id_lang = '.(int)Context::getContext()->language->id.')
					LEFT JOIN '._DB_PREFIX_.'image i ON p.id_product = i.id_product
					INNER JOIN '._DB_PREFIX_.'category_lang cl ON (p.id_category_default = cl.id_category
					AND cl.id_lang = '.(int)Context::getContext()->language->id.')
					LEFT JOIN '._DB_PREFIX_.'manufacturer m ON p.id_manufacturer = m.id_manufacturer ';

		// Stock avancé, filtre entrepot
		if ($this->advanced_stock_management)
		{
			$query .= 'LEFT JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON wpl.id_product = p.id_product ';
			$query .= 'WHERE wpl.id_warehouse = '.intval($id_warehouse).' ';

			// Filtre zone, sous zone, emplacement
			if (!$area && $subarea != '')
				$query .= 'AND'.$this->getSubQuery (intval($id_warehouse), '0', 'subarea').' = "'.intval($subarea).'"';
			elseif ($area != '' && $subarea != '')
			{
				$query .= 'AND'.$this->getSubQuery (intval($id_warehouse), '0', 'area').' = "'.intval($area).'"';
				$query .= 'AND'.$this->getSubQuery (intval($id_warehouse), '0', 'subarea').' = "'.intval($subarea).'"';
			}
			else if ($area != '')
				$query .= 'AND'.$this->getSubQuery (intval($id_warehouse), '0', 'area').' = "'.intval($area).'"';

			$query .= 'AND ';
		}
		else
			$query .= 'WHERE ';

		$query .= ('p.id_product NOT IN (SELECT pa.id_product FROM '._DB_PREFIX_.'product_attribute pa) ');

		// Ajout des autres filtres
		$query .= $this->getFiltersQueries($id_category, $id_supplier, $id_manufacturer);

		$query .= ' UNION ';

		// Récupération des déclinaisons
		if ($this->advanced_stock_management)
		{
			$query .= 'SELECT IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \',
						al.name SEPARATOR \', \')),pl.name) as name,
						pa.reference,
						if (pa.ean13 = \'\', p.ean13, pa.ean13) as ean,
						p.id_product,
						wpl.id_warehouse,
						pa.id_product_attribute,
						IFNULL(m.name, \'\') as manufacturer_name, ';

                        $query .= $this->getSubQuery(intval($id_warehouse), 'pa.id_product_attribute', 'all');
		}
		else
			$query .= ('SELECT IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name,
				   p.reference, if (pa.ean13 = \'\', p.ean13, pa.ean13) as ean, p.id_product, 0 as id_warehouse, pa.id_product_attribute, IFNULL(m.name, \'\') as manufacturer_name ');
		$query .= ('FROM '._DB_PREFIX_.'product_attribute pa ');
		$query .= (
				' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pa.id_product = pl.id_product AND pl.id_lang = '.(int)Context::getContext()->language->id.')
				 LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = pa.id_product
				 LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)
				 LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)
				 LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.id_attribute = pac.id_attribute AND al.id_lang = '.(int)Context::getContext()->language->id.')
				 LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.(int)Context::getContext()->language->id.')
				 LEFT JOIN '._DB_PREFIX_.'image i ON p.id_product = i.id_product
				 INNER JOIN '._DB_PREFIX_.'category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '.(int)Context::getContext()->language->id.')
				 LEFT JOIN '._DB_PREFIX_.'manufacturer m ON p.id_manufacturer = m.id_manufacturer
				'
				);

		// Stock avancé, filtre entrepot
		if ($this->advanced_stock_management)
		{
			$query .= 'INNER JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON wpl.id_product = p.id_product AND wpl.id_product_attribute = pa.id_product_attribute ';
                        
                        $query .= ' LEFT JOIN '._DB_PREFIX_.'erpip_warehouse_product_location ewpl ON wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location ';
                        $query .= ' LEFT JOIN '._DB_PREFIX_.'erpip_zone area ON area.id_erpip_zone = ewpl.id_zone_parent ';
                        $query .= ' LEFT JOIN '._DB_PREFIX_.'erpip_zone sub_area ON sub_area.id_erpip_zone = ewpl.id_zone ';

                        $query .= 'WHERE wpl.id_warehouse = '.(int)$id_warehouse.' ';
                        
			// Filtre zone, sous zone, emplacement
			if ($area == '' && $subarea != '')
				$query .= 'AND '.$this->getSubQuery (intval($id_warehouse), 'pa.id_product_attribute', 'subarea').' = "'.intval($subarea).'"';
			elseif ($area != '' && $subarea != '')
			{
				$query .= 'AND '.$this->getSubQuery (intval($id_warehouse), 'pa.id_product_attribute', 'area').' = "'.intval($area).'"';
				$query .= 'AND'.$this->getSubQuery (intval($id_warehouse), 'pa.id_product_attribute', 'subarea').' = "'.intval($subarea).'"';
			}
			else if ($area != '')
				$query .= 'AND '.$this->getSubQuery (intval($id_warehouse), 'pa.id_product_attribute', 'area').' = "'.intval($area).'"';
				 
		}

		// Ajout des autres filtres
		$query .= $this->getFiltersQueries($id_category, $id_supplier, $id_manufacturer);

		$query .= ' GROUP BY pa.id_product_attribute ';
		$query .= ' ORDER BY manufacturer_name ';

                
		// Execution de la requête
		$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		// Ajout de la quantity en stock
		$products_return = array();

		foreach ($products as $product)
		{
			$query = new DbQuery();

			// Si gestion de stock avancé inactive, on affiche seulement la quantité
			if (!$this->advanced_stock_management)
			{
				// Sélectionne quantité
				$query->select('IFNULL(quantity, "0") as quantity');
				$query->from('stock_available');
				$query->where('id_product = '.(int)$product['id_product'].' AND id_product_attribute = '.(int)$product['id_product_attribute']);
			}
			else
			{
				 // Sélectionne quantité physique
				$query->select('IFNULL(physical_quantity, "0") as quantity');
				$query->from('stock');
				$query->where('id_product = '.(int)$product['id_product'].' AND id_product_attribute = '.(int)$product['id_product_attribute'].' AND id_warehouse = '.(int)$id_warehouse);
			}

			// Execute la requête
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

			// Ajoute les colonnes au tableau
			$product['quantity'] = $res['quantity'];

			// Récupération de l'id image du produit
			$id_image = Product::getCover((int)$product['id_product']);

			// Si on a une image pour le produit on la récupère
			if ($id_image != false)
			{
				$image = new Image($id_image['id_image']);
				$product['image'] = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().'-large_default.jpg';
			}
			// Sion on affiche aucune image disponible dans la bonne langue
			else
				$product['image'] = _PS_IMG_DIR_.'l/'.Context::getContext()->language->iso_code.'-default-home_default.jpg';

			$products_return[] = $product;
		}
                
		return $products_return;
	}


	/*
	* Requêts imbriquées : récupération des zones, sous zone et emplacement pour produit et déclinaison
	*
	*/
	private function getSubQuery($id_warehouse, $id_product_attribute, $return)
	{
                $rq_area = '(SELECT area.id_erpip_zone FROM '._DB_PREFIX_.'warehouse_product_location wpl
                             LEFT JOIN '._DB_PREFIX_.'erpip_warehouse_product_location ewpl ON wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location
                             LEFT JOIN '._DB_PREFIX_.'erpip_zone area ON area.id_erpip_zone = ewpl.id_zone_parent
                             WHERE wpl.id_product = p.id_product
                             AND wpl.id_product_attribute = '.intval($id_product_attribute).'
                             AND wpl.id_warehouse = '.intval($id_warehouse).') ';
		
		$rq_subarea = '(SELECT sub_area.id_erpip_zone FROM '._DB_PREFIX_.'warehouse_product_location wpl
                             LEFT JOIN '._DB_PREFIX_.'erpip_warehouse_product_location ewpl ON wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location
                             LEFT JOIN '._DB_PREFIX_.'erpip_zone sub_area ON sub_area.id_erpip_zone = ewpl.id_zone
                             WHERE wpl.id_product = p.id_product 
                             AND wpl.id_product_attribute = '.intval($id_product_attribute).'
                             AND wpl.id_warehouse = '.intval($id_warehouse).') ';
                
                
		$rq_location = '(SELECT wpl.location FROM '._DB_PREFIX_.'warehouse_product_location wpl
						WHERE wpl.id_product = p.id_product AND wpl.id_product_attribute = '.(int)$id_product_attribute.'
						AND wpl.id_warehouse = '.(int)$id_warehouse.') ';

		$output = '';

		switch ($return)
		{
			case 'all':
				$output = $rq_area.' as zone, '.$rq_subarea.' as sous_zone, '.$rq_location.' as location ';
			break;

			case 'area':
				$output = $rq_area;
			break;

			case 'subarea':
				$output = $rq_subarea;
			break;

			case 'location':
				$output = $rq_location;
			break;

			default:
				$output = $rq_area.' '.$rq_subarea.' '.$rq_location;
			break;
		}

		return $output;
	}

	private function getFiltersQueries($id_category, $id_supplier, $id_manufacturer)
	{
		$query = '';
		//Filtre catégorie
		if ($id_category != '')
		{
			$query .= ' AND p.id_product IN (
						SELECT cp.id_product
						FROM '._DB_PREFIX_.'category_product cp
						WHERE cp.id_category = '.intval($id_category).'
				)';
		}

		// Filtre fournisseur
		if ($id_supplier != '')
		{
			$query .= ' AND p.id_product IN (
								SELECT ps.id_product
								FROM '._DB_PREFIX_.'product_supplier ps
								WHERE ps.id_supplier = '.intval($id_supplier).'
						)';
		}

		// Filtre Marque
		if ($id_manufacturer != false)
			$query .= ' AND p.id_manufacturer = '.(int)$id_manufacturer.' ';

		return $query;
	}

	/*
        * return global stock gap of inventory  
	*/
	public static function getTotalStockGap($id_container)
	{
		$query = new DbQuery();
		$query->select('id_product, id_product_attribute, (qte_after - qte_before) as gap');
		$query->from('erpip_inventory_product');
		$query->where('id_erpip_inventory = '.(int)$id_container);

		$rows = Db::getInstance()->executeS($query);

		$total = 0;

                if (!empty($rows))
                {
                    foreach ($rows as $row) 
                    {
                        $wholesale_price = InventoryProduct::getWholesalePrice($row['id_product'], $row['id_product_attribute']);
                        $total += (int)$row['gap'] * (int)$wholesale_price;
                    }
                }
                
		return $total;
	}

	/*
	*   return wholesale price of product of product attribute
	*/
	public static function getWholesalePrice($id_product, $id_product_attribute = 0, $id_supplier = 0)
	{
		//S'il y a fournisseur
                if (!empty($id_supplier))
                {
                        //On récupère tout d'abord le prix du fournisseur
                        $prices = ErpProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $id_supplier, true);
                        $price = $prices['product_supplier_price_te'];
                }

                // Si pas de prix pour ce fournisseur, ou prix fournisseur nul, on cherche le prix du produit ou de la déclinaison
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
                                $query->from('product_attribute', 'pa');
                                $query->where('pa.id_product = '.(int)$id_product);
                                $query->where('pa.id_product_attribute = '.(int)$id_product_attribute);
                                $query->innerJoin('product', 'p', ' p.id_product = pa.id_product');
                                $prices = Db::getInstance()->getRow($query);

                                //si la déclinaison à un prix
                                if (!empty($prices['wholesale_price_product_attribute']) && $prices['wholesale_price_product_attribute'] != '0.000000')
                                        $price = $prices['wholesale_price_product_attribute'];

                                //sinon, on prend le prix du produit
                                elseif (!empty($prices['wholesale_price_product']) && $prices['wholesale_price_product'] != '0.000000')
                                        $price = $prices['wholesale_price_product'];

                                //Sinon zero
                                else
                                        $price = '0.00000';
                        }
                }
                return $price;
	}
}