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

{extends file="helpers/list/list_header.tpl"}

{block name=override_header}
 
    
{if isset($is_template_list) && !$is_template_list }

<div class="panel block-filter">
    <h3><i class="icon-cogs"></i> {l s='Filters' mod='erpillicopresta'}</h3>
    <div class="filter-stock-extended" style="background-color: transparent;border:none;padding:10px;">
        <form id="supply_orders" type="get" class="form-horizontal">
            <input type="hidden" name="controller" value="AdminAdvancedSupplyOrder" />
            <input type="hidden" name="token" value="{$token|escape:'htmlall'}" />

            {if isset($warehouses) && count($warehouses) > 0 }
                <div class="form-group">
                         <label for="id_warehouse" class="control-label col-lg-3">{l s='Filter by warehouse:' mod='erpillicopresta'}</label>
                         <div class="col-lg-3">
                                 <select name="id_warehouse" id="id_warehouse" onChange="$('#supply_orders').submit();">
                                     {foreach from=$warehouses key=k item=i}
                                             <option {if $i.id_warehouse == $current_warehouse} selected="selected"{/if} value="{$i.id_warehouse}">{$i.name}</option>
                                     {/foreach}
                                 </select>
                         </div>
                </div>
            {/if}
           
            {if isset($suppliers) && count($suppliers) > 0 }
            
                <div class="form-group">
                         <label for="id_supplier" class="control-label col-lg-3">{l s='Filter by supplier:' mod='erpillicopresta'}</label>
                         <div class="col-lg-3">
                                 <select name="id_supplier" id="id_supplier" onChange="$('#supply_orders').submit();">
                                    <option value="-1">{l s='All suppliers' mod='erpillicopresta'}</option>
                                    {foreach from=$suppliers key=k item=i}
                                            <option {if $i.id_supplier == $current_supplier} selected="selected"{/if} value="{$i.id_supplier}">{$i.name}</option>
                                    {/foreach}
                                </select>
                         </div>
                </div>
            
            {/if}

            {if isset($filter_status)}
                <div class="form-group">
                        <div class="checkbox col-lg-3 col-lg-push-3">
                                <label for="filter_status">
                                        <input type="checkbox" name="filter_status" class="noborder" onChange="$('#supply_orders').submit();" {if $filter_status == 1}value="on" checked{/if}></input> 
                                        {l s='Choose not to display completed/canceled orders:' mod='erpillicopresta'}
                                </label>
                        </div>
                </div>
            {/if}

        </form>
    </div>
            </div>

    {/if}	

{/block}