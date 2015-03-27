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

// Recovery Token to secure AJAX queries
var token = document.location.href.split("token=");
token = token[1].split("#");
token = token[0].split("&");
token = token[0];

// Global variables
var quantity_changed = false;
var submited = false;
var error = '';
var values;
var localStore = new Array();
var csv = false;

// Viewing variations of a product
function expandAll()
{
    $('.product tbody > tr').each(function()
    {
        // Expand all
        var id_product = trim($(this).find('td.id_product').text());
        var token = $('#token').val();

        // Only the first click
        if (!submited) 
        {   
            if (getPrestashopMailVersion() == '1.6')
                display_action_details_16(id_product, 'AdminInventory', token, 'details', '');
            else
                display_action_details(id_product, 'AdminInventory', token, 'details', '');
        }
    });
}

// Saves values entered for a product
function saveValues(product)
{
    // RECOVERY OF LATEST UPDATE VALUES OF THE MODIFIED LINE
    var currentValues = $('input[name=inventory_values]');

    var id_product = 0;
    var id_product_attribute = 0;
    var physical_quantity = 0;
    var prefix = '';

    // Retrieving type of inventory management
    var advanced_stock_management = $('#advanced_stock_management').val();

    // Adding prefix if advanced stock management
    if (advanced_stock_management == 1)
        prefix = 'physical_';

    var ids = trim(product.find('td.id_product').text());

    if (ids != '')
        physical_quantity = trim(product.find('td.' + prefix + 'quantity').text());
    else
    {
        ids = product.find('td span.id_product').text();
        physical_quantity = trim(product.find('td span.' + prefix + 'quantity').text());
    }

    // Filled quantity
    var found_quantity = product.find('td input.filled_quantity').val();

    // If ';' --> id product & variations
    if (ids.indexOf(';') != -1)
    {
        ids = ids.split(';');
        id_product = ids[0];
        id_product_attribute = ids[1];
    }
    else
        id_product = ids;


    var id_reason = product.find('select[name=reason] option:selected').val();
    var area = product.find('select[name="area"] option:selected').html();
    var subarea = product.find('select[name="subarea"] option:selected').html();
    var location = product.find('input[name="location"]').val();

    // If no reason chosen, default is applied depending on the input value (+ or -)
    if (id_reason == -1)
    {
        if (parseInt(trim(found_quantity)) >= parseInt(trim(physical_quantity)))
            id_reason = $('#reason_increase').val();
        else
            id_reason = $('#reason_decrease').val();
    }

    // In the case of a display product / variations, we do not treat the products with variations
    if (id_reason != undefined)
        var productLine = 'idproduct==' + id_product + '|' + 'idproductattribute==' + id_product_attribute + '|' +
            'idreason==' + id_reason + '|' + 'area==' + area + '|' + 'subarea==' +  subarea + '|' + 'location==' + location + '|' +
            'physicalquantity==' + physical_quantity + '|' + 'foundquantity==' + found_quantity + '_';

    // Updating values
    currentValues.val(currentValues.val() + productLine);
    currentValues.val(currentValues.val().replace('undefined',''));
}

// Returns values recorded for a product id
function getSavedValues(productLine, id_product, id_product_attribute)
{
    if (id_product_attribute == '')
        id_product_attribute = 0;

    // browsing recorded values to find an equivalence
    for(var k=0; k<=values.length; k++)
    {
        var loop = false;

        if (values[k] != '' && values[k] != undefined)
        {
            //alert(id_product +'=='+ values[k]["idproduct"] + '&&' + id_product_attribute +'=='+ values[k]["idproductattribute"]);
            if (id_product == values[k]["idproduct"] && id_product_attribute == values[k]["idproductattribute"])
            {
                // If most recent value updated, skip
                for(var l=0; l<=localStore.length; l++)
                {
                    if (id_product + ';' +id_product_attribute == localStore[l])
                        loop = true;
                }

                // Only if already proccessed this line
                if (loop == false)
                {
                    // Quantity
                    productLine.find('td input.filled_quantity').val(values[k]["foundquantity"]);

                    // Location
                    productLine.find('td input[name=location]').val(values[k]["location"]);

                    // Area
                    productLine.find('td select[name=area] option')
                        .removeAttr('selected')
                        .filter('[value='+values[k]["area"]+']')
                        .attr('selected', true)

                    // Sub-area
                    productLine.find('td select[name=subarea] option')
                        .removeAttr('selected')
                        .filter('[value='+values[k]["subarea"]+']')
                        .attr('selected', true)

                    // Add the row to the table of processed rows
                    localStore.push(id_product + ';' +id_product_attribute);
                }
            }
        }
    }
}

