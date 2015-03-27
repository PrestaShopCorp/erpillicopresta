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

<div style="font-size: 9pt; color: #444">

	<!-- SHOP ADDRESS -->
	<table style="width: 100%">
	<tr><td style="width:50%">
	<!-- <div style="float:right;width: 20%;"> -->
		<table style="width: 100%;">
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$shop_name|escape:'htmlall'}</td>
			</tr>
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_warehouse->address1|escape:'htmlall'}</td>
			</tr>
			{* if the address has two parts *}
			{if !empty($address_warehouse->address2)}
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_warehouse->address2|escape:'htmlall'}</td>
			</tr>
			{/if}
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_warehouse->postcode|escape:'htmlall'} {$address_warehouse->city|escape:'htmlall'}</td>
			</tr>
		</table>
		</td>

	<!--</div>
	<!-- / SHOP ADDRESS -->

	<td style="width:50%; text-align: right">
	<!-- SUPPLIER ADDRESS -->
	<!--<div style="text-align: right;float:left;width:20%">-->
		<table style="width: 70%">
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$supply_order->supplier_name|escape:'htmlall'}</td>
			</tr>
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_supplier->address1|escape:'htmlall'}</td>
			</tr>
			{* if the address has two parts *}
			{if !empty($address_supplier->address2)}
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_supplier->address2|escape:'htmlall'}</td>
			</tr>
			{/if}
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_supplier->postcode|escape:'htmlall'} {$address_supplier->city|escape:'htmlall'}</td>
			</tr>
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{$address_supplier->country|escape:'htmlall'}</td>
			</tr>
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{l s='Fax :' mod='erpillicopresta'} {$fax|escape:'htmlall'}</td>
			</tr>
			<tr>
				<td style="font-size: 13pt; font-weight: bold">{l s='Phone : ' mod='erpillicopresta'} {$address_supplier->phone|escape:'htmlall'}</td>
			</tr>
		</table>
	<!--</div>-->
	</td></tr></table>
	<!-- / SUPPLIER ADDRESS -->

	<table>
		<tr><td style="line-height: 8px">&nbsp;</td></tr>
	</table>

	<span style="font-weight: bold; font-size: 120%;">{l s='Products ordered:' mod='erpillicopresta'}</span>
	<!-- PRODUCTS -->
	<div style="font-size: 10pt;">
		<table style="width: 100%;" border="1">
			<tr style="line-height:6px; border: none">
				<td style="width: 12%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Supplier reference' mod='erpillicopresta'}</td>
				{if $action == 'generateSupplyReceivingSlipFormPDF'}
					<td style="width: 41%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Name' mod='erpillicopresta'}</td>
				{else}
					<td style="width: 45%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Name' mod='erpillicopresta'}</td>
				{/if}
				<td style="width: 5%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Qty' mod='erpillicopresta'} <br /> {l s='O.' mod='erpillicopresta'}</td>
								{if $action == 'generateSupplyReceivingSlipFormPDF'}
									<td style="width: 5%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Qty' mod='erpillicopresta'} <br /> {l s='R.' mod='erpillicopresta'}</td>
								{/if}
				<td style="width: 10%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Unit Price TE' mod='erpillicopresta'}</td>
				
				<td style="width: 9%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Discount Rate' mod='erpillicopresta'}</td>
				<td style="width: 11%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TE' mod='erpillicopresta'} <br /> {l s='After discount' mod='erpillicopresta'}</td>
				<td style="width: 9%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Tax rate' mod='erpillicopresta'}</td>
				
			</tr>

						{*Initialisation compteurs totaux réception*}
						{if $action == 'generateSupplyReceivingSlipFormPDF'}
							{assign var=total_te_discount_excluded value=0}
							{assign var=total_order_discount value=0}
							{assign var=total_to_pay value=0}
							{assign var=total_price_tax_receiving value=0}
						{/if}
						
							{* for each product ordered *}
							{$total_price_ht_nr = 0}
							{$total_price_with_discount = 0}
							{foreach $supply_order_details as $supply_order_detail}
							
								{*Valeurs calculées pour la réception*}
								{if $action == 'generateSupplyReceivingSlipFormPDF'}
									{assign var=total_te_before_discount value=$supply_order_detail->unit_price_te * $supply_order_detail->quantity_received}
									{assign var=total_te_after_discount value=$total_te_before_discount - (($total_te_before_discount * $supply_order_detail->discount_rate) / 100)}
									{assign var=total_ti value=$total_te_after_discount + (($total_te_after_discount * $supply_order_detail->tax_rate) / 100)}
								{/if}
								
								{if $action == 'generateSupplyReceivingSlipFormPDF' && {$supply_order_receipt_history[$supply_order_detail->id]|@count} > 0}
								
										{$total_te_discount_excluded = $total_te_discount_excluded + $total_te_after_discount}
										{$total_price_tax_receiving = $total_price_tax_receiving + (($total_te_after_discount * $supply_order_detail->tax_rate) / 100) }
										{$toto = 0}
										{foreach $supply_order_receipt_history[$supply_order_detail->id] as $supply_order_receipt_history_detail}
											{$unit_price = $supply_order_receipt_history_detail['unit_price']}
											{$qty = $supply_order_receipt_history_detail['quantity']}
											{$discount = $supply_order_receipt_history_detail['discount_rate']}
											{$total_price_ht_nr = $total_price_ht_nr + ($unit_price * $qty)}
												<tr>
													<td style="text-align: left; padding-left: 1px;">{$supply_order_detail->supplier_reference|escape:'htmlall'}</td>
													<td style="text-align: left; padding-left: 1px;">{$supply_order_detail->name|escape:'htmlall'}</td>
													<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->quantity_expected|intval}</td>
													
													<td style="text-align: right; padding-right: 1px;">{$qty|intval}</td>
													<td style="text-align: right; padding-right: 1px;">{$currency->prefix|escape:'htmlall'} {round($unit_price, 2)|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
													<td style="text-align: right; padding-right: 1px;">{round($discount, 2)|escape:'htmlall'} %</td>
													<td style="text-align: right; padding-right: 1px;">{$currency->prefix} {round($unit_price * $qty - ($unit_price * $qty * $discount / 100), 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
													<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->tax_rate|escape:'htmlall'} %</td>
																	
												</tr>
												{$toto = $toto + $unit_price * $qty - ($unit_price * $qty * $discount / 100)}
												{$total_price_with_discount = $total_price_with_discount + ($unit_price * $qty - ($unit_price * $qty * $discount / 100))}
										{/foreach}
										{$total_te_discount_excluded = $total_te_discount_excluded + $total_te_after_discount}
										{$total_price_tax_receiving = $total_price_tax_receiving + (($toto * $supply_order_detail->tax_rate) / 100) }
								{else}			
									<tr>
										<td style="text-align: left; padding-left: 1px;">{$supply_order_detail->supplier_reference|escape:'htmlall'}</td>
										<td style="text-align: left; padding-left: 1px;">{$supply_order_detail->name|escape:'htmlall'}</td>
										<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->quantity_expected|intval}</td>
										
										{if $action == 'generateSupplyReceivingSlipFormPDF'}
											<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->quantity_received|intval}</td>
										{/if}
										
										{if $action == 'generateSupplyReceivingSlipFormPDF'}
											<td style="text-align: right; padding-right: 1px;">{$currency->prefix|escape:'htmlall'} {$supply_order_detail->unit_price_te|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
										{else}
											{* Nous permet de récupérer le prix avant sa modification lors de la récéption *}
											<td style="text-align: right; padding-right: 1px;">
												{$currency->prefix|escape:'htmlall'} 
														{$supply_order_detail->unit_price_te|escape:'htmlall'} 
												{$currency->suffix|escape:'htmlall'}
											</td>
										{/if}
						
										<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->discount_rate|escape:'htmlall'} %</td>
										
										{if $action == 'generateSupplyReceivingSlipFormPDF'}
											<td style="text-align: right; padding-right: 1px;">
												{$currency->prefix|escape:'htmlall'} {round($total_te_after_discount, 2)|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
										{else}
											<td style="text-align: right; padding-right: 1px;">{$currency->prefix|escape:'htmlall'} {$supply_order_detail->price_with_discount_te|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
										{/if}
						
										<td style="text-align: right; padding-right: 1px;">{$supply_order_detail->tax_rate|escape:'htmlall'} %</td>
									  {$total_price_with_discount = $total_price_with_discount + ($supply_order_detail->unit_price_te * $supply_order_detail->quantity_received - ($supply_order_detail->unit_price_te * $supply_order_detail->quantity_received * $supply_order_detail->discount_rate / 100))}
									</tr>
								{/if}
							{/foreach}
		</table>
	</div>
	<!-- / PRODUCTS -->

	<table>
		<tr><td style="line-height: 8px">&nbsp;</td></tr>
	</table>

	<span style="font-weight: bold; font-size: 120%;">{l s='Taxes:' pdf='true' mod='erpillicopresta'}</span>
	<!-- PRODUCTS TAXES -->
	<div style="font-size: 9pt;">
		<table style="width: 30%;" border="1">
				<tr style="line-height:6px; border: none">
					<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Base TE' pdf='true' mod='erpillicopresta'}</td>
					<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Tax Rate' pdf='true' mod='erpillicopresta'}</td>
					<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Tax Value' pdf='true' mod='erpillicopresta'}</td>
				</tr>
				
				{assign var=total_base_te value=0}
				{$total_tax_receipt = 0}
				{$total_base_te_all = 0}
				{foreach $tax_order_summary as $entry}
				<tr style="line-height:6px; border: none">
						
					  {* Calcul de base HT en ne prenant en compte que la quantité réçue*}
					  {$total_base_te = 0}
					  {if $action == 'generateSupplyReceivingSlipFormPDF'}
					  
							{foreach $supply_order_details as $supply_order_detail}
									
									{if $supply_order_receipt_history[$supply_order_detail->id]|@count == 0}
										{if $supply_order_detail->tax_rate == $entry['tax_rate'] }
												{assign var=total_te_before_discount2 value=$supply_order_detail->unit_price_te * $supply_order_detail->quantity_received}
												{assign var=total_te_after_discount2 value=$total_te_before_discount2 - (($total_te_before_discount2 * $supply_order_detail->discount_rate) / 100)}
												{$total_base_te_all = $total_base_te_all + $total_te_after_discount2}
												{$total_base_te = $total_base_te + $total_te_after_discount2}

										{/if}
									{else}									
									
										{foreach $supply_order_receipt_history[$supply_order_detail->id] as $supply_order_receipt_history_detail}
											{$unit_price = $supply_order_receipt_history_detail['unit_price']}
											{$qty = $supply_order_receipt_history_detail['quantity']}
											{$discount = $supply_order_receipt_history_detail['discount_rate']}
								
											{if $supply_order_detail->tax_rate == $entry['tax_rate'] }
													{assign var=total_te_before_discount2 value=$unit_price * $qty}
													{assign var=total_te_after_discount2 value=$total_te_before_discount2 - (($total_te_before_discount2 * $discount) / 100)}
													{$total_base_te_all = $total_base_te_all + $total_te_after_discount2}
													{$total_base_te = $total_base_te + $total_te_after_discount2}

											{/if}
										{/foreach}
									{/if}
									
							{/foreach}
							
							<td style="text-align: right; padding-right: 1px;">{$currency->prefix|escape:'htmlall'} {round($total_base_te, 2, 1 )|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
							<td style="text-align: right; padding-right: 1px;">{$entry['tax_rate']|escape:'htmlall'} %</td>
							<td style="text-align: right; padding-right: 1px;">
									{$currency->prefix|escape:'htmlall'} 
										{round( $total_base_te * $entry['tax_rate'] / 100  , 2, 1)|escape:'htmlall'}
									{$currency->suffix|escape:'htmlall'}
							</td>
							
							{$total_tax_receipt = $total_tax_receipt + ($total_base_te * $entry['tax_rate'] / 100)}
					
					  {else}
					  
						<td style="text-align: right; padding-right: 1px;">{$currency->prefix|escape:'htmlall'} {$entry['base_te']|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
						<td style="text-align: right; padding-right: 1px;">{$entry['tax_rate']|escape:'htmlall'} %</td>
						<td style="text-align: right; padding-right: 1px;">{$currency->prefix|escape:'htmlall'} {$entry['total_tax_value']|escape:'htmlall'} {$currency->suffix|escape:'htmlall'}</td>
					
					  {/if}
					  
					
				</tr>
				{/foreach}
		</table>
	</div>
	<!-- / PRODUCTS TAXES -->

	<table>
		<tr><td style="line-height: 8px">&nbsp;</td></tr>
	</table>

		<!-- TOTAL COMMANDE -->

	<table>
		<tr>
			<td><span style="font-weight: bold; font-size: 120%;">{l s='Order Summary : ' mod='erpillicopresta'}</span></td>
			<td>{if $action == 'generateSupplyReceivingSlipFormPDF'}<span style="font-weight: bold; font-size: 120%;">{l s='Reception summary : ' mod='erpillicopresta'} </span>{/if}</td>
		</tr>
		<tr>
			<td>

			
				{* ERP information*}
				{if isset($erp_supply_order) && $erp_supply_order != NULL}
					{$shipping_amount = $erp_supply_order->shipping_amount|string_format:"%d"}
					{$escompte = $erp_supply_order->escompte|string_format:"%d"}
				{else}
					{$shipping_amount = 0|string_format:"%d"}
					{$escompte  = 0|string_format:"%d"}
				{/if}
				
				{* Valeurs calculées*}
				{assign var=total_shipping value=$supply_order->total_with_discount_te + $shipping_amount}
				{assign var=escompte_amount value=($total_shipping*$escompte)/100}
				{assign var=total_escompte value=$total_shipping -$escompte_amount}
				{assign var=total_to_pay value=$total_escompte + $supply_order->total_tax}
				
				<table style="width: 100%;" border="1">
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TE' pdf='true' mod='erpillicopresta'} {l s='(DE)' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {$supply_order->total_te|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Order Discount' pdf='true' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {$supply_order->discount_value_te|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TE' pdf='true' mod='erpillicopresta'} {l s='(DI)' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {$supply_order->total_with_discount_te|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
						<!-- FDP -->
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Shipping amount' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {$shipping_amount|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
										<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total with Shipping amount' pdf='true' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {$total_shipping|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
										
										
										
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Tax value' pdf='true' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {$supply_order->total_tax|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TI' pdf='true' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_to_pay, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
						<tr style="line-height:6px; border: none">
							<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='TOTAL TO PAY' pdf='true' mod='erpillicopresta'}</td>
							<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_to_pay, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
						</tr>
				</table>

			</td>
	<!-- / TOTAL -->
		
		<td> 
		<!-- TOTAL RECEPTION -->
		{if $action == 'generateSupplyReceivingSlipFormPDF'}
		   
			
			{$total_te_discount_excluded = $total_price_ht_nr}
			{*Totaux prenant compte des sommes de réduction ou de taxe*}
			{assign var=total_te_discount_included value=$total_te_discount_excluded - $supply_order->discount_value_te}
			
			{*totaux shipping & escompte*}
			{assign var=total_shipping value=$total_te_discount_included + $shipping_amount}
			{assign var=escompte_amount value=($total_shipping*$escompte)/100}
			{assign var=total_escompte value=$total_shipping - $escompte_amount}
			
			{assign var=total_to_pay value=$total_escompte + $total_price_tax_receiving}
			
			
			
			
					<table style="width: 100%;" border="1">
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TE' pdf='true' mod='erpillicopresta'} {l s='(DE)' mod='erpillicopresta'}</td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_price_with_discount, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Order Discount' pdf='true' mod='erpillicopresta'}</td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($supply_order->discount_value_te, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TE' pdf='true' mod='erpillicopresta'} {l s='(DI)' mod='erpillicopresta'}</td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_price_with_discount + $supply_order->discount_value_te, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									
									<!-- FDP -->
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Shipping amount' mod='erpillicopresta'} </td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {$shipping_amount|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total width shipping amount' mod='erpillicopresta'} </td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_price_with_discount + $supply_order->discount_value_te + $shipping_amount, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									
								   

									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Tax value' pdf='true' mod='erpillicopresta'}</td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_tax_receipt, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Total TI' pdf='true' mod='erpillicopresta'}</td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_price_with_discount + $supply_order->discount_value_te + $shipping_amount + $total_tax_receipt, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
									<tr style="line-height:6px; border: none">
											<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='TOTAL TO PAY' pdf='true' mod='erpillicopresta'}</td>
											<td width="100px" style="text-align: right;">{$currency->prefix} {round($total_price_with_discount + $supply_order->discount_value_te + $shipping_amount + $total_tax_receipt, 2, 1)|number_format:2:".":""} {$currency->suffix}</td>
									</tr>
					</table>
		{/if}
		<!-- / TOTAL RECEPTION -->
		
		</td>
			</tr>
			</table>
</div>