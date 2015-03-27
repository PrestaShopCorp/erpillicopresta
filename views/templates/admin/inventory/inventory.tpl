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

{if $empty == 0 && $subareaError == 0}
	{if $areas|@count > 0 && $subareas|@count > 0}
		{foreach $areas AS $index_a => $area}
			{if $index_a != 0}
				<h3 style="page-break-before:always">{l s='Area' mod='erpillicopresta'} : {$area['label']|escape:'htmlall'}</h3>
			{else}
				<h3>{l s='Area' mod='erpillicopresta'} : {$area['label']|escape:'htmlall'}</h3>
			{/if}
			<table id="inventory_grid" style="font-size: 22px;">
				{foreach $subareas AS $index_s => $subarea}
					
					<!-- Récupération du  nombre de produit pour la zone complète -->
					{capture name=nbProducts}
						{countProductInFullArea area=$area['label'] subarea=$subarea['label']}
					{/capture}
					
					<!-- On construit une ligne si on trouve des produits à y mettre-->
					{if $smarty.capture.nbProducts > 0}
						<tr style="border:1px solid black">
							<td style="border:1px solid black; width:14px;">{$subarea['label']|escape:'htmlall'}</td>
						{$i = 0}
					
						{foreach $products AS $index_p => $product}
							{if $product['zone'] == $area['label'] && $product['sous_zone'] == $subarea['label']}
		
								{if (($i % 7) == 0) && ($i != 0)}
									</tr>
										<tr style="border:1px solid black">
											<td style="border:1px solid black; width:14px;">{$subarea['label']|escape:'htmlall'}</td>
								{/if}
			
										<td style="border:1px solid black; width: 100px; text-align: left">
											<span><b>{l s='Location' mod='erpillicopresta'}</b> : {$product['location']|escape:'htmlall'}</span>
											<br />
											<span>{$product['name']|escape:'htmlall'}</span>
											<br /><br />
											<span style="font-weight: bold; text-transform: capitalize">{$product['reference']|escape:'htmlall'}</span>
											<br />
											{if $product['image'] != null}<img style="height:50px; width:40px;" src="{$product['image']}" />{/if}
											<br/>
											<span><b>{l s='Stock' mod='erpillicopresta'}</b> : {$product['quantity']|escape:'htmlall'}</span>
											<br />
											<span><b>{l s='Quantity found' mod='erpillicopresta'}</b> : </span>
											<br />
										</td>
									{$i = $i + 1}
							{/if}
						{/foreach}
						</tr>
					  {/if}
				{/foreach}
				
			</table>
			<br />
		{/foreach}
	{else}
		<strong style="text-align: center; font-size:150%;">{l s='No area and/or subarea defined for this warehouse' mod='erpillicopresta'}</strong>
	{/if}
{else}
	{if $empty != 0}
		<strong style="text-align: center; font-size:150%;">{l s='No product in this warehouse' mod='erpillicopresta'}</strong>
	{else}
		<strong style="text-align: center; font-size:150%;">{l s='At least one product is defined with an area and without subarea for this warehouse' mod='erpillicopresta'}</strong>
	{/if}
{/if}