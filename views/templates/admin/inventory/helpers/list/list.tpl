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

{$header}
{$content}
{$footer}

<!-- Champs caché dans lequel on stock le type de stock utilisé (avancé ou non) -->
<input id="advanced_stock_management" type="hidden" value="{$advanced_stock_management|escape:'htmlall'}" />

<!-- Champs caché dans lequel on stock l'entrepôt courant -->
<input id="current_warehouse" type="hidden" value="{$id_warehouse|intval}" />

<!-- Champs caché dans lequel on stock le token -->
<input id="token" type="hidden" value="{$token|escape:'htmlall'}" />
<input type="hidden" name="controller" value="AdminInventory" />

<!-- Champs caché dans lequel on stock le context object -->
<input id="id_employee" type="hidden" value="{$employee->id|intval}" />
<input id="firstname" type="hidden" value="{$employee->firstname|escape:'htmlall'}" />
<input id="lastname" type="hidden" value="{$employee->lastname|escape:'htmlall'}" />

<!-- Champs caché dans lequel on stock l'écart de stock en conf -->
<input id="gap_stock" type="hidden" value="{$gap_stock|intval}" />

<!-- Champs caché dans lequel on stock les raisons d'inventaire par défaut -->
<input id="reason_increase" type="hidden" value="{$reason_increase|escape:'htmlall'}" />
<input id="reason_decrease" type="hidden" value="{$reason_decrease|escape:'htmlall'}" />

<input id="trad_cancel" type="hidden" value="{l s='Cancel' mod='erpillicopresta'}" />
<input id="trad_validate" type="hidden" value="{l s='Validate the inventory' mod='erpillicopresta'}" />
<input id="trad_atleastoneproduct" type="hidden" value="{l s='You have to fill out at least one product' mod='erpillicopresta'}" />
<input id="trad_onlyinteger" type="hidden" value="{l s='A positive number is expected' mod='erpillicopresta'}" />
<input id="trad_locationerror" type="hidden" value="{l s='Warning : location already taken by the product' mod='erpillicopresta'}" />
<input id="trad_locationerror2" type="hidden" value="{l s='you have to manually change it.' mod='erpillicopresta'}" />
<input id="trad_containererror" type="hidden" value="{l s='Error while creating a new container' mod='erpillicopresta'}" />
<input id="trad_quantityerror" type="hidden" value="{l s='Gap between physical quantity in stock and found quandity is greater than the maximum authorized stock gap (see Configuration) : ' mod='erpillicopresta'}" />
<input id="trad_emptyinventoryname" type="hidden" value="{l s='Inventory name cannot be empty' mod='erpillicopresta'}" />
<input id="trad_confirm" type="hidden" value="{l s='This action will update the found quantity with the current physical quantity. All the modifications will be canceled' mod='erpillicopresta'}" />
<input id="trad_advancedstock_warning" type="hidden" value="{l s='The products which are not used in the advanced stock management are not concerned' mod='erpillicopresta'}" />
<input id="trad_classic_warning" type="hidden" value="{l s='The products which are used in the advanced stock management are not concerned' mod='erpillicopresta'}" />


<!-- Dialog de sélection d'un container d'inventaire -->
<div id="dialog-select_container" title="{l s='Create or select a new inventory directory' mod='erpillicopresta'}">
    <form id="form_validate_inventory" method="post" action="index.php?controller=AdminInventory&token={$token|escape:'htmlall'}" enctype="multipart/form-data">
        <input type="hidden" name="submitAction" id="submitAction" value="submitCreateInventory" />
        <input type="hidden" name="inventory_values" id="inventory_values" value="" />
        <input type="hidden" name="advanced_stock_management" id="advanced_stock_management" value="" />
        <input type="hidden" name="id_warehouse" id="id_warehouse" value="" />
        <input type="hidden" name="id_employee" id="id_employee" value="" />
        <input type="hidden" name="firstname" id="firstname" value="" />
        <input type="hidden" name="lastname" id="lastname" value="" />
        <input type="hidden" name="id_inventory" id="id_inventory" value="" />
        <input type="hidden" name="name" id="name" value="" />
        
        <div style="overflow:auto; height:150px;">
            <table id="tbl_container" class="table" width='100%'>
                <thead>
                    <tr>
                        <th></th>
                        <th>{l s='Name' mod='erpillicopresta'}</th>
                        <th>{l s='Creation date' mod='erpillicopresta'}</th>
                        <th>{l s='Last modification date' mod='erpillicopresta'}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="selected">
                        <td><input type="radio" class="id_inventory" name="id_inventory" value="-1" checked="checked" /></td>
                        <td class='name'><input type="text" id="new_inventory" name="new_inventory" placeHolder="{l s='New inventory' mod='erpillicopresta'}" width="100%" /></td>
                        <td class='date_add'>--</td>
                        <td class='date_upd'>--</td>
                    </tr>
                    {foreach $containers AS $key => $container}
                        <tr>
                            <td><input type="radio" class="id_inventory" name="id_inventory" value="{$container['id_erpip_inventory']|intval}_{$container['name']|escape:'htmlall'}" /></td>
                            <td class='name'>{$container['name']|escape:'htmlall'}</td>
                            <td class='date_add'>{$container['date_add']|escape:'htmlall'}</td>
                            <td class='date_upd'>{$container['date_upd']|escape:'htmlall'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        <!-- Champs pour l'import CSV masqués-->
        <div id="csv_fields" class="hide">
            <br />
            <table id="tbl_csv">
                <tr>
                    <td>{l s='Filename : ' mod='erpillicopresta'}</td>
                    <td><input type="file" name="file" id="file"></td>
                </tr>
                <tr>
                    <td>{l s='Warehouse :' mod='erpillicopresta'}</td>
                    <td>
                        <select name="selected_warehouse">
                                {foreach from=$warehouses key=key item=warehouse}
                                        <option value="{$warehouse['id_warehouse']|intval}">{$warehouse['name']|escape:'htmlall'}</option>
                                {/foreach}
                        </select>
                    </td>
                </tr>
            </table>

        </div>

    </form>
</div>

<!-- Dialog de confirmation d'inventaire -->
<div id="dialog-confirm_inventory" title="{l s='Do you really want to make this inventory ?' mod='erpillicopresta'}">
    <p>{l s='Some quantities are not in accordance with the maximum authorized stock gap defined in the module configuration :' mod='erpillicopresta'}</p>
    <br />
    <b><ul>
    </ul></b>
    <br />
    <p>{l s='Are you really sure you want to validate this inventory ?' mod='erpillicopresta'}</p>
</div>