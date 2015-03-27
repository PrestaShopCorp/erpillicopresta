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

<input type="hidden" id="trad_noquantityfilled" value="{l s='Impossible to perform the transfers : quantities not filled out' mod='erpillicopresta'}" />
<input type="hidden" id="trad_notstockenough" value="{l s='You do not have enough stock' mod='erpillicopresta'}" />
<input type="hidden" id="trad_negativetransfert" value="{l s='The transferred quantity must be a positive number !' mod='erpillicopresta'}" />

<input type="hidden" id="warehouse_id_{$warehouse_id|escape:'html':'UTF-8'}" name="warehouse_id_{$warehouse_id|escape:'html':'UTF-8'}" value="{$warehouse_real_id|escape:'html':'UTF-8'}" />
<input type="hidden" id="warehouse_name_{$warehouse_id|escape:'html':'UTF-8'}" name="warehouse_name_{$warehouse_id|escape:'html':'UTF-8'}" value="{$warehouse_name|escape:'html':'UTF-8'}" />

<input type="hidden" id="warehouse_id_{$warehouse2_id|escape:'html':'UTF-8'}" name="warehouse_id_{$warehouse2_id|escape:'html':'UTF-8'}" value="{$warehouse2_real_id|escape:'html':'UTF-8'}" />
<input type="hidden" id="warehouse_name_{$warehouse2_id|escape:'html':'UTF-8'}" name="warehouse_name_{$warehouse2_id|escape:'html':'UTF-8'}" value="{$warehouse2_name|escape:'html':'UTF-8'}" />

<!-- Sauvegarde des valeurs de filrage pour les filtres post recherche et pagination du tableau -->
<input type="hidden" name="warehouseA" value="{$warehouseA|escape:'html':'UTF-8'}" />
<input type="hidden" name="warehouseB" value="{$warehouseB|escape:'html':'UTF-8'}" />
<input type="hidden" name="id_category" value="{$id_category|escape:'html':'UTF-8'}" />
<input type="hidden" name="id_supplier" value="{$id_supplier|escape:'html':'UTF-8'}" />
<input type="hidden" name="id_manufacturer" value="{$id_manufacturer|escape:'html':'UTF-8'}" />

<input type="hidden" name="stockOrderby" value="{$stockOrderby|urlencode}" />
<input type="hidden" name="stockOrderway" value="{$stockOrderway|escape:'html':'UTF-8'}" />
<input type="hidden" name="name_or_ean" class="name_or_ean" value="{$name_or_ean|escape:'htmlall':'UTF-8'}" />

<input class="transfers" name="transfers" type="hidden" value="{$transfers|escape:'html':'UTF-8'}" />
{* exemple 5;12|100_6;3|200_5|300 ==> id_product;id_combination|qty_next *}

{if $smarty.const._PS_VERSION_|substr:0:3 == '1.6'}
<div class="row">
    <div class="panel block-transfert-hold">
        <h3>
            <i class="icon-camera"></i> 
            {l s='Transfer(s) on hold' mod='erpillicopresta'}
           <p class="pull-right"> <b>{$warehouse_name|escape:'html':'UTF-8'} - {$warehouse2_name|escape:'html':'UTF-8'}</b></p>
        </h3>
        
        <div class='clearfix'></div>
        
        <div class="center-block" style="width:40%">
            <table class="table" id="transfert_attente">
            </table>
        </div>
        
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
                   value="{l s='Proceed to the transfer' 
                   mod='erpillicopresta'}" 
                   title="{l s='Proceed to the transfer' mod='erpillicopresta'}" />
        </form>
   
        <div class='clearfix'></div>
        
     </div>
</div>
{else}

{/if}

{/block}