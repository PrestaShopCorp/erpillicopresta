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

function orderLegend()
{
    $('form.erp legend').removeAttr('style');
    var previous_pos = 0;
    $('form.erp legend').each(function()
    {
        $(this).attr('style', 'left:'+previous_pos+'px;');
        previous_pos = $(this).outerWidth() + $(this).position().left - 1;        
    });
}

$(document).ready(function()
{

   orderLegend();
   $('form.erp legend').click(selectLegend);   
   $('form.erp legend').eq(0).click();
   
   hide_forecast_config();
   
   $('#forecast_period').click(function(){
        hide_forecast_config();
        $('#erp_projected_period').parent().parent().parent().show('slow');
        $('#erp_comparison_period').parent().parent().parent().show('slow');
        $('#erp_exceptional_order_limit').parent().parent().show('slow');
   });
   
   $('#forecast_six_last_month').click(function(){
       hide_forecast_config();
       $('#erp_coefficients').parent().parent().show('slow');
       $('#erp_exceptional_order_limit').parent().parent().show('slow');
   });
   
   $('#none').click(function(){
       hide_forecast_config();
   });
   
   // add required attribute to the cvg input 
   // prestashop do not add this automacly
   if( $('.erp-configuration-page form #_erp_cgv').length > 0)
       $('.erp-configuration-page form #_erp_cgv').attr("required",'required');
   
   if( getPrestashopMailVersion() == '1.5')
   {
        if( $('.erp-configuration-page form #erp_licence_password').length > 0)
            $('.erp-configuration-page form #erp_licence_password').attr("required",'required');
   
        if( $('.erp-configuration-page form #erp_contact_mail').length > 0)
            $('.erp-configuration-page form #erp_contact_mail').attr("required",'required');

        if( $('.erp-configuration-page form #erp_contact_name').length > 0)
            $('.erp-configuration-page form #erp_contact_name').attr("required",'required');    
    }
   
   
   // Validate form  activation form 
   $('.erp-configuration-page button[name=submitActivateLicence]').click(function(e){
       
        e.preventDefault();
        
        // init alert message
        var msg_alert = '';
               
        // check if required fields are empty and regex 
        if( $(".erp-configuration-page form.erpillicopresta").valid() )
        {
            if($("[name='erp_contact_mail']").val().length > 0)
            {
                if(!isValidEmailAddress($("[name='erp_contact_mail']").val()))
                {
                    msg_alert += alert_invalid_email;
                    $("[name='erp_contact_mail']").addClass('error');
                }
                else {
                    $("[name='erp_contact_mail']").removeClass('error');
                }
            }
            
            var name = $("[name='erp_contact_name']").val();

            if(!isValidName(name))
            {
                 msg_alert += alert_invalid_name;
                 $("[name='erp_contact_name']").addClass('error');
            }
            else {
                $("[name='erp_contact_name']").removeClass('error');
            }
        }
        else {
            msg_alert = alert_check_all;
        }
        
        // if error
        if( msg_alert != '')
        {
            $('html, body').animate({ scrollTop: $(".blok_licence").offset().top - 110 }, "slow", function(){
                jAlert(msg_alert);
            });
        }
        
        // else, submit form activate licence
        else 
        {
            var html_enabled_input =  $('#cart_features form input:enabled');               
            $(".erp-configuration-page form.erpillicopresta").prepend(html_enabled_input);
            $(".erp-configuration-page form.erpillicopresta").submit();
        }
   });
   
   /*hide input licence on set licence form*/
   if( $('#erp_set_licence').length > 0)
   {
        if( $('input[name=erp_has_licence_number]:checked').val() == 0 )
        {
            if( getPrestashopMailVersion() == '1.6')
                $('#erp_set_licence').closest('.form-group').hide();
            else
            {
                $('#erp_set_licence').closest('.margin-form').hide();
                $('#erp_set_licence').closest('.margin-form').prev('label').hide();
            }
        }
   }
   
   /*display input licence if merchat has a licence number*/
   $('input[name=erp_has_licence_number]').change(function(){
       
       if( getPrestashopMailVersion() == '1.6' )
       {
           if( $(this).val() == 1)
                $('#erp_set_licence').closest('.form-group').show(); 
            else
                $('#erp_set_licence').closest('.form-group').hide(); 
       }
       else
       {
            if( $(this).val() == 1)
            {
                $('#erp_set_licence').closest('.margin-form').show(); 
                $('#erp_set_licence').closest('.margin-form').prev('label').show();
            }
            else
            {
                $('#erp_set_licence').closest('.margin-form').hide();
                $('#erp_set_licence').closest('.margin-form').prev('label').hide();
            }
       }
   });
   
   // Make ERP CART PRICE Sticky if existe
   if( $('.blok-cart-price').length > 0 )
        make_sticky('.blok-cart-price'); 
    
});

