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

{if count($products) > 0}
<table class="table_popup">
    <tr>
        <th>{l s='SKU' mod='erpillicopresta'}</th>
        <th>{l s='Name' mod='erpillicopresta'}</th>
        <th>{l s='Qty' mod='erpillicopresta'}</th>
        {if Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')}
            <th>{l s='Physical stocks' mod='erpillicopresta'}</th>
            <th>{l s='Usable stock' mod='erpillicopresta'}</th>
            <th>{l s='Real stock' mod='erpillicopresta'}</th>
        {else}
            <th>{l s='Stock' mod='erpillicopresta'}</th>
        {/if}
    </tr>
    {foreach from=$products item=product}   
        <tr>
           <td>{$product['reference']|escape: 'htmlall'}</td>
           <td>{$product['name']|escape: 'htmlall'}</td>
           <td>{$product['quantity']|intval}</td>
           {if Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')}
               <td>{$product['physical_stock']|intval}</td>
               <td>{$product['usable_stock']|intval}</td>
               <td>{$product['real_stock']|intval}</td>
           {else}
               <td>{$product['stock']|intval}</td>
           {/if}
       </tr>
    {/foreach}
</table>
{else}
    {l s='No product found !' mod='erpillicopresta'}
{/if}