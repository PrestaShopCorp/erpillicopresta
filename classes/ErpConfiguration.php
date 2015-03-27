<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Description of ErpConfiguration
 *
 * @author Illicopresta SA <contact@illicopresta.com>
 * @copyright 2007-2015 Illicopresta
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class ErpConfiguration extends ErpIllicopresta {
    //put your code here
        
    public function __construct()
    {
        $this->erpLicence =  new Licence();
        parent::__construct();
    }    
    
    /*
    * Form to ask merchant if has a lincence number
    */
    public function getFormHasLicence()
    {
        $fields_form = array();
                
        $fields_form[1]['form']['input'][] = array(
            'type' => $this->is_1_6 ? 'switch' : 'radio',
            'class' => 't',
            'is_bool' => true,
            'label' => $this->l('Do you have a license number ?'),
            'name' => 'erp_has_licence_number',
            'desc' => $this->l('If you have a license number please to choose "Yes". Otherwise a number will be generated for you.'),
            'values' => array(
                    array(
                            'id' => 'erp_has_licence_number_1',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                    ),
                    array(
                            'id' => 'erp_has_licence_number_0',
                            'value' => 0,
                            'label' => $this->l('No')
                    )
            )
        );

        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Licence number'),
            'name' => 'erp_set_licence', 
            'desc' => $this->l('Please enter here your license number'),
            'size' => 50,
            'required' => true,
        );
        
        $fields_form[1]['form']['submit'] = array(
                'title' => $this->l('Validate'),
                'class' => $this->is_1_6 ? null : 'button',
                'name' => 'submitValidateHasLicence',    
        );      
        
        return $fields_form;
    }
    
    public function getFormConfigurationController()
    {
        
        // get all status 
        $advanced_order_status = Configuration::get(self::getControllerStatusName('AdminAdvancedOrder'));
        
        $fields_form = array();
        
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

        // Get activate module table
        $features = ErpFeature::getFeaturesWithToken($this->context->language->iso_code);
        
        //Display controller parameters if activ and good state
        foreach($features as $feature)
        {
            //INVENTORY
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

            //ORDER
            if ($feature['active'] && $feature['controller'] == 'AdminAdvancedOrder' && $advanced_order_status != STATUS0 && $advanced_order_status != STATUS1 )
            {
                // Get default Language
                $states = OrderState::getOrderStates((int)Configuration::get('PS_LANG_DEFAULT'));

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

            //SUPPLIER ORDER
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
                        'type' => 'select',
                        'label' => $this->l('Status of customer orders that count for the product sales statistic'),
                        'name' => 'erp_so_state_to_product_sales',
                        'desc' => $this->l('In the selected status, the products of the order will be counted in the product sales statistic.'),
                        'required' => true,
                        'options' => array(
                                'query' => OrderState::getOrderStates((int)$this->context->language->id),
                                'id' => 'id_order_state',
                                'name' => 'name'
                        )
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
        
            
        return $fields_form; 
    }
    
    
    public function getFormBeforeActivation()
    {
        $fields_form = array();
        
        // create a new licence
        if(!Configuration::hasKey('ERP_NEW_LICENCE') || Configuration::get('ERP_NEW_LICENCE') == '')
        {
           $uniqid = uniqid( Configuration::get('PS_SHOP_DOMAIN'), true);
           Configuration::updateValue('ERP_NEW_LICENCE', $uniqid);
        }
        
        // generate a new domaine name if is developper
        if(!Configuration::hasKey('ERP_LICENCE_DOMAINE_GENERATE') || Configuration::get('ERP_LICENCE_DOMAINE_GENERATE') == '')
        {
           // domaine name is a local domaine
           if( self::isDevelopper())
           {
                $uniqid = uniqid( Configuration::get('PS_SHOP_DOMAIN').'@@', true);
                Configuration::updateValue('ERP_LICENCE_DOMAINE_GENERATE', $uniqid);
           }
           else {
              Configuration::updateValue('ERP_LICENCE_DOMAINE_GENERATE', Configuration::get('PS_SHOP_DOMAIN')); 
           }
        }
        
        // get default forcast orders
        if(Configuration::get('ERP_COMMANDE_PREVISIONNEL') == '')
            Configuration::updateValue('ERP_COMMANDE_PREVISIONNEL', $this->getDefaultForcastOrders());
        
        // hidden field to send the licence type : normal or developper
        $fields_form[1]['form']['input'][] = array(
            'type' => 'hidden',
            'name' => 'erp_licence_is_free'
        );
        
        // hidden field to send the action type
        $fields_form[1]['form']['input'][] = array(
            'type' => 'hidden',
            'name' => 'submitCreateNewLicence'
        );

        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Licence number'),
            'name' => 'erp_new_licence', 
            'size' => 100,
            'desc' => $this->l('This is your auto-generated license number'),
            'required' => 'required',
            'disabled' => true
        );
        
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Licence password'),
            'name' => 'erp_licence_password', 
            'size' => 100,
            'desc' => $this->l('Please choose a password for your license'),
            'required' => true,
            'disabled' => false
        );
        
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Domaine name'),
            'name' => 'erp_licence_domaine_name',
            'cast' => 'intval',
            'required' => true,
            'disabled' => false,
            'readonly' => true,
            'size' => 100,
            'hint' => self::isDevelopper() ? $this->l('Your store is currently installed on a local environment, so your domain name has been automaticly generated. You could later ask for a free migration of your license on a real store.') : null,
            'desc' => self::isDevelopper() ? $this->l('Your store is currently installed on a local environment. You will later be able to ask for a free transfer of your license to your actual store.') : null,
        );
        
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('E-shop email (licence email)'),
            'name' => 'erp_licence_mail',
            'size' => 100,
            'required' => true,
            'disabled' => true
        );

        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Contact email'),
            'name' => 'erp_contact_mail',
            'size' => 100,
            'required' => true,
            'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
        );
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Last Name'),
            'name' => 'erp_contact_name',
            'size' => 100,
            'required' => true,
            'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
        );
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('First Name'),
            'name' => 'erp_contact_firstname',
            'size' => 100,
            'required' => false,
            'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
        );
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Partner code'),
            'name' => 'erp_partner_code',
            'size' => 100,
            'required' => false,
            'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
        );
        
        $fields_form[1]['form']['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Average number of orders per month'),
            'name' => 'erp_commande_previsionnel',
            'required' => false,
            'options' => array(
                'query' => array(
                    array('Id' => '0-100', 'Value' => '0-100'),
                    array('Id' => '100-200', 'Value' => '100-200'),
                    array('Id' => '200-300', 'Value' => '200-300'),
                    array('Id' => '300-500', 'Value' => '300-500'),
                    array('Id' => '500-1000', 'Value' => '500-1000'),
                    array('Id' => '1000-2000', 'Value' => '1000-2000'),
                    array('Id' => '2000+', 'Value' => '2000+'),
                ),
                'id' => 'Id',
                'name' => 'Value'
            ),
            'disabled' => false
        );
        
        $fields_form[1]['form']['input'][] = array(
            'type' => 'select',
            'label' => $this->l('How do you known us?'),
            'name' => 'erp_knowledge_source',
            'required' => false,
            'options' => array(
                'query' => $this->erpLicence->getKnowledgeSource(),
                'id' => 'Id',
                'name' => 'Value'
            ),
            'disabled' => Configuration::get('ERP_LICENCE_VALIDITY') == '1' ? true : false
        );
        
        // values to input cgv
        $erp_cgv[] = array( 'id' => 'erp_cgv', 'name' => '');
        
        $url_cgu = ERP_URL_CGU_EN;
        if($this->context->language->iso_code == 'fr')
                $url_cgu = ERP_URL_CGU_FR;
                
        $fields_form[1]['form']['input'][] = array(
            'type' => 'checkbox',
            'label' => $this->l('GTU'),
            'name' => '',
            'values' => array(
                'query' => $erp_cgv, 
                'id' => 'id',
                'name' => 'name'
            ),
            'desc' => $this->l('Please agree to our ').' <a href="'.$url_cgu.'" target="_blank" >'.$this->l(' General Terms Of Use.').'</a>',
            'required' => true,
        );
        
        // values to newsletter cgv
        $erp_newsletter[] = array( 'id' => 'erp_newsletter', 'name' => '');
        
        $fields_form[1]['form']['input'][] = array(
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

        $html = 
            '<div style="font-size:1.2em">'.
                $this->l('Back up your comments and needs for future developments?').'<br/>'.
                $this->l('Write an email to ').
                '<a href="mailto:commercial@illicopresta.com">commercial@illicopresta.com</a>'.
            '</div>';

        $fields_form[1]['form']['input'][] = array(
            'type' => '',
            'name' => '',
            'required' => false,
            'values' => array(),
            'desc' => $html,
        );
                 
        return $fields_form;
    }
    
    
    public function getFormAfterActivation()
    {
        
        $fields_form = array();
                 
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Licence number'),
            'name' => 'erp_licence', 
            'size' => 50,
            'required' => false,
            'disabled' => true
        );

        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('E-shop email (licence email)'),
            'name' => 'erp_licence_mail',
            'size' => 100,
            'required' => false,
            'disabled' => true
        );

        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Domaine name'),
            'name' => 'erp_licence_domaine_name',
            'size' => 50,
            'required' => false,
            'disabled' =>  true
        );

        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Contact email'),
            'name' => 'erp_contact_mail',
            'size' => 100,
            'required' => true,
            'disabled' => 'disabled',
        );
        
        $fields_form[1]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Licence password'),
            'name' => 'erp_licence_password', 
            'size' => 100,
            'desc' => $this->l('Your licence password, Useful when you want to migrate your license'),
            'required' => true,
            'disabled' => true
        );
        
        $fields_form[1]['form']['submit'] = array(
                'title' => $this->l('Update my license.'),
                'class' => $this->is_1_6 ? null : 'button',
                'name' => 'submitCheckLoadLicence',
                'desc' => $this->l('Update my license.')
        );

        return $fields_form; 
    }
        
    public function l($string, $class = 'ErpConfiguration', $addslashes = false, $htmlentities = false)
    {
        if (!empty($class))
        {
            $str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'ErpConfiguration');
            $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
            return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
        }
    }
}