/*
 * return prestashop mail version (1.4 or 1.5 or 1.6)
 * @returns {String}
 */
function getPrestashopMailVersion()
{
    return _PS_VERSION_.split('.')[0]+'.'+_PS_VERSION_.split('.')[1];
}


function selectLegend()
{
    // tout masquer
    $('form.erp legend').removeClass('selected');
    $('form.erp fieldset').removeClass('selected');
    
    $(this).toggleClass('selected');
    $(this).parent().toggleClass('selected');    
    
    $('form.erp').height($(this).parent().outerHeight() + 10);
}

function display_action_details_16(row_id, controller, token, action, params)
{
	var id = action+'_'+row_id;
	var current_element = $('#details_'+id);
	if (!current_element.data('dataMaped')) {
		var ajax_params = {
			'id': row_id,
			'controller': controller,
			'token': token,
			'action': action,
			'ajax': true
		};
        
                if(params)
                {
                    $.each(params, function(k, v) {
                            ajax_params[k] = v;
                    });
                }

		$.ajax({
			url: 'index.php',
			data: ajax_params,
			dataType: 'json',
			cache: false,
			context: current_element,
			async: false,
			success: function(data) {
                            if (typeof(data.use_parent_structure) == 'undefined' || (data.use_parent_structure == true))
                            {
                                if (current_element.parent().parent().parent().hasClass('alt_row'))
                                    var alt_row = true;
                                else
                                    var alt_row = false;
                                current_element.parent().parent().parent().after($('<tr class="details_'+id+' small '+(alt_row ? 'alt_row' : '')+'"></tr>')
                                        .append($('<td style="border:none!important;" class="empty"></td>')
                                        .attr('colspan', current_element.parent().parent().parent().find('td').length)));
                                $.each(data.data, function(it, row)
                                {
                                    var bg_color = ''; // Color
                                    if (row.color)
                                            bg_color = 'style="background:' + row.color +';"';

                                    var content = $('<tr class="action_details details_'+id+' '+(alt_row ? 'alt_row' : '')+'"></tr>');
                                    content.append($(''));
                                    var first = true;
                                    var count = 0; // Number of non-empty collum
                                    $.each(row, function(it)
                                    {
                                            if(typeof(data.fields_display[it]) != 'undefined')
                                                    count++;
                                    });
                                    $.each(data.fields_display, function(it, line)
                                    {
                                        if (typeof(row[it]) == 'undefined')
                                        {
                                            if (first || count == 0)
                                                content.append($('<td class="'+current_element.align+' empty"' + bg_color + '></td>'));
                                            else
                                                content.append($('<td class="'+current_element.align+'"' + bg_color + '></td>'));
                                        }
                                        else
                                        {
                                            count--;
                                            if (first)
                                            {
                                                first = false;
                                                content.append($('<td class="'+current_element.align+' first"' + bg_color + '>'+row[it]+'</td>'));
                                            }
                                            else if (count == 0)
                                                content.append($('<td class="'+current_element.align+' last"' + bg_color + '>'+row[it]+'</td>'));
                                            else
                                                content.append($('<td class="'+current_element.align+' '+count+'"' + bg_color + '>'+row[it]+'</td>'));
                                        }
                                    });
                                    content.append($('<td class="empty"></td>'));
                                    current_element.parent().parent().parent().after(content.show('slow'));
                                });
                            }
                            else
                            {
                                if (current_element.parent().parent().parent().hasClass('alt_row'))
                                        var content = $('<tr class="details_'+id+' alt_row"></tr>');
                                else
                                        var content = $('<tr class="details_'+id+'"></tr>');
                                content.append($('<td style="border:none!important;">'+data.data+'</td>').attr('colspan', current_element.parent().parent().parent().find('td').length));
                                current_element.parent().parent().parent().after(content);
                                current_element.parent().parent().parent().parent().find('.details_'+id).hide();
                            }
                            current_element.data('dataMaped', true);
                            current_element.data('opened', false);

                            if (typeof(initTableDnD) != 'undefined')
                                initTableDnD('.details_'+id+' table.tableDnD');
			}
		});
	}

	if (current_element.data('opened'))
	{
            current_element.find('i.icon-collapse-top').attr('class', 'icon-collapse');
            current_element.parent().parent().parent().parent().find('.details_'+id).hide('fast');
            current_element.data('opened', false);
	}
	else
	{
            current_element.find('i.icon-collapse').attr('class', 'icon-collapse-top');
            current_element.parent().parent().parent().parent().find('.details_'+id).show('fast');
            current_element.data('opened', true);
	}
}

