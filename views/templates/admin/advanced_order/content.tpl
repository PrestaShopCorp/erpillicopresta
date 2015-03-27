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

<form id="hidden_form" method="post" action="index.php?controller=AdminAdvancedOrder&token={$token|escape:'htmlall'}#order">

    <input type="hidden" id="transtation_select_least_one_order" value="{l s='Please select at least one valid order' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_alert" value="{l s='Alert' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_cancel" value="{l s='Cancel' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_confirm" value="{l s='Confirm' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_update" value="{l s='Update' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_confirm_update" value="{l s='Confirm update' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_continue" value="{l s='Continue' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_weight" value="{l s='Weight' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_ex_error1" value="{l s='Expeditor... Error' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_mr_error1" value="{l s='Mondial Relay... Error' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_mr_error2" value="{l s='Error : MondialRelay does not answer. The orders of this carrier cannot be processed. Please try again in a few minutes.' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_order_not_processeed" value="{l s='The following orders have not been processed' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_carrier_not_supported" value="{l s='Supported carriers are TNT, ExpeditorInet and MondialRelay. Please install their modules if needed. The carrier of the following orders is not supported :' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_tnt_error" value="{l s='Error : TNT module does not respond. The orders of this carrier cannot be processed. Please try again in a few minutes.' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_other_done" value="{l s='Others ... Done' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_order_state_1" value="{l s='has been modified successfully to state' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_order_state_2" value="{l s='Order #' mod='erpillicopresta'}" />
    <input type="hidden" id="transtation_order_state_3" value="{l s='Error' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_mr_done" value="{l s='Mondial Relay... Done' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_ExpInet_done" value="{l s='Expeditor Inet... Done' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_other_done" value="{l s='Others... Done' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_order_status_error" value="{l s='Error on order #' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_order_status_error_EXP" value="{l s='to have orders shipped by Expeditor Inet, these orders must be in the status defined in the configuration of Expeditor Inet' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_order_status_error_MR" value="{l s='to have orders shipped by Mondial Relay, these orders must be in the status defined in the configuration of Mondial Relay' mod='erpillicopresta'}" />
    <input type="hidden" id="translation_other_parameter" value="{l s='Other parameter' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_MR_no_insurance" value="{l s='No insurance' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_MR_complementary_insurance" value="{l s='Complementary insurance Lv' mod='erpillicopresta'}" />
    <input type="hidden" id="translation_error_call_exp" value="{l s='Error while calling Expeditor Inet... Please make sure that your webstore is not configurated in development mode.' mod='erpillicopresta'}" />
    <input type="hidden" id="translation_error_call_MR" value="{l s='Error while calling Mondial Relay... Please make sure that your webstore is not configurated in development mode.' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_order_num" value="{l s='Order #' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_mr_wtf_error" value="{l s='Mondial relay error : ' mod='erpillicopresta'}" />
    <input type="hidden" id="translate_non-standard_size" value="{l s='Non standard size' mod='erpillicopresta'}" />


    <input type="hidden" id="MRToken" name="MRToken" value="{$token_mr|escape:'htmlall'}" />
    <input type="hidden" id="ExpeditorToken" name="ExpeditorToken" value="{$token_expeditor|escape:'htmlall'}" />
    <input type="hidden" id="etiquettesMR" name="etiquettesMR" />
    <input type="hidden" id="deliveryNumbersMR" name="deliveryNumbersMR" />
    <input type="hidden" id="expeditorCSV" name="expeditorCSV" />
    <input type="hidden" id="idOthers" name="idOthers" />
    <input type="hidden" id="id_employee" name="id_employee" value="{$id_employee|intval}" />
    <input type="hidden" id="expeditor_status" name="expeditor_status" value="{$expeditor_status|intval}" />
    {*<input type="hidden" id="MR_status" name="MR_status" value="{$MR_status|intval}" />*}
</form>


<div id="dialog-confirmUpdateOrderState" title="{l s='Confirm order update ?' mod='erpillicopresta'}"> {l s='Change state of order #' mod='erpillicopresta'} <b id="dialog-idOrder"></b> {l s='to' mod='erpillicopresta'} "<b id="dialog-textStateOrder"></b>" {l s='?' mod='erpillicopresta'}</div>
<div id="dialog-updateStates" title="{l s='Select new state' mod='erpillicopresta'}">&nbsp;<br/>

    <select class="selectUpdateStates">
        {if $order_statuses}
            {foreach $order_statuses AS $indice => $statut}
                {if $indice != 'curr'}
                    <option class="selectedOrderState-{$indice|escape:'htmlall'}" value ="{$statut|escape:'htmlall'}">{$statut|escape:'htmlall'}</option>
                {/if}
            {/foreach}
        {/if}
    </select>      
    <img src="../modules/erpillicopresta/img/cluetip/ajax-loader-yellow.gif" class="loader-update-states" style="display:none;" />
</div>


<div id="dialog-confirmUpdateOrderInAlert" title="{l s='Update orders which are in stock warning ?' mod='erpillicopresta'}"></div>
<div id="dialog-confirmWeight" title="{l s='Parameters confirmation' mod='erpillicopresta'}">
    <p>{l s='Expeditor: edit the weight of orders and choose the size option' mod='erpillicopresta'}</p>
    <p>{l s='Mondial Relay : edit the weight of orders and select insurance' mod='erpillicopresta'}</p>
    <p id="dialog-confirmWeight-content"></p>
</div>
    
    {if $controller_status == $smarty.const.STATUS1}
	{literal}
        <script>
            $(document).ready(function(){
            $('input[type="checkbox"]').change(function(){
				var msg = "{/literal} {l s='You are using a FREE version of 1-CLICK ERP. So your limited to 3 orders maximum. Switch to up-version to break the limit' mod='erpillicopresta'} {literal}";
                var counter = $('input[type="checkbox"]:checked').length;
                if(counter > 3)
                {
                    jAlert(msg);
                    $(this).attr('checked', false);
                }
            
			});
			});
        </script>
	{/literal}
    {/if}
{* sidebar *}
{include file=$template_path|cat:'common/erp_sidebar.tpl' erp_feature=$erp_feature}
 <script>
     var _module_dir_ = "{$_module_dir_|escape:'htmlall'}";
 </script>
     
{$content}