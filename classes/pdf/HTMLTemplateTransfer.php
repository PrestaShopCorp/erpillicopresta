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

class HTMLTemplateTransfer extends HTMLTemplate
{
	/**/
	public function __construct($products, $smarty)
	{
		$this->smarty = $smarty;
		$this->products = $products;

		// header informations
		$this->title = HTMLTemplateTransfer::l('Stock Transfers');

		$this->date = date('d/m/Y');

		// footer informations
		$this->shop = new Shop(Context::getContext()->shop->id);

		/** Rustine NDE ... ... */
		$this->order = new stdClass();
		$this->order->id_shop = Context::getContext()->shop->id;
	}

	/* return the HTML template */
	public function getContent()
	{
		// assign smarty values
		$this->smarty->assign(array(
                        'products' => $this->products,
                        'stockA' => Warehouse::getWarehouseNameById(Tools::getValue('stockA')),
                        'stockB' => Warehouse::getWarehouseNameById(Tools::getValue('stockB'))
		));

		return $this->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/stock_transfer/transfers.tpl');
	}

	/* No footer */
	public function getFooter()
	{
		return false;
	}

	/**/
	public function getBulkFilename()
	{
		return 'stock_transfers_'.Date('dmY').'.pdf';
	}

	/**/
	public function getFilename()
	{
		return 'stock_transfers_'.Date('dmY').'.pdf';
	}
}