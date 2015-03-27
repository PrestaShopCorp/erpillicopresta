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

<p>
    {l s='Transfers from warehouse ' mod='erpillicopresta'} 
    <b>{$stockA|escape:'htmlall'}</b>{l s=' to warehouse ' mod='erpillicopresta'} 
    <b>{$stockB|escape:'htmlall'}</b> :
</p>

<table style="font-size: 30px;">
    <tr style="border:1px solid black">
        <th style="background-color: #4D4D4D; color: #FFF;border:1px solid black; width: 100px; text-align: center;font-weight:bold">{l s='Reference' mod='erpillicopresta'}</th>
        <th style="background-color: #4D4D4D; color: #FFF;border:1px solid black; width: 100px; text-align: center;font-weight:bold">{l s='Manufacturer' mod='erpillicopresta'}</th>
        <th style="background-color: #4D4D4D; color: #FFF;border:1px solid black; width: 150px; text-align: center;font-weight:bold">{l s='Name' mod='erpillicopresta'}</th>
        <th style="background-color: #4D4D4D; color: #FFF;border:1px solid black; width: 50px; text-align: center;font-weight:bold">{l s='Quantity' mod='erpillicopresta'}</th>
        <th style="background-color: #4D4D4D; color: #FFF;border:1px solid black; width: 140px; text-align: center;font-weight:bold">{l s='Comment' mod='erpillicopresta'}</th>
    </tr>
    {foreach $products AS $index_p => $product}
        <tr style="border:1px solid black">
            <td style="border:1px solid black; width: 100px; text-align: left;font-weight:bold">{$product['reference']|escape:'htmlall'}</td>
            <td style="border:1px solid black; width: 100px; text-align: left;font-weight:bold">{$product['manufacturer_name']|escape:'htmlall'}</td>
            <td style="border:1px solid black; width: 150px; text-align: left;">{$product['name']|escape:'htmlall'}</td>
            <td style="border:1px solid black; width: 50px; text-align: right;">{$product['physical_quantity']|intval}</td>
            <td style="border:1px solid black; width: 140px; text-align: right;"></td>
        </tr>
    {/foreach}
</table>

<p></p><p></p><p></p><p></p>
<div style="font-size: 11pt;">
    <table style="width: 30%;border:1px solid black;">
        <tr>
            <td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Date' mod='erpillicopresta'} :</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Signature' mod='erpillicopresta'} :</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
    </table>
</div>