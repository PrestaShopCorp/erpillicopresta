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

class ErpSupplierClass extends Supplier
{
		/*
		* supplier is french or not
	*/
		static public function isSupplierFrench($id_supplier)
		{
				// get supplier addresse id
				$supplier_id_address = Address::getAddressIdBySupplierId((int)$id_supplier);

				// get adresse object
				$supplier_address = new Address( $supplier_id_address);

				// --> France
				//France        : 8 - FR
				//Suisse        : 19 - CH
				//Belgique      : 3 - BE
				//Canada        : 4 - CA
				//$french_id_counrty = array('8','19','3','4');

				// --> DOM
				//Guyane française (Amérique du Sud)    : 241 - GF
				//Guadeloupe (Antilles)                 : 98 - GP
				//La Réunion (océan Indien)             : 176 - RE
				//Martinique (Antilles)                 : 141 - MQ
				//$dom_id_counrty = array('241','98','176','141');

				// --> TOM
				//Nouvelle-Calédonie (Océanie)                              : 158 - NC
				//Polynésie française (Océanie)                             : 242 - PF
				//Wallis-et-Futuna (Océanie)                                : 225 - WF
				//Terres australes et antarctiques françaises (Antarctique) : 243 - TF
				//$tom_id_counrty = array('158','242','225','243');
				
				$iso_code_country = array('FR','CH','BE','CA','GF','GP','RE','MQ','NC','PF','WF','TF');
				
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("SELECT * FROM `"._DB_PREFIX_."_country` WHERE `iso_code` in ('".implode("','",$iso_code_country)."')");


				$countries = array();
				foreach ($result AS &$country)
					$countries[$country['id_country']] = $country;
					
				// check in countries that speak french
				 if (in_array($supplier_address->id_country, $countries))
					return true;
				// // check in countries that speak french
				// if (in_array($supplier_address->id_country, $french_id_counrty))
					   // return true;

				// //check in the DOM
				// elseif (in_array($supplier_address->id_country, $dom_id_counrty))
					   // return true;

				// // check in the TOM
				// elseif (in_array($supplier_address->id_country, $tom_id_counrty))
					   // return true;

				//eslse, no french
				else
				  return false;

		}
}