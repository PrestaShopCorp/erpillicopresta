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

<table>
	<tr><td style="line-height: 8px">&nbsp;</td></tr>
	<tr>
		 <td style="font-size: 9pt; text-align:left">{l s='Customer order :' mod='erpillicopresta'} {$customer_name|escape:'htmlall'}</td>
	</tr>
</table>


<div style="font-size: 9pt; color: #444;margin-top:20px;">

	<!-- SUPPLIER ADDRESS -->
	<div style="border:1px black solid;">
		<table style="width: 100%;">
			<tr>
				<td>
					<br/><br/>
					<u>{l s='Supplier' mod='erpillicopresta'}</u>
				</td>
				<td>
									<br/><br/>
									<table>
											<tr>
												<td style="font-size: 9pt; ">{$supply_order->supplier_name|escape:'htmlall'}</td>
											</tr>
											<tr>
													<td style="font-size: 9pt; ">{$address_supplier->address1|escape:'htmlall'}</td>
											</tr>
											{* if the address has two parts *}
											{if !empty($address_supplier->address2)}
											<tr>
													<td style="font-size: 9pt; ">{$address_supplier->address2|escape:'htmlall'}</td>
											</tr>
											{/if}
											<tr>
													<td style="font-size: 9pt; ">{$address_supplier->postcode|escape:'htmlall'} {$address_supplier->city|escape:'htmlall'}</td>
											</tr>
											<tr>
													<td style="font-size: 9pt; ">{$address_supplier->country|escape:'htmlall'}</td>
											</tr>
											<tr>
													<td style="font-size: 9pt; ">{l s='Fax : ' mod='erpillicopresta'} {$fax|escape:'htmlall'}</td>
											</tr>
											<tr>
													<td style="font-size: 9pt; ">{l s='Phone :' mod='erpillicopresta'} {$address_supplier->phone|escape:'htmlall'}</td>
											</tr>
									</table>
				</td>
			</tr>
		</table>
	</div>
	<!-- / SUPPLIER ADDRESS -->

	<!-- PRODUCTS -->
	<div style="font-size: 9pt;">
		<table style="width: 100%;" border="1">
			<tr style="line-height:6px; border: none">
				<td style="width: 12%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Reference' mod='erpillicopresta'}</td>
				{if $action == 'generateSupplyReceivingSlipFormPDF'}
					<td style="width: 46%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Name' mod='erpillicopresta'}</td>
				{else}
					<td style="width: 50%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Name' mod='erpillicopresta'}</td>
				{/if}
				<td style="width: 5%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Qty Ordered' mod='erpillicopresta'}</td>
								{if $action == 'generateSupplyReceivingSlipFormPDF'}
									<td style="width: 5%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Qty Received' mod='erpillicopresta'}</td>
								{/if}
				<td style="width: 13%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Unit Price TE' mod='erpillicopresta'}</td>
				<td style="width: 11%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='TOTAL TI' mod='erpillicopresta'} </td>
				<td style="width: 9%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Tax' mod='erpillicopresta'}</td>
				
			</tr>

						{*Initialisation compteurs totaux réception*}
						{if $action == 'generateSupplyReceivingSlipFormPDF'}
							{assign var=total_te_discount_excluded value=0}
							{assign var=total_order_discount value=0}
							{assign var=total_to_pay value=0}
							{assign var=total_price_tax_receiving value=0}
						{/if}
						
						{assign var=total_price_unit_price_te value=0}
						{assign var=total_total_price_tax_incl value=0}

						{* for each product ordered *}
			{foreach $supply_order_details as $supply_order_detail}
							
							{$total_price_unit_price_te = $total_price_unit_price_te + ( $supply_order_detail->unit_price_tax_excl * $supply_order_detail->product_quantity) }
							{$total_total_price_tax_incl = $total_total_price_tax_incl + $supply_order_detail->total_price_tax_incl  }

			<tr>
				<td style="text-align: left; padding-left: 1px;">{$supply_order_detail->product_supplier_reference|escape:'htmlall'}</td>
				<td style="text-align: left; padding-left: 1px;">{$supply_order_detail->product_name|escape:'htmlall'}</td>
				<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->product_quantity|intval}</td>
								
								{if $action == 'generateSupplyReceivingSlipFormPDF'}
									<td style="text-align: right; padding-right: 1px;">{* $supply_order_detail->quantity_received *}</td>
								{/if}
								
							   <td style="text-align: right; padding-right: 1px;">
										{displayPrice price=$supply_order_detail->unit_price_tax_excl currency=$currency->id|intval}
								</td>
												
								<td style="text-align: right; padding-right: 1px;">
											{displayPrice price=$supply_order_detail->total_price_tax_incl currency=$currency->id|intval}
								</td>
				
				<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->tax_rate|escape:'htmlall'} %</td>
			</tr>
			{/foreach}
						<tr>
							<td colspan="{if $action == 'generateSupplyReceivingSlipFormPDF'}4{else}3{/if}" style="text-align:center;padding-top:5px;"> {l s='TOTAL' mod='erpillicopresta'} </td>
							<td style="text-align: right; padding-right: 1px;"> {displayPrice price=$total_price_unit_price_te currency=$currency->id|intval} </td>
							<td style="text-align: right; padding-right: 1px;"> {displayPrice price=$total_total_price_tax_incl currency=$currency->id|intval} </td>
							<td style="text-align: right; padding-right: 1px;"> {displayPrice price=$supply_order->total_tax currency=$currency->id|intval} </td>
						</tr>
		</table>
	</div>
	<!-- / PRODUCTS -->
			

</div>