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

class ErpFeatureLang extends ObjectModel
{
	public $id_erpip_feature_language;
        public $id_erpip_feature;
        public $iso_code;
        public $name;
        

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_feature_language',
		'primary' => 'id_erpip_feature_language',
		'multilang' => false,
		'fields' => array
		(
			'id_erpip_feature_language' => array('type' => ObjectModel::TYPE_INT),
			'id_erpip_feature' => array('type' => ObjectModel::TYPE_INT, 'required' => true),
			'iso_code' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
                        'name' => array('type' => ObjectModel::TYPE_STRING, 'required' => true)
		)
	);

}