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

{include file=$template_path|cat:'common/erp_sidebar.tpl' erp_feature=$erp_feature}

{* order description *}
<br/>
<fieldset>
        <legend>{l s='Sypply order description' mod='erpillicopresta'}</legend>
        <p> {$supply_order_description|escape:'htmlall'} </p>
</fieldset>

<br/>

<!-- Div cachée affichée sur le passage de la souris sur une colonne -->
<div id="dialog-wholesale" style="display:none;" title="{l s='Confirmation' mod='erpillicopresta'}">
    <p style='text-align:left'>{l s='Would you really want to update the purchase price ?' mod='erpillicopresta'}</p>
</div>


{* include template with html popup to multiple selection *}
{include file=$template_path|cat:'advanced_supply_order/product_multiple_selection.tpl'}


<fieldset>
        <legend>{l s='Total' mod='erpillicopresta'}</legend>
        <label>{l s='Total price of the products received : ' mod='erpillicopresta'}</label>
        <p style="font-weight: bold; float: right; padding-right: 12%;">
        <span id="total_price"></span> {$currency->sign|escape:'htmlall'}
        </p>
        <br/><br/>
</fieldset>

<br/>

<fieldset>
        <legend>
                <img alt="Supply Order Management" src="../img/admin/edit.gif">
                {l s='Manage the products you want to add to the order' mod='erpillicopresta'}
        </legend>

        <p class="clear">{l s='To add a product to the order, type the first letters of the product name, then select it from the drop-down list:' mod='erpillicopresta'}
        <br />{l s='Please be carefull, with the autocompletion, you can add a product already in your order!' mod='erpillicopresta'}</p>
        <input type="text" size="100" id="cur_product_name" />
        <span onclick="addProduct();" style="cursor: pointer;"><img src="../img/admin/add.gif" alt="{l s='Add a product to the supply order' mod='erpillicopresta'}" title="{l s='Add a product to the supply order' mod='erpillicopresta'}" /></span>

        <br/><br/>

        {* filtre pour selection multiple *}
        <table class="table">
            <tr>
                <td>{l s='Filter by categorie:' mod='erpillicopresta'}</td>
                <td>
                     <select name="id_categorie" id="id_categorie">
                        {foreach from=$categories key=k item=i}
                                <option value="{$i.id_category|intval}">{$i.name|escape:'htmlall'}</option>
                        {/foreach}
                     </select>
                </td>
                <td>{l s='Filter by manufacturer:' mod='erpillicopresta'}</td>
                <td>
                    <select name="id_manufacturer" id="id_manufacturer">
                    {foreach from=$manufacturers key=k item=i}
                            <option value="{$i.id_manufacturer|intval}">{$i.name|escape:'htmlall'}</option>
                    {/foreach}
                    </select>
                </td>
                <td>
                    <span class="multiple_selection" style="cursor:pointer;"> <img src="../img/admin/choose.gif"/> {l s='Selection multiple' mod='erpillicopresta'} </span>
                    <img class="multiple_selection_loder" alt="{l s='Supply Order Management' mod='erpillicopresta'}" src="../img/loader.gif"/>
                </td>
            </tr>
        </table>
</fieldset>

<input type="hidden" id="product_ids" name="product_ids" value="{$ids|escape:'htmlall'}">
<input type="hidden" id="product_ids_to_delete" name="product_ids_to_delete" value="">
<input type="hidden" id="id_supplier" name="id_supplier" value="{$supplier_id|intval}">
<input type="hidden" name="updatesupply_order" value="1" />
        
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

                $('input.quantity_received_today').live('click', function() {
                    /* checks checkbox when the input is clicked */
                    $(this).parents('tr:eq(0)').find('input[type=checkbox]').attr('checked', true);
            });
		function addProduct()
		{
			// check if it's possible to add the product
			if (product_infos == null || $('#cur_product_name').val() == '')
			{
				jAlert('{l s='Please select at least one product.' mod='erpillicopresta'}');
				return false;
			}

			if (!product_infos.unit_price_te)
				product_infos.unit_price_te = 0;
                        
			if (!product_infos.quantity_expected)
				product_infos.quantity_expected = 0;
                        
			if (!product_infos.discount_rate)
				product_infos.discount_rate = 0;
                        
			if (!product_infos.tax_rate)
				product_infos.tax_rate = 0;
                        
                         if (!product_infos.comment)
                                product_infos.comment = '';
                            
                        var milliseconds = {$random|escape:'htmlall'} + new Date().getTime();
           
			
			// add a new line in the products table ----> Nouveau produit, numéro random pour le repérer lors du traitement
			$('.supply_order_detail > tbody:last').append(
				'<tr style="height:50px;" class="received_sup_expected">'+
                                '<td class="center"><input type="checkbox" name="supply_order_detailBox[]" value="new_'+milliseconds+'" class="noborder"></td>' +
                                '<td>'+
                                    product_infos.supplier_reference+'<input type="hidden" name="input_supplier_reference_'+milliseconds+'" value="'+product_infos.supplier_reference+'" />'+
                                    product_infos.reference+'<input type="hidden" name="input_check_'+milliseconds+'" value="'+product_infos.checksum+'" /><input type="hidden" name="input_reference_{$random|escape:'htmlall'}" value="'+product_infos.reference+'" />'+
                                '</td>'+
				'<td>'+
                                        product_infos.ean13+'<input type="hidden" name="input_ean13_'+milliseconds+'" value="'+product_infos.ean13+'" />'+
                                        product_infos.upc+'<input type="hidden" name="input_upc_'+milliseconds+'" value="'+product_infos.upc+'" />'+
                                '</td>'+
				
				'<td>'+product_infos.name+'<input type="hidden" name="input_name_displayed_'+milliseconds+'" value="'+product_infos.name+'" /></td>'+
				'<td><input type="text" name="quantity_received_today_'+milliseconds+'" value="'+product_infos.quantity_expected+'" class="quantity_received_today"></td>'+
				'<td>0</td>' +
                                '<td>0</td>' +
                                '<td>0</td>' +
                                '<td class="right"><input type="text" size="5" name="input_unit_price_te_'+milliseconds+'" value="'+product_infos.unit_price_te+'"></td>' +
                                '<td class="right"><input type="text" size="5"  name="input_discount_rate_'+milliseconds+'" value="0"></td>' +
                                '<td class="right"><input type="text" size="5"  name="input_tax_rate_'+milliseconds+'" value="'+product_infos.tax_rate+'"></td>' +
                                '<td class="right"><input type="text" size="15"  name="input_comment_'+milliseconds+'" value="'+product_infos.comment+'"></td>' +
                                '<td class="center">'+
                                    '<a href="#" class="removeProductFromSupplyOrderLink" id="deletelink|'+product_infos.id+'">'+
                                    '<img src="../img/admin/delete.gif" alt="{l s='Remove this product from the order' mod='erpillicopresta'}" title="{l s='Remove this product from the order' mod='erpillicopresta'}" />'+
				'</a><input type="hidden" size="5"  name="input_id_product_'+milliseconds+'" value="'+product_infos.id+'" /></td>' +
				'</tr>'
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
				max:20,
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
					token : '{$smarty.get.token|escape:'htmlall'}',
					action : 'searchProduct'
	            },
	            parse: function(data) {
		            if (data == null || data == 'undefined')
			        	return [];
	            	var res = $.map(data, function(row) {
		            	// filter the data to chaeck if the product is already added to the order
	            		//if (jQuery.inArray(row.id, product_ids) == -1)
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