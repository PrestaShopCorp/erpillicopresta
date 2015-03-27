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

{if isset($content)}

{if isset($erp_feature)}
    {include file=$template_path|cat:'common/erp_sidebar.tpl' erp_feature=$erp_feature}
{/if}

<input type="hidden" id="trad_order" value="{l s='Order' mod='erpillicopresta'}" />
<input type="hidden" id="trad_cancel" value="{l s='Cancel' mod='erpillicopresta'}" />
<input type="hidden" id="trad_add" value="{l s='Add' mod='erpillicopresta'}" />
<input type="hidden" id="trad_alert" value="{l s='Alert' mod='erpillicopresta'}" />
<input type="hidden" id="trad_nodatasreceived" value="{l s='No data received' mod='erpillicopresta'}" />
<input type="hidden" id="trad_selectatleastoneproduct" value="{l s='Select at least one product to order' mod='erpillicopresta'}" />
<input type="hidden" id="trad_invoicenumberdate" value="{l s='You first have to fill invoice number and date' mod='erpillicopresta'}" />
<input type="hidden" id="trad_receiptupdated" value="{l s='Receipt successfully updated' mod='erpillicopresta'}" />
<input type="hidden" id="trad_receiptcanceled" value="{l s='Receipt successfully cancelled' mod='erpillicopresta'}" />
<input type="hidden" id="trad_updateerror" value="{l s='Error while updating receipt' mod='erpillicopresta'}" />
<input type="hidden" id="trad_atleastoneorder" value="{l s='Please select at least one order' mod='erpillicopresta'}" />
<input type="hidden" id="trad_orderlinkingtobilling" value="{l s='Order successfully linked with billing' mod='erpillicopresta'}" />
<input type="hidden" id="trad_wholesalepriceok" value="{l s='Purchase price successfully updated' mod='erpillicopresta'}" />
<input type="hidden" id="trad_wholesalepriceko" value="{l s='Error while updating purchase price' mod='erpillicopresta'}" />

<input type="hidden" id="transtation_no_data" value="{l s='Warning, no data has been received' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_add_to_so" value="{l s='Add to supply order' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_select_one_product" value="{l s='Please select at least one product to order !' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_cancel" value="{l s='Cancel' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_fill_invoice_number" value="{l s='You first have to fill the invoice number and date to invoice.' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_error_receipt1" value="{l s='Error while cancelling receipt.' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_error_receipt2" value="{l s='Error while updating receipt.' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_select_one_order" value="{l s='Please select at least one order.' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_confirm" value="{l s='Confirm.' mod='erpillicopresta'}" />
<input type="hidden" id="transtation_order_linked_billing" value="{l s='Orders successfully linked to a billing' mod='erpillicopresta'}" />


    <div id="dialog-confirmUpdateSupplyOrderState" title="{l s='Confirm supply order update ?' mod='erpillicopresta'}" style="text-align: center; display:none;"> 
            {l s='Change state of order #' mod='erpillicopresta'}<b id="dialog-id-supply-order"></b> {l s='to' mod='erpillicopresta'} <br/><br/>
            <b id="dialog-name-supply-order-state"></b> {l s='?' mod='erpillicopresta'}
    </div>
    
    <!-- Div cachée affichée sur le passage de la souris sur une colonne -->
    <div id="div_mouseover" class="hide">
        <table id="table_popup"></table>
    </div>
    
    <div id="dialog-billing" title="{l s='Create a new bill' mod='erpillicopresta'}" style="display: none;">
        <p>{l s='Please fill the following fields :' mod='erpillicopresta'}</p>
        
        <table>
            <tr>
                <td>{l s='Invoice number' mod='erpillicopresta'}</td>
                <td><input type="text" name="invoice_number" id="invoice_number" /></td>
            </tr>
            <tr>
                <td>{l s='Date to invoice' mod='erpillicopresta'}</td>
                <td><input type="text" id="date_to_invoice_group" name="date_to_invoice" class="date_to_invoice" /></td>
            </tr>
        </table>
    </div>
    
    {$content}

{/if}