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

    <div class="hint" style="display:block;">
        {l s='To transfer stock of product between two warehouses, the product must be registered in both warehouses' mod='erpillicopresta'}
    </div>
    <br/>

    <div class="filter-stock">
            <!-- Ajout du bandeau de filtres -->
            <form id="filters" type="get" style="width:85%;float:left;">
                <input type="hidden" name="controller" value="AdminStockTransfer" />
                <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}" />

                <input class="transfers" name="transfers" type="hidden" value="{$transfers|escape:'htmlall':'UTF-8'}" />

				<div class="filter-one">
					{if count($warehousesA) > 0 && count($warehousesB) > 0}
						<!-- entrepot source -->
						<div id="warehouseA_filter">
							<label for="warehouseA">{l s='Source warehouse :' mod='erpillicopresta'}</label>
							<select name="warehouseA" onchange="$('#filters').submit();" class="chosen">
								<option value="-1">{l s='No selected warehouse' mod='erpillicopresta'}</option>
								{foreach from=$warehousesA key=key item=item_warehouse}
										<option {if $item_warehouse['id_warehouse'] == $warehouseA} selected="selected"{/if} value="{$item_warehouse['id_warehouse']}">{$item_warehouse['name']}</option>
								{/foreach}
							</select>
						</div>
						
						<!-- entrepot destination -->
						<div id="warehouseB_filter">
							<label for="warehouseB">{l s='Destination warehouse :' mod='erpillicopresta'}</label>
							<select name="warehouseB" onchange="$('#filters').submit();" class="chosen">
								<option value="-1">{l s='No selected warehouse' mod='erpillicopresta'}</option>
								{foreach from=$warehousesB key=key item=item_warehouse}
										<option {if $item_warehouse['id_warehouse'] == $warehouseB} selected="selected"{/if} value="{$item_warehouse['id_warehouse']}">{$item_warehouse['name']}</option>
								{/foreach}
							</select>
						</div>
					{/if}
					<br style="clear:both;">
					<input type="search" name="name_or_ean" id="name_or_ean" value="{$name_or_ean|escape:'htmlall':'UTF-8'}" placeholder="{l s='Search by name or EAN' mod='erpillicopresta'}" onblur="$('#filters').submit();" />
					
				</div>
				<div class="filter-two">
					
					<label>{l s='Select the filters below :' mod='erpillicopresta'}</label>
					<br /><br />
					
					<!-- Filtre catégories -->
					{if count($categories) > 0}
							<div id="categories_filter">
									<label for="id_categories">{l s='Filter by categorie:' mod='erpillicopresta'}</label>
									<select name="id_category" onchange="$('#filters').submit();" class="chosen">
										<option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
											{foreach from=$categories key=key item=category}
													<option {if $category['id_category'] == $id_category} selected="selected"{/if} value="{$category['id_category']}">{$category['name']}</option>
											{/foreach}
									</select>
							</div>
					{/if}
					
					<!-- Filtre fournisseur -->
					{if count($suppliers) > 0}
							<div id="suppliers_filter">
									<label for="id_supplier">{l s='Filter by supplier:' mod='erpillicopresta'}</label>
                                                                        <select name="id_supplier" onchange="$('#filters').submit();" class="chosen">
											<option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
											{foreach from=$suppliers key=key item=supplier}
													<option {if $supplier['id_supplier'] == $id_supplier} selected="selected"{/if} value="{$supplier['id_supplier']}">{$supplier['name']}</option>
											{/foreach}
									</select>
							</div>
					{/if}
					
					<!-- Filtre marques -->
					{if count($manufacturers) > 0}
							<div id="manufacturers_filter">
									<label for="id_manufacturer">{l s='Filter by manufacturer:' mod='erpillicopresta'}</label>
									<select name="id_manufacturer" onchange="$('#filters').submit();" class="chosen">
											<option value="-1">{l s='No filter' mod='erpillicopresta'}</option>
											{foreach from=$manufacturers key=key item=manufacturer}
													<option {if $manufacturer['id_manufacturer'] == $id_manufacturer} selected="selected"{/if} value="{$manufacturer['id_manufacturer']}">{$manufacturer['name']}</option>
											{/foreach}
									</select>
							</div>
					{/if}
					
				</div>
				
            </form>

            <!-- Bouton valider -->
            <form type="get" id="submitTransfers" {if !isset($smarty.get.transfers) || $smarty.get.transfers == ''}style="display:none;"{/if}>
                <input type="hidden" name="warehouseA" value="{$warehouseA|escape:'html':'UTF-8'}" />
                <input type="hidden" name="warehouseB" value="{$warehouseB|escape:'html':'UTF-8'}" />
                <input type="hidden" name="controller" value="AdminStockTransfer" />
                <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}" />
                
                <input type="hidden" id="id_stockA" name="id_stockA" value="" />
                <input type="hidden" id="id_stockB" name="id_stockB" value="" />
                <input type="hidden" id="lastname" name="lastname" value="" />
                <input type="hidden" id="firstname" name="firstname" value="" />
                <input type="hidden" id="id_employee" name="id_employee" value="" />
                <input type="hidden" id="values" name="values" value="" />
                <input type="hidden" id="deleteCookie" name="deleteCookie" value="" />
                <input type="hidden" id="ids_mvt_csv" name="ids_mvt_csv" value="" />
                <input type="hidden" id="id_warehouse_src" name="id_warehouse_src" value="" />
                <input type="hidden" id="id_warehouse_dst" name="id_warehouse_dst" value="" />
                
                <input name="validate_transfer" 
                       id="validate_transfer" 
                       class="button validate-transfert" 
                       type="submit" 
                       value="{l s='Proceed to the transfer' mod='erpillicopresta'}" 
                       title="{l s='Proceed to the transfer' mod='erpillicopresta'}" />
            </form>
			
            <br style="clear:both;">
    </div>
    
    <div id="stock_transfer_content">
    {$content}
    </div>
{/if}