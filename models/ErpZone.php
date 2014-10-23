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

class ErpZone extends ObjectModel
{
    public $id_erpip_zone;
    public $id_warehouse;
    public $name;
    public $id_parent;
    public $active;
    public $date_add;
    public $date_upd;
    public static $zone_breadcrumbs = array();
    public static $secure_loop = 0;
    
    /*
    *	ORM
    */
    public static $definition = array
    (
        'table' => 'erpip_zone',
        'primary' => 'id_erpip_zone',
        'multilang' => false,
        'fields' => array
        (
            'id_warehouse' => array(
                'type' => self::TYPE_INT, 
                'validate' => 'isUnsignedInt'
            ),
            'name' => array(
                'type' => ObjectModel::TYPE_STRING, 
                'validate' => 'isGenericName',
                'required' => true
            ),
            'id_parent' => array(
                'type' => self::TYPE_INT, 
                'validate' => 'isUnsignedInt'
            ),
            'active' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'date_add' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );
    
    public static function getZones($except = null)
    {
        $query = new DbQuery();
        $query->select('name, id_erpip_zone');
        $query->from('erpip_zone');
        
        if (!is_null($except) && (int)$except > 0 )
            $query->where('id_erpip_zone != '.(int)$except);
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    }
    
    public static function getZonesByWarehouse($id_warehouse = null)
    {
        $query = new DbQuery();
        $query->select('name, id_erpip_zone');
        $query->from('erpip_zone');
        
        if (!is_null($id_warehouse) && (int)$id_warehouse > 0 )
            $query->where('id_warehouse != '.(int)$id_warehouse);
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    }
    
    public static function getZoneByNameAndWarehouse($name, $id_warehouse)
    {
        $query = new DbQuery();
        $query->select('name');
        $query->from('erpip_zone');
        $query->where('id_warehouse = '.(int)$id_warehouse . ' AND name = "'. pSQL($name) . '"');
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    }
    
    public static function getZoneBreadcrumbs($first_id_zone)
    {
        if (self::$secure_loop > 50)
            return array();
        
        $query = new DbQuery();
        $query->select('name, id_parent');
        $query->from('erpip_zone');
        $query->where('id_erpip_zone = '.(int)$first_id_zone);
        
        $zone = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        
        if (!empty($zone) && (int)$zone['id_parent'] > 0)
        {
            self::$zone_breadcrumbs[] = $zone['name'];
            self::getZoneBreadcrumbs($zone['id_parent']);
        } 
        else if (!empty($zone)) 
        {
            self::$zone_breadcrumbs[] = $zone['name'];
        }
        
        return self::$zone_breadcrumbs;
    }
    
    
   public static function getZoneByProducts()
   {
       $query = new DbQuery();
       $query->select(
               'id_product, id_product_attribute, wpl.id_warehouse_product_location, 
                id_wharehouse, location, id_erpip_warehouse_product_location, id_zone, id_zone_parent');
       
       $query->from('warehouse_product_location','wpl');
       $query->inner('erp_warehouse_product_location','ewpl','wpl.id_warehouse_product_location = ewpl.id_warehouse_product_location');
       
       return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
   }
    
    public static function getZonesName($id_warehouse, $level = 'area' , $id_parent = 0)
    {        
        $result = Db::getInstance()->executeS('
                SELECT DISTINCT z.`id_erpip_zone`, z.`name` , z.`id_parent`
                FROM `'._DB_PREFIX_.'erpip_zone` z
                WHERE z.active = 1 AND z.id_warehouse = '.(int)$id_warehouse.' 
                AND z.id_parent '.($level == 'area' ? ' = 0 ' : ' > 0 ').'  
                '.((int)$id_parent > 0 ? ' AND z.id_parent = '.intval($id_parent) : '').'
                GROUP BY z.`id_erpip_zone`
                LIMIT 100 ');
        
        return $result;
    }
}