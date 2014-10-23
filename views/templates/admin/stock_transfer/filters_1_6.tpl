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

{if isset($content)}

    <!-- Champs caché dans lequel on stock le context object -->
    <input id="id_employee" type="hidden" value="{$employee->id|escape:intval}" />
    <input id="firstname" type="hidden" value="{$employee->firstname|escape:'htmlall':'UTF-8'}" />
    <input id="lastname" type="hidden" value="{$employee->lastname|escape:'htmlall':'UTF-8'}" />

    <input id="link_pdf" type="hidden" value="{$link_pdf|escape:'htmlall':'UTF-8'}" />
    <input id="link_csv" type="hidden" value="{$link_pdf|escape:'htmlall':'UTF-8'}" />
    
    <!-- Ajout du bandeau de filtres -->
    <form id="filters" type="get" class="form-horizontal">
               
        <div class="row">
         
        <div class="col-lg-6">    
        <div class="panel block-filter">
            <h3><i class="icon-cogs"></i> {l s='Search products' mod='erpillicopresta'}</h3>
                <div class="filter-stock">
            
                                <input type="hidden" name="controller" value="AdminStockTransfer" />
                                <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}" />

                                <input class="transfers" name="transfers" type="hidden" value="{$transfers|escape:'htmlall':'UTF-8'}" />

				<div class="filter-one">
					{if count($warehousesA) > 0 && count($warehousesB) > 0}
						<!-- entrepot source -->                                                
                                                <div class="form-group" id="warehouseA_filter">
                                                    <label for="warehouseA" class="control-label col-lg-5">{l s='Source warehouse :' mod='erpillicopresta'}</label>
                                                    <div class="col-lg-5">
                                                        <select name="warehouseA" id="warehouseA" onchange="$('#filters').submit();" class="chosen">
								<option value="-1">{l s='No selected warehouse' mod='erpillicopresta'}</option>
								{foreach from=$warehousesA key=key item=item_warehouse}
										<option {if $item_warehouse['id_warehouse'] == $warehouseA} selected="selected"{/if} value="{$item_warehouse['id_warehouse']}">{$item_warehouse['name']}</option>
								{/foreach}
							</select>
                                                    </div>
                                                </div> 
						
						<!-- entrepot destination -->                                                
                                                <div class="form-group" id="warehouseB_filter">
                                                    <label for="warehouseB" class="control-label col-lg-5">{l s='Destination warehouse :' mod='erpillicopresta'}</label>
                                                    <div class="col-lg-5">
                                                        <select name="warehouseB" onchange="$('#filters').submit();" class="chosen">
								<option value="-1">{l s='No selected warehouse' mod='erpillicopresta'}</option>
								{foreach from=$warehousesB key=key item=item_warehouse}
										<option {if $item_warehouse['id_warehouse'] == $warehouseB} selected="selected"{/if} value="{$item_warehouse['id_warehouse']}">{$item_warehouse['name']}</option>
								{/foreach}
							</select>
                                                    </div>
                                                </div> 
                                                
					{/if}

                                        <div class="form-group" id="warehouseB_filter">
                                            <label for="warehouseB" class="control-label col-lg-5">{l s='Search by name or EAN' mod='erpillicopresta'}</label>
                                            <div class="col-lg-5">
                                                <input type="search" name="name_or_ean" id="name_or_ean" value="{$name_or_ean|escape:'htmlall':'UTF-8'}" onblur="$('#filters').submit();" />
                                            </div>
                                        </div> 
                                        
				</div>
        </div>        
        </div>        
        </div>        
        <div class="col-lg-6">    
            <div class="panel block-filter">
                <h3> <i class="icon-cogs"></i> {l s='Filters' mod='erpillicopresta'} </h3>
				<div class="filter-two">
					
					<!-- Filtre catégories -->
					{if count($categories) > 0}
                                        
                                            <div class="form-group" id="categories_filter">
                                                <label for="id_categories" class="control-label col-lg-5">{l s='Filter by categorie:' mod='erpillicopresta'}</label>
                                                <div class="col-lg-5">
                                                    <select name="id_category" id="id_categories" onchange="$('#filters').submit();" class="chosen">
                                                        <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                            {foreach from=$categories key=key item=category}
                                                                    <option {if $category['id_category'] == $id_category} selected="selected"{/if} value="{$category['id_category']}">{$category['name']}</option>
                                                            {/foreach}
                                                    </select>
                                                </div>
                                            </div> 
                                        
					{/if}
					
					<!-- Filtre fournisseur -->
					{if count($suppliers) > 0}
							
                                                <div class="form-group" id="suppliers_filter">
                                                    <label for="id_supplier" class="control-label col-lg-5">{l s='Filter by supplier:' mod='erpillicopresta'}</label>
                                                    <div class="col-lg-5">
                                                        <select name="id_supplier" id="id_supplier" onchange="$('#filters').submit();" class="chosen">
                                                            <option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
                                                            {foreach from=$suppliers key=key item=supplier}
                                                                            <option {if $supplier['id_supplier'] == $id_supplier} selected="selected"{/if} value="{$supplier['id_supplier']}">{$supplier['name']}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div> 
                                        
					{/if}
					
					<!-- Filtre marques -->
					{if count($manufacturers) > 0}
                                        
                                                 <div class="form-group" id="manufacturers_filter">
                                                    <label for="id_manufacturer" class="control-label col-lg-5">{l s='Filter by manufacturer:' mod='erpillicopresta'}</label>
                                                    <div class="col-lg-5">
                                                        <select name="id_manufacturer" id="id_manufacturer" onchange="$('#filters').submit();" class="chosen">
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
                </div>
            </div>	
    </form>

    <div id="stock_transfer_content">
    {$content}
    </div>
    
{/if}