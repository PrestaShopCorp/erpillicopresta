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

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}

<h2>{$supplier->name|escape:'htmlall'}</h2>

<h3>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp{l s='Number of products:' mod='erpillicopresta'} {count($products)|intval}</h3>
<table border="0" cellpadding="0" cellspacing="0" class="table" width="100%">
	<tr>
		<th>{l s='Product name' mod='erpillicopresta'}</th>
		<th>{l s='Attribute name' mod='erpillicopresta'}</th>
		<th>{l s='Supplier Reference' mod='erpillicopresta'}</th>
		<th>{l s='Purchase price' mod='erpillicopresta'}</th>
		<th>{l s='Reference' mod='erpillicopresta'}</th>
		<th>{l s='EAN13' mod='erpillicopresta'}</th>
		<th>{l s='UPC' mod='erpillicopresta'}</th>
		{if $stock_management && $shopContext != Shop::CONTEXT_ALL}<th class="right">{l s='Available Quantity' mod='erpillicopresta'}</th>{/if}
	</tr>
{foreach $products AS $product}
	{if !$product->hasAttributes()}
		<tr>
			<td><a target='_blank' href="?tab=AdminProducts&id_product={$product->id}&updateproduct&token={getAdminToken tab='AdminProducts'}">{$product->name}</a></td>
			<td>{l s='N/A' mod='erpillicopresta'}</td>
			<td>{if empty($product->product_supplier_reference)}{l s='N/A' mod='erpillicopresta'}{else}{$product->product_supplier_reference}{/if}</td>
			<td>{if empty($product->product_supplier_price_te)}0{else}{$product->product_supplier_price_te}{/if}</td>
			<td>{if empty($product->reference)}{l s='N/A' mod='erpillicopresta'}{else}{$product->reference}{/if}</td>
			<td>{if empty($product->ean13)}{l s='N/A' mod='erpillicopresta'}{else}{$product->ean13}{/if}</td>
			<td>{if empty($product->upc)}{l s='N/A' mod='erpillicopresta'}{else}{$product->upc}{/if}</td>
			{if $stock_management && $shopContext != Shop::CONTEXT_ALL}<td class="right" width="150">{$product->quantity}</td>{/if}
		</tr>
	{else}
		{foreach $product->combination AS $id_product_attribute => $product_attribute}
			<tr {if $id_product_attribute %2}class="alt_row"{/if} >
				<td><a target='_blank'  href="?tab=AdminProducts&id_product={$product->id}&updateproduct&token={getAdminToken tab='AdminProducts'}">{$product->name}</a></td>
				<td>{if empty($product_attribute.attributes)}{l s='N/A' mod='erpillicopresta'}{else}{$product_attribute.attributes}{/if}</td>
				<td>{if empty($product_attribute.product_supplier_reference)}{l s='N/A' mod='erpillicopresta'}{else}{$product_attribute.product_supplier_reference}{/if}</td>
				<td>{if empty($product_attribute.product_supplier_price_te)}0{else}{$product_attribute.product_supplier_price_te}{/if}</td>
				<td>{if empty($product_attribute.reference)}{l s='N/A' mod='erpillicopresta'}{else}{$product_attribute.reference}{/if}</td>
				<td>{if empty($product_attribute.ean13)}{l s='N/A' mod='erpillicopresta'}{else}{$product_attribute.ean13}{/if}</td>
				<td>{if empty($product_attribute.upc)}{l s='N/A' mod='erpillicopresta'}{else}{$product_attribute.upc}{/if}</td>
				{if $stock_management && $shopContext != Shop::CONTEXT_ALL}<td class="right">{$product_attribute.quantity}</td>{/if}
			</tr>
		{/foreach}
	{/if}
{/foreach}
</table>
{/block}