// Removes a gap stock value into hidden
function deleteGapValue(ids, name)
{
    $('#gap_values').val($('#gap_values').val().replace(ids+'|', ''));
    $('#gap_values').val($('#gap_values').val().replace(name+'__', ''));
}

// Inventory of recorded data
function makeInventory()
{

    // Recovery values needed
    var directory = $('input[name=id_inventory]:checked').val().split("_");
    var id_inventory = directory[0];

    var name = ($('.selected td.name input').val() == undefined) ? $('.selected td.name').text() : $('.selected td.name input').val();
    var inventory_values = $('input[name=inventory_values]').val();
    var advanced_stock_management = $('#advanced_stock_management').val();
    var id_warehouse = $('#current_warehouse').val();
    var id_employee = $('#id_employee').val();
    var firstname = $('#firstname').val();
    var lastname = $('#lastname').val();

    $('#form_validate_inventory #inventory_values').val(inventory_values);
    $('#form_validate_inventory #advanced_stock_management').val(advanced_stock_management);
    $('#form_validate_inventory #id_warehouse').val(id_warehouse);
    $('#form_validate_inventory #id_employee').val(id_employee);
    $('#form_validate_inventory #firstname').val(firstname);
    $('#form_validate_inventory #lastname').val(lastname);
    $('#form_validate_inventory #name').val(name);
    $('#form_validate_inventory #id_inventory').val(id_inventory);
    
    $('#form_validate_inventory').submit();
    
    return true;

}


