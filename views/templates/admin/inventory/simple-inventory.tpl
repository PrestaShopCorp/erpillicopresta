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

{if $empty == 0}
{assign var="current_manufacturer" value=""}
	<table id="inventory_grid" style="font-size: 30px;">
		{foreach $products AS $index_p => $product}
			
				{if $product['manufacturer_name'] == ""}
					{$product['manufacturer_name'] = {l s='Products without manufacturer name' mod='erpillicopresta'}}
				{/if}
				{if $current_manufacturer != $product['manufacturer_name']}
					{if $index_p!=0}
						<h1 style="page-break-before:always">{$product['manufacturer_name']|escape:'htmlall'}</h1>
					{else}
						<h1>{$product['manufacturer_name']|escape:'htmlall'}</h1>
					{/if}
					
					<tr style="border:1px solid black">
						<!--<th style="border:1px solid black; width: 80px; text-align: center;font-weight:bold">{l s='Image' mod='erpillicopresta'}</th>-->
						<th style="border:1px solid black; width: 180px; text-align: center;font-weight:bold">{l s='Name' mod='erpillicopresta'}</th>
						<th style="border:1px solid black; width: 80px; text-align: center;font-weight:bold">{l s='Reference' mod='erpillicopresta'}</th>
						<th style="border:1px solid black; width: 80px; text-align: center;font-weight:bold">{l s='EAN' mod='erpillicopresta'}</th>
						<th style="border:1px solid black; width: 80px; text-align: center;font-weight:bold">{l s='Available stock' mod='erpillicopresta'}</th>
						<th style="border:1px solid black; width: 80px; text-align: center;font-weight:bold">{l s='Found quantity' mod='erpillicopresta'}</th>
					</tr>
				{/if}
				
				<tr style="border:1px solid black">
					{* <td style="border:1px solid black; width: 80px; text-align: center;">{if $product['image'] != null}<img height="60" width="50" src="{$product['image']|replace:'-medium_default':'-thickbox_default'}" />{/if}</td> *}
					<td style="border:1px solid black; width: 180px; text-align: left">{$product['name']|escape:'htmlall'}</td>
					<td style="border:1px solid black; width: 80px; text-align: left">{$product['reference']|escape:'htmlall'}</td>
					<td style="border:1px solid black; width: 80px; text-align: left">{$product['ean']|escape:'htmlall'}</td>
					<td style="border:1px solid black; width: 80px; text-align: center">{$product['quantity']|intval}</td>
					<td style="border:1px solid black; width: 80px; text-align: left"></td>
				</tr>
				
				{$current_manufacturer = $product['manufacturer_name']}
		{/foreach}
	</table>
{else}
	<strong style="text-align: center; font-size:150%;">{l s='No product in this warehouse' mod='erpillicopresta'}</strong>
{/if}