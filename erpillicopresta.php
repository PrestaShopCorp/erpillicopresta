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

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpFeatureLang.php');
require_once _PS_MODULE_DIR_.'erpillicopresta/models/StockImage.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/StockImageContent.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/Inventory.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/InventoryProduct.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplier.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/define.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/Licence.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class ErpIllicopresta extends Module
{
	public static $checksum = array();

	public function __construct()
	{
		
				
		// recupération langue iso
		
		$this->context = Context::getContext();

		// On n'autorise que les iso_code fr ou en (par défaut)
		
		$iso_code = $this->context->language->iso_code;
		
		if($iso_code != "fr")
		{
			$iso_code = "en";
		}
		
		$this->iso_code = $iso_code;
		
		
		$this->bootstrap = true;

		$this->name = 'erpillicopresta';
		$this->tab = 'administration';
		$this->version = '2.6.1';
		$this->author = 'illicopresta';
		$this->displayName = $this->l('1 Click ERP Illicopresta');
		$this->description = $this->l('Save 2 hours / day with this complete and scalable ERP pack, quickly and effectively manage your shop (shipments, inventory, management and export inventory, purchase orders ...)');

                $this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0;
                 
		$this->trash_category_name = 'Divers'; //used in ajax/addProduct.php file

		// list of stock mvt reason to install
		$this->stock_mvt_reason = array
		(
			'Increase inventory' => array(
				'sign' => 1,
				'lang' => array('fr' => 'Augmentation d\'inventaire', 'en' => 'Increase of inventory')
			),

			'Decrease inventory' => array(
				'sign' => -1,
				'lang' => array('fr' => 'Diminution d\'inventaire', 'en' => 'Decrease of inventory')
			),

			'Reception cancelling' => array(
				'sign' => -1,
				'configuration_name' => 'ERP_RECEPTION_CANCELING_ID',
				'lang' => array('fr' => 'Annulation de réception', 'en' => 'Cancellation of reception')
			)
		);

		// list of original menu to enable/disable
		$this->original_menus = array
		(
			'AdminOrders',
			'AdminSupplyOrders',
			'AdminSuppliers',
		);

                // list of original menu for 1.6
               if ($this->is_1_6)
                     array_push($this->original_menus, 'AdminParentOrders');
                        
		// list of fileds name configuration
		$this->field_name_configuration = array
		(
			'erp_exceptional_order_limit' => array('default' => ''),
			'erp_comparison_period' => array('default' => 6),
			'erp_projected_period' => array('default' => 15),
			'erp_coefficients' => array('default' => '1.4;1.2;1;1;0.8;0.6'),
			'erp_sales_forecast_choice' => array('default' => 0),
			'erp_rolling_months_nb_so' => array('default' => 6),
			'erp_generate_order_state_to' => array('default' => 4),
			'erp_generate_order_state' => array('default' => 3),
			'erp_so_state_to_send_mail' => array('default' => ''),
			'erp_enable_sending_mail_supplier' => array('default' => 0),
			'erp_prefix_reference' => array('default' => 'SO'),
			'erp_disable_original_menus' => array('default' => '0'),
			'erp_state_to_send_mail_so' => array('default' => '2'),
			'erp_contact_mail' => array('default' => ''),
			'erp_licence_mail' => array('default' => ''),
			'erp_knowledge_source' => array('default' => ''),
			'erp_contact_name' => array('default' => ''),
			'erp_newsletter' => array('default' => 1),
			'erp_month_free_active' => array('default' => ( Configuration::get('ERP_MONTH_FREE_ACTIVE') == '1' ) ? '1' : '0' ),
		);

		parent::__construct();
	}

	public function install()
	{
		$e = get_headers(WS);
		if ($e[0] == 'HTTP/1.1 200 OK')
		{
		
			if($this->isCurlInstalled() == false)
			{
				$this->_errors[] = $this->l('Error while installing the module. CURL Extension is not active on your server. Please contact your server administrator.');
				return false;
			}
			
			if (Shop::isFeatureActive())
				Shop::setContext(Shop::CONTEXT_ALL);

                        if (!Configuration::hasKey('ERP_ADMIN_PARENT_ORDERS_TAB_ID'))
                            Configuration::updateValue('ERP_ADMIN_PARENT_ORDERS_TAB_ID', Tab::getIdFromClassName('AdminParentOrders'));
            
			if (parent::install() != false
				&& $this->parseSQL('install.sql') != false
				&& $this->installStockMvtReason() != false
				&& $this->installPackConf() != false
				//&& $this->changeStatusOfOriginalMenus(0) != false
				&& $this->installErpTab() != false
				&& $this->addTrashCategory() != false
				&& $this->addOrderState($this->l('Order to the supplier')) != false
				&& $this->registerHook('actionOrderStatusUpdate') != false
				&& $this->registerHook('displayBackOfficeHeader') != false)
			{
                                Configuration::updateValue('ERP_SALES_FORECAST_CHOICE', 0);
                                Configuration::updateValue('ERP_ROLLING_MONTHS_NB_SO', 6);
                                Configuration::updateValue('ERP_PREFIX_REFERENCE_SO', 'SO');
                                
                                foreach ($this->field_name_configuration as $field_name => $param)
                                    Configuration::updateValue(Tools::strtoupper($field_name), $param['default']);
					
                                return true;
			}

			return false;
		}
		else
                {
                        $this->_errors[] = $this->l('Error while getting headers of WS ! Please contact the customer service.');
			return false;
                }
	}

	public function uninstall()
	{
            
		if ($this->uninstallModuleTabs() != false
			&& $this->uninstallPackConf() != false
			&& $this->uninstallStockMvtReason() != false
			&& $this->uninstallErpTab() != false
			&& $this->parseSQL('uninstall.sql') != false
			&& $this->deleteTrashCategory() != false
			&& $this->changeStatusOfOriginalMenus(1) != false
			&& $this->unregisterHook('actionOrderStatusUpdate') != false
			&& parent::uninstall() != false)
		{
				Configuration::deleteByName('ERP_GAP_STOCK');
				Configuration::deleteByName('ERP_STATUS_WARNING_STOCK');
				Configuration::deleteByName('ERP_SALES_FORECAST_CHOICE');
				Configuration::deleteByName('ERP_COEFFICIENTS');
				Configuration::deleteByName('ERP_PROJECTED_PERIOD');
				Configuration::deleteByName('ERP_COMPARISON_PERIOD');
				Configuration::deleteByName('ERP_ROLLING_MONTHS_NB_SO');
				Configuration::deleteByName('ERP_PREFIX_REFERENCE_SO');
				Configuration::deleteByName('ERP_NSTOCK_ALERTE_SO');
				Configuration::deleteByName('ERP_NSTOCK_NORMAL_SO');
				Configuration::deleteByName('ERP_STATE_TO_SEND_MAIL_SO');
				Configuration::deleteByName('ERP_ENABLE_SENDING_MAIL_SUPPLIER');
				Configuration::deleteByName('ERP_EXCEPT_ORDER_LIMIT_SO');
				Configuration::deleteByName('ERP_GENERATE_ORDER_STATE');
				Configuration::deleteByName('ERP_GENERATE_ORDER_STATE_TO');
				Configuration::deletebyName('ERP_RECEPTION_CANCELING_ID');
				Configuration::deletebyName('ERP_DISABLE_ORIGINAL_MENUS');
                                
                                Configuration::deletebyName('ERP_ADMIN_PARENT_ORDERS_TAB_ID');
                                
				return true;
		}

		return false;
	}

	/* SQL install */
	private function parseSQL($file)
	{
		// Request in install.sql
		if (($sql = Tools::file_get_contents(_PS_MODULE_DIR_.$this->name.'/sql/'.$file)) != false)
		{
			// Each request in a array
			// Explode request with ;; for handle request with ';' in string
			$tab_query = explode(';;', $sql);

			// Put DB_PREFIX and DB_NAME
			$tab_query = str_replace('[DB_PREFIX]', _DB_PREFIX_, $tab_query);
			$tab_query = str_replace('[DB_NAME]', _DB_NAME_, $tab_query);

			// Execute requests
			foreach ($tab_query as $sql)
			{
				$sql = trim($sql);
				if (!empty($sql))
					Db::getInstance()->Execute(trim($sql), false);
			}

			return true;
		}
                
                $this->_errors[] = $this->l('Error while parsing SQL. Please contact the customer service.');
                
		return false;
	}

	/* Configuration */
	public function getContent()
	{
		//update fields
		$output = $this->postProcess();

		// display form
		return $output.$this->displayForm();
	}

	/* Configuration Form */
	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		// Init Fields form array
		$fields_form = array();
		$source_options = Tools::jsonDecode(Licence::SourceKnowledge());

		// Récupération du tableau des modules activés
		$features = ErpFeature::getFeaturesWithToken($this->context->language->iso_code);
		$erp_cgv[] = array( 'id' => 'erp_cgv', 'name' => '');
		$erp_newsletter[] = array( 'id' => 'erp_newsletter', 'name' => '');
		
		// echo $this->display(__FILE__, 'views\templates\admin\configuration\description.tpl');
		$fields_form[1]['form'] = array(
				'legend' => array(
						'title' => $this->l('Licence'),
						'image' => '../img/admin/AdminPayment.gif'
				),
				'input' => array(
						array(
								'type' => 'text',
								'label' => $this->l('Module access'),
								'name' => 'module_access',
								'size' => 100,
								'required' => false,
								'disabled' => true
								),
						array(
								'type' => 'text',
								'label' => $this->l('License Management Mail (store mail)'),
								'name' => 'erp_licence_mail',
								'size' => 100,
								'required' => false,
								'disabled' => true
							)	
				));
				
				
				// si l'utilisateur à une licence non valide (expirée ou autre) ET qu'il a déjà activé son mois gratuit
				if (Configuration::get('ERP_LICENCE_VALIDITY') == '0' && Configuration::get('ERP_MONTH_FREE_ACTIVE') == '1' )
				{
					$fields_form[1]['form'] = array_merge($fields_form[1]['form'], array(
						'submit' => array(
								'title' => $this->l('Buy or update a licence'),
								'class' => $this->is_1_6 ? null : 'button',
								'name' => 'submitValidateLicence',
								'desc' => $this->l('Buy or update a licence')
						)
					));
				}
			
		
		if (Configuration::get('ERP_LICENCE_VALIDITY') == '1')
		{		
				// Get licence
	            $licence = Licence::crypt(Configuration::get('ERP_LICENCE'));

				$fields_form[1]['form']['input'][] = array(
										'type' => 'text',
										'label' => $this->l('Licence number'),
										'name' => 'erp_licence',
										'desc' => $this->l('Use this licence number to subscribe ').
																				'<a href="http://shop.illicopresta.com?iso='.$this->iso_code.'&licence='.$licence['licence_encode'].'&iv='.$licence['iv'].'" target="_blank">('.$this->l('Illicopresta web store').')</a>',
										'size' => 50,
										'required' => false,
										'disabled' => true
								);
				$fields_form[1]['form']['input'][] = array(
										'type' => 'text',
										'label' => $this->l('Account type'),
										'name' => 'erp_account_type',
										'size' => 25,
										'disabled' => 'disabled',
										'required' => false
								);
				$fields_form[1]['form']['input'][] = array(
										'type' => 'text',
										'label' => $this->l('Availability'),
										'name' => 'erp_ws_date_end',
										'size' => 25,
										'disabled' => 'disabled',
										'required' => false
								);
				$fields_form[1]['form']['input'][] = array(
										'type' => 'text',
										'label' => $this->l('Pack name'),
										'name' => 'erp_pack',
										'size' => 25,
										'desc' => (Configuration::get('ERP_ID_ERP_PACK') < 3) ? $this->l('Access to a superior pack on').'<a href="http://shop.illicopresta.com?iso='.$this->iso_code.'&licence='.$licence['licence_encode'].'&iv='.$licence['iv'].'" target="_blank">('.$this->l('Illicopresta web store').')</a>' : '',
										'disabled' => 'disabled',
										'required' => false,
								);					

				$fields_form[1]['form'] = array_merge($fields_form[1]['form'], array(
					'submit' => array(
							'title' => $this->l('Buy or update a licence'),
							'class' => $this->is_1_6 ? null : 'button',
							'name' => 'submitValidateLicence',
							'desc' => $this->l('Buy or update a licence')
					)
				));
			}
			
			else if( Configuration::get('ERP_MONTH_FREE_ACTIVE') == '0')
			{
					$fields_form[1]['form']['input'][] = array(
							'type' => 'text',
							'label' => $this->l('Contact Mail (if different)'),
							'name' => 'erp_contact_mail',
							'size' => 100,
							'required' => false,
							'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
						);
						
					$fields_form[1]['form']['input'][] = array(
							'type' => 'text',
							'label' => $this->l('Name'),
							'name' => 'erp_contact_name',
							'size' => 100,
							'required' => true,
							'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
						);
						
					$fields_form[1]['form']['input'][] = array(
							'type' => 'select',
							'label' => $this->l('How have you known us?'),
							'name' => 'erp_knowledge_source',
							'required' => true,
							'options' => array(
								'query' => $source_options,
								'id' => 'Id',
								'name' => 'Value'
							),
							'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
						);
						
				$fields_form[1]['form']['input'][] = 
						 array(
							'type' => 'checkbox',
							'label' => $this->l('TOU'),
							'name' => '',
							'required' => true,
							'values' => array(
								'query' => $erp_cgv,
								'id' => 'id',
								'name' => 'name'
							),
							'desc' => $this->l('Please agree to our ').'<a href="http://www.illicopresta.com/?page_id=10408" target="_blank" >'.$this->l('General Terms Of Use.').'</a>',
						);
				$fields_form[1]['form']['input'][] = 
						 array(
							'type' => 'checkbox',
							'label' => $this->l('Newsletter'),
							'name' => '',
							'required' => false,
							'values' => array(
								'query' => $erp_newsletter,
								'id' => 'id',
								'name' => 'name'
							),
							'desc' => $this->l('Subscribe to our newsletter and keep aware about improvements and offers.'),
						);
								
					$fields_form[1]['form'] = array_merge($fields_form[1]['form'], array(
						'submit' => array(
								'title' => $this->l('Activate your one month free offer'),
								'class' => $this->is_1_6 ? null : 'button',
								'name' => 'submitActivateLicence',
								'desc' => $this->l('Activate your one month free offer')
						)
					));
					$fields_form[1]['form']['input'][] = 
						 array(
							'type' => '',
							'name' => '',
							'required' => false,
							'values' => array(),
							'desc' => $this->l('You already have a licence ? please contact us at : support@illicopresta.com'),
						);
			}					
            
            $licence_invalid = '';
            
            // check licence validity
            if (Configuration::get('ERP_LICENCE_VALIDITY') == '1')
            {
                    // configure stock gap
                    $fields_form[2]['form'] = array(
                        'legend' => array(
                                'title' => $this->l('General settings'),
                                'image' => '../img/admin/cog.gif'
                        ),
                        'input' => array(
                                array(
                                                'type' => 'radio',
                                                'label' => $this->l('Disable original menus of PrestaShop'),
                                                'name' => 'erp_disable_original_menus',
                                                'required' => true,
                                                'br' => true,
                                                'class' => 't',
                                                'default_value' => '1',
                                                'values' => array(
                                                        array(
                                                                'id' => 'erp_disable_original_menus'._PS_SMARTY_NO_COMPILE_,
                                                                'value' => '1',
                                                                'label' => $this->l('Yes')
                                                        ),
                                                        array(
                                                                'id' => 'erp_disable_original_menus'._PS_SMARTY_CHECK_COMPILE_,
                                                                'value' => '0',
                                                                'label' => $this->l('No')
                                                        )
                                                ),
                                                'desc' => $this->l('This option allows you to disable the menus :  orders, supply order and supplier')
                                ),
                        ),
                        'submit' => array(
                                'title' => $this->l('Save'),
                                'class' => $this->is_1_6 ? null : 'button',
                                'name' => 'submitGeneralSettings'
                        )
                    );

                    //On affiche les paramètres des controleurs s'ils sont actifs et s'ils ont le bon statut
                    foreach($features as $feature)
                        {
                            //INVENTAIRE
                            if ($feature['active'] && $feature['controller'] == 'AdminInventory' && Configuration::get(self::getControllerStatusName('AdminInventory')))
                            {
                                // configure stock gap
                                $fields_form[3]['form'] = array(
                                    'legend' => array(
                                        'title' => sprintf($this->l('%s settings'),$feature['name']),
                                        'image' => '../modules/erpillicopresta/img/features/inventory.png'
                                    ),
                                    'input' => array(
                                        array(
                                            'type' => 'text',
                                            'label' => $this->l('Maximum authorized stock gap'),
                                            'name' => 'erp_gap_stock',
                                            'size' => 20,
                                            'required' => true,
                                            'desc' => $this->l('If the difference between the found quantity and the expected quantity is greater than this value, an alert will be generated. (0 to disable)')
                                        ),
                                    ),
                                    'submit' => array(
                                        'title' => $this->l('Save'),
                                        'class' => $this->is_1_6 ? null : 'button',
                                        'name' => 'submitInventorySettings'
                                    )
                                );
                            }
                            
                            //COMMANDES
                            if ($feature['active'] && $feature['controller'] == 'AdminAdvancedOrder')
                            {
                                $states = OrderState::getOrderStates($default_lang);

                                $fields_form[4]['form'] = array(
                                                                'legend' => array(
                                                                        'title' => sprintf($this->l('%s settings'),$feature['name']),
                                                                        'image' => '../modules/erpillicopresta/img/features/order.png'
                                                                ),
                                                                'input' => array(
                                                                        array(
                                                                                        'type' => 'text',
                                                                                        'label' => $this->l('Stock level - ALERT'),
                                                                                        'name' => 'erp_level_stock_alert',
                                                                                        'desc' => $this->l('Please indicate the stock quantity above which an alert will notify you : from 1 to [X]'),
                                                                                        'size' => 4,
                                                                        ),
                                                                        array(
                                                                                        'type' => 'text',
                                                                                        'label' => $this->l('Stock level - NORMAL'),
                                                                                        'name' => 'erp_level_stock_normal',
                                                                                        'desc' => $this->l('Please indicate the stock quantity corresponding to a normal stock level : from ALERT to [X]'),
                                                                                        'size' => 4,
                                                                        ),
                                                                        array(
                                                                                        'type' => 'checkbox',
                                                                                        'label' => $this->l('Order status to notify'),
                                                                                        'name' => 'erp_status_warning_stock',
                                                                                        'required' => true,
                                                                                        'values' => array(
                                                                                                        'query' => $states,
                                                                                                        'id' => 'id_order_state',
                                                                                                        'name' => 'name'
                                                                                        ),
                                                                                        'desc' => $this->l('Select all status which are concerned by stock warnings'),
                                                                        ),
                                                                ),
                                                                'submit' => array(
                                                                        'title' => $this->l('Save'),
                                                                        'class' => $this->is_1_6 ? null : 'button',
                                                                        'name' => 'submitAdvancedOrderSettings'
                                                                )
                                );

                            }
                            
                            //COMMANDES FOURNISSEURS
                            if ($feature['active'] && $feature['controller'] == 'AdminAdvancedSupplyOrder')
                            {
                                $fields_form[5]['form'] = array(
                                    'legend' => array(
                                            'title' => sprintf($this->l('%s settings'),$feature['name']),
                                            'image' => '../modules/erpillicopresta/img/features/supply_order.png'
                                    ),
                                    'input' => array(
                                            array(
                                                            'type' => 'text',
                                                            'label' => $this->l('References prefix'),
                                                            'name' => 'erp_prefix_reference',
                                                            'desc' => $this->l('References prefixes for the supplier order: maximum of two characters. Default prefix is SO.'),
                                                            'size' => 2,
                                                            'maxlength' => 2,
                                                            'required' => true
                                            ),
                                            array(
                                                            'type' => 'radio',
                                                            'label' => $this->l('Activate email sending to suppliers'),
                                                            'name' => 'erp_enable_sending_mail_supplier',
                                                            'required' => true,
                                                            'br' => true,
                                                            'class' => 't',
                                                            'default_value' => '0',
                                                            'values' => array(
                                                                    array(
                                                                                    'id' => 'erp_enable_sending_mail_supplier'._PS_SMARTY_NO_COMPILE_,
                                                                                    'value' => '1',
                                                                                    'label' => $this->l('Yes')
                                                                    ),
                                                                    array(
                                                                                    'id' => 'erp_enable_sending_mail_supplier'._PS_SMARTY_CHECK_COMPILE_,
                                                                                    'value' => '0',
                                                                                    'label' => $this->l('No')
                                                                    )
                                                            ),
                                                            'desc' => $this->l('If you select this option, please define the default status to be considered.')
                                            ),
                                            array(
                                                            'type' => 'select',
                                                            'label' => $this->l('Status of supplier orders that activates an email sending'),
                                                            'name' => 'erp_so_state_to_send_mail',
                                                            'desc' => $this->l('In the selected status, an email will be sent to the supplier. Default : 2 - Order validated.'),
                                                            'required' => true,
                                                            'options' => array(
                                                                    'query' => SupplyOrderState::getStates(),
                                                                    'id' => 'id_supply_order_state',
                                                                    'name' => 'name'
                                                            )
                                            )
                                    ),
                                    'submit' => array(
                                            'title' => $this->l('Save'),
                                            'class' => $this->is_1_6 ? null : 'button',
                                            'name' => 'submitAdvancedSupplyOrderSettings'
                                    )
                                );

                                if (Configuration::get(self::getControllerStatusName('AdminAdvancedSupplyOrder')))
                                {
                                                                // configure stock gap
                                                                $fields_form[5]['form']['input'][] = array(
                                                                                        'type' => 'select',
                                                                                        'label' => $this->l('Status of customer orders that generates supplier orders'),
                                                                                        'name' => 'erp_generate_order_state',
                                                                                        'desc' => $this->l('Please choose here the status of customer orders that will generate an automatic supplier order.'),
                                                                                        'required' => true,
                                                                                        'options' => array(
                                                                                                        'query' => OrderState::getOrderStates((int)$this->context->language->id),
                                                                                                        'id' => 'id_order_state',
                                                                                                        'name' => 'name'
                                                                                        )
                                                                                );

                                                                $fields_form[5]['form']['input'][] = array(
                                                                                        'type' => 'select',
                                                                                        'label' => $this->l('Status of customer orders after generation of supplier orders'),
                                                                                        'name' => 'erp_generate_order_state_to',
                                                                                        'desc' => $this->l('Select the state to apply to customer orders after the automatic generation of supplier orders occured.'),
                                                                                        'required' => true,
                                                                                        'options' => array(
                                                                                                'query' => OrderState::getOrderStates((int)$this->context->language->id),
                                                                                                'id' => 'id_order_state',
                                                                                                'name' => 'name'
                                                                                        )
                                                                                );

                                                                // configure stock gap
                                                                $fields_form[5]['form']['input'][] = array(
                                                                        'type' => 'text',
                                                                        'label' => $this->l('Number of rolling months'),
                                                                        'name' => 'erp_rolling_months_nb_so',
                                                                        'size' => 20,
                                                                        'required' => true,
                                                                        'desc' => $this->l('Used to display the quantities sold for x rolling months.')
                                                                );


                                                                $fields_form[5]['form']['input'][] = array(
                                                                                        'type' => 'radio',
                                                                                        'label' => $this->l('Sales forecast type'),
                                                                                        'name' => 'erp_sales_forecast_choice',
                                                                                        'br' => true,
                                                                                        'class' => 't',
                                                                                        'values' => array(
                                                                                                array(
                                                                                                        'id' => 'none',
                                                                                                        'value' => 0,
                                                                                                        'label' => $this->l('No projected sales')
                                                                                                ),
                                                                                                array(
                                                                                                        'id' => 'forecast_six_last_month',
                                                                                                        'value' => 1,
                                                                                                        'label' => $this->l('Weighted average sales on the six last rolling months')
                                                                                                ),
                                                                                                array(
                                                                                                        'id' => 'forecast_period',
                                                                                                        'value' => 2,
                                                                                                        'label' => $this->l('Sales forecast by period')
                                                                                                )
                                                                                        ),
                                                                                        'desc' => $this->l('Sales forecast will be calculated during supplier orders.').('</br>')
                                                                                                            .$this->l('The "Sales forecast by period" method calculates ')
                                                                                                            .$this->l('the sales growth factor based on the comparison between ')
                                                                                                            .$this->l('x rolling months of the current year ')
                                                                                                            .$this->l('and the same x rolling months of the previous year at the same date. ')
                                                                                                            .$this->l('Sales for the choosen projection period are then estimated by multiplying ')
                                                                                                            .$this->l('this sales growth factor by the sales performed during that period on the previous year.')
                                                                );

                                                                $fields_form[5]['form']['input'][] = array(
                                                                                                'type' => 'text',
                                                                                                'label' => $this->l('Weighting coefficients'),
                                                                                                'name' => 'erp_coefficients',
                                                                                                'desc' => $this->l('Coefficients that will be used to calculate the "Weighted average sales on the six last rolling months". Positive numbers expected.')
                                                                                                          .'<br/>'.$this->l('Syntax : M-1;M-2;M-3;M-4;M-5;M-6 (coefficient of the month M-1; etc.)'),
                                                                                                'size' => 20
                                                                );

                                                                $fields_form[5]['form']['input'][] = array(
                                                                                                'type' => 'text',
                                                                                                'label' => $this->l('Projection period'),
                                                                                                'name' => 'erp_projected_period',
                                                                                                'suffix' => $this->l('days'),
                                                                                                'desc' => $this->l('The "Sales forecast by period" method will return the total sales estimation for the filled out period.'),
                                                                                                'size' => 20,
                                                                );

                                                                $fields_form[5]['form']['input'][] = array(
                                                                                                'type' => 'text',
                                                                                                'label' => $this->l('Comparison period for the growth factor calculation'),
                                                                                                'name' => 'erp_comparison_period',
                                                                                                'suffix' => $this->l('month'),
                                                                                                'desc' => $this->l('Number of months preceding the current date that will be used to calculate the sales growth factor in the "Sales forecast by period" method.'),
                                                                                                'size' => 20,
                                                                );

                                                                $fields_form[5]['form']['input'][] = array(
                                                                        'type' => 'text',
                                                                        'label' => $this->l('Exceptional sales threshold'),
                                                                        'name' => 'erp_exceptional_order_limit',
                                                                        'desc' => $this->l('Above this value, a sale will be considered exceptional and will not be taken into account in forecasts. 0 if non applicable.'),
                                                                        'size' => 20,
                                                                        'required' => true
                                                                );
                                }

                            }
                        }
            }// end check licence validity
//            else
//                $licence_invalid = $this->displayError($this->l('Error : the licence is invalid ! Please buy a new licence or contact the customer service.'));     
                
                $helper = new HelperForm();

                // Module, token and currentIndex
                $helper->module = $this;
                $helper->name_controller = $this->name;
                $helper->token = Tools::getAdminTokenLite('AdminModules');
                $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

                // Language
                $helper->default_form_language = $default_lang;
                $helper->allow_employee_form_lang = $default_lang;

                // Title and toolbar
                $helper->title = $this->displayName;
                $helper->show_toolbar = true;
                $helper->toolbar_scroll = true;
                $helper->submit_action = 'submit'.$this->name;
                $helper->toolbar_btn = array(

                        'new' => array(
                                'href' => 'http://shop.illicopresta.com/',
                                'desc' => $this->l('Buy or update a licence')
                        ),
                        'refresh-index' => array(
                                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure=erpillicopresta&tab_module=shipping_logistics&module_name=erpillicopresta&submitValidateLicence',
                                'desc' => $this->l('Check my licence')
                        ),
                        'back' => array(
                                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                                'desc' => $this->l('Back to list')
                        )
                );

                if($this->is_1_6)
                {
                    $helper->page_header_toolbar_btn = array(

                            'new' => array(
                                    'href' => 'http://shop.illicopresta.com/',
                                    'desc' => $this->l('Buy or update a licence')
                            ),
                            'refresh-index' => array(
                                    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure=erpillicopresta&tab_module=shipping_logistics&module_name=erpillicopresta&submitValidateLicence',
                                    'desc' => $this->l('Check my licence')
                            ),
                            'back' => array(
                                    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                                    'desc' => $this->l('Back to list')
                            )
                    );
                }

                $helper->tpl_vars = array(
                        'fields_value' => $this->getConfigFieldsValues(),
                        'languages' => $this->context->controller->getLanguages(),
                        'id_language' => $this->context->language->id
                );


                // Générate helper
                return $this->display(__FILE__, 'views/templates/admin/configuration/description.tpl').$licence_invalid.$helper->generateForm($fields_form);
	}

	/*
	* Load current value
	*/
	public function getConfigFieldsValues()
	{
		$fields_values = array();
		$field_name = array(
			'ps_shop_email',
			'erp_licence',
			'erp_gap_stock',
			'erp_level_stock_alert',
			'erp_level_stock_normal',
			'erp_account_type',
			'erp_ws_date_end',
			'erp_pack'
		);

		$field_name = array_merge( array_keys($this->field_name_configuration), $field_name); // we need for keys only

		foreach ($field_name as $name)
			$fields_values[$name] = Tools::getValue( $name, Configuration::get(Tools::strtoupper($name)));

		$order_states = OrderState::getOrderStates((int)$this->context->language->id);
		foreach ($order_states as $state)
		{
			$erp_status_warning_stock = Tools::getValue('erp_status_warning_stock_'.$state['id_order_state'], Configuration::get('ERP_STATUS_WARNING_STOCK_'.$state['id_order_state']));
			$fields_values['erp_status_warning_stock_'.$state['id_order_state']] = $erp_status_warning_stock;
		}
                $fields_values['_erp_newsletter'] = 1;
				$fields_values['erp_licence_mail'] = Configuration::get('PS_SHOP_EMAIL');
                $fields_values['module_access'] = $this->l('The 1 CLICK ERP ILLICOPRESTA module is in the Orders tab'); 
		return $fields_values;
	}

	/* Save configuration submit */
	public function postProcess()
	{
		$output_success = null;
		$output_error = null;

		// Check licence (toolbar button)
		if (Tools::isSubmit('submitValidateLicence'))
		{
            $email = Configuration::get('PS_SHOP_EMAIL');
            $licence = Configuration::get('ERP_LICENCE');

            if ($licence != '' && $email != '')
            {
                // If get checksum for each file
                if (Licence::getChecksum(_PS_MODULE_DIR_.'erpillicopresta'))
                {
                    // Create global checksum
                    $checksum = md5(serialize(ErpIllicopresta::$checksum));
                    Configuration::updateValue('ERP_CHECKSUM', $checksum);

                    // Call WS
                    $result = Licence::wsCall($licence, $email, Configuration::get('PS_SHOP_DOMAIN'), $checksum, 'get');
                    
                    if($result['code'] == 200)
                    {
                    	if ($this->installModuleTabs())
			{
				Configuration::updateValue('ERPILLICOPRESTA_CONFIGURATION_OK', true);
                             $output_success .= $this->l('Licence updated successfully').'<br/>';
			}
                         else
                             $output_error .= $this->l('Licence updated successfully but unable to install tabs').'<br/>';
                    }
                    else
                    {
                    	$output_error .= $result['message'];
                    }   
                }
                else
                	$output_error .= $this->l('Error while getting checksum.').'<br/>';	
            }
            else
                $output_error .= $this->l('Empty licence or email.').'<br/>';
		}


		// save general setting
		else if (Tools::isSubmit('submitGeneralSettings'))
		{
			$result_general_setting = false;

			// save enable/disable original menu of prestashop
			if (Tools::getValue('erp_disable_original_menus') == 1)
				$result_general_setting = $this->changeStatusOfOriginalMenus(0);
			else
				$result_general_setting = $this->changeStatusOfOriginalMenus(1);

			if ($result_general_setting)
			{
				Configuration::updateValue('ERP_DISABLE_ORIGINAL_MENUS', (int)Tools::getValue('erp_disable_original_menus'));
				$output_success .= $this->l('General setting updated successfully').'<br/>';
			}
			else
				$output_error .= $this->l('Error while saving general settings. Please try again.').'<br/>';
		}

		else if (Tools::isSubmit('submitInventorySettings'))
		{
			if (Configuration::get(self::getControllerStatusName('AdminInventory')))
			{
				// Récupération valeur de gap
				$gap_stock = (string)Tools::getValue('erp_gap_stock');
                                
				// Si c'est bien un unsigned int
				if (!preg_match('#^[0-9]*$#', $gap_stock))
								$output_error .= $this->l('Invalid stock gap value ! The stock gap value must be a positive integer.').'<br/>';
				else
				{
					// Si valeur "0", alors on supprime l'ecart en base
					if ((int)$gap_stock == 0)
								$gap_stock = '';

					// SI ok on insert / update en conf
					Configuration::updateValue('ERP_GAP_STOCK', $gap_stock);
					$output_success .= $this->l('Inventory settings updated successfully').'<br/>';
				}
			}
		}

		// save advanced order settings
		else if (Tools::isSubmit('submitAdvancedOrderSettings'))
		{
			// save stock level configuration
			$erp_level_stock_alert = Tools::getValue('erp_level_stock_alert');
			$erp_level_stock_normal = Tools::getValue('erp_level_stock_normal');

			$sauve1 = Configuration::updateValue('ERP_LEVEL_STOCK_ALERT', $erp_level_stock_alert);
			$sauve2 = Configuration::updateValue('ERP_LEVEL_STOCK_NORMAL', $erp_level_stock_normal);

			// save status_warning_stock
			$order_states = OrderState::getOrderStates((int)$this->context->language->id);
			foreach ($order_states as $state)
			{
				if (Tools::isSubmit('erp_status_warning_stock_'.$state['id_order_state']))
					Configuration::updateValue('ERP_STATUS_WARNING_STOCK_'.$state['id_order_state'], 'on');
				else
					Configuration::updateValue('ERP_STATUS_WARNING_STOCK_'.$state['id_order_state'], '');
			}

			if ($sauve1 && $sauve2)
				$output_success .= $this->l('Advanced order settings updated successfully');
		}

		// save advanced supply orders setting
		elseif (Tools::isSubmit('submitAdvancedSupplyOrderSettings'))
		{
			foreach (array_keys($this->field_name_configuration) as $field_name)
			{
				if (Tools::isSubmit($field_name))
				{
                                        if (Tools::strtoupper($field_name) == 'ERP_COEFFICIENTS')
                                        {
                                                $regex = '/^([0-9]+([\.,][0-9]+)?;){5}([0-9]+([\.,][0-9]+)?)$/';
                                                if (!preg_match($regex, Tools::getValue($field_name)))
                                                {
                                                    $output_error .= $this->l('The weighting coefficients that you filled out do not fit the required syntax ! Please rewrite the weighting coefficients.').'<br/>';
                                                    continue;
                                                }
                                                elseif (array_sum(explode(';', Tools::getValue($field_name))) == 0)
                                                {
                                                    $output_error .= $this->l('All weigthing coefficients must not be null at the same time !').'<br/>';
                                                    continue;
                                                }
                                        }
					elseif (!Configuration::updateValue( Tools::strtoupper($field_name), Tools::getValue($field_name)))
					{
						$output_error .= $this->l('Error while saving supplier orders settings : '.$field_name).'<br/>';
						continue;
					}
				}
			}
			$output_success .= $this->l('Supplier orders settings updated successfully').'<br/>';
		}

		if (!is_null($output_success))
			$output_success = $this->displayConfirmation( $output_success);

		if (!is_null($output_error))
			$output_error = $this->displayError( $output_error);

		return $output_error.$output_success;
	}

	/*
	* get name of status controller
	*/
	public static function getControllerStatusName($controller)
	{
		// delete ADMIN in the name
		$controller = str_replace('Admin', '', $controller);

		// configuration name is limited by 32 characteres
		$controller = Tools::strlen($controller) > 21 ? Tools::substr($controller, 0, 21) : $controller;

		// uppercase
		$controller = Tools::strtoupper($controller);

		// final name to controller statut
		return 'ERP_'.$controller.'_STATUS';
	}

	/*
	* install ERP controller tabs
	*/
	private function installErpTab()
	{
		@copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/AdminERP.gif');

		// erp tab name by lang
		$erp_management_name_tab = array('en'=>'1 Click ERP Illicopresta', 'fr'=>'1 Click ERP Illicopresta', 'es'=>'1 Click ERP Illicopresta');

		$tab = new Tab();

		foreach (Language::getLanguages(false) as $language)
		{
			$iso_code = array_key_exists($language['iso_code'], $erp_management_name_tab) ? $language['iso_code'] : 'en';
			$tab->name[$language['id_lang']] = $erp_management_name_tab[$iso_code];
		}

		$tab->class_name = 'AdminERP';
		$tab->module = $this->name;
		$tab->id_parent = (int)Configuration::get('ERP_ADMIN_PARENT_ORDERS_TAB_ID'); // return the id of orders tab
				
		if (!$tab->save())
		{
			$this->_errors[] = $this->l('Error while creating ERP tab. Please contact the customer service.');
			return false;

		}
		return true;
	}

	/*
	* uninstall ERP controller tabs
	*/
	private function uninstallErpTab()
	{
		$id_tab = Tab::getIdFromClassName('AdminERP');
		if ($id_tab != 0)
		{
                    $tab = new Tab($id_tab);
                    if (!$tab->delete())
                    {
                        $this->_errors[] = $this->l('Error while uninstalling ERP tab !');
                        return false;
                    }
		}
		return true;
	}

	/*
	* Create new subtab
	*/
	public function installModuleTabs()
	{
		// get controllers list
		$erp_features = ErpFeature::getFeatures($this->context->language->iso_code);

		if ($erp_features)
		{
			foreach ($erp_features as $feature)
			{
				@copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/'.$feature['controller'].'.gif');
				$tab = new Tab();
				foreach (Language::getLanguages(false) as $language)
					$tab->name[$language['id_lang']] = $feature['name'];

				$tab->class_name = $feature['controller'];
				$tab->module = $this->name;
				$tab->id_parent = -1; // -1 to not display the tab in BO
				if (!$tab->save())
					return false;

				// configuration name is limited to 32 caracteres
				$controller_status_name = self::getControllerStatusName($feature['controller']);

				// save feature statut
				if (!Configuration::updateValue($controller_status_name, ErpFeature::isPro($feature['status'])))
					return false;
			}
		}
		return true;
	}

	/* Delete subtab */
	public function uninstallModuleTabs()
	{
		// uninstall module tabs only if the module is installed
		// else,module tables do not exist
		if (Module::isInstalled('erpillicopresta'))
		{
                    // get controllers list
                    $erp_features = ErpFeature::getFeatures($this->context->language->iso_code);

                    if ($erp_features)
                    {
                        foreach ($erp_features as $feature)
                        {
						
                            $id_tab = Tab::getIdFromClassName($feature['controller']);
							
                            if ($id_tab != 0)
                            {
                                    $tab = new Tab($id_tab);
									
                                    if (!$tab->delete())
                                    {
                                        $this->_errors[] = $this->l('Error while uninstalling module tabs !');
                                        return false;
                                    }
                            }

                            // get controller status name
                            $controller_status_name = self::getControllerStatusName($feature['controller']);

                            // save feature statut
                            if (!Configuration::deleteByName($controller_status_name))
                                    return false;
                        }
                    }
		}
		return true;
	}

	

	/* */
	private function installPackConf()
	{
            return Licence::installPackConf();
	}

	/* */
	private function uninstallPackConf()
	{
            return Licence::uninstallPackConf();
	}

	/**/
        public static $_MODULE = array();
	public static function findTranslation($name, $string, $source)
	{
                $l_cache = array();
                static $modules;

                if (!is_array($modules))
                {
                    $file = _PS_MODULE_DIR_.$name.'/translations/'.Context::getContext()->language->iso_code.'.php';
                    $file_global = _PS_MODULE_DIR_.$name.'/translations/global_'.Context::getContext()->language->iso_code.'.php';
                    
                    if (file_exists($file) && include($file))
                    {
                        if(!isset($_MODULE) && is_null($_MODULE))
                            $_MODULE = Array();
                        $modules = !empty($modules) ? array_merge($modules, $_MODULE) : $_MODULE;
                    }
                    
                    //include file global_[iso] that content global transtation as "Deletion successful"
                    if (file_exists($file_global) && include($file_global))
                    {
                        if(!isset($_ERP_GLOBAL_MODULE) && is_null($_ERP_GLOBAL_MODULE))
                            $_ERP_GLOBAL_MODULE = Array();
                        $modules = !empty($modules) ? array_merge($modules, $_ERP_GLOBAL_MODULE) : $_ERP_GLOBAL_MODULE;
                    }
                }
		$cache_key = $name.'|'.$string.'|'.$source;
		if (!isset($l_cache[$cache_key]))
		{
			if (!is_array($modules))
				return $string;
			$modules = array_change_key_case($modules);
			if (defined('_THEME_NAME_'))
				$current_key = '<{'.Tools::strtolower($name).'}'.Tools::strtolower(_THEME_NAME_).'>'.Tools::strtolower($source).'_'.md5($string);
			else
				$current_key = '<{'.Tools::strtolower($name).'}default>'.Tools::strtolower($source).'_'.md5($string);

			$default_key = '<{'.Tools::strtolower($name).'}prestashop>'.Tools::strtolower($source).'_'.md5($string);
			$current_key = $default_key;

			if (isset($modules[$current_key]))
				$ret = Tools::stripslashes($modules[$current_key]);
			elseif (isset($modules[Tools::strtolower($current_key)]))
				$ret = Tools::stripslashes($modules[Tools::strtolower($current_key)]);
			elseif (isset($modules[$default_key]))
				$ret = Tools::stripslashes($modules[$default_key]);
			elseif (isset($modules[Tools::strtolower($default_key)]))
				$ret = Tools::stripslashes($modules[Tools::strtolower($default_key)]);
			else
				$ret = Tools::stripslashes($string);
			$l_cache[$cache_key] = $ret;
		}
		return $l_cache[$cache_key];
	}

	/*
	*  Check if curl is available
	*/
	public function isCurlInstalled()
	{
		if (in_array('curl', get_loaded_extensions()) && function_exists('curl_version'))
			return true;
		else {
			$this->_errors[] = $this->l('Error : cURL is not available. Please contact the customer service.');
			return false;
		}
	}

	private function installStockMvtReason()
	{
			require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStockMvtReason.php';

			foreach ($this->stock_mvt_reason as $name => $mvt_param)
			{
				if (ErpStockMvtReason::existsByName($name) == false)
				{
					$stock_mvt_reason_increase = new ErpStockMvtReason();
					$stock_mvt_reason_increase->name = array();

					foreach (Language::getLanguages(false) as $language)
					{
							$iso_code = array_key_exists($language['iso_code'], $mvt_param['lang']) ? $language['iso_code'] : 'en';
							$stock_mvt_reason_increase->name[$language['id_lang']] = $mvt_param['lang'][$iso_code];
					}

					$stock_mvt_reason_increase->sign = $mvt_param['sign'];
					if (!$stock_mvt_reason_increase->add(true))
					{
						$this->_errors[] = $this->l('Error while creating stock movement reason : ').$name.' '.$this->l('Please try again or contact the customer service.');
						return false;
					}

					if (isset($mvt_param['configuration_name']))
							Configuration::updateValue($mvt_param['configuration_name'], (int)ErpStockMvtReason::existsByName($name));
				}
			}

			return true;
	}

	private function uninstallStockMvtReason()
	{
			require_once _PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStockMvtReason.php';

			if (!empty($this->stock_mvt_reason))
			{
				// get array keys only
				$stock_mvt_reason_array = array_keys($this->stock_mvt_reason);

				foreach ($stock_mvt_reason_array as $name)
				{
                                    $id_stock_mvt_reason = ErpStockMvtReason::existsByName($name);
                                    
					if ($id_stock_mvt_reason != false && (int)$id_stock_mvt_reason > 0)
					{
						$obj_stock_mvt_reason = new ErpStockMvtReason( $id_stock_mvt_reason);
						if (!$obj_stock_mvt_reason->delete())
                                                {
                                                    $this->_errors[] = $this->l('Error while deleting stock movement reason !');
                                                    return false;
                                                }
					}
				}
			}
			return true;
	}

	/*
	* Create a new category : trash category
	*/
	private function addTrashCategory()
	{
		$obj_category = new Category();

		$obj_category->name = array();
		foreach (Language::getLanguages(false) as $language)
			$obj_category->name[$language['id_lang']] = $this->trash_category_name;

		$obj_category->id_parent = '2';

		$obj_category->link_rewrite  = array();
		foreach (Language::getLanguages(false) as $language)
			$obj_category->link_rewrite[$language['id_lang']] = $this->trash_category_name;

		$obj_category->active  = 0;
		if ($obj_category->add())
				return true;
		else
		{
			$this->_errors[] = $this->l('Error while creating trash category. Please try again or contact the customer service.');
			return false;
		}
	}

	/*
	* Create a new order state if not exist
	*
	*/
	public function addOrderState($name)
	{
		$state_existe = false;
		$states = OrderState::getOrderStates( (int)$this->context->language->id);

		// check if order state exist
		foreach ($states as $state)
		{
			if (in_array($name, $state))
			{
				$state_existe = true;
				$id_state = $state['id_order_state'];
				break;
			}
		}

		// The state does not exist, we create it.
		if (!$state_existe)
		{
			// create new order state
			$order_state = new OrderState();
			$order_state->color = 'DarkOrange';
			$order_state->name = array();
			$languages = Language::getLanguages(false);
				foreach ($languages as $language)
					$order_state->name[$language['id_lang']] = $name;

			// Update object
			if (!$order_state->add())
					return false;

			Configuration::updateValue('ERP_GENERATE_ORDER_STATE_TO', $order_state->id);
		}
		else
			Configuration::updateValue('ERP_GENERATE_ORDER_STATE_TO', $id_state);

		return true;
	}

	/*
	* Delete trash category
	*/
	private function deleteTrashCategory()
	{
		$category = Category::getCategories( false, false, false, ' AND cl.`name` = \''.pSQL($this->trash_category_name).'\' ');
		if (!empty( $category))
		{
			$obj_category = new Category();
			$obj_category->id = $category[0]['id_category'];
			return $obj_category->delete();
		}
	}

	/*
	* Change status of original menus of Prestashop
        * 
        * $active : 
        *   1 ==> display original menu
        *   0 ==> hide original menu
	*/
	private function changeStatusOfOriginalMenus($active)
	{
            
            if ($active !== 0 && $active !== 1)
                            return true;

            if (!empty($this->original_menus))
            {
                foreach ($this->original_menus as $class_menu)
                {
                     // change link of admin orders menu
                    if ($class_menu == 'AdminParentOrders')
                    {
                        $parent_orders_tab_id = (int)Configuration::get('ERP_ADMIN_PARENT_ORDERS_TAB_ID');
                        
                        if ($parent_orders_tab_id > 0)
                        {
                            $tab = new Tab($parent_orders_tab_id);
                            $tab->active = 1;
                            if ($active == 0)
                            {
                                $tab->class_name = 'AdminAdvancedOrder';
                                $tab->module = 'erpillicopresta';
                            }
                            else{
                                $tab->class_name = 'AdminParentOrders';
                                $tab->module = '';
                            }
                        }
                    }
                    else
                    {
                        $id_tab = Tab::getIdFromClassName($class_menu);
                        if ($id_tab != 0)
                        {
                            $tab = new Tab($id_tab);
                            $tab->active = (int)$active;
                        }
                    }
                    
                    if (!$tab->update())
                    {
                        $this->_errors[] = $this->l('Error while updating original menus : ').$class_menu.' '.$this->l('Please try again or contact the customer service.');
                        return false;
                    }
                }
            }
            return true;
	}

	public function hookDisplayBackOfficeHeader()
	{
		//load global.css on any BO controller to display icon Order (and other things)
		$this->context->controller->addCSS($this->_path.'css/global.css');
		
		// get current controller
		$current_controller = Tools::getValue('controller');

		//allowed contoller to display side bar left
		$allowed_controller = array(
				'AdminAdvancedOrder',
				'AdminAdvancedSupplyOrder',
				'AdminStockTransfer',
				'AdminInventory',
				'AdminGenerateSupplyOrders',
				'AdminAdvancedSupplier',
				'AdminAdvancedStock',
				'AdminStockGap',
				'AdminErpZone',
		);
                
		if (in_array($current_controller, $allowed_controller))
		{
                    
                    $this->context->controller->addCSS($this->_path.'css/design.css');
                    $this->context->controller->addJS($this->_path.'js/tools.js');

                    if (!$this->is_1_6) 
                    {
                        $this->context->controller->addCSS($this->_path.'css/fieldset_to_tab.css');
                        $this->context->controller->addCSS($this->_path.'css/bootstrap.css');
                    }
                
                    $this->context->controller->addJS($this->_path.'js/mbExtruder/jquery.hoverIntent.min.js');
			$this->context->controller->addJS($this->_path.'js/mbExtruder/jquery.mb.flipText.js');
			$this->context->controller->addJS($this->_path.'js/mbExtruder/mbExtruder.js');
			$this->context->controller->addCSS($this->_path.'css/mbExtruder/mbExtruder.css', 'all');
		}
                
                if ($current_controller == 'AdminModules' && Tools::getValue('configure') == 'erpillicopresta')
				{
				
					// pour la version 1605 de prestashop tools est chargé avant Jquery ce qui cause des erreurs
					// On demande à Prestashop d'intégrer ce jquery d'abord
					if( _PS_VERSION_ == '1.6.0.5')
					{
						$this->context->controller->addJquery();
					}
					
                    $this->context->controller->addJS($this->_path.'js/tools.js');
				}
                
	}
}
