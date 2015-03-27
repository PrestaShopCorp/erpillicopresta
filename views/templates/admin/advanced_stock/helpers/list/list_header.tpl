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

    <!-- Dialog de confirmation de création d'image -->

    <div id="dialog-confirm_image" title="{l s='Confirmation' mod='erpillicopresta'}" style="display: none;">
       {if $controller_status == $smarty.const.STATUS3}
        <form method="get" id="form-confirm-image">
            <input type="hidden" name="id_warehouse" value="{$id_warehouse|escape:intval}" />
            <input type="hidden" name="token" value="{$smarty.get.token|escape:'htmlall'}" />
            <input type="hidden" name="controller" value="AdminAdvancedStock" />
            <input type="hidden" name="createImageStock" value="1" />
            <table id="tbl_container">
                <thead>
                        <tr>
                            <th></th>
                            <th>{l s='Name' mod='erpillicopresta'}</th>
                            <th>{l s='Creation date' mod='erpillicopresta'}</th>
                        </tr>
                </thead>
                <tbody>
                    <!-- On permet la création d'un nouveau pack que si on a encore des emplacment dispo dans le pack -->
                    {if $images|@count lt $pack}
                            <tr class="selected">
                                    <td><input type="radio" class="id_stock_image" name="images[0][id_stock_image]" value="-1" checked="checked" /></td>
                                    <td class='name'>
                                        <input type="text" id="new_image" name="images[0][name_stock_image]" placeholder="{l s='New stock image' mod='erpillicopresta' width='100%'}" />
                                    </td>
                                    <td class='date_add'>--</td>
                            </tr>
                    {/if}
                    {foreach $images AS $key => $image}
                            {$key = $key +1 }
                            <tr>
                                    <td><input type="radio" class="id_stock_image" name="images[{$key|escape:intval}][id_stock_image]" value="{$image['id_stock_image']|escape:intval}" /></td>
                                    <td class='name'><input type="text" id="existing_image" name="images[{$key|escape:intval}][name_stock_image]" value="{$image['name']|escape:'htmlall'}" width="100%" /></td>
                                    <td class='date_add'>{$image['date_add']|escape:'htmlall'}</td>
                            </tr>
                    {/foreach}
                </tbody>
                
            </table>
        </form>
                
        <div class="legende_stockimg">
            {if $advanced_stock_management}
                <p>{l s='Caution, the stock image is based on the selected warehouse. The other current filters are not used' mod='erpillicopresta'}</p>
            {else}
                <p>{l s='Caution, the current filters are not used' mod='erpillicopresta'}</p>
            {/if}
        </div>
        {else}
            <p>{l s='To use this functionnality switch to PRO offer.' mod='erpillicopresta'}</p>
        {/if}
    </div>
    
    <!-- On affiche seulement un seul bloc de filtre -->
    {if $list == 'first'}
    <div class="bootstrap prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'} advanced_stock">
        <form id="filters" type="get" class="form-horizontal">
        <div class="row">
            <div class="{if $advanced_stock_management}col-lg-6{else}col-lg-12{/if}">
                <div class="panel block-filter">
                    <h3><i class="icon-cogs"></i> {l s='General filters' mod='erpillicopresta'}</h3>
                    <div>
                        <!-- Ajout du bandeau de filtres -->
                        
                                <input type="hidden" name="controller" value="AdminAdvancedStock" />
                                <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />

                                {if $controller_status == $smarty.const.STATUS3}
                                <!-- Liste des images de stock -->
                                {if count($images) > 0}
                                        
                                        <div class="form-group">
                                                <label for="images" class="control-label col-lg-3">{l s='Select a stock image:' mod='erpillicopresta'}</label>
                                                <div class="col-lg-5">
                                                    <select name="id_image" id="images"  onchange="$('#filters').submit();">
                                                            <option value="-1">{l s='Current stock' mod='erpillicopresta'}</option>
                                                            {foreach from=$images key=key item=item_image}
                                                                <option {if $item_image['id_stock_image'] == $id_image} selected="selected"{/if} value="{$item_image['id_stock_image']|escape:'html':'UTF-8'}">{$item_image['name']|escape:'html':'UTF-8'} -- {$item_image['date_add']|escape:'html':'UTF-8'}</option>
                                                            {/foreach}
                                                    </select>
                                                </div>
                                        </div>

                                       <label>{l s='Or use the filters below' mod='erpillicopresta'}</label>

                                {/if}
                                {/if}

                                <!-- Filtre par entrepôt seulement en gestion de stock avancée active -->
                                <div id="filter_list">

                                                

                                                {* categories filter *}
                                                {if count($categories) > 0}

                                                        <div class="form-group">
                                                                <label for="id_categories_filter" class="control-label col-lg-3">{l s='Filter by categorie:' mod='erpillicopresta'}</label>
                                                                <div class="col-lg-5">
                                                                        <select name="id_category" id="id_categories_filter" onchange="submit();">
                                                                        <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                                            {foreach from=$categories key=key item=category}
                                                                                            <option {if $category['id_category'] == $id_category} selected="selected"{/if} value="{$category['id_category']|escape:'html':'UTF-8'}">{$category['name']|escape:'html':'UTF-8'}</option>
                                                                            {/foreach}
                                                                        </select>
                                                                </div>
                                                        </div>

                                                {/if}

                                                {* supplier filter *}
                                                {if count($suppliers) > 0}

                                                    <div class="form-group">
                                                        <label for="id_supplier_filter" class="control-label col-lg-3">{l s='Filter by supplier:' mod='erpillicopresta'}</label>
                                                        <div class="col-lg-5">
                                                            <select name="id_supplier" id="id_supplier_filter" onchange="submit();">
                                                                <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                                {foreach from=$suppliers key=key item=supplier}
                                                                                <option {if $supplier['id_supplier'] == $id_supplier} selected="selected"{/if} value="{$supplier['id_supplier']|escape:'html':'UTF-8'}">{$supplier['name']|escape:'html':'UTF-8'}</option>
                                                                {/foreach}
                                                            </select>
                                                        </div>
                                                    </div>

                                                {/if}

                                                {* manufacturer filter *}
                                                {if count($manufacturers) > 0}

                                                    <div class="form-group">
                                                        <label for="id_manufacturer_filter"class="control-label col-lg-3">{l s='Filter by manufacturer:' mod='erpillicopresta'}</label>
                                                        <div class="col-lg-5">
                                                                <select name="id_manufacturer" id="id_manufacturer_filter" onchange="submit();">
                                                                <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                                    {foreach from=$manufacturers key=key item=manufacturer}
                                                                                    <option {if $manufacturer['id_manufacturer'] == $id_manufacturer} selected="selected"{/if} value="{$manufacturer['id_manufacturer']|escape:'html':'UTF-8'}">{$manufacturer['name']|escape:'html':'UTF-8'}</option>
                                                                    {/foreach}
                                                                </select>
                                                        </div>
                                                    </div>

                                                {/if}
                                                
                                                 <div class="form-group">
                                                            <label for="moreless_filter" class="control-label col-lg-3">{l s='Filter by quantity:' mod='erpillicopresta'}</label>
                                                            <div class="col-lg-6">
                                                                    <select name="moreless" class="moreless_select" id="moreless_filter">
                                                                        <option value='-1'>--</option>
                                                                        {foreach from=$tokens key=key item=moreless_token}
                                                                                <option {if $moreless_token['value'] == $moreless} selected="selected"{/if} value="{$moreless_token['value']|escape:'html':'UTF-8'}">{$moreless_token['label']|escape:'html':'UTF-8'}</option>
                                                                        {/foreach}
                                                                    </select>

                                                                    <input type="text" id="quantity_filter" name="quantity_filter" size="5" value="{if isset($smarty.get.quantity_filter)}{$smarty.get.quantity_filter}{else}0{/if}" onchange="submit();"/>
                                                                    <button onclique="submit();" class="button moreless_button">
                                                                        <i class="process-icon-ok"></i>{l s='Quantity filter' mod='erpillicopresta'}
                                                                    </button>

                                                            </div>
                                                    </div>
                                </div>
                       

                    </div>
                </div>
            </div>
            
            {if $advanced_stock_management}
             
            <div class="col-lg-6">
                <div class="panel block-filter">
                    <h3><i class="icon-cogs"></i> {l s='Location filter' mod='erpillicopresta'}</h3>
                     <!-- quantity filter more / less -->
                     
                     
                   {if $controller_status == $smarty.const.STATUS3}
                     <div>
                     
                     {if count($warehouses) > 0}

                        <div class="form-group">
                                <label for="id_warehouse_filter" class="control-label col-lg-3">{l s='Filter by warehouse:' mod='erpillicopresta'}</label>
                                <div class="col-lg-5">
                                        <select name="id_warehouse" id="id_warehouse_filter" onchange="razAreaFilter();submit();">
                                            <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                            {foreach from=$warehouses key=key item=warehouse}
                                                            <option {if $warehouse['id_warehouse'] == $id_warehouse} selected="selected"{/if} value="{$warehouse['id_warehouse']|escape:'html':'UTF-8'}">{$warehouse['name']|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                </div>
                        </div>

                        {if $controller_status}
                            {if isset($areas) && count($areas) > 0 && $id_warehouse != -1}
                                {$area_disabled = ''}
                            {else}
                                {$area_disabled = 'disabled'}
                            {/if}

                            {if isset($sub_areas) && count($sub_areas) > 0 && $id_warehouse != -1}
                                {$sub_area_disabled = ''}
                            {else}
                                {$sub_area_disabled = 'disabled'}
                            {/if}

                            <div class="form-group">
                                    <label for="area_filter" class="control-label col-lg-3">{l s='Filter by area:' mod='erpillicopresta'}</label>
                                    <div class="col-lg-5">
                                            <select name="area" id="area_filter" onchange="submit();" {$area_disabled|escape:'htmlall'}>
                                                <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                {if isset($areas) && count($areas) > 0}
                                                    {foreach from=$areas key=key item=item_area}
                                                            <option {if isset($smarty.get.area) && $item_area.id_erpip_zone == $smarty.get.area} selected="selected"{/if} value="{$item_area.id_erpip_zone|escape:'html':'UTF-8'}">
                                                                {$item_area.name|escape:'html':'UTF-8'}
                                                            </option>
                                                    {/foreach}
                                                {/if}
                                            </select>
                                    </div>
                            </div>

                            <div class="form-group">
                                    <label for="subarea_filter" class="control-label col-lg-3">{l s='Filter by sub area:' mod='erpillicopresta'}</label>
                                    <div class="col-lg-5">
                                            <select name="subarea" id="subarea_filter" onchange="submit();" {$sub_area_disabled|escape:'htmlall'}>
                                                <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                {if isset($sub_areas) && count($sub_areas) > 0 && $id_warehouse != -1}
                                                    {foreach from=$sub_areas key=key item=item_subarea}
                                                            <option {if isset($smarty.get.subarea) && $item_subarea.id_erpip_zone == $smarty.get.subarea} selected="selected"{/if} value="{$item_subarea.id_erpip_zone|escape:'html':'UTF-8'}">
                                                                {$item_subarea.name|escape:'html':'UTF-8'}
                                                            </option>
                                                    {/foreach}
                                                {/if}
                                            </select>
                                    </div>
                            </div>
                        {/if}    
                {else}
                {l s='No warehouse has been found !' mod='erpillicopresta'}
                {/if}
                   
                     </div>
                {else}
                    <p>{l s='To use Warehouse/Area filter, switch to PRO version.' mod='erpillicopresta'}</p>
                {/if}
                 
                <br style="clear:both;"/>
                </div>
                
            </div>
            
            {/if}
            
        </div>
         </form>
        
    </div>
    {/if}

    {if $list == 'image'}
        <div class="panel block-filter">
            <h3><i class="icon-cogs"></i> {l s='Select a stock image:' mod='erpillicopresta'}</h3>
            <div class="form-group">
                <!-- Ajout du bandeau de filtres -->
                <form id="filters-images" type="get" class="form-horizontal">
                    <input type="hidden" name="controller" value="AdminAdvancedStock" />
                    <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />

                    <!-- Liste des images de stock -->
                    {if count($images) > 0}
                            <label for="images" class="control-label col-lg-3">{l s='Stock image:' mod='erpillicopresta'}</label>
                            
                            <div class="col-lg-5">
                                <select name="id_image" id="images" onchange="submit();">
                                        <option value="-1">{l s='Current stock' mod='erpillicopresta'}</option>
                                        {foreach from=$images key=key item=item_image}
                                                        <option {if $item_image['id_stock_image'] == $id_image} selected="selected"{/if} value="{$item_image['id_stock_image']|escape:'html':'UTF-8'}">{$item_image['name']|escape:'html':'UTF-8'} -- {$item_image['date_add']|escape:'html':'UTF-8'}</option>
                                        {/foreach}
                                </select>
                            </div>
                    {/if}
                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    {/if}
    <h2>{$sub_title|escape:'html':'UTF-8'}</h2>
{/block}