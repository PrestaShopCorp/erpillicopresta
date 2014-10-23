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

class ErpWarehouseProductLocation extends ObjectModel
{
    public $id_erpip_warehouse_product_location;
    public $id_warehouse_product_location;
    public $zone;
    public $sous_zone;

    /*
    *	ORM
    */
    public static $definition = array
    (
        'table' => 'erpip_warehouse_product_location',
        'primary' => 'id_erpip_warehouse_product_location',
        'multilang' => false,
        'fields' => array
        (
            'id_warehouse_product_location' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_zone' => array(
                'type' => self::TYPE_INT, 
                'validate' => 'isUnsignedInt'
            ),
            'id_zone_parent' => array(
                'type' => self::TYPE_INT, 
                'validate' => 'isUnsignedInt'
            )
        )
    );

    /**
     * Returns id_erpip_warehouse_product_location for a given id_warehouse_product_location
     * @param int $id_warehouse_product_location
     * @return int $id_erpip_warehouse_product_location
     */
//    public static function getErpAssociation()
//    {
//            $query = new DbQuery();
//            $query->select('id_erpip_warehouse_product_location');
//            $query->from('erpip_warehouse_product_location');
//            $query->where('id_warehouse_product_location = '.(int)$id_warehouse_product_location);
//            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
//    }
}