{*
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
*}

{* input for JS translation *}
<input type="hidden" id="trad_updateproductfail" value="{l s='Error while updating the stock for a product' mod='erpillicopresta'}" />
<input type="hidden" id="trad_updatestockfail" value="{l s='Error while updating the stock for a product' mod='erpillicopresta'}" />
<input type="hidden" id="trad_errorstockimage" value="{l s='Error while creating a new stock image' mod='erpillicopresta'}" />
<input type="hidden" id="trad_errordeletingcookie" value="{l s='Error while deleting cookie' mod='erpillicopresta'}" />
<input type="hidden" id="trad_cancel" value="{l s='Cancel' mod='erpillicopresta'}" />
<input type="hidden" id="trad_validate" value="{l s='OK' mod='erpillicopresta'}" />

{* token of erp zone controller *}
<input type="hidden" id="erp_zone_token" value="{$erp_zone_token|escape:'htmlall'}" />

{include file=$template_path|cat:'common/erp_sidebar.tpl' erp_feature=$erp_feature}

{if isset($content)}

        <!-- Champs caché dans lequel on stock le type de stock utilisé (avancé ou non) -->
        <input id="advanced_stock_management" type="hidden" value="{$advanced_stock_management|escape:'htmlall'}" />

        <!-- Champs caché dans lequel on stock le token -->
        <input id="token" type="hidden" value="{$token|escape:'htmlall'}" />
         
        <div class="prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'}">
            {$content}
        </div>
{/if}
