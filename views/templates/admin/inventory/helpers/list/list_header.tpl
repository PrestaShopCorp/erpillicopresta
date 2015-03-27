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

{if $show_toolbar}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
{/if}

{extends file="helpers/list/list_header.tpl"}
{block name=override_header}

<div id="extruderLeft" class="{literal}{title:'ERP'}{/literal} prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'}" style="display: none;">
    {if $erp_feature}
        {foreach from=$erp_feature item=feature}
        <div class="voice">
            {if $feature['active']}
            <a class="label" href="index.php?controller={$feature.controller|escape:'htmlall'}&token={$feature.token|escape:'htmlall'}">
                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall'}erpillicopresta/img/features/{$feature.picture|escape:'htmlall'}" style="width:32px;height:32px;"/>
                {$feature.name|escape:'htmlall'}
            </a>
            {else}
                <a class="label" href="javascript:void();">
                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall'}erpillicopresta/img/features/none.png" style="width:32px;height:32px;"/>
                <i>{$feature.name|escape:'htmlall'}</i>
                </a>
            {/if}
        </div>
        {/foreach}
    {/if}
</div>

<div class="filter-stock bootstrap prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'} inventory">
    <!-- Ajout du bandeau de filtres -->
    <form id="filters" type="get" class="form-horizontal">
        <input type="hidden" name="controller" value="AdminInventory" />
        <input type="hidden" name="token" value="{$token|escape:'htmlall'}" />

        <div class='row'>
             <!-- Choix du type d'affichage seulement pour la gestion de stock avancé (pas de gestion de zone sinon) -->
            {if $advanced_stock_management == '1'}
                <!-- Sélection du type d'affichage -->            
                <div class="form-group" id="displayed_type">
                        <label for="id_display" class="control-label col-lg-3 col-lg-offset-1">{l s='Display type :' mod='erpillicopresta'}</label>
                        <div class="col-lg-3">
                            <select name="id_display" id="id_display" onchange="$('#filters').submit();">
                                {if $controller_status == true && $controller_status == STATUS3}
                                    <option value="0" {if $id_display==0}selected=selected{/if}>{l s='Products and combinations' mod='erpillicopresta'}</option>
                                    <option value="1" {if $id_display==1}selected=selected{/if}>{l s='Area, subarea and location' mod='erpillicopresta'}</option>
                                {else}
                                    <option value="0" {if $id_display==0}selected=selected{/if}>{l s='Products and combinations' mod='erpillicopresta'}</option>
                                {/if}
                            </select>
                        </div>
                </div>
            {/if}
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="panel block-filter">
                    <h3>
                        <i class="icon-cogs"></i>
                        {l s='General filters' mod='erpillicopresta'}
                    </h3>
                    
                    {if count($categories) > 0}
                            <div class="form-group" id="categories_filter">
                                <label for="id_category" class="control-label col-lg-3">{l s='Filter by category:' mod='erpillicopresta'}</label>
                                <div class="col-lg-5">
                                     <select name="id_category" id="id_category" onchange="$('#filters').submit();">
                                            <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                            {foreach from=$categories key=key item=category}
                                                    <option {if $category['id_category'] == $id_category} selected="selected"{/if} value="{$category['id_category']}">{$category['name']}</option>
                                            {/foreach}
                                    </select>
                                </div>
                            </div>



                    {/if}

                    {if count($suppliers) > 0}
                            <div class="form-group" id="suppliers_filter">
                                <label for="id_supplier" class="control-label col-lg-3">{l s='Filter by supplier:' mod='erpillicopresta'}</label>
                                <div class="col-lg-5">
                                     <select name="id_supplier" id="id_supplier" onchange="$('#filters').submit();">
                                            <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                            {foreach from=$suppliers key=key item=supplier}
                                                    <option {if $supplier['id_supplier'] == $id_supplier} selected="selected"{/if} value="{$supplier['id_supplier']}">{$supplier['name']}</option>
                                            {/foreach}
                                    </select>
                                </div>
                            </div>
                    {/if}

                    {if count($manufacturers) > 0}

                        <div class="form-group" id="manufacturers_filter">
                            <label for="id_manufacturer" class="control-label col-lg-3">{l s='Filter by manufacturer:' mod='erpillicopresta'}</label>
                            <div class="col-lg-5">
                                 <select name="id_manufacturer" id="id_manufacturer" onchange="$('#filters').submit();">
                                        <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                        {foreach from=$manufacturers key=key item=manufacturer}
                                                <option {if $manufacturer['id_manufacturer'] == $id_manufacturer} selected="selected"{/if} value="{$manufacturer['id_manufacturer']}">{$manufacturer['name']}</option>
                                        {/foreach}
                                </select>
                            </div>
                        </div>

                    {/if}
                    
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel block-filter">
                    <h3>
                        <i class="icon-cogs"></i>
                         {l s='Location filters' mod='erpillicopresta'}
                    </h3>
                    <!-- Filtre par entrepôt seulement en gestion de stock avancée active -->
                    {if $advanced_stock_management && count($warehouses) > 0}
                        {if $controller_status == $smarty.const.STATUS3}
                            <div id="warehouses_filter">


                                        <div class="form-group">
                                                <label for="id_warehouse" class="control-label col-lg-3">{l s='Filter by warehouse:' mod='erpillicopresta'}</label>
                                                <div class="col-lg-5">
                                                    <select name="id_warehouse" id="id_warehouse" onchange="$('#filters').submit();">
                                                    {foreach from=$warehouses key=key item=warehouse}
                                                            <option {if $warehouse['id_warehouse'] == $id_warehouse} selected="selected"{/if} value="{$warehouse['id_warehouse']}">{$warehouse['name']}</option>
                                                    {/foreach}
                                            </select>
                                                </div>
                                        </div>

                                        {if $controller_status == true}
                                        
                                            {if isset($areas) && count($areas) > 0 && $id_warehouse != -1 && $id_display == 1} 
                                            {$area_disabled = ''}
                                            {else}
                                            {$area_disabled = 'disabled'}
                                            {/if}

                                            {if isset($areas) && isset($sub_areas) && count($sub_areas) > 0 && $id_warehouse != -1 && $id_display == 1}
                                            {$sub_area_disabled = ''}
                                            {else}
                                            {$sub_area_disabled = 'disabled'}
                                            {/if}
                                        
                                            <div class="form-group">
                                                <label for="areaFilter" class="control-label col-lg-3">{l s='Filter by area:' mod='erpillicopresta'}</label>
                                                <div class="col-lg-5">
                                                    <select name="areaFilter" id="areaFilter" onchange="$('#filters').submit();" {$area_disabled|escape:'htmlall'}>
                                                        <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                        {foreach from=$areas key=key item=item_area}
                                                            <option {if isset($smarty.get.areaFilter) && $item_area.id_erpip_zone == $smarty.get.areaFilter} selected="selected"{/if} value="{$item_area.id_erpip_zone}">
                                                                {$item_area.name|escape:'htmlall'}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group" >
                                                <label for="subareaFilter" class="control-label col-lg-3">{l s='Filter by sub area:' mod='erpillicopresta'}</label>
                                                <div class="col-lg-5">
                                                     <select name="subareaFilter" id="subareaFilter" onchange="$('#filters').submit();" {$sub_area_disabled|escape:'htmlall'}>
                                                        <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                        {foreach from=$sub_areas key=key item=item_subarea}
                                                            <option {if isset($smarty.get.subareaFilter) && $item_subarea.id_erpip_zone == $smarty.get.subareaFilter} selected="selected"{/if} value="{$item_subarea.id_erpip_zone}">
                                                                {$item_subarea.name|escape:'htmlall'}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                    {/if}
                            </div>
                        {else}
                             <p>{l s='To use this functionnality, switch to PRO offer.' mod='erpillicopresta'}</p>
                        {/if}
                    {/if}
        
                </div>
            </div>
        </div>
        
    </form>
</div>
{/block}