function hide_forecast_config()
{
    // Only in 1.6, displaying menus from selected item only
    if (getPrestashopMailVersion() == '1.6')
    {
        var index = parseInt($('input[name=erp_sales_forecast_choice]:checked', '#configuration_form').val());
        
        switch(index)
        {
            case 1:
                $('#erp_projected_period').parent().parent().parent().hide();
                $('#erp_comparison_period').parent().parent().parent().hide();
            break;
            
            case 2:
                $('#erp_coefficients').parent().parent().hide();
            break;
            
            default:
                $('#erp_coefficients').parent().parent().hide();
                $('#erp_projected_period').parent().parent().parent().hide();
                $('#erp_comparison_period').parent().parent().parent().hide();
                $('#erp_exceptional_order_limit').parent().parent().hide();
            break;
        }
    }
}

// create o new tab to url 
function newTab(url) {
     form = document.createElement("form");
     form.method = "GET";
     form.action = url;
     form.target = "_blank";
     document.body.appendChild(form);
     form.submit();
}

function isValidEmailAddress(emailAddress) {
        var pattern = new RegExp(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/);
        return pattern.test(emailAddress);
};
	
function isValidName(name) {
	var pattern = new RegExp(/^[a-zA-Z_\-\.\ \'àáâãäåçèéêëìíîïðòóôõöùúûüýÿ]{3,60}$/);
	return pattern.test(name);
};


/*
 * return selected controller and level : free, light, pro 
*/
function getSelectedLevel(features_list)
{
    var level = features_list.find('.slider').slider('value');
    
    // if not free level
    if( level != 0)
    {
        var controller_title = features_list.find('h4').text();
        return controller_title+' '+feature_options[level];
    }
    
    return '';
}

/*
 * refresh ERP cart 
*/
function refreshErpCart()
{
    var erp_cart = $('.blok-cart-price .block-left ul').html('');
    var controller = '';
    var price_purchasse = 0;
    var price_sub = 0;
    var selected_level_id = 0;
    var type = '';
    var global_slider_value = parseInt($('.slider.global').slider('value')) + 1 ;
    
    $('#cart_features .features_list').each(function(index, features_list) {
        
        controller = getSelectedLevel($(features_list));

        if( controller != '')
           erp_cart.append('<li>'+controller+'</li>');

        // the levels in base are managed from 1 to 3
        // in base : 1 -> free, 2 -> light, 3 pro
        // in the JS slider they are managed from 0 to 2
        // in slider : 0 -> free, 1 -> light, 2 pro
        // we add + 1 to match database
        selected_level_id = parseInt($(this).find('.slider').slider("value")) +1 ; 

        // if light selected 
        if( selected_level_id == 2)
           type = 'light';
        
        // if pro selected 
        if( selected_level_id == 3)
            type = 'pro';

        // basket price is updated only if feature is not in basket
        if( $(this).find('input#selected_feature_'+selected_level_id).length > 0 && 
                $(this).find('input#selected_feature_'+selected_level_id).attr('in_basket') != 'true' )
        {
            // if pro selected
            if( selected_level_id == 3)
            {
                // if light is already in basket : price = pro price - light price
                if( $(this).find('input#selected_feature_2').length > 0 && 
                    $(this).find('input#selected_feature_2').attr('in_basket') == 'true' )
                {
                    
                    if( $(this).find('input[name=pro_price_purchasse]').length > 0 && $(this).find('input[name=light_price_purchasse]').length > 0)
                    {
                        price_purchasse += parseFloat($(this).find('input[name=pro_price_purchasse]').val()) - parseFloat($(this).find('input[name=light_price_purchasse]').val());
                    }
                    
                    if( $(this).find('input[name=pro_price_sub]').length > 0 && $(this).find('input[name=light_price_sub]').length > 0)
                    {
                        price_sub += parseFloat($(this).find('input[name=pro_price_sub]').val()) - parseFloat($(this).find('input[name=light_price_sub]').val());
                    }
                }
                
                // if light is not in basket : price = pro price
                else 
                {                    
                    if( $(this).find('input[name='+type+'_price_purchasse]').length > 0 )
                        price_purchasse += parseFloat($(this).find('input[name='+type+'_price_purchasse]').val());
                
                    if( $(this).find('input[name='+type+'_price_sub]').length > 0 )
                        price_sub += parseFloat($(this).find('input[name='+type+'_price_sub]').val());
                }
            }
            
            // if light selected
            if( selected_level_id == 2)
            {
                // basket is updated only if pro is not in basket
                if( $(this).find('input#selected_feature_3').length > 0 && 
                    $(this).find('input#selected_feature_3').attr('in_basket') != 'true' )
                {
                    if( $(this).find('input[name='+type+'_price_purchasse]').length > 0 )
                        price_purchasse += parseFloat($(this).find('input[name='+type+'_price_purchasse]').val());
                
                    if( $(this).find('input[name='+type+'_price_sub]').length > 0 )
                        price_sub += parseFloat($(this).find('input[name='+type+'_price_sub]').val());
                }
            }
        }
    });
    
    // if is free
    if( price_purchasse == 0)
    {
        $('#cart_features .blok-cart-price button.purchasse').hide();
        $('#cart_features .blok-cart-price button.free').show();
        $('#cart_features .blok-cart-price .block-left').hide();
        $('#cart_features .blok-cart-price .block-right').removeClass('col-lg-6').addClass('col-lg-12');
    }
    
    // if is not free
    else {
        $('#cart_features .blok-cart-price button.purchasse').show();
        $('#cart_features .blok-cart-price button.free').hide();
        $('#cart_features .blok-cart-price .block-left').show();
        $('#cart_features .blok-cart-price .block-right').removeClass('col-lg-12').addClass('col-lg-6');
    }
    
    // is not a free licence
    if( price_sub > 0 && price_purchasse > 0 )
    {   
        // not free
        if( $('input#erp_licence_is_free').length > 0 )
            $('input#erp_licence_is_free').val('0');
           
        price_purchasse = price_purchasse.toFixed(2);
        price_sub = price_sub.toFixed(2);
                 
        // if global slider is used we take the global price
        if(global_slider_value > 1 )
        {
            var globa_level_selected = parseInt($('#globa_level_selected').val());
            
            // global level 2 (light) in basket
            if( globa_level_selected == 2 && global_slider_value == 3)
            {
                price_purchasse = parseFloat($('.blok-economiser').find('input#global_price_purchasse_diff').val()).toFixed(2);
                price_sub = parseFloat($('.blok-economiser').find('input#global_price_sub_diff').val()).toFixed(2);
            }
            else if(globa_level_selected != global_slider_value )
            {
                price_purchasse = parseFloat($('.blok-economiser').find('input#global_price_purchasse_'+global_slider_value).val()).toFixed(2);
                price_sub = parseFloat($('.blok-economiser').find('input#global_price_sub_'+global_slider_value).val()).toFixed(2);
            }
        }
    }
    
    // free licence
    else {
        
        // free
        if( $('input#erp_licence_is_free').length > 0 )
            $('input#erp_licence_is_free').val('1');
    }
    
    $('#cart_features #total_price').text(price_purchasse);
    $('#cart_features #total_price_sub span').text(price_sub);
}

/*
 * Make ERP CART PRICE Sticky 
*/
function make_sticky(id) {
    var e = $(id);
    var w = $(window);
    $('<div/>').insertBefore(id);
    $('<div/>').hide().css('height',e.outerHeight()).insertAfter(id);
    var n = e.next();
    var p = e.prev();
    function sticky_relocate() {
      var window_top = w.scrollTop();
      var div_top = p.offset().top;
      if (window_top > div_top) {
        e.addClass('sticky');
        n.show();
      } else {
        e.removeClass('sticky');
        n.hide();
      }
    }
    w.scroll(sticky_relocate);
    sticky_relocate();
}

function cancelBubble(e,text) {
    jAlert(text);
    e.stopImmediatePropagation();
    e.preventDefault();
}