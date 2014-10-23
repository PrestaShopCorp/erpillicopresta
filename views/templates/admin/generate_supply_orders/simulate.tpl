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

{* input for js translation *}
<input type="hidden" id="translate_choosewarehouse" value="{l s='Please choose a warehouse and an expected delivery date for each supplier !' mod='erpillicopresta'}" />

{$is_1_6 = $smarty.const._PS_VERSION_|substr:0:3 == '1.6'}

{if $show_toolbar && !$is_1_6}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
{/if}

{if isset($erp_feature)}
{include file=$template_path|cat:'common/erp_sidebar.tpl' erp_feature=$erp_feature}
{/if}

<br/>
<div class="hint clear {if $is_1_6}alert{/if} alert-info" style="display:block;">
   {l s='Number of supply orders : ' mod='erpillicopresta'} <b class="badge">{$nbr_commande_genere|intval}</b> 
</div>
<br/><br/>

<form action="{$url_post_ordering|escape:'htmlall'}" method="post" id="form-ordering-info" class="form-horizontal">

    {foreach from=$content key=k item=list}

        {if $is_1_6}
            <p class="clear"></p>
            <div class="panel block-filter">
                
                <h3>
                    <i class="icon-cogs"></i> 
                    {$list.supplier_name|escape:'htmlall'} : 
                    {displayPrice price=$list.supplier_price_total_text_excl currency=$id_default_currency|intval} {l s='TE' mod='erpillicopresta' } 
                </h3>

                 <div class="form-group">
                        <label for="images" class="control-label alignRight col-lg-3">{l s='Warehouse' mod='erpillicopresta'}</label>
                        <div class="col-lg-5">
                            <select class="id_warehouse_simulation required id_warehouse_{$list.supplier_id|intval}" data-id_supplier="{$list.supplier_id|intval}">
                                {foreach from=$warehouses key=k item=warehouse}
                                    <option value="{$warehouse.id_warehouse|intval}">{$warehouse.name|escape:'htmlall'}</option>
                                {/foreach}
                            </select>
                        </div>
                </div>
                
                <p class="clear"></p>
                 
                <div class="form-group">
                        <label for="images" class="control-label alignRight col-lg-3">{l s='Expected delivery date' mod='erpillicopresta'}</label>
                        <div class="col-lg-5">
                            <input type="text"
                                   id="date_delivery_expected_{$list.supplier_id|intval}" 
                                   value="{$current_date|escape:'htmlall'}" 
                                   class="date_delivery_expected_simulation required date_delivery_expected_{$list.supplier_id|intval}"
                                   data-id_supplier="{$list.supplier_id|intval}" />
                        </div>
                </div>
                <p class="clear"></p>              
            </div>
        {else}
            <div style="float:left"> 
                    <h2 style="padding:0; margin:0;">
                        {$list.supplier_name} : {displayPrice price=$list.supplier_price_total_text_excl currency=$id_default_currency|intval} {l s='TE' mod='erpillicopresta' } 
                    </h2>
            </div>

            <!-- Choix entrepros et date de livraison -->
            <div style="float:right;margin:0px 0 20px 0;">

                    <span ><b>{l s='Warehouse' mod='erpillicopresta'} : </b></span>
                    <select id="id_warehouse_{$list.supplier_id|intval}" class="id_warehouse_simulation required id_warehouse_{$list.supplier_id|intval}" data-id_supplier="{$list.supplier_id|intval}">
                        {foreach from=$warehouses key=k item=warehouse}
                            <option value="{$warehouse.id_warehouse|intval}">{$warehouse.name|escape:'htmlall'}</option>
                        {/foreach}
                    </select>

                    &nbsp; &nbsp; 

                    <span ><b>{l s='Expected delivery date' mod='erpillicopresta'} : </b></span>
                    <input type="text" id="date_delivery_expected_{$list.supplier_id|intval}" value="{$current_date|escape:'htmlall'}" class="date_delivery_expected_simulation required date_delivery_expected_{$list.supplier_id|intval}" data-id_supplier="{$list.supplier_id|intval}" />

            </div>
        {/if}
        
        <!-- Liste des produits par fournisseur -->
        <div style="margin:0px 0 40px 0;">
            {$list.list}
        </div>
        <!-- / Liste des produits par fournisseur -->
       
    {/foreach}

 </form>    