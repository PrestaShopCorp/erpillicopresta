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

// Cette classe ne doit être appelée qu'à la condition que le module TNT soit installé et actif !!!
require_once _PS_MODULE_DIR_.'tntcarrier/tntcarrier.php';


class ErpTntCarrier extends TntCarrier
{
    private $_moduleName = 'tntcarrier';
    
    public function generateShipping($id_order)
    {
        
        
            if (!$this->active)
                    return false;
            
            global $currentIndex, $smarty;
            $table = 'order';
            $token = Tools::safeOutput(Tools::getValue('token'));
            $errorShipping = 0;
            if ($currentIndex == '')
                    $currentIndex = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller'));
            $currentIndex .= "&id_order=".(int)($id_order);
            $carrierName = Db::getInstance()->getRow('SELECT c.external_module_name FROM `'._DB_PREFIX_.'carrier` as c, `'._DB_PREFIX_.'orders` as o WHERE c.id_carrier = o.id_carrier AND o.id_order = "'.(int)$id_order.'"');
            if ($carrierName!= null && $carrierName['external_module_name'] != $this->_moduleName)
                    return false;
            if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
            {
                    $var = array("error" => $this->l("You don't have a TNT account"),
                                             'shipping_numbers' => '',
                                             'sticker' => '');
                    $smarty->assign('var', $var);
                    return $this->display( __FILE__, 'tpl/shippingNumber.tpl' );
            }
            
            if (!Configuration::get('TNT_CARRIER_SHIPPING_COMPANY') || !Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1') || !Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE') || !Configuration::get('TNT_CARRIER_SHIPPING_CITY') || !Configuration::get('TNT_CARRIER_SHIPPING_EMAIL')
                    || !Configuration::get('TNT_CARRIER_SHIPPING_PHONE') || !Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))
                    $errorShipping = 1;

            if ($errorShipping)
            {
                    $var = array("error" => $this->l("You didn't give a collect address in the TNT module configuration"),
                                             'shipping_numbers' => '',
                                             'sticker' => '');
                    $smarty->assign('var', $var);
                    return $this->display( __FILE__, 'tpl/shippingNumber.tpl' );
            }
            $order = new Order($id_order);

            $orderInfoTnt = new OrderInfoTnt((int)($id_order));
            $info = $orderInfoTnt->getInfo();
            if (is_array($info) && isset($info[3]) && (Tools::strlen($info[3]['option']) == 1 || Tools::substr($info[3]['option'], 1, 1) == 'S'))
                    $smarty->assign('weight', '30');
            else
                    $smarty->assign('weight', '20');

            $products = $order->getProducts();
            $productWeight = array();

            foreach ($products as $product)
            {
                    $p = new Product($product['product_id']);
                    if ((float)$p->weight == 0 && (!Tools::getIsset('product_weight_'.$product['product_id']) || (float)Tools::getValue('product_weight_'.$product['product_id']) <= 0))
                            $productWeight[] = array('id' => $product['product_id'], 'name' => $product['product_name']);
                    else if (Tools::getIsset('product_weight_'.$product['product_id']) && (float)Tools::getValue('product_weight_'.$product['product_id']) > 0)
                    {
                            $p->weight = (float)Tools::getValue('product_weight_'.$product['product_id']);
                            $p->update();
                    }
            }

            if (count($productWeight) > 0)
            {
                    $var = array('currentIndex' => $currentIndex, 'table' => $table, 'token' => $token);
                    $smarty->assign('var', $var);
                    $smarty->assign('productWeight', $productWeight);
                    return $this->display( __FILE__, 'tpl/weightForm.tpl' );
            }

            if (!is_array($info) && $info != false)
            {
                    $var = array("error" => $info, "date" => '', "dateHidden" => '1', 'currentIndex' => $currentIndex, 'table' => $table, 'token' => $token);
                    $smarty->assign('var', $var);
                    return $this->display( __FILE__, 'tpl/formerror.tpl' );
            }

            $pack = new PackageTnt((int)$id_order);
            if ($info[0]['shipping_number'] == '' && $pack->getOrder()->hasBeenShipped())
            {
                    $tntWebService = new TntWebService();
                    try
                    {
                            if (!Tools::getIsset('dateErrorOrder'))
                                    $orderInfoTnt->getDeleveryDate((int)$id_order, $info);
                            $package = $tntWebService->getPackage($info);
                    }
                    catch(SoapFault $e)
                    {
                            $errorFriendly = '';
                            if (strrpos($e->faultstring, "shippingDate"))
                                    $dateError = date("Y-m-d");
                            if (strrpos($e->faultstring, "receiver"))
                            {
                                    $receiverError = 1;
                                    $errorFriendly = $this->l('Can you please modify the field').' '.Tools::substr($e->faultstring, strpos($e->faultstring, "receiver") + 9, strpos($e->faultstring, "'", strpos($e->faultstring, "receiver" ) - strpos($e->faultstring, "receiver")) + 1).' '.$this->l('in the box "shipping address" below.');
                            }
                            if (strrpos($e->faultstring, "sender"))
                            {
                                    $senderError = 1;
                                    $errorFriendly = $this->l('Can you please modify the field').' '.Tools::substr($e->faultstring, strpos($e->faultstring, "sender") + 7, strpos($e->faultstring, "'", strpos($e->faultstring, "sender" ) - strpos($e->faultstring, "sender")) + 1).' '.$this->l('in your tnt module configuration.');
                            }

                            $error = $this->l("Problem : ") . $e->faultstring;
                            $var = array("error" => $error, "errorFriendly" => $errorFriendly, "date" => (isset($dateError) ? $dateError : ''), 'currentIndex' => $currentIndex, 'table' => $table, 'token' => $token);
                            $smarty->assign('var', $var);
                            return $this->display( __FILE__, 'tpl/formerror.tpl' );
                    }
                    if (isset($package->Expedition->parcelResponses->parcelNumber))
                    {
                            $pack->setShippingNumber($package->Expedition->parcelResponses->parcelNumber);
                            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_package_history` (`id_order`, `pickup_date`) VALUES ("'.(int)$id_order.'", "'.pSQL($info[2]['delivery_date']).'")');
                    }
                    else
                            foreach ($package->Expedition->parcelResponses as $k => $v)
                                    $pack->setShippingNumber($v->parcelNumber);
                    
                    // ASA - Security Audit
                    $shipping_number = str_replace(array("/","\\","\0","/"),"-",$pack->getOrder()->shipping_number);
                    
                    file_put_contents("../modules/".$this->_moduleName.'/pdf/'.$shipping_number.'.pdf', $package->Expedition->PDFLabels);
            }
            
            if ($pack->getShippingNumber() != '')
            {
                
                    $var = array(
                            'error' => '',
                            'shipping_numbers' => $pack->getShippingNumber(),
                            'sticker' => "../modules/".$this->_moduleName.'/pdf/'.$pack->getOrder()->shipping_number.'.pdf',
                            'date' => Db::getInstance()->getValue('SELECT `pickup_date` FROM `'._DB_PREFIX_.'tnt_package_history` WHERE `id_order` = "'.(int)$id_order.'"'),
                            'relay' => (isset($info[4]) ? $info[4]['name'].'<br/>'.$info[4]['address'].'<br/>'.$info[4]['zipcode'].' '.$info[4]['city']: ''),
                            'place' => Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1')." ".Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS2')."<br/>".Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE')." ".$this->putCityInNormeTnt(Configuration::get('TNT_CARRIER_SHIPPING_CITY')));
                    $smarty->assign('var', $var);
                    return $this->display( __FILE__, 'tpl/shippingNumber.tpl' );
            }
            return false;
    }
}