$('document').ready(function()
{
    // Hover on a product line, selected product id is stored into a hidden
    $('.row_hover').mouseover(function()
    {
        var id_product = $($(this).find('td.id_product')).text();
        $('#selectedProductId').val(id_product);

        // Reset id variation
        $('#selectedProductAttributeId').val('0');
    });

    // Hover on a variation line, selected product variation id is stored into a hidden
    $(".action_details").live('mouseover', function()
    {
        // Retrieving product id & variation id then split
        var ids = $($(this).find('td span.id_product')).text().split(';');

        var id_product = ids[0];
        var id_product_attribute = ids[1];

        $('#selectedProductId').val(id_product);
        $('#selectedProductAttributeId').val(id_product_attribute);
    });

    $('a.category').cluetip({
        showTitle: true,
        closePosition: 'title',
        ajaxCache:false,
        sticky: false,
        tracking: true,
        waitImage:true,
        closeText: '<img src="../img/admin/close.png">'
    });

    $('img.cluetip').cluetip({splitTitle: '|',  showTitle:false, width:'330'});
    
    // Hover on a main reference supplier, displaying all product references into a list -- MAIN PRODUCT
    $("a.supplier_ref").live('mouseover', function()
    {
        $(this).cluetip(
        {
            showTitle: true,
            closePosition: 'title',
            ajaxCache:false,
            sticky: false,
            width: '250px',
            tracking: true,
            waitImage:true,
            closeText: '<img src="../img/admin/close.png">'
        })
    });

    // Massive quantity application
    $('#desc-product-duplicate,#page-header-desc-product-duplicate').click(function()
    {
        var advanced_stock_management = $('#advanced_stock_management').val();
        var jstring = (advanced_stock_management == 1) ? $("#trad_confirm").val() + '<br />' + $("#trad_advancedstock_warning").val() : $("#trad_confirm").val() + '<br />' + $("#trad_classic_warning").val();
        
        // Confirm before action
        jConfirm(jstring, 'Attention', function(event)
        {
            // if Ok..
            if(event)
            {
                // Quantity applied
                if (!quantity_changed)
                {
                    // Displaying variations
                    expandAll();

                    $('.product tbody > tr').each(function()
                    {
                        // Ignoring empty separation lines ..
                        if ($(this).find('td').length > 1)
                        {
                            // Quantity applied
                            var prefix = '';

                            // If advanced stock management, the physical quantity is recovered
                            if (advanced_stock_management == 1)
                                prefix = 'physical_';

                            var physical_quantity = trim($(this).find('td.' + prefix + 'quantity').text());
                            var found_quantity = $(this).find('td input.filled_quantity');

                            // If '' is found, maybe a variation : searching into a span
                            if (physical_quantity == '')
                                    physical_quantity = trim($(this).find('td span.' + prefix + 'quantity').text());

                                                if (physical_quantity == '')
                                                        physical_quantity = '0';
                            if(physical_quantity != '')
                                found_quantity.val((physical_quantity !='--') ? physical_quantity : '0');

                            // Save values
                            saveValues($(this));
                        }
                    });

                    quantity_changed = true;
                }
                // RAZ
                else
                {
                    $('.filled_quantity').val('0');
                    quantity_changed = false;
                }

                submited = true;
            }
        });
    });

    // Creation and management of dialog popup for selecting a container
    $("#dialog-select_container").dialog({
        autoOpen: false,
        show: "clip",
        hide: "clip",
        width: "550",
        position: ['center', 'center'],
        buttons:
            [
                // Cancel
                {
                    text: $("#trad_cancel").val(),
                    click: function() {
                        $(this).dialog("close");
                    }
                },

                {
                        text: $("#trad_validate").val(),
                        // New inventory
                        click: function()
                        {
                            // If classical inventory
                            if (!csv)
                            {
                                if (($('.selected td.name input').val() == undefined || $('.selected td.name input').val() == "") && $('.selected td.name').text() == "")
                                {
                                        jAlert($("#trad_emptyinventoryname").val());
                                }
                                else
                                {
                                        var gap_values = $('#gap_values');
                                        // Over gap! confirm ?
                                        if (gap_values.val() != '_')
                                        {
                                            // Reset over gap products list
                                            $("#dialog-confirm_inventory ul").empty();

                                            // Recovering id|name table
                                            gap_values = gap_values.val().split('_');
                                            // for each id|name couple
                                            for(var i = 0; i<= gap_values.length; i++)
                                            {
                                                // If values != empty
                                                if (gap_values[i] != undefined && gap_values[i] != '')
                                                {
                                                     // Recovering id|name table
                                                     var gap_values_id = gap_values[i].split('|');
                                                     $("#dialog-confirm_inventory ul").append('<li>ID : '+ gap_values_id[0]+'&nbsp&nbsp&nbsp&nbsp' + gap_values_id[1] + '</li>');
                                                 }
                                            }
                                            if ($('#inventory_values').val() != '')
                                                    $("#dialog-confirm_inventory").dialog("open");
                                            else
                                                    jAlert($("#trad_atleastoneproduct").val());
                                        }
                                        else
                                        {
                                            // On product to modify at least
                                            if ($('#inventory_values').val() != '')
                                                    makeInventory();
                                            else
                                                    jAlert($("#trad_atleastoneproduct").val());
                                        }

                                        $(this).dialog("close");
                                        submited = true;
                                }
                            }
                            else
                                $('#form_validate_inventory').submit();
                        }
                }
            ]
    });

    // Creation and management of dialog popup for inventory confirmation
    $("#dialog-confirm_inventory").dialog({
        autoOpen: false,
        show: "clip",
        hide: "clip",
        width: "500",
        buttons:
            [
				{
                // Cancel
					text: $("#trad_cancel").val(),
					click: function()
					{
						$(this).dialog("close");
					}
				},

                // Confirm
				{
					text: "OK",
					click: function()
					{
						makeInventory();
						$(this).dialog("close");
					}
				}
            ]
    });

    // New inventory
    $('#desc-product-save, #desc-product-save-and-stay, #page-header-desc-product-save, #page-header-desc-product-save-and-stay').click(function(e)
    {
        // Displaying box for selecting a new container
        $("#dialog-select_container").dialog("open");

        // upload csv file
        if (this.id == 'desc-product-save-and-stay' || this.id == 'page-header-desc-product-save-and-stay')
        {
            $('#form_validate_inventory input#submitAction').val('submitCreateInventoryFromCsv');
            $('#form_validate_inventory input#id_warehouse').val($('select[name=id_warehouse] option:selected').val());
            $('#csv_fields').show();
            csv = true;
        }
        
        // create manual inventory
        else
        {
            $('#csv_fields').hide();
            csv = false;
        }
    });

    // Selecting an inventory, adding the class 'selected' on the line to locate it
    $('input[name=id_inventory]').click(function()
    {
        // Browse table and deleting class selected
        $('#tbl_container tbody > tr').each(function()
        {
            $(this).removeClass('selected');
        });

        // Adding class on selected one
        $(this).parent().parent().addClass('selected');
    });

    // Checking gap inventory and recording filled values
    $(".filled_quantity").live(
    {
        focusout:function()
        {
            // Initialization
            var prefix = '';
            var physical_quantity = 0;
             var name = '';

            // Retrieving type of inventory management
            var advanced_stock_management = $('#advanced_stock_management').val();

            // Adding the prefix if advanced stock
            if (advanced_stock_management == 1)
                prefix = 'physical_';

            // Maximum stock gap values in conf
            var gap_stock = $('#gap_stock').val();

            // Quantity filled
            var found_quantity = $(this).parent().parent().find('td input.filled_quantity').val();

            // Physical quantity recovery
            // If no product id found, it's a variation
            var ids = trim($(this).parent().parent().find('td.id_product').text());
            if (ids != '')
            {
                physical_quantity = trim($(this).parent().parent().find('td.' + prefix + 'quantity').text());
                name = trim($(this).parent().parent().find('a.product_name').text());
            }
            else
            {
                ids = trim($(this).parent().parent().find('td span.id_product').text());
                physical_quantity = trim($(this).parent().parent().find('td span.' + prefix + 'quantity').text());
                name = trim($(this).parent().parent().find('td span.product_name').text());
            }
            
            // CHECKING THE STOCK GAP
            // if integer
            if (isInt(found_quantity) && Number(found_quantity) >= 0)
            {
                // If larger gap than parametered, saving, red box then alert
				if (gap_stock != '' && gap_stock != 0)
				{
					if (Math.abs((found_quantity - physical_quantity)) > gap_stock)
					{
						jAlert($('#trad_quantityerror').val() + ' ' + gap_stock);
						
						$(this).addClass('overGap');
						
						deleteGapValue(ids, name);
						$('#gap_values').val($('#gap_values').val() + ids + '|' + name + '__');
					}
					else // Else erase value then withdraw class red box
					{
						deleteGapValue(ids, name);
						$(this).removeClass('overGap');
					}
				}
            }
            else
            {
				// => There's some letters
                if (found_quantity != '')
                    jAlert($("#trad_onlyinteger").val());
				else // => c'est vide
				{
                                            // Get product id and variation id if exists

                                            var ids = trim($(this).parent().parent().find('td.id_product').text());
					    if (ids == '')
							ids = $(this).parent().parent().find('td.id_product').text();				
						if (ids.indexOf(';') != -1)
						{
							ids = ids.split(';');
							id_product = ids[0];
							id_product_attribute = ids[1];
						}
						else
						{
							id_product = ids;
							id_product_attribute = -1;
						}

						// Get "History" of inventory_values to delete entrie of id product and id product atribute in question
						values  = $('#inventory_values').val().split("_");
						
						// Creating table that will contain the strings
						last_values = new Array();
						for(var i=0; i <= values.length; i++)
						{
							if (values[i] != undefined && values[i] != '')
								if (id_product_attribute != -1 && values[i].indexOf('idproduct=='+id_product+'|idproductattribute=='+id_product_attribute) == -1) // If this is not the right variation about the right product
									last_values.push(values[i]); // We can keep this string
								else if (id_product_attribute == -1 && values[i].indexOf('idproduct=='+id_product+'|idproductattribute==0') == -1) // If this is not the right product
									last_values.push(values[i]);
						}
						
						// Returns the complete string separated by "/"
						last_values = last_values.join('_');
						// So we only keep the string values about other products and replace what was there before
						$('#inventory_values').val(last_values);
						
						deleteGapValue(ids, name);
						$(this).removeClass('overGap');
				}
				$(this).parent().parent().find('td input.filled_quantity').val('')
            }

            // Saving value filled
            if (found_quantity != '')
            {
                saveValues($(this).parent().parent());
            }
        }

    });

    // Changing location : Check if this is not already taken
    $('td input[name=location], td select[name="area"], td select[name="subarea"]').live("change", function()
    {
        var area = $(this).parent().find('select[name="area"] option:selected').html();
        var subarea = $(this).parent().find('select[name="subarea"] option:selected').html();
        var location = $(this).parent().find('input[name="location"]').val();

         $.ajax({
            type: 'GET',
            data: {
                area:area,
                subarea:subarea,
                location:location,
                action:"swapLocation",
                token:token
				},
            cache:false,
            async: true,
            url: '../modules/erpillicopresta/ajax/searchLocation.php',
            success: function(data) {
                if (data != 'false')
                {
                    data = JSON.parse(data);
                    jAlert($("#trad_locationerror").val() + ' ['+ data.name + '], ' + $("#trad_locationerror2").val());
                }
            }
        });

        // Saving value filled
        saveValues($(this).parent().parent());
    });

    // Changing move reason : on save
    $('td select[name=reason]').live("change", function()
    {
        saveValues($(this).parent().parent());
    });

    // Displaying product's variation, get already saved values
    $('a[id^="details_details_"]').click(function()
    {
        var id_product = trim($(this).parent().parent().find('td.id_product').text());

        // Looking for every line of variations
        $('table.product tbody > tr.details_details_'+id_product).each(function()
        {
            // We do not take separation lines
            if ($(this).find('td').length > 1)
            {
                var ids = $(this).find('td span.id_product').text();
                var id_product_attribute = ids.split(';')[1];

                // If there is a gap value recorded, framing Quantity field in red
                var gap_values = $('#gap_values').val();

                if (gap_values.indexOf(ids) != -1)
                    $(this).find('td input.filled_quantity').addClass('overGap');

                // Saving filled product values
                getSavedValues($(this), id_product, id_product_attribute);
            }
        });
    });

    // After filter or pagination, recovery values entered and recorded
    $('table.product').ready(function()
    {
        // Recovery of inventory lines
        values  = $('#inventory_values').val().split("_");
        values = values.reverse();

        for(var i=0; i<= values.length; i++)
        {
            if (values[i] != '' && values[i] != undefined)
            {
                // Recovery column
                values[i] = values[i].split("|");

                for(var j=0; j<=values[i].length; j++)
                {
                    if (values[i][j] != '' && values[i][j] != undefined)
                    {
                        // Recovery contents of each cell
                        var attributes = values[i][j].split("==");

                        // values[line][column] = value
                        values[i][attributes[0]] = attributes[1];
                    }
                }
            }
        }

        // Browse table to display saved values
        $('table.product tbody > tr').each(function()
        {
            var ids = trim($(this).find('td.id_product').text());

            var id_product = 0;
            var id_product_attribute = 0;

            // if ';' --> id product & variation
            if (ids.indexOf(';') != -1)
            {
                ids = ids.split(';');
                id_product = ids[0];
                id_product_attribute = ids[1];
            }
            else
                id_product = ids;

            var gap_values = $('#gap_values').val();

            // If there's a gap value recorded, framing Quantity field in red
            if (gap_values.indexOf('_' + trim($(this).find('td.id_product').text()) + '|') != -1)
                $(this).find('td input.filled_quantity').addClass('overGap');

            // Check values entered on the product
            getSavedValues($(this), id_product, id_product_attribute);
        });
    });
    
    
    // only if prestashop version is 1.6 
    if( getPrestashopMailVersion() == '1.6' )
    {
        $('[id^="details_details"]').click(function(){

            $(this).attr("href",'');
            var token = $('#token').val();
            var id = $(this).attr("id").split('details_details_');                
            display_action_details_16(id[1], 'AdminInventory', token , 'details','');
            return false;
        }); 
    }
});
