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

            $this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';

            // Only allow iso_code fr or en (default)
            $iso_code = $this->context->language->iso_code;

            if($iso_code != "fr")
                $iso_code = "en";

            $this->iso_code = $iso_code;

            $this->_select = ' efl.name, CONCAT("index.php?controller=",a.controller) as link';
            $this->_join = ' INNER JOIN `'._DB_PREFIX_.'erpip_feature_language` as efl ON (efl.id_erpip_feature = a.id_erpip_feature AND efl.iso_code = "' . pSQL($iso_code) . '")';
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
                $this->no_link = true;
            else
                $this->list_no_link = true;

            parent::__construct();
    }
        
    public function initContent()
    {
        if (!Configuration::hasKey('ERP_LICENCE') || Configuration::get('ERP_LICENCE') == '')
            $this->warnings[] = '<a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Your license has not been validated yet, please go to your module configuration page to validate it.').'</a>';
        
        elseif (Configuration::get('ERP_BLACKLIST') == '1')
            $this->errors[] = sprintf($this->l('Your license is blacklisted, please contact our technical support to this email: %s .'), ERP_EMAIL_SUPPORT);
        
        elseif (Configuration::get('ERP_LICENCE_VALIDITY') == '0')
            $this->errors[] = sprintf($this->l('Your license number is not valid, please contact our technical support to this mail adress: %s.'), ERP_EMAIL_SUPPORT);
        
        else {
        
            $link_conf = '<br/><a href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Go to your back-office, module tab, page 1-Click ERP.').'</a>' ;
            $this->informations[] = $this->l('Win up to 2hours a day in your store management with 1-Click ERP! Optimise your module in your Back-Office, Module tab, Page 1-Click ERP!').$link_conf;    
        }
        parent::initContent();
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

            $currentLanguage = Language::getIsoById((int)$this->context->language->id);

            $urlEShop = URL_ESHOP_EN;
			$urlTechnicalSupport = URL_TECHNICAL_SUPPORT_EN;
			
            if($currentLanguage == 'fr')
			{
                $urlEShop = URL_ESHOP_FR;
                $urlTechnicalSupport = URL_TECHNICAL_SUPPORT_FR;
			}
            elseif($currentLanguage == 'es')
                $urlTechnicalSupport = URL_TECHNICAL_SUPPORT_ES;

            elseif($currentLanguage == 'it')
                    $urlTechnicalSupport = URL_TECHNICAL_SUPPORT_IT;

            $this->context->smarty->assign(array(
                'token' => Tools::getValue('token'),
                'urlEShop' => $urlEShop,
                'urlTechnicalSupport' => $urlTechnicalSupport,
                'licenceActive' => (date('Y-m-d') > Configuration::get('ERP_WS_DATE_END')) ? false : true,
            ));

            // Configuration CSS/JS
            if(version_compare( _PS_VERSION_ , '1.6' ) == -1)
                $this->context->controller->addCSS(_MODULE_DIR_.'/erpillicopresta/css/bs3.css');

            $this->context->controller->addCSS(_MODULE_DIR_.'/erpillicopresta/css/adminerp.css');
            $this->context->controller->addJS(_MODULE_DIR_.'/erpillicopresta/js/tools.js');
    }


    /**
    * Get feature image
    * @param string $path image path
    */
    public function renderImageColumn($path)
    {
        return '<img style="height: 32px;" alt="logo" src="../modules/erpillicopresta/img/features/'.$path.'"/>';
    }

    public function renderIdErpFeatureLinkColumn($link, $data)
    {
        // Is the controller authorized in the pack ?
        if(ERPControl::checkController($data['controller']))
        {
            $html = '<a href="'.$link.'&token='.$data['token'].'">';
            $html .= '<img src="../modules/erpillicopresta/img/features/arrow.png" alt="'.$this->l('Display selected feature').'"/></a>';
            return $html;
        }
        else
            return '<a class="admin_links" href="?controller=AdminModules&configure=erpillicopresta&token='.Tools::getAdminTokenLite('AdminModules').'#cart_features" target="_blank">'.$this->l('Check out our upper packs !').'</a>';
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
     * Add to translate AdminERP controller
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