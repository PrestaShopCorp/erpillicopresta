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

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<div style="margin-top: 20px;">
        <fieldset>
                <legend>{if isset($is_template) && $is_template == 1} {l s='Template'  mod='erpillicopresta'} {/if}{l s='General information' mod='erpillicopresta'}</legend>
                <table style="width: 400px;" classe="table">
                        <tr>
                                <td>{l s='Creation date:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_creation_date|escape:'htmlall'}</td>
                        </tr>
                        <tr>
                                <td>{l s='Supplier:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_supplier_name|escape:'htmlall'}</td>
                        </tr>
                        <tr>
                                <td>{l s='Last update:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_last_update|escape:'htmlall'}</td>
                        </tr>
                        <tr>
                                <td>{l s='Delivery expected:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_expected|escape:'htmlall'}</td>
                        </tr>
                        <tr>
                                <td>{l s='Warehouse:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_warehouse|escape:'htmlall'}</td>
                        </tr>
                        <tr>
                                <td>{l s='Currency:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_currency->name|escape:'htmlall'}</td>
                        </tr>
                        <tr>
                                <td>{l s='Global discount rate:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_discount_rate|escape:'htmlall'} %</td>
                        </tr>
                        <tr>    
                                <td colspan="2">&nbsp;</td>
                        </tr>    
                        <tr>
                                <td>{l s='Global discount amount:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_discount_amount|escape:'htmlall'} {$supply_order_currency->sign|escape:'htmlall'}</td> 
                        </tr>
                        <tr>
                                <td>{l s='Discount:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_escompte|escape:'htmlall'} %</td> 
                        </tr>
                        <tr>
                                <td>{l s='Shipping amount:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_shipping_amount|escape:'htmlall'} {$supply_order_currency->sign|escape:'htmlall'}</td> 
                        </tr>
                        <tr>
                                <td>{l s='Invoice number:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_invoice_number|escape:'htmlall'}</td> 
                        </tr>
                        <tr>
                                <td>{l s='Date of invoice creation :' mod='erpillicopresta'}</td>
                                <td>{$supply_order_date_to_invoice|escape:'htmlall'}</td> 
                        </tr>
                        <tr>
                                <td>{l s='Description:' mod='erpillicopresta'}</td>
                                <td>{$supply_order_description|strip_tags|escape:'htmlall'}</td> 
                        </tr>
                </table>
        </fieldset>
</div>
 <!-- Affichage des commandes clients liées à la commande fournissseur dans le cas de la génération automatique-->
        {if !empty( $concerned_customer ) } 
            <div style="margin-top: 20px;">
                    <fieldset>
                            <legend>{l s='Related customer orders:' mod='erpillicopresta'}</legend>
                                 <ul>  
                                     {foreach key=key item=customer from=$concerned_customer}
 
                                            <li>
                                                {$customer.customer_name|escape:'htmlall'} - 
                                                <a href="{$link->getAdminLink('AdminOrders',true)|escape:'htmlall'}&amp;id_order={$customer.id_order|intval}&vieworder" target="_blank">
                                                    {l s='Order #' mod='erpillicopresta'} : {$customer.id_order|intval}
                                                </a>
                                            </li>    

                                    {/foreach}
                                <ul>
                    </fieldset>
            </div>
        {/if}
	<div style="margin-top: 20px;">
		<fieldset>
			<legend>{if isset($is_template) && $is_template == 1} {l s='Template' mod='erpillicopresta'} {/if}{l s='Products:' mod='erpillicopresta'}</legend>
			{$supply_order_detail_content}
		</fieldset>
	</div>

	<div style="margin-top: 20px;">
		<fieldset>
			<legend>{if isset($is_template) && $is_template == 1} {l s='Template' mod='erpillicopresta'} {/if}{l s='Summary' mod='erpillicopresta'}</legend>
			<table style="width: 400px;" classe="table">
				<tr>
					<th>{l s='Designation' mod='erpillicopresta'}</th>
					<th width="100px">{l s='Value' mod='erpillicopresta'}</th>
				</tr>
				<tr>
					<td bgcolor="#000000"></td>
					<td bgcolor="#000000"></td>
				</tr>
				<tr>
					<td>{l s='Total (tax excl.)' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_total_te|escape:'htmlall'}</td>
				</tr>
				<tr>
					<td>{l s='Discount' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_discount_value_te|escape:'htmlall'}</td>
				</tr>
				<tr>
					<td>{l s='Total with discount (tax excl.)' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_total_with_discount_te|escape:'htmlall'}</td>
				</tr>
				<tr>
					<td>{l s='Shipping amount' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_shipping_amount|escape:'htmlall'}</td>
				</tr>
				<tr>            
					<td>{l s='Total with shipping amount' mod='erpillicopresta'}</td>
					<td align="right">{$total_shipping|escape:'htmlall'}</td>
				</tr>
                <tr>
					<td bgcolor="#000000"></td>
					<td bgcolor="#000000"></td>
				</tr>
				<tr>
					<td>{l s='Discount (%)' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_escompte|escape:'htmlall'} %</td>
				</tr>
                                <tr>
					<td>{l s='Discount (amount)' mod='erpillicopresta'}</td>
					<td align="right">{$escompte_amount|escape:'htmlall'} </td>
				</tr>
                                <tr>
					<td>{l s='Total with discount' mod='erpillicopresta'}</td>
					<td align="right">{$total_escompte|escape:'htmlall'}</td>
				</tr>
				<tr>
					<td bgcolor="#000000"></td>
					<td bgcolor="#000000"></td>
				</tr>
				<tr>
					<td>{l s='Total Tax' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_total_tax|escape:'htmlall'}</td>
				</tr>
				<tr>
					<td>{l s='Total (tax incl.)' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_total_ti|escape:'htmlall'}</td>
				</tr>
				<tr>
					<td bgcolor="#000000"></td>
					<td bgcolor="#000000"></td>
				</tr>
				<tr>
					<td>{l s='Total to pay' mod='erpillicopresta'}</td>
					<td align="right">{$supply_order_total_ti|escape:'htmlall'}</td>
				</tr>
			</table>
		</fieldset>
	</div>

{/block}