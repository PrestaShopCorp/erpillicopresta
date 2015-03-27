/**
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
*/

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){
    
    // check all buttons
    $("input.checkAll-order-generate").click(function() {
        var checked_status = this.checked;       
        $("input.selected_orders").each(function()
        {
            this.checked = checked_status;
            
           if( checked_status == false )
                setUnselectedOrdersList( this );

           else if( checked_status == true )
                unsetUnselectedOrdersList( this );       
        });
    });
    
    // click on the chosen orders
    $("input.selected_orders").click(function()				
    {
        var checked_status = this.checked;       
        
        if( checked_status == false )
           setUnselectedOrdersList( this );
       
        else if( checked_status == true )
           unsetUnselectedOrdersList( this );
    });	
    
    // Sending selected orders to simulation
    $('.toolbarBox a#desc-order-simulate, .toolbarBox a#page-header-desc-order-simulate').click(function(){
         
        if ($('input.selected_orders').length > 0)
        {
            var selected_orders_checked = false;
            $('input.selected_orders').each(function(){
                if ($(this).is(':checked'))
                {
                    selected_orders_checked = true;
                    
                    if (getPrestashopMailVersion() == '1.5')
                    {
                        $('.form').append('<input type="hidden" name="submitSimulate" value="1">');
                        $('.form').submit();
                    }
                    else
                    {
                        $('.form-horizontal').append('<input type="hidden" name="submitSimulate" value="1">');
                        $('.form-horizontal').submit();
                    }
                    return true;
                }     
            });
            
            if(!selected_orders_checked)
                jAlert($('#translate_select_least_one_order').val());
        }
        else {
            jAlert($('#translate_select_least_one_order').val());
        }
        return false;
    });

    // Sending selected orders to generate supply order
    $('.toolbarBox a#desc-order-generate-supply-orders').click(function(){
	
        
    });

    // changing billing date field into datepicker
    $('.date_delivery_expected_simulation').datepicker(
    {
        defaultDate:new Date,
        dateFormat:'yy-mm-dd',
        minDate: new Date()
    });
	
	
    $(".tax").change(function()
    {
            var value = $(this).val();
            if (!isFloat(value))
            {
                    $(this).val("0");
                    value = 0;
            }

            var price_ht = $(this).parent().parent().find(".price_ht").html();
            price_ht = price_ht.replace(",", ".");
            price_ht = price_ht.replace(" ", "");
            price_ht = parseInt(price_ht);

            var res = price_ht + (price_ht * value / 100);
            res = res.toFixed(2);
            res = res.toString();
            res = res.replace(".", ",");
            res = formatMillier(res) + " €";
            $(this).parent().parent().find(".price_ttc").html(res);

    });
	
	$('#page-header-desc-configuration-refresh').click(function(){
		submitFormOrdering();
		false;
	});
	
	$('#desc-configuration-refresh').click(function(){
		submitFormOrdering();
		false;
	});
    
});

function formatMillier(nombre)
{
    nombre += '';
    var sep = ' ';
    var reg = /(\d+)(\d{3})/;
    while( reg.test( nombre)) {
      nombre = nombre.replace( reg, '$1' +sep +'$2');
    }
    return nombre;
}

function isFloat(n)
{
    return parseFloat(n.match(/^-?\d*(\.\d+)?$/))>0;
}

function setUnselectedOrdersList(element)
{
    var unselected_orders_list = $('#unselected_orders_list').val();
    if( unselected_orders_list != '')
    {
        var ids_order = unselected_orders_list.split(',');
        if(  $.inArray( $(element).val() , ids_order ) == -1 )
        {
            ids_order.push( $(element).val() );
            $('#unselected_orders_list').val( ids_order.toString() );
        }
    }
    else
        $('#unselected_orders_list').val( $(element).val()  );
}

function unsetUnselectedOrdersList(element)
{
    var unselected_orders_list = $('#unselected_orders_list').val();
    if( unselected_orders_list != '')
    {
        var ids_order = unselected_orders_list.split(',');
        ids_order.splice($.inArray( $(element).val() , ids_order ),1);
        $('#unselected_orders_list').val(  ids_order.toString() );
    }
}

function submitFormOrdering()
{
    var form_valid = true;
    $('#form-ordering-info .required').each( function(){	
        if( $(this).val() == null || $(this).val() == '')
        {
            $(this).css('border', '1px red solid');
            form_valid = false;
        } 
        else
            $(this).css('border', '1px #ccc solid');
    });

    // If form is valid
    if( form_valid )
    {
        /* Annomalie smarty : le form qui devrait contenir tous les tableaux de fournisseur s'arrete au premier
         * On récupère donc les données des autres fournisseurs et on les places en hidden dans le form ...
        */
       
        // ID WAREHOUSE
        $("select[class*='id_warehouse_']").each(function()
        {
            var hidden = $("<input type='hidden' name='id_warehouse["+$(this).data('id_supplier')+"]' value='"+$(this).val()+"' />");
            $('form#form-ordering-info').append(hidden);
        });
        
        // DATE EXPECTED
        $("input[class*='date_delivery_expected_']").each(function()
        {
            var hidden = $("<input type='hidden' name='date_delivery_expected["+$(this).data('id_supplier')+"]' value='"+$(this).val()+"' />");
            $('form#form-ordering-info').append(hidden);
        });
        
        // TAX RATE
        $("input[class*='tax']").each(function()
        {
            var hidden = $("<input type='hidden' name='tax_rate["+$(this).data('id_supplier')+"][]' value='"+$(this).val()+"' />");
            $('form#form-ordering-info').append(hidden);
        });
        
        // Submit form
       $('form#form-ordering-info').submit();
    }
   else
        jAlert($('#translate_choosewarehouse').val());
    
    return false;
}


// open into blank
function _blank(url)
{
   window.open(url, '_blank');
}