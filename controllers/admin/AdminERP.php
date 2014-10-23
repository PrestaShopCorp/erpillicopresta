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

require_once _PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/interval.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminERPController extends ModuleAdminController
{

	public function __construct()
	{
                
		$this->bootstrap = true;
		$this->table = 'erpip_feature';
		$this->className = 'ErpFeature';
		
                $this->context = Context::getContext();
            
                
                // On n'autorise que les iso_code fr ou en (par défaut)
                $iso_code = $this->context->language->iso_code;
                if($iso_code != "fr")
                {
                    $iso_code = "en";
                }
				$this->iso_code = $iso_code;
            
                
                $this->_select = ' efl.name, CONCAT("index.php?controller=",a.controller) as link';
                $this->_join = ' INNER JOIN `'._DB_PREFIX_.'erpip_feature_language` as efl ON (efl.id_erpip_feature = a.id_erpip_feature AND efl.iso_code = "' . $iso_code . '")';
                $this->_orderBy = 'a.order';
			
		$this->fields_list = array(
			'picture' => array(
				'title' => $this->l('Picture'),
				'width' => 10,
				'orderby' => false,
				'filter' => false,
				'search' => false,
				'callback' => 'renderImageColumn',
                                'remove_onclick' => true
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 100,
				'orderby' => false,
				'filter' => false,
				'search' => false,
                                'remove_onclick' => true
			),
			'link' => array(
				'title' => $this->l('Link'),
				'width' => 50,
				'orderby' => false,
				'filter' => false,
				'search' => false,
                                'callback' => 'renderIdErpFeatureLinkColumn',
                                'remove_onclick' => true
			),
		);
		
		if(_PS_VERSION_ < 1.6)
		{
			$this->no_link = true;
		}
		else 
		{
			$this->list_no_link = true;
		}		

		parent::__construct();
	}

	/* Add token */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

		$nb_items = count($this->_list);
		$this->tpl_list_vars['nb_items'] = $nb_items;
                
                if($nb_items > 0)
                {
                    for ($i = 0; $i < $nb_items; ++$i)
                    {
                            $item = &$this->_list[$i];
                            $item['token'] = Tools::getAdminTokenLite($item['controller']);
                    }
                }
                else
                    $this->displayWarning($this->l('Error : there is a problem with your license. Please go to the configuration of the ERP module to update your license.'));
        }

	/**
	* Get feature image
	* @param string $path image path
	*/
	public function renderImageColumn($path)
	{
            $html = '<img style="height: 32px;" alt="logo" src="../modules/erpillicopresta/img/features/'.$path.'"/>';
            return $html;
	}
        
	public function renderIdErpFeatureLinkColumn($link, $data)
	{
		// get licence
		$licence = Licence::crypt(Configuration::get('ERP_LICENCE'));

		// Le contrôleur est-il autorisé par le pack ?
		if(ERPControl::checkController($data['controller']))
		{
			$html = '<a href="'.$link.'&token='.$data['token'].'">';
			$html .= '<img src="../modules/erpillicopresta/img/features/arrow.png" alt="'.$this->l('Display selected feature').'"/></a>';
			return $html;
		}
		else
		{
			return '<a class="admin_links" href="http://shop.illicopresta.com/?controller=order&iso='.$this->iso_code.'&feature='.$data['controller'].'&licence='.$licence['licence_encode'].'&iv='.$licence['iv'].'" target="blank">'.$this->l('Check out our upper packs !').'</a>';
		}
		
	}

	public function initToolbar()
	{

	}
        
        public function renderForm()
        {
            $feature = ErpFeature::getFeatureById((int)Tools::getValue('id_erpip_feature'), $this->context->language->iso_code);
            Tools::redirectAdmin($this->context->link->getAdminLink($feature['controller']));
        }
                
        /* RJMA
	 * Rajout pour la traduction du controller AdminERP
	*/
	protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = true)
	{
			if (!empty($class))
			{
				$str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminERP');
				$str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
				return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
			}
	}
}