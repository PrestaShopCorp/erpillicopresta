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

<p>&nbsp;</p>

	<input type="hidden" id="product_ids" name="product_ids" value="{$product_ids|escape:'htmlall'}" />
	<input type="hidden" id="product_ids_to_delete" name="product_ids_to_delete" value="{$product_ids_to_delete|escape:'htmlall'}" />
	<input type="hidden" name="updatesupply_order" value="1" />

	<div class="panel block-multiple-selection">
		<h3>
                    <i class="icon-cogs"></i>
                    {l s='Manage the products you want to order from the supplier.' mod='erpillicopresta'}
		</h3>

                <div class="alert alert-info" style="background-color: transparent;">
                    {l s='To add a product to the order, type the first letters of the product name, then select it from the drop-down list.' mod='erpillicopresta'}
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        {* multiple selection filter *}
                        <div class="row">

                            <div class="panel">
                                <h3>
                                     <i class="icon-plus-sign"></i> {l s='Add multiple product' mod='erpillicopresta'}
                                </h3>
                                
                                <div class="col-lg-3">  
                                    <label>{l s='Filter by categorie:' mod='erpillicopresta'}</label>
                                    <select name="id_categorie" id="id_categorie">
                                                {foreach from=$categories key=k item=i}
                                                                <option value="{$i.id_category|intval}">{$i.name|escape:'htmlall'}</option>
                                                {/foreach}
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label>{l s='Filter by manufacturer:' mod='erpillicopresta'}</label>
                                    <select name="id_manufacturer" id="id_manufacturer">
                                        {foreach from=$manufacturers key=k item=i}
                                                        <option value="{$i.id_manufacturer|intval}">{$i.name|escape:'htmlall'}</option>
                                        {/foreach}
                                    </select>
                                </div>

                                <div class="col-lg-3">
                                    <br/>

                                    <button type="button" class="btn btn-default multiple_selection">
                                    <i class="icon-plus-sign"></i> {l s='Add multiple product to the supply order' mod='erpillicopresta'}</button>

                                    <img class="multiple_selection_loder" alt="{l s='Supply Order Management' mod='erpillicopresta'}" src="../img/loader.gif"/>

                                </div> 
                                
                                <div class="clearfix"></div>
                                
                            </div>
                           
                        </div>
                        
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="panel">
                                <h3>
                                     <i class="icon-plus-sign"></i> {l s='Add single product' mod='erpillicopresta'}
                                </h3>
                                <div class="col-lg-6">
                                        <input type="text" id="cur_product_name" autocomplete="off" class="ac_input">
                                </div>
                                <div class="col-lg-3">
                                        <button type="button" class="btn btn-default" onclick="addProduct();">
                                        <i class="icon-plus-sign"></i> {l s='Add a product to the supply order' mod='erpillicopresta'}</button>
                                </div>
                                
                                <br/> <br/> <br/>
                                
                                <div class="clearfix"></div>
                                
                            </div>  
                        </div>
                    </div>
                    
                </div>
            		  
                <br/> 
                
		<p>&nbsp;</p>

                <table id="products_in_supply_order" class="table" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom:10px;">
                        <thead>
                            <tr class="nodrag nodrop">
                                <th style="width: 50px">{l s='Ref.' mod='erpillicopresta'}</th>
                                <th style="width: 50px">{l s='Bar code' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Name' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Unit Price (tax excl.)' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Quantity' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Real quantity' mod='erpillicopresta'}<br/>{l s='stock' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Discount rate' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Discount amount' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Tax rate' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Total' mod='erpillicopresta'}</th>
                                <th style="width: 100px">{l s='Comment' mod='erpillicopresta'}</th>
                                <th style="width: 40px">{l s='Delete' mod='erpillicopresta'}</th>
                            </tr>
                        </thead>
                        <tbody>
                                {foreach $products_list AS $product}
                                        <tr style="height:50px;">
                                                <td>
                                                        {$product.reference|escape:'htmlall'}
                                                        <input type="hidden" name="input_check_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$product.checksum|escape:'htmlall'}" />
                                                        <input type="hidden" name="input_reference_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$product.reference|escape:'htmlall'}" />
                                                        <input type="hidden" name="input_id_{$product.id_product}_{$product.id_product_attribute}" value="{if isset($product.id_supply_order_detail)}{$product.id_supply_order_detail}{/if}" />

                                                        {* save the id_erpip_supply_order_detail *}
                                                        <input type="hidden" name="id_erpip_supply_order_detail_{$product.id_product}_{$product.id_product_attribute}" value="{if isset($product.id_erpip_supply_order_detail)}{$product.id_erpip_supply_order_detail}{/if}" />
                                                        
                                                        {$product.supplier_reference|escape:'htmlall'}
                                                        <input type="hidden" name="input_supplier_reference_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$product.supplier_reference|escape:'htmlall'}" />
                                                </td>
                                               
                                                <td>
                                                        {$product.ean13|escape:'htmlall'}
                                                        <input type="hidden" name="input_ean13_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$product.ean13|escape:'htmlall'}" />
                                                        
                                                        {$product.upc|escape:'htmlall'}
                                                        <input type="hidden" name="input_upc_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$product.upc|escape:'htmlall'}" />
                                                </td>
                                                
                                                <td>
                                                        {$product.name|escape:'htmlall'}
                                                        <input type="hidden" name="input_name_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$product.name|escape:'htmlall'}" />
                                                </td>
                                                <td class="text-center">
                                                        {$currency->prefix}&nbsp;<input type="text" class="unit_price" name="input_unit_price_te_{$product.id_product}_{$product.id_product_attribute}" value="{$product.unit_price_te|htmlentities}" size="8" />&nbsp;{$currency->suffix}
                                                </td>
                                                <td class="text-center">
                                                        <input type="text" class="quantity_expected" name="input_quantity_expected_{$product.id_product}_{$product.id_product_attribute}" value="{$product.quantity_expected|htmlentities}" size="3" />
                                                </td>
                                                <td class="text-center">
                                                    <span>{$product.stock|intval}</span>
                                                </td>
                                                <td class="text-center">
                                                        <input type="text" class="discount_rate_product" name="input_discount_rate_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{round($product.discount_rate, 4)|escape:'htmlall'}" size="3" />%
                                                </td>
                                                <td class="text-center">
                                                        {assign var=discount_amount value=$product.unit_price_te|htmlentities * ($product.discount_rate / 100)}
                                                        <input type="text" class="discount_amount_product" name="input_discount_amount_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{$discount_amount|escape:'htmlall'}" size="3" /> €
                                                </td>
                                                <td class="text-center">
                                                        <input type="text" class="tax_rate" name="input_tax_rate_{$product.id_product|intval}_{$product.id_product_attribute|intval}" value="{round($product.tax_rate|escape:'htmlall', 4)}" size="3" />%
                                                </td>
                                                <td class="text-center">
                                                        {assign var=tax_value value=($product.unit_price_te|htmlentities * (round($product.tax_rate, 4) / 100))}
                                                        {assign var=total value=(($product.unit_price_te|htmlentities * $product.quantity_expected|htmlentities) - $discount_amount) + $tax_value}
                                                        <span class="total_product">{$total|escape:'htmlall'}{$currency->prefix|escape:'htmlall'}</span>
                                                </td>
                                                <td class="text-center">
                                                        <input type="text" name="input_comment_{$product.id_product}_{$product.id_product_attribute}" value="{if $product.comment != 'undefined'}{$product.comment}{/if}"/>
                                                </td>
                                                <td class="text-center">
                                                        <a href="#" id="deletelink|{$product.id_product|intval}_{$product.id_product_attribute|intval}" class="removeProductFromSupplyOrderLink">
                                                                <img src="../img/admin/delete.gif" alt="{l s='Remove this product from the order.' mod='erpillicopresta'}" title="{l s='Remove this product from the order.' mod='erpillicopresta'}" />
                                                        </a>
                                                </td>
                                        </tr>
                                {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>  
                                <td colspan="14">
                                    <span class="legende_produit_four"></span> 
                                    {l s='Products ordered to the main supplier' mod='erpillicopresta'}
                                </td> 
                            </tr>
                        </tfoot>
                </table>
                
                <button type="submit" value="1" id="supply_order_form_submit_btn" name="submitAddsupply_order" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i>  {l s='Save order' mod='erpillicopresta'}
                </button>
                
                 <div class="clearfix"></div>
	</div>

	<script type="text/javascript">
		product_infos = null;
		debug = null;
		if ($('#product_ids').val() == '')
			product_ids = [];
		else
			product_ids = $('#product_ids').val().split('|');

		if ($('#product_ids_to_delete').val() == '')
			product_ids_to_delete = [];
		else
			product_ids_to_delete = $('#product_ids_to_delete').val().split('|');


		function addProduct()
		{
			// check if it's possible to add the product
			if (product_infos == null || $('#cur_product_name').val() == '')
			{
				jAlert('{l s='Please select at least one product.' js=1 mod='erpillicopresta'}');
				return false;
			}

			if (!product_infos.unit_price_te)
                            product_infos.unit_price_te = 0;
						
                        if (!product_infos.quantity_expected)
                            product_infos.quantity_expected = 0;
				
                        product_infos.quantity_expected = parseInt(product_infos.quantity_expected);
                        
			if (!product_infos.discount_rate)
				product_infos.discount_rate = 0;
						
			if (!product_infos.tax_rate)
				product_infos.tax_rate = 0;
						
                        //if is the mail supplier we add a css class to tr tag
                        var main_supplier_class = '';
                        if( product_infos.id_default_supplier == '{$supplier_id|intval}' )
                                   main_supplier_class =  'class="four_principal"';

                        var discount_amount = parseFloat((product_infos.unit_price_te * parseInt(product_infos.quantity_expected)) * (product_infos.discount_rate / 100));

                        if( isNaN(discount_amount))
                                discount_amount = 0;

                        var total_product = (product_infos.quantity_expected == 0) ? 0 : (product_infos.unit_price_te * product_infos.quantity_expected) + (product_infos.tax_rate / 100);

                        total_product = total_product.toFixed(2);

                        if (!product_infos.comment)
                                product_infos.comment = '';
							
			// add a new line in the products table
			$('#products_in_supply_order > tbody:last').append(
				'<tr style="height:50px;" '+main_supplier_class+'>'+
				'<td>'
                                    +product_infos.reference+'<input type="hidden" name="input_check_'+product_infos.id+'" value="'+product_infos.checksum+'" /><input type="hidden" name="input_reference_'+product_infos.id+'" value="'+product_infos.reference+'" />'+
                                    '<br/>'+product_infos.supplier_reference+'<input type="hidden" name="input_supplier_reference_'+product_infos.id+'" value="'+product_infos.supplier_reference+'" />'+
                                '</td>'+
				'<td>'
                                    +product_infos.ean13+'<input type="hidden" name="input_ean13_'+product_infos.id+'" value="'+product_infos.ean13+'" />'+
                                    '<br/>'+product_infos.upc+'<input type="hidden" name="input_upc_'+product_infos.id+'" value="'+product_infos.upc+'" />'+
                                '</td>'+    
                                '<td>'+product_infos.name+'<input type="hidden" name="input_name_displayed_'+product_infos.id+'" value="'+product_infos.name+'" /></td>'+
				'<td class="text-center">'+
                                    '{$currency->prefix|escape:'htmlall'}&nbsp;'+ 
                                    '<input type="text" name="input_unit_price_te_'+product_infos.id+'" value="'+product_infos.unit_price_te+'" class="unit_price" size="8" />'+
                                    '&nbsp;{$currency->suffix|escape:'htmlall'}'+ 
                                '</td>'+
				'<td class="text-center">'+
                                    '<input type="text" name="input_quantity_expected_'+product_infos.id+'" value="'+product_infos.quantity_expected+'" size="3" class="quantity_expected"/>'+
                                '</td>'+
				'<td class="text-center">'+product_infos.stock+'</td>'+
				'<td class="text-center"><input type="text" name="input_discount_rate_'+product_infos.id+'" value="'+product_infos.discount_rate+'" class="discount_rate_product" size="3" />%</td>'+
				'<td class="text-center"><input type="text" class="discount_amount_product" name="input_discount_amount_'+product_infos.id+'" value="'+discount_amount+'" size="3" /></td>'+
				'<td class="text-center"><input type="text" name="input_tax_rate_'+product_infos.id+'" value="'+product_infos.tax_rate+'" class="tax_rate" size="3" />%</td>'+
				'<td class="text-center"><span class="total_product">{$currency->prefix|escape:'htmlall'}&nbsp;'+ total_product + '&nbsp;{$currency->suffix|escape:'htmlall'}</span></td>'+
                                '<td class="text-center"><input type="text" name="input_comment_'+product_infos.id+'" value="'+product_infos.comment+'" size="20"/></td>'+
                                '<td class="text-center"><a href="#" class="removeProductFromSupplyOrderLink" id="deletelink|'+product_infos.id+'">'+
				'<img src="../img/admin/delete.gif" alt="{l s='Remove this product from the order.' mod='erpillicopresta'}" title="{l s='Remove this product from the order.' mod='erpillicopresta'}" />'+
				'</a></td></tr>'
			);

			// add the current product id to the product_id array - used for not show another time the product in the list
			product_ids.push(product_infos.id);

			// update the product_ids hidden field
			$('#product_ids').val(product_ids.join('|'));

			// clear the cur_product_name field
			$('#cur_product_name').val("");

			// clear the product_infos var
			product_infos = null;
		}

		/* function autocomplete */
		$(function() {
			// add click event on just created delete item link
			$('a.removeProductFromSupplyOrderLink').live('click', function() {

				var id = $(this).attr('id');
				var product_id = id.split('|')[1];


				//find the position of the product id in product_id array
				var position = jQuery.inArray(product_id, product_ids);
				if (position != -1)
				{
					//remove the id from the array
					product_ids.splice(position, 1);

					var input_id = $('input[name~="input_id_'+product_id+'"]');
					if (input_id != 'undefined')
						if (input_id.length > 0)
							product_ids_to_delete.push(product_id);

					// update the product_ids hidden field
					$('#product_ids').val(product_ids.join('|'));
					$('#product_ids_to_delete').val(product_ids_to_delete.join('|'));

					//remove the table row
					$(this).parents('tr:eq(0)').remove();
				}

				return false;
			});

			btn_save = $('span[class~="process-icon-save"]').parent();

			btn_save.click(function() {
				$('#supply_order_form').submit();
			});

			// bind enter key event on search field
			$('#cur_product_name').bind('keypress', function(e) {
				var code = (e.keyCode ? e.keyCode : e.which);
				if(code == 13) { //Enter keycode
					e.stopPropagation();//Stop event propagation
					return false;
				}
			});

			// set autocomplete on search field
			$('#cur_product_name').autocomplete("ajax-tab.php", {
				delay: 100,
				minChars: 3,
				autoFill: true,
				max:100,
				matchContains: true,
				mustMatch:false,
				scroll:false,
				cacheLength:0,
								dataType: 'json',
								extraParams: {
									id_supplier: '{$supplier_id|intval}',
									id_currency: '{$currency->id|intval}',
									ajax : '1',
									controller : 'AdminAdvancedSupplyOrder',
									token : '{$token|escape:'htmlall'}',
									action : 'searchProduct'
				},
				parse: function(data) {
					if (data == null || data == 'undefined')
						return [];
					var res = $.map(data, function(row) {
						// filter the data to chaeck if the product is already added to the order
						if (jQuery.inArray(row.id, product_ids) == -1)
							return {
								data: row,
								result: row.supplier_reference + ' - ' + row.name,
								value: row.id
							}
					});
					return res;
				},
				formatItem: function(item) {
					return item.supplier_reference + ' - ' + item.name;
				}
			}).result(function(event, item){
				product_infos = item;
				if (typeof(ajax_running_timeout) !== 'undefined')
					clearTimeout(ajax_running_timeout);
			});
		});
	</script>