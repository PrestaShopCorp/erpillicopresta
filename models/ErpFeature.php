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

class ErpFeature extends ObjectModel
{
	public $id_erpip_feature;
    public $picture;	
	public $controller;
    public $is_root;
    public $order;
	public $status;
    public $key1;
    public $key2;
        

	/*
	*	ORM
	*/
	public static $definition = array
	(
		'table' => 'erpip_feature',
		'primary' => 'id_erpip_feature',
		'multilang' => false,
		'fields' => array
		(
			'id_erpip_feature' => array('type' => ObjectModel::TYPE_INT),
			'picture' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
			'controller' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
                        'is_root' => array('type' => ObjectModel::TYPE_BOOL, 'required' => true),
                        'order' => array('type' => ObjectModel::TYPE_INT, 'required' => true),
			'status' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
			'key1' => array('type' => ObjectModel::TYPE_STRING, 'required' => true),
			'key2' => array('type' => ObjectModel::TYPE_STRING, 'required' => true)
                        
		)
	);

	/*
	* check if status is pro
	*/
	public static function isPro($status)
	{
		if ($status == md5('pro'))
			return 1;

		return 0;
	}

        /*
	* Get a controller by id in erp_feature table
	*/
	public static function getFeatureById($id_erpip_feature, $iso_code)
	{
                //VERRUE : on n'autorise que les iso_code fr ou en (par défaut)
                if ($iso_code != "fr")
                {
                    $iso_code = "en";
                }
            
		//Query
		$sql = 'SELECT ef.id_erpip_feature, ef.controller, ef.picture, ef.status, efl.name';
                $sql .= ' FROM '._DB_PREFIX_.'erpip_feature ef ';
                $sql .= ' INNER JOIN '._DB_PREFIX_.'erpip_feature_language efl ON ef.id_erpip_feature = efl.id_erpip_feature ';
				$sql .=' AND efl.iso_code = "'. $iso_code.'"';
                $sql .= 'WHERE ef.id_erpip_feature = '.(int)$id_erpip_feature;

		$res = Db::getInstance()->getRow($sql);

		if (!empty($res))
			return $res;

		return false;
	}
        
	/*
	* Get list of controller in erp_feature table
	*/
	public static function getFeatures($iso_code)
	{
                //VERRUE : on n'autorise que les iso_code fr ou en (par défaut)
                if ($iso_code != "fr")
                {
                    $iso_code = "en";
                }
            
		//Query
		$sql = 'SELECT ef.id_erpip_feature, ef.controller, ef.picture, ef.status, efl.name';
                $sql .= ' FROM '._DB_PREFIX_.'erpip_feature ef ';
                $sql .= ' INNER JOIN '._DB_PREFIX_.'erpip_feature_language efl ON ef.id_erpip_feature = efl.id_erpip_feature ';
				$sql .=' AND efl.iso_code = "'. $iso_code.'"';

		$res = Db::getInstance()->executeS($sql);

		if (!empty($res))
			return $res;

		return false;
	}
        
        /*
	* Get list of controllers
	*/
	public static function getControllers()
	{
            try
            {
                //Query
                $sql = 'SELECT GROUP_CONCAT(controller) as controllers';
                $sql .= ' FROM '._DB_PREFIX_.'erpip_feature';

                $res = Db::getInstance()->getRow($sql);

                if (!empty($res))
                        return $res['controllers'];

                return false;
            } catch (Exception $ex) {
                return false;
            }
	}

	/*
	* Get list of controller in erp_feature table
	*/
	public static function getFeaturesWithToken($iso_code)
	{
                //VERRUE : on n'autorise que les iso_code fr ou en (par défaut)
                if ($iso_code != "fr")
                {
                    $iso_code = "en";
                }
                
                $erp_features_final = array();
                $order = 'order';
                
		//Query
		$sql = 'SELECT ef.id_erpip_feature, ef.key1, ef.key2, ef.controller, ef.picture, ef.status, efl.name';
                $sql .= ' FROM '._DB_PREFIX_.'erpip_feature ef ';
                $sql .= ' INNER JOIN '._DB_PREFIX_.'erpip_feature_language efl ON ef.id_erpip_feature = efl.id_erpip_feature ';
                $sql .=' AND efl.iso_code = "'. $iso_code.'"';
                $sql .= ' ORDER BY `'.$order.'` ASC';

		$erp_features = Db::getInstance()->executeS($sql);

		if (!empty($erp_features))
		{
                    foreach ($erp_features as $feature)
                    {
                            $feature['token'] = Tools::getAdminTokenLite($feature['controller']);
                            $feature['active'] = ERPControl::checkController($feature['controller']);
                            $erp_features_final[] = $feature;
                    }
		}

		return $erp_features_final;
	}
        
        /*
	* Get keys of the parameter controller
	*/
	public static function getControllerKeys($controller_name)
	{
            //Query
            $sql = 'SELECT key1, key2';
            $sql .= ' FROM '._DB_PREFIX_.'erpip_feature ';
            $sql .=' WHERE controller = "'. pSQL($controller_name).'"';

            $keys = Db::getInstance()->getRow($sql);

            return $keys;
	}
}