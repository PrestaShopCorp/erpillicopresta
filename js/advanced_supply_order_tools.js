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

// AUTOCOMPLETION FUNCTION
function AutoCompleter(elementName, sourceUrl, targetElementName)
{
    $('#' + elementName).autocomplete(
    {
        source: sourceUrl,
        minLength: 2,
        delay: 10,
        select: function (event, ui)
                {
                    if(targetElementName == '')
                    {
                        $(this).val((ui.item.label));
                    }
                    else
                    {
                       $(this).val((ui.item.reference)+' || '+(ui.item.label));
                    }
                }
    })
}

// TRIM
function trim (myString)
{
    return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
}

// Parse int
function isInt(value) 
{ 
    return !isNaN(parseInt(value,10)) && (parseFloat(value,10) == parseInt(value,10)); 
}

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