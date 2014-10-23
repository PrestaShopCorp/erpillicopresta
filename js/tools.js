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
*  @copyright 2007-2014 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/




/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
	$("[name='submitActivateLicence']").addClass("submitActivateLicence");
	if(getPrestashopMailVersion() == "1.5")
	{
		$("[name='submitActivateLicence']").removeClass("button");
		$("[name='submitActivateLicence']").css("font-size","25px");
		$("[name='submitActivateLicence']").css("padding","5px");
		$("[name='submitActivateLicence']").css("cursor","pointer");
	}
	else if(getPrestashopMailVersion() == "1.6")
	{
		$("[name='submitActivateLicence']").css("font-size","18px");
	}
	
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
   
   $('[name="submitActivateLicence"]').click(function(e){
		e.preventDefault();
		//check if required fields are empty and regex
		if($("[name='erp_contact_name']").val().length < 1 || $("[name='erp_contact_name']").val().length < 1 || !$('#_erp_cgv').is(':checked') || $("[name='erp_knowledge_source']").val().length == 0 )
		{
			jAlert('Please check all field with red star');
			return false;
		}
		
		if($("[name='erp_contact_mail']").val().length > 0)
		{
			if(!isValidEmailAddress($("[name='erp_contact_mail']").val()))
			{
				jAlert('Your contact mail address is not valid. Please write a valid mail address.');
				return false;
			}
		}
		var name = $("[name='erp_contact_name']").val();
		
		if(!isValidName(name))
		{
			jAlert('Your name is not valid. Please write a valid name (no numbers).');
			return false;
		}
		
		//get token
		var token = document.location.href.split("token=");
		token = token[1].split("#");
		token = token[0].split("&");
		token = token[0];
		
		var data_form = "";
		
		if( $('.defaultForm.erpillicopresta').length > 0)
			data_form = $(".defaultForm.erpillicopresta").serialize();
			
		if( $('#configuration_form').length > 0)
			data_form = $("#configuration_form").serialize();
		
		$.ajax({
			type: "POST",
			url: '../modules/erpillicopresta/ajax/ajax.php?task=ActivateLicense&token='+token,
			data: data_form,
			dataType: 'json',
			cache: false,
			async: false,
			success: function(data) {
				// Si activation de la licence OK, on refresh la page + popup wordpress pour achat direct
				if(data.code == 200)
				{
					window.location.reload();
					
					var a = document.createElement('a');
					a.href = 'http://www.illicopresta.com/?page_id=10649&name='+name+'&iso_lang='+iso_user+'&id_customer='+data.id_customer+'&domain_name='+window.location.hostname;
					a.target = '_blank';
					document.body.appendChild(a);
					a.click();
				}
				else
					jAlert(data.message);
			}
		});
   });
   
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
    // UNIQUEMENT en 1.6, on affiche que les menus de l'item sélectionné
    if (getPrestashopMailVersion() == '1.6')
    {
        var index = parseInt($('input[name=erp_sales_forecast_choice]:checked', '#configuration_form').val());
        
        switch(index)
        {
            default:
                $('#erp_coefficients').parent().parent().hide();
                $('#erp_projected_period').parent().parent().parent().hide();
                $('#erp_comparison_period').parent().parent().parent().hide();
                $('#erp_exceptional_order_limit').parent().parent().hide();
            break;
            
            case 1:
                $('#erp_projected_period').parent().parent().parent().hide();
                $('#erp_comparison_period').parent().parent().parent().hide();
            break;
            
            case 2:
                $('#erp_coefficients').parent().parent().hide();
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
	
	
