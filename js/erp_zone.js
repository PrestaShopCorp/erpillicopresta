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

$('document').ready(function()
{
           
        var erp_zone_token = $('#erp_zone_token').val();
            
        $.ajaxSetup({ cache: false });
            
        $('.sub_area, .area').each(function(i, element) {
           
            element = $(element);         
            
            element.bind("change mouseover click keyup", function() {
                element.flushCache();
            });

            element.autocomplete(
                    'index.php', {
                    minChars: 1,
                    max: 50,
                    width: 500,
                    selectFirst: false,
                    scroll: false,
                    dataType: 'json',
                    cacheLength: 0,
                    formatItem: function(data, i, max, value, term) {
                        return value;
                    },
                    parse: function(data) {
                        
                        // no result 
                        if( $.isEmptyObject(data))
                        {
                            element.val('');
                            element.next().val('');
                        }
                                                 
                        var mytab = new Array();
                        for (var i = 0; i < data.length; i++)
                                mytab[mytab.length] = { data: data[i], value: (data[i].name).trim() };
                        
                        return mytab;
                    },
                    extraParams: {
                        controller: 'AdminErpZone',
                        token: erp_zone_token,
                        ajax: 1,
                        action: 'checkAreaName',
                        level: element.attr('class'),
                        id_warehouse: $('#id_warehouse_filter').val(),
                        id_parent: function() { return element.hasClass('sub_area') ? element.parent().find('.id_area_hidden').val() : '0' },
                        limit: 10,
                        cache: $.now()
                    },
                }
            )
            .result(function(event, data, formatted) {
                    $(this).val(data.name);
                    $(this).next().val(data.id_erpip_zone);
            })
            .change(function(event, data, formatted) {
   
                if (element.hasClass('area'))
                {
                    element.parent().find('input.id_area_hidden[type=hidden]').val('');
                    element.parent().find('input.id_sub_area_hidden[type=hidden]').val('');
                    element.parent().find('input.sub_area').val('');
                }
                else
                    $(this).next().val('');
            })
        })
});

// set area filter to no value
function razAreaFilter()
{
    $('#area_filter').prop('selectedIndex',0);
    $('#subarea_filter').prop('selectedIndex',0);
    return true;
}