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

class StockTransferHelper
{

	/**
	 * Gets the data as an array ('id_product','id_product_attribute','quantity') equivalent to the $transfertDataString given
	 *
	 * @since 1.5.0
	 * @param string $transfertDataString
	 * @param string $elementSeparator Optional
	 * @param string $quantitySeparator Optional
	 * @param string $paSeparator Optional
	 * @return array ('id_product','id_product_attribute','quantity')
	 */
	public static function getTransfertDataAsArray ($transfertDataString, $elementSeparator = '_', $quantitySeparator = '|', $paSeparator = ';')
	{
                // transform transfert chain in an array without duplicate (to set in a method and to call in ajax method => refactoring)
		$transferts = explode ($elementSeparator, $transfertDataString);
		$transferts = array_reverse ($transferts);

		$data = array();

		foreach ($transferts as $transfert)
		{                        
			$ligne = explode ($quantitySeparator, $transfert);

			if (count($ligne) >= 2)
			{
                                // if already treated for this ID (;ID_ATTRIBUTE)  go to next
				if (isset ($data[$ligne[0]]) || empty($ligne[0]))
					continue;

				$id_product = $ligne[0];
				$id_product_attribute = null;

                                // declension case
				if (strpos ($id_product, $paSeparator))
				{
					$ids = explode ($paSeparator, $id_product);

					if (count($ids) == 2)
					{
						$id_product = $ids[0];
						$id_product_attribute 	= $ids[1];
					}

				}
				$transferQuantity = (int)$ligne[1];
				$id_stock_s1 = isset($ligne[2]) ? (int)$ligne[2] : 0;
				$id_stock_s2 = isset($ligne[3]) ? (int)$ligne[3] : 0;

				$data[$ligne[0]] = array (
                                    'id_product' => $id_product,
                                    'id_product_attribute' => $id_product_attribute,
                                    'quantity' => $transferQuantity,
                                    'id_stock_s1' => $id_stock_s1,
                                    'id_stock_s2' => $id_stock_s2
                                );
			}
		}
                
		return $data;
	}

}