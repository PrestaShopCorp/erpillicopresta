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
*  @copyright 2007-2014 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<p>&nbsp;</p>

{if $is_1_6}
    <div class="panel infos-franco">
                <h3>
                    <i class="icon-pencil"></i>
                    {l s='Information on postage paid amount' mod='erpillicopresta'}
                </h3>
        
                <div class="row">
                     <div class="col-lg-6">
                         {l s='Postage paid amount for the selected supplier' mod='erpillicopresta'} :
                     </div>
                     <div class="col-lg-6 text-right">
                        <span class="txt_franco_amount">{displayPrice price=$franco_amount}</span>
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-lg-6">
                         {l s='Total products price (with discount, tax excl)' mod='erpillicopresta'} :
                     </div>
                     <div class="col-lg-6 text-right">
                        {$currency->prefix|escape:'htmlall'}&nbsp;<span class="txt_total_product_price">{$supply_order_total_te|escape:'htmlall'}&nbsp;{$currency->suffix|escape:'htmlall'}</span>
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-lg-6">
                         {l s='Amount remaining to reach the postage paid' mod='erpillicopresta'} :
                     </div>
                     <div class="col-lg-6 text-right">
                        <span class="txt_amount_to_franco" style="color: red">
                                 {if $franco_amount == 0}
                                     <span style="color: #585A69">NA</span>
                                 {elseif $amount_to_franco_with_produc_discount <= 0}
                                     <span style="color: green">{displayPrice price=0}</span>
                                 {else}
                                    {$currency->prefix|escape:'htmlall'}&nbsp;{$amount_to_franco_with_produc_discount|escape:'htmlall'}&nbsp;{$currency->suffix|escape:'htmlall'}
                                 {/if}
                         </span>
                     </div>
                 </div>
        
    </div>
{else}
    <fieldset>
                <legend>
                        <img alt="Supply Order Management" src="../img/admin/edit.gif">
                        {l s='Information on postage paid amount' mod='erpillicopresta'}
                </legend>
                <table class="table_grid">
                       <tr>
                            <th>{l s='Postage paid amount for the selected supplier' mod='erpillicopresta'} : </th>
                            <td> <span class="txt_franco_amount">{displayPrice price=$franco_amount}</span></td>
                       </tr>
                       <tr>     
                            <th>{l s='Total products price (with discount, tax excl)' mod='erpillicopresta'} : </th>
                            <td>{$currency->prefix|escape:'htmlall'}&nbsp;<span class="txt_total_product_price">{$supply_order_total_te|escape:'htmlall'}&nbsp;{$currency->suffix|escape:'htmlall'}</span></td>
                       </tr>     
                       <tr>     
                            <th>{l s='Amount remaining to reach the postage paid' mod='erpillicopresta'} : </th>
                            <td> <span class="txt_amount_to_franco" style="color: red">
                                    {if $franco_amount == 0}
                                        <span style="color: #585A69">NA</span>
                                    {elseif $amount_to_franco_with_produc_discount <= 0}
                                        <span style="color: green">{displayPrice price=0}</span>
                                    {else}
                                       {$currency->prefix|escape:'htmlall'}&nbsp;{$amount_to_franco_with_produc_discount|escape:'htmlall'}&nbsp;{$currency->suffix|escape:'htmlall'}
                                    {/if}
                            </span></td>
                       </tr>
               </table>        
    </fieldset>
{/if}