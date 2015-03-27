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

// Fetching token to secure AJAX queries
var token = document.location.href.split("token=");
token = token[1].split("#");
token = token[0].split("&");
token = token[0];

$('document').ready(function()
{
    $('a.category').cluetip({
        showTitle: true,
        closePosition: 'title',
        ajaxCache:false,
        sticky: false,
        tracking: true,
        waitImage:true,
        closeText: '<img src="../img/admin/close.png">'
    });

    $('a.supplier_ref').cluetip({
        showTitle: true,
        closePosition: 'title',
        ajaxCache:false,
        sticky: false,
        width: '250px',
        tracking: true,
        waitImage:true,
        closeText: '<img src="../img/admin/close.png">'
    });

    $('a.supplier_price').cluetip({
        showTitle: true,
        closePosition: 'title',
        ajaxCache:false,
        sticky: false,
        width: '250px',
        tracking: true,
        waitImage:true,
        cluezIndex: 110,
        closeText: '<img src="../img/admin/close.png">'
    });


    // dialog to create a stock image
    $("#dialog-confirm_image").dialog({
        autoOpen: false,
        show: "clip",
        hide: "clip",
        width: "500",
        buttons:
            [
                // cancel
                {
                    text: $("#trad_cancel").val(),
                    click: function()
                    {
                        $(this).dialog("close");
                    }
                },

                // new location
                {
                    text: $("#trad_validate").val(),
                    click: function()
                    {
						$('#form-confirm-image').submit();
                        //createImage();
                        $(this).dialog("close");
                    }
                }
            ]
    });


//    $('#desc-product-save-and-stay,#page-header-desc-product-save-and-stay, #desc-stock_available-save-and-stay').click(function()
//    {
//        $("#dialog-confirm_image").dialog("open");
//    }); --> DIRECT DANS LE HREF DU INITTOOLBAR..

	// Selecting an inventory, adding the class 'selected' on the line to locate it
    $('input.id_stock_image').click(function()
    {
		$('#form-confirm-image input.id_stock_image').each( function(){
			$(this).attr('checked',  false);
		});			
		$(this).attr('checked', true);
    });
	
    $('img.cluetip').cluetip({splitTitle: '|',  showTitle:false, width:'330'});  
	
	//for 1.6
	$('#page-header-desc-stock_available-save-and-stay, #page-header-desc-product-save-and-stay').click(function(){
		$('#dialog-confirm_image').dialog('open');
		return false;
	});
		
	$('#page-header-desc-product-update').click(function(){
		$('#form-configuration').submit();
		return false;
	});
	
	//for 1.5
	$('#desc-product-save-and-stay').click(function(){
		$('#dialog-confirm_image').dialog('open');
		return false;
	});
	
	$('#desc-product-update').click(function(){
		$('#form-configuration').submit();
		return false;
	});
		
		
});