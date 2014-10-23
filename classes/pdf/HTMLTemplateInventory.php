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

class HTMLTemplateInventory extends HTMLTemplate
{
	public $id_wh = 1;

	public function __construct($products, $smarty)
	{
		$this->smarty = $smarty;
		$this->products = $products;

		// header informations
		$this->title = HTMLTemplateInventory::l('Inventory');

		$this->date = date('d/m/Y');

		if (Tools::getValue('id_warehouse') != '')
			$this->id_wh = Tools::getValue('id_warehouse');
	}

	public function manufacturerAscSort($product1, $product2)
	{
			if ($product1['manufacturer_name'] == $product2['manufacturer_name'])
			{
					if ($product1['id_product'] == $product2['id_product'])
					{
							if ($product1['id_product_attribute'] == $product2['id_product_attribute'])
									return 0;
							else if ($product1['id_product_attribute'] < $product2['id_product_attribute'])
									return -1;
							return 1;
					}
					else if ($product1['id_product'] < $product2['id_product'])
							return -1;
					return 1;
			}
			else if ($product1['manufacturer_name'] < $product2['manufacturer_name'])
			{
					if ($product1['manufacturer_name'] == '')
							return 1;
					return -1;
			}
			if ($product2['manufacturer_name'] == '')
					return -1;
			return 1;
	}

	public function productAscSort($product1, $product2)
	{
			if ($product1['zone'] == $product2['zone'])
			{
					if ($product1['sous_zone'] == $product2['sous_zone'])
					{
							   if ($product1['location'] == $product2['location'])
								{
									if ($product1['id_product'] == $product2['id_product'])
									{
										if ($product1['id_product_attribute'] == $product2['id_product_attribute'])
											return 0;
										else if ($product1['id_product_attribute'] < $product2['id_product_attribute'])
											return -1;
										return 1;
									}
									else if ($product1['id_product'] < $product2['id_product'])
										return -1;
									return 1;
								}
							else if ($product1['location'] < $product2['location'])
									return -1;
							return 1;
					}
					else if ($product1['sous_zone'] < $product2['sous_zone'])
							return -1;
					return 1;
			}
			else if ($product1['zone'] < $product2['zone'])
					return -1;
			return 1;
	}

	public function checkSubareaEmpty()
	{
			foreach ($this->products as $product)
			{
					if ($product['zone'] != '' && $product['sous_zone'] == '')
							return true;
			}
			return false;
	}


	// Retourne le template HTML
	public function getContent()
	{

		//$areas = WarehouseProductLocation::getAreasByWarehouseId($this->id_wh);
		//$subareas = WarehouseProductLocation::getSubAreasByWarehouseId($this->id_wh);
		$areas = array();
		$subareas = array();

		// Assignation valeurs Smarty
		// Si il y a des produit dans l'entrepot
		if (count($this->products) > 0)
		{
			// On tri par zone, sous-zone, emplacement puis id et id_product attribute en PDF avance
			if (Tools::getValue('advanced') == 'true')
				usort($this->products, array($this, 'productAscSort'));
			else // On tri par fabricant id et id_attribute si on est en PDF simple
				usort($this->products, array($this, 'manufacturerAscSort'));


			if (Tools::getValue('advanced') == 'true' && $this->checkSubareaEmpty() == true)
			{
				// Si on est en PDF avance et qu'il existe des produits avec une area mais sans subarea
				$this->smarty->assign('subareaError', 1);
				$this->smarty->assign('empty', 0);
			}
			else
			{

				$this->smarty->assign(array(
						'products' => $this->products,
						'areas' => $areas,
						'subareas' => $subareas,
						'empty' => 0,
						'subareaError' => 0
						));
			}
		}
		else
		{
			$this->smarty->assign('empty', 1);
			$this->smarty->assign('subareaError', 0);
		}

		// Ajout de la fonction de vérification du nombre de produit dans une zone complète, dans smarty
		$callback = array(&$this, 'countProductInFullArea');
		$this->smarty->registerPlugin('function', 'countProductInFullArea', $callback);

		if (Tools::getValue('advanced') == 'true')
			return $this->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/inventory/inventory.tpl');
		else
			return $this->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/inventory/simple-inventory.tpl');
	}

	// Redefinition de la methode pour bon fonctionnement en 1.5.4
	public function getHeader()
	{
		$shop_name = Configuration::get('PS_SHOP_NAME');
		$path_logo = $this->getLogo();

		$width = 0;
		$height = 0;
		if (!empty($path_logo))
			list($width, $height) = getimagesize($path_logo);

		$this->smarty->assign(array(
			'logo_path' => $path_logo,
			'img_ps_dir' => 'http://'.Tools::getMediaServer(_PS_IMG_)._PS_IMG_,
			'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
			'title' => $this->title,
			'date' => $this->date,
			'shop_name' => $shop_name,
			'width_logo' => $width,
			'height_logo' => $height
		));

		return $this->smarty->fetch($this->getTemplate('header'));
	}

	// Redefinition de la methode pour bon fonctionnement en 1.5.4
	public function getLogo() {
		$logo = '';

		//$physical_uri = Context::getContext()->shop->physical_uri.'img/';

		if (Configuration::get('PS_LOGO_INVOICE') != false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE')))
			$logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE');
		elseif (Configuration::get('PS_LOGO') != false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO')))
			$logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO');
		return $logo;
	}

	// Aucun footer
	public function getFooter()
	{
		return false;
	}

	public function getBulkFilename()
	{
		return 'inventory.pdf';
	}

	public function getFilename()
	{
		return 'inventory.pdf';
	}

	// Retourne le nombre de produits dans une zone complète (area / subarea)
	public function countProductInFullArea($params)
	{
		if (!empty($params))
		{
			/*$area = null;  //$params['area'];
			$subarea = null; // $params['subarea'];

			return WarehouseProductLocation::countProductInFullArea($area, $subarea, $this->id_wh);
			*/
		}
	}
}