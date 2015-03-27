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

// PROD
define('ERP_WS', 'http://apiv3.illicopresta.com/api/');

// RCT
//define('ERP_WS', 'http://apiv3-rct.illicopresta.com/api/');

// DEV
//define('ERP_WS', 'http://127.0.0.1/illicopresta_licence/api/');
//define('ERP_WS', 'http://127.0.0.1/illicopresta_licence/api/');


define('PRIVATE_KEY', 'db4e341a8a7c977573a47fad616839cfed610883');
        
// level satut : free, light or pro
define('STATUS0', '334c4a4c42fdb79d7ebc3e73b517e6f8');  // none
define('STATUS1', 'b24ce0cd392a5b0b8dedc66c25213594');  // Free
define('STATUS2', '9914a0ce04a7b7b6a8e39bec55064b82');  // Light
define('STATUS3', 'abd900517e55dce0437dac136a8568d7');  // Pro

// Urls
//define('ERP_URL_ESHOP', 'http://176.31.104.77/shop.illicoprestav99/');
define('ERP_URL_ESHOP', 'http://shop.illicopresta.com/');
define('URL_ESHOP_EN', 'http://shop.illicopresta.com/en/?utm_source=prestashop&utm_medium=back-office&utm_campaign=page-commande');
define('URL_ESHOP_FR', 'http://shop.illicopresta.com/fr/?utm_source=prestashop&utm_medium=back-office&utm_campaign=page-commande');
define('URL_TECHNICAL_SUPPORT_EN', 'https://addons.prestashop.com/en/write-to-developper?id_product=18033');
define('URL_TECHNICAL_SUPPORT_FR', 'https://addons.prestashop.com/fr/ecrire-au-developpeur?id_product=18033s');
define('URL_TECHNICAL_SUPPORT_IT', 'https://addons.prestashop.com/it/write-to-developper?id_product=18033');
define('URL_TECHNICAL_SUPPORT_ES', 'https://addons.prestashop.com/es/write-to-developper?id_product=18033');

define('ERP_TAGS_GA_COMMANDE', 'utm_source=prestashop&utm_medium=back-office&utm_campaign=page-commande');

define('ERP_URL_DOC_EN', 'http://www.illicopresta.com/en/documentation-1-click-erp-2/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');
define('ERP_URL_DOC_FR', 'http://www.illicopresta.com/documentation-1-click-erp/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');

define('ERP_URL_CONTACT_FR', 'http://www.illicopresta.com/contact/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');
define('ERP_URL_CONTACT_EN', 'http://www.illicopresta.com/en/contact-us/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');

define('ERP_URL_CGU_FR', 'http://www.illicopresta.com/conditions-generales-vente/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');
define('ERP_URL_CGU_EN', 'http://www.illicopresta.com/en/terms-and-conditions-of-sale/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');

define('ERP_URL_VIDEO_FR','http://www.illicopresta.com/1-click-erp-pourquoi-et-comment-lutiliser-sur-prestashop/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');
define('ERP_URL_VIDEO_EN','http://www.illicopresta.com/en/1-click-erp-how-to-use-it-and-why/?utm_source=back-office&utm_medium=page-de-configuration&utm_campaign=1-ClickERP');

define('MIGRATION_URL', 'http://migration.illicopresta.com');

define('ERP_STCKMGTFR', 10); 
define('ERP_IVTFR', 10); 
define('ERP_CMFOFR', 1); 
define('ERP_ORDERFR', 3); // free order limitation 
define('ERP_SLOT_IPTIMEMACHINE', 3); // limit of historic slots

// Emails
define('ERP_EMAIL_SUPPORT', 'support@illicopresta.com'); // email to contact support 
