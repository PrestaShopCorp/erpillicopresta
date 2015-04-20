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

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php');
require_once(_PS_MODULE_DIR_.'erpillicopresta/models/ErpFeatureLang.php');
require_once _PS_MODULE_DIR_.'erpillicopresta/models/StockImage.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/StockImageContent.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpInventory.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/InventoryProduct.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplier.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/define.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class ErpIllicopresta extends Module
{
	public static $checksum = array();

	public function __construct()
	{

                // these two classes extends ErpIllicopresta
                require_once _PS_MODULE_DIR_.'erpillicopresta/config/Licence.php';
                require_once _PS_MODULE_DIR_.'erpillicopresta/classes/ErpConfiguration.php';

		// Get ISO Lang
		$this->context = Context::getContext();

		// Only iso_code "fr" or "en" are allowed (by default)
		$this->iso_code = $this->context->language->iso_code != 'fr' ? 'en' : 'fr';

		$this->bootstrap = true;
                $this->diplayFormHasLicence = true;         // display form to ask if merchant has a licence number
                $this->blockLicence = false;                 // block licence if fatal error

		$this->name = 'erpillicopresta';
		$this->tab = 'administration';
		$this->version = '3.0.2';
		$this->author = 'illicopresta';
		$this->displayName = $this->l('1 Click ERP Illicopresta');
		$this->description = $this->l('Save time in managing your E-Shop with the first ERP FREE and flexible(customer orders, Shipment, Suppliers, Stock management and export, inventory, ...).');

		$this->is_1_6 = version_compare( _PS_VERSION_ , '1.6' ) > 0 ? true : false;

		$this->trash_category_name = 'Divers';

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
                    'erp_exceptional_order_limit' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_comparison_period' => array('default' => 6, 'deleteOnUninstall' => true),
                    'erp_projected_period' => array('default' => 15, 'deleteOnUninstall' => true),
                    'erp_coefficients' => array('default' => '1.4;1.2;1;1;0.8;0.6', 'deleteOnUninstall' => true),
                    'erp_sales_forecast_choice' => array('default' => 0, 'deleteOnUninstall' => true),
                    'erp_rolling_months_nb_so' => array('default' => 6, 'deleteOnUninstall' => true),
                    'erp_generate_order_state_to' => array('default' => 4, 'deleteOnUninstall' => true),
                    'erp_generate_order_state' => array('default' => 3, 'deleteOnUninstall' => true),
                    'erp_so_state_to_send_mail' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_enable_sending_mail_supplier' => array('default' => 0, 'deleteOnUninstall' => true),
                    'erp_prefix_reference' => array('default' => 'SO', 'deleteOnUninstall' => true),
                    'erp_disable_original_menus' => array('default' => '0', 'deleteOnUninstall' => true),
                    'erp_state_to_send_mail_so' => array('default' => '2', 'deleteOnUninstall' => true),
                    'erp_contact_mail' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_licence_mail' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_knowledge_source' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_contact_name' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_contact_firstname' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_newsletter' => array('default' => 1, 'deleteOnUninstall' => true),
                    'erp_partner_code' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_commande_previsionnel' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_sales_forecast_choice' => array('default' => 0, 'deleteOnUninstall' => true),
                    'erp_rolling_months_nb_so' => array('default' => 6, 'deleteOnUninstall' => true),
                    'erp_prefix_reference_so' => array('default' => 'SO', 'deleteOnUninstall' => true),
                    'erp_reception_canceling_id' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_licence_validity' => array('default' => 0, 'deleteOnUninstall' => true),
                    'erp_msg_after_process' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_licence_install_error' => array('default' => 0, 'deleteOnUninstall' => true),
                    'erp_new_licence' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_licence_domaine_generate' => array('default' => '', 'deleteOnUninstall' => true),
                    'erp_configuration_ok' => array('default' => false, 'deleteOnUninstall' => true),
                    'erp_so_state_to_product_sales' => array('default' => 5, 'deleteOnUninstall' => true)
		);

		parent::__construct();
	}

	public function install()
	{
		$e = get_headers(ERP_WS);
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
                            && $this->installErpTab() != false
                            && $this->addTrashCategory() != false
                            && $this->addOrderState($this->l('Order to the supplier')) != false
                            && $this->registerHook('actionOrderStatusUpdate') != false
                            && $this->registerHook('displayBackOfficeHeader') != false)
                    {
                            foreach ($this->field_name_configuration as $field_name => $param)
                                Configuration::updateValue(Tools::strtoupper($field_name), $param['default']);

                            // load a licence if exits
                            $this->loadLicenceIfExists();

                            // save the first install date
                            if(!Configuration::hasKey('ERP_FIRST_INSTALL_DATE') || Configuration::get('ERP_FIRST_INSTALL_DATE') == '' || Configuration::get('ERP_FIRST_INSTALL_DATE') == false )
                                Configuration::updateValue('ERP_FIRST_INSTALL_DATE', date("Y-m-d H:i:s"));

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
                && $this->uninstallStockMvtReason() != false
                && $this->uninstallErpTab() != false
                && $this->parseSQL('uninstall.sql') != false
                && $this->deleteTrashCategory() != false
                //&& $this->changeStatusOfOriginalMenus(1) != false
                && $this->unregisterHook('actionOrderStatusUpdate') != false
                && parent::uninstall() != false)
            {

                // delete all ERP configuration
                foreach ($this->field_name_configuration as $field_name => $param)
                    if ($param['deleteOnUninstall'])
                        Configuration::deleteByName(Tools::strtoupper($field_name));

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
			$sql_instance = Db::getInstance();
			foreach ($tab_query as $sql)
			{
                            $sql = trim($sql);
                            if (!empty($sql))
                                    $sql_instance->Execute($sql, false);
			}

			return true;
		}

                $this->_errors[] = $this->l('Error while parsing SQL. Please contact the customer service.');

		return false;
	}

	/* Configuration */
	public function getContent()
	{
		//To do after first install v3
		$this->doAfterFirstIntallV3();

		// redirect to shop
		if($this->context->cookie->__isset('erp_do_redirect_shop') && !empty($this->context->cookie->erp_do_redirect_shop))
		{
			$url = $this->context->cookie->erp_do_redirect_shop;
			$this->context->cookie->__unset('erp_do_redirect_shop');
			Tools::redirectLink($url);
		}

		//update fields
		$output = $this->postProcess();
		// display form
		return $output.$this->displayForm().$this->displayCart();
	}

	/* Configuration Form */
	public function displayForm()
	{
                // init licence error
                $licence_msg = '';

                // display if exist message after installation
                $licence_msg .= Configuration::get('ERP_MSG_AFTER_PROCESS') != '' ? html_entity_decode(Configuration::get('ERP_MSG_AFTER_PROCESS')) : '';

                // delete message after installation
                Configuration::deleteByName('ERP_MSG_AFTER_PROCESS');

                // create an ERP Configuration boject
                $this->erpConfiguration = new ErpConfiguration();

                // Init fields form array
		$fields_form = array();

                // no license found in configuration or submit "has licence" form
                if( (!Configuration::hasKey('ERP_LICENCE') || Tools::isSubmit('submitValidateHasLicence')) && Configuration::get('ERP_LICENCE') == false )
                {
                    // display form to ask if merchant has a licence number
                    // if diplayFormHasLicence is TRUE
                    // and if, ERP NEW LICENCE not exists
                    if( $this->diplayFormHasLicence && Configuration::get('ERP_NEW_LICENCE') == '')
                        $fields_form[] = $this->erpConfiguration->getFormHasLicence();

                    // display form to generate a new licence
                    else {

                        $this->diplayFormHasLicence = false;
                        $fields_form[] = $this->erpConfiguration->getFormBeforeActivation();
                    }
                }

                // the license already exists
                else
                {

                    $this->diplayFormHasLicence = false;

                    // if licence is valid
                    if (Configuration::get('ERP_LICENCE_VALIDITY') == '1')
                    {
                        // get form information after activate licence
                        $fields_form[] = $this->erpConfiguration->getFormAfterActivation();

                        // get form to configure features
                        $fields_form[] = $this->erpConfiguration->getFormConfigurationController();
                    }
                }

                $helper = new HelperForm();

                // Module, token and currentIndex
                $helper->module = $this;
                $helper->name_controller = $this->name;
                $helper->token = Tools::getAdminTokenLite('AdminModules');
                $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

                // Get default Language
                $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

                // Language
                $helper->default_form_language = $default_lang;
                $helper->allow_employee_form_lang = $default_lang;

                // Title and toolbar
                $helper->title = $this->displayName;
                $helper->show_toolbar = true;
                $helper->toolbar_scroll = true;
                $helper->submit_action = 'submit'.$this->name;

                // hide the toolbar in PS 1.5
                if(!$this->is_1_6)
                {
                    $helper->show_toolbar = false;
                    $helper->toolbar_btn = null;
                }

                if($this->is_1_6)
                {
                    $helper->page_header_toolbar_btn = array(

                            'new' => array(
                                    'href' => 'http://shop.illicopresta.com/',
                                    'desc' => $this->l('Buy or update a licence')
                            ),
                            'refresh-index' => array(
                                    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure=erpillicopresta&tab_module=shipping_logistics&module_name=erpillicopresta&submitCheckLoadLicence',
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

                // init array form
                $forms = array();

                // if there are field form
                if(!empty($fields_form))
                {
                    foreach ($fields_form as $field_form)
                    {
                        $forms[] = $helper->generateForm($field_form);
                    }
                }

                // Add Licence activate form to the tpl
                $this->context->smarty->assign(array(
                    'forms' => $forms,
                    'urlTechnicalSupport' => $urlTechnicalSupport,
                    'urlEShop' => $urlEShop,
                    'isDevelopper' => self::isDevelopper(),
                    'blockLicence' => $this->blockLicence,
                    'erp_iso_code' => $this->context->language->iso_code
		));

                return $licence_msg.$this->display(__FILE__, 'views/templates/admin/configuration/description.tpl');

                // Générate helper
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
			'erp_pack',
                        'erp_contact_firstname'
		);

		$field_name = array_merge( array_keys($this->field_name_configuration), $field_name); // we need for keys only

		foreach ($field_name as $name)
			$fields_values[$name] = Tools::getValue( $name, Configuration::get(Tools::strtoupper($name)));

		foreach( OrderState::getOrderStates((int)$this->context->language->id) as $state)
			$fields_values['erp_status_warning_stock_'.$state['id_order_state']] = Tools::getValue('erp_status_warning_stock_'.$state['id_order_state'], Configuration::get('ERP_STATUS_WARNING_STOCK_'.$state['id_order_state']));

                $fields_values['_erp_newsletter'] = 1;
                $fields_values['_erp_cgv'] = Tools::isSubmit('_erp_cgv') ? 1 : 0;

                $fields_values['erp_licence_mail'] = Configuration::get('PS_SHOP_EMAIL');
                $fields_values['erp_licence_domaine_name'] = Configuration::get('ERP_LICENCE_DOMAINE_GENERATE');
                $fields_values['erp_licence_is_free'] = '1';
                $fields_values['erp_set_licence'] = Tools::getValue('erp_set_licence', '');
                $fields_values['erp_has_licence_number'] = Tools::getValue('erp_has_licence_number', 0);
                $fields_values['erp_licence_password'] = Tools::getValue('erp_licence_password', Configuration::get('ERP_LICENCE_PASSWORD'));

                $fields_values['submitCreateNewLicence'] = '';
                $fields_values['erp_new_licence'] = Configuration::get('ERP_NEW_LICENCE');

                $fields_values['module_access'] = $this->l('The 1 CLICK ERP ILLICOPRESTA module is in the Orders tab');
                return $fields_values;
	}

	/* Save configuration submit */
	public function postProcess()
	{
            $output_success = array();
            $output_error = array();

            // check existing licence and load it if valid
            if (Tools::isSubmit('submitCheckLoadLicence'))
            {
                // check licence
                $obLicence = new Licence();
                $obLicence->number = Configuration::get('ERP_LICENCE');
                $result = $obLicence->loadLicence();

                // no error
                if( $result['error'] == false )
                {
                    // redirect to module configuration
                    Tools::redirectAdmin('?controller=AdminModules&configure=erpillicopresta&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                // error, the licence does not exist
                else
                    $output_error[] = $result['msg'];
            }

            // save an existing license number
            else if (Tools::isSubmit('submitValidateHasLicence'))
            {
                $existing_license = Tools::getValue('erp_set_licence');
                $has_licence_number = Tools::getValue('erp_has_licence_number');

                // the merchant already has a license number
                if( $has_licence_number == 1 )
                {
                    // check licence validation
                    if( !empty($existing_license) )
                    {
                        // Check if licence exists in server
                        $obLicence = new Licence();
                        $obLicence->number = $existing_license;
                        $result = $obLicence->loadLicence();

                        // the licence exist we save it and install all controller
                        if( $result['error'] == false )
                        {
                            // redirect to module configuration
                            Tools::redirectAdmin('?controller=AdminModules&configure=erpillicopresta&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));
                        }
                        // error, the licence does not exist
                        else
                            $output_error[] = $result['msg'];
                    }
                    else
                        $output_error[] = $this->l('Please enter your existing license number.');
                }

                // no licence, hide the "has licence" form
                else {
                    $this->diplayFormHasLicence = false;
                }
            }

            // create a new licence
            elseif(Tools::isSubmit('submitCreateNewLicence'))
            {
                // get configuration value
                $email = Configuration::get('PS_SHOP_EMAIL');
                $licence_number = Configuration::get('ERP_NEW_LICENCE');

                // get activation form input
                $domaine_name = Tools::getValue('erp_licence_domaine_name');    // entered domaine name
                $licence_password = Tools::getValue('erp_licence_password');    // entered licence password
                $contact_name = Tools::getValue('erp_contact_name');            // entered contact name
                $_erp_cgv = Tools::isSubmit('_erp_cgv');                        // entered CGV
                $selected_feature = Tools::getValue('selected_feature');        // selected feature to create a new basket
                $contact_mail = Tools::getValue('erp_contact_mail');            // entered contact mail

                // is a free licence or not
                $licence_is_free = Tools::getValue('erp_licence_is_free', null) === '1' ? true : false;

                // if contact mail is given
                if( !empty($contact_mail) )
                {
                    $email = $contact_mail;
                    Configuration::updateValue('ERP_CONTACT_MAIL', $email); // save ontact mail in configuration
                }

                // check value
                if ($licence_number != '' && $email != '' && $domaine_name != '' && $licence_password != '' && $contact_name != '' && $_erp_cgv)
                {
                    // if feature not empty
                    if( !empty($selected_feature) )
                    {
                        // if get checksum for each file
                        if (Licence::getChecksum(_PS_MODULE_DIR_.'erpillicopresta'))
                        {
                            // Create global checksum
                            $checksum = md5(serialize(ErpIllicopresta::$checksum));
                            Configuration::updateValue('ERP_CHECKSUM', $checksum);

                             // create a new licence
                            $objLicence = new Licence();
                            $objLicence->number = $licence_number;

                            // Data to create a new licence
                            $objLicence->post_data = array(
                                'number' => $licence_number,
                                'mode' => 'purchase',
                                'email' => $email,
                                'password' => $licence_password,
                                'domain_name' => $domaine_name,
                                'active' => 1,
                                'blacklist' => 0,
                                'date_add' => date('Y-m-d H:i:s'),
                                'date_upd' => date('Y-m-d H:i:s'),
                                'date_end' => NULL,
                            );

                            // create a new licence
                            $result_add_licence = $objLicence->addLicence();

                            // if no error while creating licence
                            if( $result_add_licence['error'] == false )
                            {
                                // To know when the module is properly configured
                                Configuration::updateValue('ERP_CONFIGURATION_OK', true);

                                //is free licence, redirect to module configuration
                                if( $licence_is_free === true )
                                    Tools::redirectAdmin('?controller=AdminModules&configure=erpillicopresta&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));

                                 // else, redirect to ILLICOPRESTA shop
                                else
                                {
                                    $url = ERP_URL_ESHOP.$this->iso_code.'/?passkeyUpdateCart='.urlencode(base64_encode(sha1('updateCart').'||'.PRIVATE_KEY.'||'.$licence_number.'||'.'add'.'||'.implode(',', $selected_feature)));
                                    $url .= '&'.ERP_TAGS_GA_COMMANDE;
                                    $this->context->cookie->__set('erp_do_redirect_shop',$url);
                                    Tools::redirectAdmin('?controller=AdminModules&configure=erpillicopresta&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));
                                }
                            }
                            // if error while creating licence
                            else if( $result_add_licence['error'] == true)
                            {
                                $this->diplayFormHasLicence = false;
                                $output_error[] = $result_add_licence['msg'];
                            }
                        }
                        else
                            $output_error[] = $this->l('Error while getting the checksum.').'<br/>';
                    }
                    else
                        $output_error[] = $this->l('Error while getting the basket content.').'<br/>';
                }
                else {
                    $output_error[] = $this->l('Dear merchant, please fill out the entire form above in order to finalise your order.').'<br/>';
                }
            }

            // upadte basket
            elseif (Tools::isSubmit('submitUpdateBasket'))
            {
                $selected_feature = Tools::getValue('selected_feature');        // selected feature to create a new basket
                $current_basket_ids = Tools::getValue('current_basket_ids');      // list of current basket ids
                $licence_id = Configuration::get('ERP_LICENCE_ID');

                // if feature not empty
                if( !empty($selected_feature) && !empty($current_basket_ids) && !empty($licence_id) )
                {
                    // redirect to shop
                    $url = ERP_URL_ESHOP.$this->iso_code.'/?passkeyUpdateCart='.urlencode(base64_encode(sha1('updateCart').'||'.PRIVATE_KEY.'||'.Configuration::get('ERP_LICENCE').'||'.'update'.'||'.implode(',', $selected_feature)));
                    $url .= '&'.ERP_TAGS_GA_COMMANDE;
                    Tools::redirectLink($url);
                }
                else
                    $output_error[] = $this->l('An error has occurred while retrieving the contents of your cart. '
                            . 'Either the ids of your current cart are empty or your license number is empty.').'<br/>';
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
                        $output_success[] = $this->l('General setting updated successfully').'<br/>';
                    }
                    else
                        $output_error[] = $this->l('Error while saving general settings. Please try again.').'<br/>';
            }

            else if (Tools::isSubmit('submitInventorySettings'))
            {
                if (Configuration::get(self::getControllerStatusName('AdminInventory')))
                {
                    // Get "gap" value
                    $gap_stock = (string)Tools::getValue('erp_gap_stock');

                    // If it is an unsigned int
                    if (!ctype_digit($gap_stock))
                        $output_error[] = $this->l('Invalid stock gap value ! The stock gap value must be a positive integer.').'<br/>';
                    else
                    {
                        // if value "0", then we delete gap in database
                        if ((int)$gap_stock == 0)
                                                $gap_stock = '';

                        // if ok we insert / update in configuration
                        Configuration::updateValue('ERP_GAP_STOCK', $gap_stock);
                        $output_success[] = $this->l('Inventory settings updated successfully').'<br/>';
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
                    $output_success[] = $this->l('Advanced order settings updated successfully');
                else
                    $output_error[] = $this->l('Error while saving advanced order settings !');
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
                                                $output_error[] = $this->l('The weighting coefficients that you filled out do not fit the required syntax ! Please rewrite the weighting coefficients.').'<br/>';
                                                continue;
                                            }
                                            elseif (array_sum(explode(';', Tools::getValue($field_name))) == 0)
                                            {
                                                $output_error[] = $this->l('All weigthing coefficients must not be null at the same time !').'<br/>';
                                                continue;
                                            }
                                    }
                                    elseif (!Configuration::updateValue( Tools::strtoupper($field_name), Tools::getValue($field_name)))
                                    {
                                            $output_error[] = $this->l('Error while saving supplier orders settings : '.$field_name).'<br/>';
                                            continue;
                                    }
                            }
                    }
                    $output_success[] = $this->l('Supplier orders settings updated successfully').'<br/>';
            }

            // user has licence but it's not valid
            // If user has a non-valid licence (expired or else)
            if (Configuration::hasKey('ERP_LICENCE') && Configuration::get('ERP_LICENCE') != '' && Configuration::get('ERP_LICENCE_VALIDITY') != '1' && empty($output_error))
            {
                // do not display cart
                $this->blockLicence = true;

                // display error message
                if( !Configuration::hasKey('ERP_MSG_AFTER_PROCESS'))
                {
                    $output_error[] = $this->l('A license number exists for your store but is invalid.').' '.sprintf($this->l('Please contact our technical service to this email: %s'), ERP_EMAIL_SUPPORT);
                }
            }

            $output_error_str = $output_success_str = '';

            if (!empty($output_success))
                foreach ($output_success as $success)
                    $output_success_str .= $this->displayConfirmation( $success);

            if (!empty($output_error))
                    $output_error_str = $this->displayErrorList($output_error);

            return $output_error_str.$output_success_str;
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

            $tab = new Tab();

            foreach (Language::getLanguages(false) as $language)
                $tab->name[$language['id_lang']] = '1 Click ERP Illicopresta';

            $tab->class_name = 'AdminERP';
            $tab->module = $this->name;
            $tab->id_parent = (int)Configuration::get('ERP_ADMIN_PARENT_ORDERS_TAB_ID'); // return the id of orders tab

            if (!$tab->save())
                $this->_errors[] = $this->l('Error while creating ERP tab. Please contact the customer service.');

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
                    $this->_errors[] = $this->l('Error while uninstalling ERP tab !');
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

            if (!empty($erp_features))
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
                    {
                        $this->_errors[] = $this->l('Error while installing module tabs !');
                        return false;
                    }

                    // configuration name is limited to 32 caracteres
                    $controller_status_name = self::getControllerStatusName($feature['controller']);

                    // save feature statut
                    if (!Configuration::updateValue($controller_status_name, $feature['status']) )
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

                if (!empty($erp_features))
                {
                    foreach ($erp_features as $feature)
                    {
                        $id_tab = Tab::getIdFromClassName($feature['controller']);

                        if ($id_tab != 0)
                        {
                            $tab = new Tab($id_tab);

                            if (!$tab->delete())
                                $this->_errors[] = $this->l('Error while uninstalling module tabs !');
                        }

                        // get controller status name
                        $controller_status_name = self::getControllerStatusName($feature['controller']);

                        // save feature statut
                        Configuration::deleteByName($controller_status_name);
                    }
                }
            }
            return true;
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
                $stock_mvt_reason_increase = new ErpStockMvtReason();
                $stock_mvt_reason_increase->name = array();

                foreach (Language::getLanguages(false) as $language)
                {
                    $iso_code = array_key_exists($language['iso_code'], $mvt_param['lang']) ? $language['iso_code'] : 'en';
                    $stock_mvt_reason_increase->name[$language['id_lang']] = $mvt_param['lang'][$iso_code];
                }

                $stock_mvt_reason_increase->sign = $mvt_param['sign'];
                if (!$stock_mvt_reason_increase->add(true))
                    $this->_errors[] = $this->l('Error while creating stock movement reason : ').$name.' '.$this->l('Please try again or contact the customer service.');

                if (isset($mvt_param['configuration_name']))
                    Configuration::updateValue($mvt_param['configuration_name'], null);
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
                            $this->_errors[] = $this->l('Error while deleting stock movement reason !');
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
            $obj_category->link_rewrite  = array();

            foreach (Language::getLanguages(false) as $language)
            {
                $obj_category->name[$language['id_lang']] = $this->trash_category_name;
                $obj_category->link_rewrite[$language['id_lang']] = $this->trash_category_name;
            }

            $obj_category->id_parent = Configuration::get('PS_HOME_CATEGORY');
            $obj_category->active  = 0;

            if (!$obj_category->add())
                $this->_errors[] = $this->l('Error while creating trash category. Please try again or contact the customer service.');

            return true;
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
                $order_state->add();

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
            //load global_v3.css on any BO controller to display icon Order (and other things)
            $this->context->controller->addJquery();
            $this->context->controller->addCSS($this->_path.'css/global_v3.css');
            $this->context->controller->addJS($this->_path.'js/tools_v3.js');

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
                $this->context->controller->addJS($this->_path.'js/mbExtruder/jquery.hoverIntent.min.js');
                $this->context->controller->addJS($this->_path.'js/mbExtruder/jquery.mb.flipText.js');
                $this->context->controller->addJS($this->_path.'js/mbExtruder/mbExtruder.js');
                $this->context->controller->addCSS($this->_path.'css/mbExtruder/mbExtruder.css', 'all');

                if (!$this->is_1_6)
                    $this->context->controller->addCSS($this->_path.'css/bootstrap.css');
            }

            // for module configuration page only
            else if ( Tools::getValue('configure') == 'erpillicopresta' && ( $current_controller == 'adminmodules' || $current_controller == 'AdminModules'))
            {
                $this->context->controller->addJS($this->_path.'js/jquery.validate/jquery.validate.js');
                $this->context->controller->addJS($this->_path.'js/jquery.validate/messages_'.$this->context->language->iso_code.'.js');
                $this->context->controller->addJqueryUI('ui.slider');
                $this->context->controller->addJS($this->_path.'js/jquery-ui-slider-pips/jquery-ui-slider-pips.js');
                $this->context->controller->addCSS($this->_path.'css/jquery-ui-slider-pips/jquery-ui-slider-pips.css');
                $this->context->controller->addCSS($this->_path.'css/configuration_v3.css');

                // Add BS3 Css for 1.5
                if(!$this->is_1_6)
                    $this->context->controller->addCSS($this->_path.'css/bs3.css');
            }
	}


        /*
        * Load a licence if exists while installing module
        */
        public function loadLicenceIfExists()
        {
            // if licence exists in configuration and is not empty
            if(Configuration::hasKey('ERP_LICENCE') && Configuration::get('ERP_LICENCE') != '')
            {
                // Check if licence exists in server and install all controller
                $obLicence = new Licence();
                $obLicence->number = Configuration::get('ERP_LICENCE');
                $result = $obLicence->loadExistingLicence();

                // no error
                if( $result['error'] == false )
                {
                    // save success message to display after install
                    Configuration::updateValue('ERP_MSG_AFTER_PROCESS', htmlentities($this->displayConfirmation($result['msg'])));
                }
                // existing licence is not valid
                else
                {
                    // save error message to display after install
                    Configuration::updateValue('ERP_MSG_AFTER_PROCESS', htmlentities($this->displayError($result['msg'])));

                    // set licence has invalid
                    Configuration::updateValue('ERP_LICENCE_VALIDITY', '0');
                    Configuration::updateValue('ERP_LICENCE_INSTALL_ERROR', '1');
                }
            }

            // this function does not block the installation of the module
            return true;
        }

        public function displayCart()
        {
            // no license found in configuration
            if( $this->diplayFormHasLicence || Configuration::get('ERP_LICENCE_INSTALL_ERROR') == '1' || $this->blockLicence )
                return;

            // create a new licence
            $objLicence = new Licence();
            $objLicence->number = Configuration::hasKey('ERP_LICENCE') ? Configuration::get('ERP_LICENCE') : '';

            // get all containers
            $containers_data = $objLicence->getAllContainers();

            // if contailers getted successfully
            if( !$containers_data['error'])
            {
                // get current basket
                $current_basket = $objLicence->getCurrentBasket( true );

                // Error if basket is empty and licence existe
                if( empty($current_basket['msg']['feature_id']) && Configuration::get('ERP_LICENCE') != '')
                {
                    $er = $this->l('Error while getting the license basket.');
                    $er .= sprintf($this->l('Please contact our technical support to this mail adress: %s'), ERP_EMAIL_SUPPORT);
                    return $this->displayError($er);
                }

                // no error display cart
                else
                {
                    // asign var to cart template
                    $this->smarty->assign( array(
                            'containers' => $containers_data['msg'],
                            'current_basket' => $current_basket['msg']['feature_id'],
                            'basket_ids' => $current_basket['msg']['basket_id'],
                            'globa_level_selected' => $current_basket['msg']['globa_level_selected'],
                        )
                    );

                    // call template cart
                    return $this->display(__FILE__,'views/templates/admin/configuration/cart.tpl');
                }

            }
            else {
                return $this->displayError( $this->l('Error while getting container.').' '.$containers_data['msg'] );
            }
        }

        /*
        * Check if a domaine name is a developper domaine like 127.0.0.1 or localhost
        */
        static public function isDevelopper($domaine = null)
        {

            // is not domain
            if(empty($domaine))
                $domaine = Configuration::get('PS_SHOP_DOMAIN');

            // check domain_name : if it's IP non-routable, so it's a developper access
            $pattern = "/(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^192\.168\.)|(^127\.0\.0\.1)|(localhost)/";
            preg_match($pattern, $domaine , $matches);

            if(count($matches) > 0)
                return true;
            else
                return false;
        }

        public function displayErrorList($errors)
        {
            // asign var to cart template
            $this->smarty->assign(array('errors' => $errors));
            return $this->display(__FILE__,'views/templates/admin/configuration/error.tpl');
        }

        public function getDefaultForcastOrders()
        {
            // Number of order passed last month
            $dateFrom = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
            $dateTo = date('Y-m-d', mktime(0, 0, 0, date('m'), 1 - 1, date('Y')));

            if($this->is_1_6)
                $nbOrderLastMonth = AdminStatsControllerCore::getOrders($dateFrom, $dateTo);
            else
            {
                $orderLastMonth = Order::getOrdersIdByDate($dateFrom, $dateTo);
                $nbOrderLastMonth = count($orderLastMonth);
            }

            if($nbOrderLastMonth < 100 ) $default = '0-100';
            elseif($nbOrderLastMonth < 200) $default = '100-200';
            elseif($nbOrderLastMonth < 300) $default = '200-300';
            elseif($nbOrderLastMonth < 500) $default = '300-500';
            elseif($nbOrderLastMonth < 1000) $default = '500-1000';
            elseif($nbOrderLastMonth < 2000) $default = '1000-2000';
            else $default = '2000+';

            return $default;
        }

        /*
        *   To do after first install v3
        */
        public function doAfterFirstIntallV3()
        {
            // first install if this conf does not exit
            if(!Configuration::hasKey('ERP_FIRST_INSTALL_DATE') || Configuration::get('ERP_FIRST_INSTALL_DATE') == '' || Configuration::get('ERP_FIRST_INSTALL_DATE') == false )
            {
                $this->loadLicenceIfExists();
                Configuration::updateValue('ERP_FIRST_INSTALL_DATE', date("Y-m-d H:i:s"));
            }
        }

}
