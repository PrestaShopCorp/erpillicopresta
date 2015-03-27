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
{extends file="controllers/supply_orders_change_state/helpers/form/form.tpl"}

{block name="other_fieldsets"}							
<br />
<div class="panel">
    <h3><i class="icon-download-alt"></i> {l s='Print the supply order form' mod='erpillicopresta'}</h3>
{if isset($supply_order_state) && $supply_order_state->editable == false && isset($supply_order)}
<fieldset>
    <legend>
            <img src="../img/admin/pdf.gif" alt="{l s='Supply Order State' mod='erpillicopresta'}">
            {l s='Print the supply order form' mod='erpillicopresta'}
    </legend>

    <a href="{$link->getAdminLink('AdminPdf')|escape:'htmlall':'UTF-8'}&submitAction=generateSupplyOrderFormPDF&id_supply_order={$supply_order->id}" target="_blank" title="Export as PDF">{l s='Click here to download the supply order form.' mod='erpillicopresta'}.</a>
    
    <div id="invoice" class="invoice">
        {if $controller_status == 1}
            <label>{l s='Invoice number :' mod='erpillicopresta'}</label>
            <div class="margin-form">    
                <input type="text" name="invoice_number" id="invoice_number" />
            </div>

            <label>{l s='Invoice date :' mod='erpillicopresta'}</label>
            <div class="margin-form">    
                    <input type="text" name="date_to_invoice" id="date_to_invoice" class="date_to_invoice" />
            </div>
        {/if}
    </div>
</fieldset>
</div>


{/if}

{/block}
