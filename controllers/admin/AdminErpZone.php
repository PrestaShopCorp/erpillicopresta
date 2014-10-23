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

require_once(_PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpZone.php');
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminErpZoneController extends ModuleAdminController
{
        public $bootstrap = true;
        private $id_current_zone = -1;
        
	public function __construct()
	{
            $this->table = 'erpip_zone';
            $this->className = 'ErpZone';
            $this->lang = false;
            $this->context = Context::getContext();
            if(_PS_VERSION_ < 1.6)
			{
				$this->no_link = true;
			}
			else 
			{
				$this->list_no_link = true;
			}	
            
            $this->addRowAction('view');
            $this->addRowAction('edit');
            $this->addRowAction('delete');
              
            // template path
            $this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';
            
            $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selection'), 'confirm' => $this->l('Delete selected items?')));
            
            $this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0;
             
            $this->_select = ' IFNULL(ez.name, \''.$this->l('Home').'\') as parent_name, w.name as warehouse_name, w.id_warehouse';
            $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'erpip_zone` as ez ON (ez.`id_erpip_zone` = a.`id_parent`)';
            $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'warehouse` as w ON (w.`id_warehouse` = a.`id_warehouse`)';
            
            if ((Tools::isSubmit('id_erpip_zone') && (int)Tools::getValue('id_erpip_zone') > 0) || (Tools::isSubmit('id_parent') && (int)Tools::getValue('id_parent') > 0))
            {
                $id_erpip_zone = ((int)Tools::getValue('id_erpip_zone') > 0) ? (int)Tools::getValue('id_erpip_zone') : (int)Tools::getValue('id_parent');
                
                $this->_where .= ' AND a.id_parent = '.$id_erpip_zone;
                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA : ');
                $this->toolbar_title .= implode(' > ', array_reverse(ErpZone::getZoneBreadcrumbs($id_erpip_zone)));
            }
            else
            {
                $this->_where .= ' AND a.id_parent = 0';
                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA : Home zones');
            }   
            
            $this->fields_list = array(
                    'id_erpip_zone' => array(
                            'title' => $this->l('ID'),
                            'width' => '30',
                            'remove_onclick' => true
                    ),
                    'name' => array(
                            'title' => $this->l('Zone name'),
                            'width' => 'auto',
                            'filter_key' => 'a!name',
                            'remove_onclick' => true
                        
                    ),
                    'parent_name' => array(
                            'title' => $this->l('Parent zone'),
                            'width' => 'auto',
                            'search' => false,
                            'remove_onclick' => true
                    ),
                    'warehouse_name' => array(
                            'title' => $this->l('Warehouse'),
                            'width' => 'auto',
                            'filter_key' => 'w!name',
                            'remove_onclick' => true
                    ),
                    'active' => array(
                            'title' => $this->l('Enabled'),
                            'width' => 70,
                            'align' => 'center',
                            'active' => 'status',
                            'type' => 'bool',
                            'orderby' => false,
                            'remove_onclick' => true
                    )
            );
            
            
            $this->context->smarty->assign(array(
                    'template_path' => $this->template_path,
                    'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code)
            ));
             
            parent::__construct();
        }
        
        // Non utilisable hors gestion de stock avancé
        public function initContent()
	{
            if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
            {
                $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management prior to using this feature. (Preferences/Products/Products Stock)');
                return false;
            }
            
            return parent::initContent();
        }
        
        // Surcharge bouton action VIEW
        public function displayViewLink($token, $id)
        {
            $nb_items = count($this->_list);

            // Pärcours de la liste des zones et récupération de la zone parent & warehouse pour la rajouter dans le lien
            for ($i = 0; $i < $nb_items; ++$i)
            {
                $item = &$this->_list[$i];
                
                // Si on est sur le bon id, on surcharge le bouton avec les infos (que sur les zones de nv1)
                if($item['id_erpip_zone'] == $id && $item['id_parent'] == '0')
                {
                    $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');

                    $tpl->assign(array(
                        'href' => self::$currentIndex.'&token='.$this->token.'&'.$this->identifier.'='.$id.'&id_zone_parent='.$item['id_parent'].'&id_warehouse='.$item['id_warehouse'].'&warehouse_name='.$item['warehouse_name'].'&zone_name='.$item['name'],
                            'action' => $this->l('View')
                    ));
                }
            }
            
            if(isset($tpl))
                return $tpl->fetch();
       
        }
        
        // Surcharge bouton action EDIT
        public function displayEditLink($token, $id)
        {
            $nb_items = count($this->_list);

            // Pärcours de la liste des zones et récupération de la zone parent & warehouse pour la rajouter dans le lien
            for ($i = 0; $i < $nb_items; ++$i)
            {
                $item = &$this->_list[$i];
                
                // Si on est sur le bon id, on surcharge le bouton avec les infos
                if($item['id_erpip_zone'] == $id)
                {
                    $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');

                    $tpl->assign(array(
                        'href' => self::$currentIndex.'&token='.$this->token.'&'.$this->identifier.'='.$id.'&id_zone_parent='.$item['id_parent'].'&id_warehouse='.$item['id_warehouse'].'&warehouse_name='.$item['warehouse_name'].'&zone_name='.$item['parent_name'].'&updateerpip_zone',
                         'action' => $this->l('Edit')
                    ));
                }
            }

            return $tpl->fetch();
        }
        
        public function renderForm()
	{   
		// loads current warehouse
		if (!($this->loadObject(true)))
			return;
                
                // Recupération des zones de l'entrepot en cours
                $zones = ErpZone::getZonesByWarehouse(Tools::getValue('id_warehouse'));
                
                array_unshift($zones, array('name' => $this->l('Home'), 'id_erpip_zone' => 0));
                              
                // gets warehouses
		$warehouses_add = Warehouse::getWarehouses(true);
		
		// displays warning if no warehouses
		if (!$warehouses_add)
			$this->displayWarning($this->l('You must choose a warehouse before adding areas. See Stock/Warehouses'));
                
                // Valeurs par défaut
                if(Tools::isSubmit('id_zone_parent') && Tools::getValue('id_zone_parent') != '')
                    $this->fields_value['id_parent'] = Tools::getValue('id_zone_parent');
                
                if(Tools::isSubmit('id_warehouse') && Tools::getValue('id_warehouse') != '')
                    $this->fields_value['id_warehouse'] = Tools::getValue('id_warehouse');
                
                $this->fields_value['active'] = true;
                               
                // -- Définition des champs du formulaire
                
                // Nom
                $base = array(
                            array(
                                    'type' => 'text',
                                    'label' => $this->l('Name'),
                                    'name' => 'name',
                                    'size' => 40,
                                    'required' => true,
                                    'hint' => $this->l('Invalid characters:').' <>;=#{}',
                            )
                    );
                
                // Entrepot
                // Sélection d'un entrepot que si création d'une ZONE (nv 1)
                if(Tools::getValue('id_zone_parent') == '' &&  Tools::getValue('id_warehouse') == '')
                {
                    $warehouse = array(
                                        array(
                                                'type' => 'select',
                                                'label' => $this->l('Warehouse'),
                                                'name' => 'id_warehouse',
                                                'class'=> 'chosen',
                                                'required' => true,
                                                'options' => array(
                                                        'query' => $warehouses_add,
                                                        'id' => 'id_warehouse',
                                                        'name' => 'name'
                                                )
                                            )
                        );
                }
                else
                {
                    $warehouse = array(
                                    array(
                                                'type' => 'text',
                                                'label' => $this->l('Warehouse'),
                                                'name' => 'warehouse_name',
                                                'disabled' => true,
                                                'size' => 40,
                                                'required' => true,
                                    ),
                                    array(
                                            'type' => 'hidden',
                                            'name' => 'id_warehouse',
                                            'required' => true,
                                    )
                        
                    );
                }
                
                // Affichage de la zone (nv1) et sélection actif / inactif
                // Si on est sur une création de zone (nv1) --> on set les variables utilisés par le helperform sur l'accueil (on obglie a créer une ZONE)
                if(Tools::getValue('id_parent') == '' && Tools::getValue('id_zone_parent') == '')
                {
                    $_GET['zone_name'] = 'Accueil';
                    $_GET['id_parent'] = '0';
                }
                $areaAndActive = array(
                                        array(
                                                'type' => 'text',
                                                'label' => $this->l('Parent zone'),
                                                'name' => 'zone_name',
                                                'disabled' => true,
                                                'size' => 40,
                                                'required' => true,
                                        ),
                                        array(
                                                'type' => 'hidden',
                                                'name' => 'id_parent',
                                                'required' => true,
                                        ),
                                        array(
                                            'type' => $this->is_1_6 ? 'switch': 'radio',
                                            'label' => $this->l('Enable:'),
                                            'name' => 'active',
                                            'required' => false,
                                            'class' => 't',
                                            'is_bool' => true,
                                            'values' => array(
                                                    array(
                                                            'id' => 'active_on',
                                                            'value' => 1,
                                                            'label' => $this->l('Enabled')
                                                    ),
                                                    array(
                                                            'id' => 'active_off',
                                                            'value' => 0,
                                                            'label' => $this->l('Disabled')
                                                    )
                                            )
                                        )
                );
                        
                // Fomulaire complet
                $this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Add or edit an area'),
				'image' => '../img/admin/suppliers.gif'
			),
			'input' => array_merge($base, $warehouse, $areaAndActive),
                        'submit' => array(
				'title' => $this->l('   Save   '),
				//'class' => 'button'
			)
                );
                
                return parent::renderForm();
        }
        
        // Vérification que la zone n'existe pas déjà sur ce warehouse
        public function beforeAdd($object)
	{
            $area = ErpZone::getZoneByNameAndWarehouse($object->name, $object->id_warehouse);
            if(count($area) == 0)
                return parent::beforeAdd($object);
            else
            {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminErpZone').'&exist=true');
            }
                
        }
        
        // ne pas rediriger dans la sous zone apres enregistrement
        public function processSave()
	{
            $url_redirect = self::$currentIndex.'&';
            $url_redirect .= 'token='.$this->token.'&';
            $url_redirect .= 'id_erpip_zone='.$this->context->cookie->id_erpip_zone.'&';
            $url_redirect .= 'id_zone_parent=0&id_warehouse='.$this->context->cookie->id_warehouse.'&';
            $url_redirect .= 'warehouse_name='.$this->context->cookie->warehouse_name.'&';
            $url_redirect .= 'zone_name='.$this->context->cookie->zone_name;   
            
            $this->redirect_after = $url_redirect;
            
            return parent::processSave();
        }
        
        /**
	 * AdminController::postProcess() override
	 * @see AdminController::postProcess()
	 */
	public function postProcess()
	{
            if(Tools::isSubmit('exist') && Tools::getValue('exist') == 'true')
                $this->errors[] = $this->l('This area already exists in this warehouse !');
            
            // checks access
            if (Tools::isSubmit('submitAdd'.$this->table) && !($this->tabAccess['add'] === '1'))
            {
                    $this->errors[] = Tools::displayError('You do not have permission to add zone.');
                    return parent::postProcess();
            }
            
            return parent::postProcess();
        }
        
        public function renderView()
	{
		$this->initToolbar();
		return $this->renderList();
	}
        
        public function renderList()
	{
            $this->toolbar_title = $this->l('Areas');
            
            $cookie = new Cookie('psAdmin');
            $cookie->id_erpip_zone = Tools::getValue('id_erpip_zone');
            $cookie->id_warehouse = Tools::getValue('id_warehouse');
            $cookie->warehouse_name = Tools::getValue('warehouse_name');
            $cookie->zone_name = Tools::getValue('zone_name');
            
            return parent::renderList();
        }
        
        // toolbar 1.5
        public function initToolbar()
	{
            parent::initToolbar();
            unset($this->toolbar_btn['new']);
                        
            if ($this->display != 'add' && $this->display != 'edit')
            {
                if (!$this->is_1_6)
                {
                    $this->toolbar_btn['new'] = array(
                        'href' => self::$currentIndex.'&amp;add'.$this->table.'&amp;token='.$this->token.'&id_parent='.Tools::getValue('id_erpip_zone').'&id_warehouse='.Tools::getValue('id_warehouse').'&warehouse_name='.Tools::getValue('warehouse_name').'&zone_name='.Tools::getValue('zone_name').'&display=add',
                        'desc' => $this->l('Add new')
                    );
                }
            }
            
            if(!empty($this->display) || Tools::isSubmit('id_erpip_zone'))
            {
                $this->toolbar_btn['back'] = array(
                    'href' => self::$currentIndex.'&amp;token='.$this->token,
                    'desc' => $this->l('Back')
                );
            }
        }
        
        // toolbar 1.6
        public function initPageHeaderToolbar()
	{
            parent::initPageHeaderToolbar();
            
            if ($this->display != 'add' && $this->display != 'edit')
            {            
                $url_add_zone = self::$currentIndex.'&';
                $url_add_zone .= 'add'.$this->table.'&';
                $url_add_zone .= 'token='.$this->token.'&';
                $url_add_zone .= 'id_parent='.Tools::getValue('id_erpip_zone').'&';
                $url_add_zone .= 'id_warehouse='.Tools::getValue('id_warehouse').'&';
                $url_add_zone .= 'warehouse_name='.Tools::getValue('warehouse_name').'&';
                $url_add_zone .= 'zone_name='.Tools::getValue('zone_name').'&';
                $url_add_zone .= 'display=add';
                        
                $this->page_header_toolbar_btn['new'] = array(
                    'href' => $url_add_zone,
                    'desc' => $this->l('Add new'),
                );
            }
            
            if(!empty($this->display) || Tools::isSubmit('id_erpip_zone'))
            {
                $this->page_header_toolbar_btn['back_to_list'] = array(
                    'href' => self::$currentIndex.'&token='.$this->token,
                    'desc' => $this->l('Back'),
                    'icon' => 'process-icon-back'
                );
            }
        }
                
        // Non utilisé dans ERP Zone mais dans gestion de stock : récupère les zones en AC.
        public function ajaxProcessCheckAreaName()
	{
                $result = array();
                $limit = Tools::getValue('limit');
                $search = Tools::getValue('q');
                $id_warehouse = (int)Tools::getValue('id_warehouse');
                $id_parent = (int)Tools::getValue('id_parent');
                
                if (Tools::getValue('level') == 'sub_area' && empty($id_parent))
                   $result = false;
                else 
                {
                   if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP)
                        $result = false;
                    else 
                    {
                            $result = Db::getInstance()->executeS('
                                    SELECT DISTINCT z.`id_erpip_zone`, z.`name` , z.`id_parent`
                                    FROM `'._DB_PREFIX_.'erpip_zone` z
                                    WHERE z.`name` LIKE "%'.pSQL($search).'%" AND z.`id_warehouse` = '.$id_warehouse.' AND z.active = 1
                                    AND z.id_parent '.(Tools::getValue('level') == 'area' ? ' = 0 ' : ' > 0 ').'  
                                    '.($id_parent > 0 ? ' AND z.id_parent = '.$id_parent : '').'
                                    GROUP BY z.`id_erpip_zone`
                                    LIMIT '.(int)$limit, true, false);
                    }
                }
                
                die(Tools::jsonEncode($result));
	}
        
        
        /* RJMA
	 * Rajout pour la traduction du controller AdminAdvancedStock
	*/
	protected function l($string, $class = 'AdminErpZone', $addslashes = false, $htmlentities = false)
	{
            if (!empty($class))
            {
                $str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminErpZone');
                $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
                return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
            }
	}
}