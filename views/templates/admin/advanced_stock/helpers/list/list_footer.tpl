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

{extends file="helpers/list/list_footer.tpl"}
{block name=after}
    <!-- Bloc de totaux affiché seulement pour la première liste -->
    {if $list == 'first'}
            <div class="footer_total">
                    <h4>{l s='Total purchase prices per supplier' mod='erpillicopresta'}</h4>
                    <ul>
                            {foreach $suppliers_prices AS $supplier_price}
                                    <!-- On n'affiche plus le base price -->
                                    {if (($id_supplier == -1 || $supplier_price['id'] == $id_supplier) && $supplier_price['id'] != -1)}
                                            <li><b>{$supplier_price['name']|escape:'htmlall'}</b> = {$supplier_price['wholesale_price']|escape:'htmlall'}€</li>
                                    {/if}
                            {/foreach}

                    </ul>

                    <h4>{l s='Total sell price (TE)' mod='erpillicopresta'} = {$price|escape:'htmlall'}€ </h4>
            </div>
    {/if}
{/block}