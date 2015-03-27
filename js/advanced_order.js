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

$('document').ready(OnReady);

var listChange; // Variable pour les listes d'états
var tabChecked = []; // Séléction multiple
var tabCheckedAlert = []; // Séléction multiple en alerte

var token = document.location.href.split("token=");
token = token[1].split("#");
token = token[0].split("&");
token = token[0];

var MRelayCheck;
var IEexpeCheck;


function OnReady () {
	// Tooltip init
	/*$("a.info-orders").cluetip({
		showTitle: true,
		ajaxCache: false,
		sticky: true,
		width: '600px',
		tracking: true,
		waitImage: false
	});*/
	
	$('a.info-orders').cluetip({
            sticky: true, 
            closePosition: 'title', 
            arrows: true, 
            'closeText': '<img src="../img/admin/cross.png">'
        });
	
	// Blocking tooltip on stocks not orange
	var tabLinkStock = $("a#info-stock");
	for (i in  tabLinkStock){
		if (((tabLinkStock.eq(i)).children("img")).attr('alt') !== '2'){
			(tabLinkStock.eq(i)).unbind('mouseover');
		}
	};

	// Init dialog confirmation status change
	$("#dialog-confirmUpdateOrderState").dialog({
		autoOpen: false,
		show: "clip",
		hide: "clip",
		width: "500"
	});

        $("#dialog-confirmUpdateOrderState").dialog({ buttons: [
                {
                    text: $('#transtation_cancel').val(),
                    click: function() 
                    {
                        $(this).dialog("close");
                    }
                },
                {
                    text: $('#transtation_confirm').val(),
                    click: function() 
                    {
                        updateOrderState();
                        $(this).dialog("close");
                    }
                }
        ]}); 
    
	// Init dialog confirmation weights 
	$("#dialog-confirmWeight").dialog({
		autoOpen: false,
		show: "clip",
		hide: "clip",
		width: "350"
	});
        
        $("#dialog-confirmWeight").dialog({ buttons: [
                {
                    text: $('#transtation_cancel').val(),
                    click: function() 
                    {
                        $(this).dialog("close");
                    }
                },
                {
                    text: $('#transtation_confirm').val(),
                    click: function() 
                    {
                        sendToCarrier();
                    }
                }            
        ]});
        
	// Init dialog confirmation massive status change
	$("#dialog-updateStates").dialog({
		autoOpen: false,
		show: "clip",
		hide: "clip",
		width: "500"
	});
        
        $("#dialog-updateStates").dialog({ buttons: [
                {
                    text: $('#transtation_cancel').val(),
                    click: function() 
                    {
                        $(this).dialog("close");
                    }
                },
                {
                    text: $('#transtation_update').val(),
                    click: function() 
                    {
                        // Check if some commands are on stock alert
                        if (tabCheckedAlert.length > 0){

                                // Fill in dialog
                                var tableContent = '<table style="text-align: center;" class="table_popup"><tr><th>ID</th><th>'+$('#transtation_alert').val()+'</th><th>'+$('#transtation_confirm_update').val()+'</th></tr>';
                                for (var i in tabCheckedAlert){
                                        tableContent += '<tr><td>&nbsp;'+i+'&nbsp;</td><td><img src="'+tabCheckedAlert[i]+'"/></td><td><input type="checkbox" class="check-confirmOrderInAlert-'+i+'" checked="checked"/></td></tr>';
                                }
                                tableContent += '</table>'
                                $('#dialog-confirmUpdateOrderInAlert').html(tableContent);

                                $('#dialog-confirmUpdateOrderInAlert').dialog("open");
                        }
                        else{
                                updateStates($(this));
                        }
                    }
                }            
        ]});
    
	// Init dialog stock alert
	$("#dialog-confirmUpdateOrderInAlert").dialog({
		autoOpen: false,
		show: "clip",
		hide: "clip",
		width: "400"
	});

        $("#dialog-confirmUpdateOrderInAlert").dialog({ buttons: [
                {
                    text: $('#transtation_continue').val(),
                    click: function() 
                    {
                        // Take all unchecked orders, then delete from tabChecked
                        var checkBoxAlert = $("input[class^='check-confirmOrderInAlert-']");
                        for (var i = 0; i < checkBoxAlert.length; i += 1){
                                if (!(checkBoxAlert.eq(i)).attr("checked")){
                                        tabChecked.splice((tabChecked.indexOf(checkBoxAlert.eq(i).attr('class').substr(26))),1);
                                }
                        }                               
                        updateStates();
                        $(this).dialog("close");
                    }
                }
        ]});
    
	$('#desc-order-update-selection,#page-header-desc-order-update-selection').click(updateSelection);
	$('#order-checkAll').click(checkAllId);
	$('#desc-order-expedition,#page-header-desc-order-expedition').click(openDialogWeight);
	$('#desc-order-print_invoices_delivery').click(printOrders);

	$("select[class^='selectUpdateOrderState-']").change(function () {
		listChange = $(this);
		$('#dialog-idOrder').html((listChange.attr('class')).substring(23));
		$('#dialog-textStateOrder').html($('.selectUpdateOrderState-'+(listChange.attr('class')).substring(23)+' option:selected').html());  
		$("#dialog-confirmUpdateOrderState").dialog("open");
	});

	//Variables for shipping
	MRelayCheck = ($('#MRToken').val() !== 'false') ? false : true;
	IEexpeCheck = ($('#ExpeditorToken').val() !== 'false') ? false : true;
	
	$('#page-header-desc-order-print_invoices_delivery').click(function(){
		printOrders();
		false;
	});
	
	$('#desc-order-print_invoices_delivery').click(function(){
		printOrders();
		false;
	});
}

function updateSelection (){

	// Fill table of Selected orders
	tabChecked = [];
	tabCheckedAlert = [];
        var id_order_checked;
        
        var checkBox = $("input[name='orderBox[]']");
        
	for (var i = 0; i < checkBox.length; i += 1){
		if ((checkBox.eq(i)).attr("checked")){
                        id_order_checked = checkBox.eq(i).val();
			tabChecked.push(id_order_checked);
			if ((checkBox.eq(i).parent().parent().find("#info-stock").children('img').attr('src')) === '2' || (checkBox.eq(i).parent().parent().find("#info-stock").children('img').attr('src')) === '3'){
				tabCheckedAlert[id_order_checked] = (checkBox.eq(i).parent().parent().find("#info-stock").children('img').attr('src'));
			}
		}
	}    
	// If no command selected
	if (tabChecked.length === 0){
                jAlert ($('#transtation_select_least_one_order').val(), $('#transtation_alert').val());
		return false;
	}
	  
	$("#dialog-updateStates").dialog("open");
}


function updateStates (dialog_element) {

        $('#dialog-updateStates img.loader-update-states').show('slow');
	var idState = ($('.selectUpdateStates option:selected').attr('class')).substring(19);
	$.ajax({
		type: 'POST',
		url: 'index.php?controller=AdminAdvancedOrder&ajax=1&task=updateOrderStatus&token='+token,
		dataType:'json',
		data: {
                    'idOrder' : tabChecked,
                    'idState' : idState,
                    //'token': token,
                    'action': 'masse',
                    //'task' : 'updateOrderStatus',
                    'id_employee' : $('input#id_employee').val()
                },
		success: (function (retour) {
                        if( retour.free_limitation_msg )
                        {
                            jAlert(retour.free_limitation_msg);
                            dialog_element.dialog("close");
                            $('#dialog-updateStates img.loader-update-states').hide();
                        }
                        else {
                            //dialog_element.dialog("close");
                            $('#content form').prepend('<input type="hidden" id="linkPDF" name="linkPDF" value="'+retour['ordersWithoutError']+'"/>');
                            $('#content form').prepend('<input type="hidden" id="newState" name="newState" value="'+idState+'"/>');
                            $('#content form').prepend('<input type="hidden" id="handle" name="handle" value="'+retour['message']+'"/>');
                            $('#content form').submit();
                        }
		})
	});

}

function updateOrderState (){

	var idOrder  = (listChange.attr('class')).substring(23);
	var idState = ($('.selectUpdateOrderState-'+idOrder+' option:selected').attr('class')).substring(19);

	$.ajax({
		type: 'POST',
		url: 'index.php?controller=AdminAdvancedOrder&ajax=1&task=updateOrderStatus&token='+token,
		dataType:'json',
		data: {
                    'idOrder' : idOrder, 
                    'idState' : idState, 
                    //'token': token, 
                    'action': 'unique', 
                    //'task':'updateOrderStatus', 
                    'id_employee' : $('input#id_employee').val() 
                },
		success: (function (retour) {
                    
			if (retour['res']) {
				// CONFIRMATION POPUP
				($('.selectUpdateOrderState-'+idOrder+'').parent()).css('background-color', retour['newColor']);
				
                                var message = $('#transtation_order_state_2').val();
                                message += " "+idOrder;
                                message += " "+$('#transtation_order_state_1').val()+" : ";
                                message += " "+$('.selectUpdateOrderState-'+idOrder+' option:selected').html();
                                showSuccessMessage(message);
                                
                                if (retour['message'])
                                    jAlert(retour['message']);
			}
			else{
                                showErrorMessage($('#transtation_order_state_3').val()+" : "+retour);
			}
		})
	});
}


function checkAllId () {

	var checkBox = $("input[class^='check-order-']");
         
	if($('#order-checkAll').is(':checked')){
		for (var i = 0; i < checkBox.length; i += 1){
			checkBox.eq(i).attr('checked', true);
		}
	}
	else{
		for (var i = 0; i < checkBox.length; i += 1){
			checkBox.eq(i).attr('checked', false);
		}       
	}
}

//function updateStates (dialog_element) {
//
//        $('#dialog-updateStates img.loader-update-states').show('slow');
//	var idState = ($('.selectUpdateStates option:selected').attr('class')).substring(19);
//	$.ajax({
//		type: 'POST',
//		url: '../modules/erpillicopresta/ajax/ajax.php',
//		dataType:'json',
//		data: {
//                    'idOrder' : tabChecked,
//                    'idState' : idState,
//                    'token': token,
//                    'action': 'masse',
//                    'task' : 'updateOrderStatus',
//                    'id_employee' : $('input#id_employee').val()
//                },
//		success: (function (retour) {
//                    
//                        if( retour.free_limitation_msg )
//                        {
//                            jAlert(retour.free_limitation_msg);
//                            dialog_element.dialog("close");
//                            $('#dialog-updateStates img.loader-update-states').hide();
//                        }
//                        else {
//                            //dialog_element.dialog("close");
//                            $('#content form').prepend('<input type="hidden" id="linkPDF" name="linkPDF" value="'+retour['ordersWithoutError']+'"/>');
//                            $('#content form').prepend('<input type="hidden" id="newState" name="newState" value="'+idState+'"/>');
//                            $('#content form').prepend('<input type="hidden" id="handle" name="handle" value="'+retour['message']+'"/>');
//                            $('#content form').submit();
//                        }
//		})
//	});
//
//}

function printOrders () {
        
	// Retrieving selected orders 
	tabChecked = [];
        var id_order_checked;
        var checkBox = $("input[name='orderBox[]']");
	for (var i = 0; i < checkBox.length; i += 1){
            if ((checkBox.eq(i)).attr("checked")){
                    id_order_checked = checkBox.eq(i).val(); 
                    tabChecked.push(id_order_checked);
            } 
	}
	// If no command selected
	if (tabChecked.length === 0){
                jAlert ($('#transtation_select_least_one_order').val(), $('#transtation_alert').val());
		return false;
	}
        $('#content form').prepend('<input type="hidden" id="linkPDFPrint" name="linkPDFPrint" value="'+tabChecked+'"/>');
	$('#linkPDFPrint').val(tabChecked);
	$('#content form').submit();


}

// --------------------------------------
//          SHIPMENT
// --------------------------------------

// Dialog window is filled with weights to let the user change
function openDialogWeight (){
    
	// Fill table of selected orders
	tabChecked = [];
        
	numSelected = $("input[name='orderBox[]']:checked").length;
        
        var expeditor_status = $('#expeditor_status').val();
        //var MR_status = $('#MR_status').val();
	$("input[name='orderBox[]']:checked").each(function()
	{
            order = [];
            order['id'] = $(this).val();
            
            
            //HACK :  Case of a bad placed order whose displayed status is "--"
            //Testing existence of status selection tag for this command
            //This command won't be put in tabchecked
            if (($("select[class='selectUpdateOrderState-" + $(this).val() + "']")).length)
                {
                    tabChecked.push(order);
                    
                     // Fetching order status
                    // Do not forget the "option:first" in the selection in case the user displayed another status without saving it (status still displayed but not correct)
                    var current_order_status = $("select[class='selectUpdateOrderState-" + $(this).val() + "'] option:first").prop('class').match(/\d+$/)[0];
                    //var reference = $(this).parent().parent().children('td').eq(3).text().trim();
                    var carrier = $(this).parent().parent().children('td').eq(5).find('img').data('carrier');

                    // If command expeditor with a different status than set in expeditor : error is recorded 
                    if(carrier == 'Expeditor' && (expeditor_status != current_order_status))
                        {
                        erreurs += $('#translate_order_status_error').val() + $(this).val() + ' : ' + $('#translate_order_status_error_EXP').val() + ' : ' + $("select[class='selectUpdateOrderState-" + $(this).val() + "'] option[class='selectedOrderState-" + expeditor_status + "']").text() + '<br/>';
                        }
                    // If command MR with a different status than set in MR : error is recorded
//                    if(carrier == 'MR' && (MR_status != current_order_status))
//                        {
//                        erreurs += $('#translate_order_status_error').val() + $(this).val() + ' : ' + $('#translate_order_status_error_MR').val() + ' : ' + $("select[class='selectUpdateOrderState-" + $(this).val() + "'] option[class='selectedOrderState-" + expeditor_status + "']").text() + '<br/>';
//                        }
                }
	});

	// If no command selected
	if (tabChecked.length === 0){
		jAlert ($('#transtation_select_least_one_order').val(), $('#transtation_alert').val());
		return false;
	}

	// Fill the window
	var tableContent = '<table style="text-align: center;" class="table_popup">\n\
                                <tr>\n\
                                    <th>ID</th>\n\
                                    <th>' + $('#transtation_weight').val() + '</th>\n\
                                    <th id="insurance_column">' + $('#translation_other_parameter').val() + '</th>\n\
                                </tr>';
        for (var i in tabChecked)
        {
            
		var carrier =  $('input[value="'+tabChecked[i]['id']+'"]').parent("td").parent("tr").find("img.carrier_image").attr("alt");
		
                // If command MR, offering the choice of insurance policy.
                if(carrier === 'MR')
                    {
                        // MONDIAL RELAY insurances may have 6 levels, including 0 corresponding to no insurance.
                        // See mondialrelay\views\templates\admin\generate_tickets.tpl lines 90 to 96.
			tableContent += '<tr>\n\
                                            <td>&nbsp;'+tabChecked[i]['id']+'&nbsp;</td>\n\
                                            <td><input type="text" value="' + $('input[value="'+tabChecked[i]['id']+'"]').parent("td").parent("tr").find("input[name~='weight-carrier_id']").attr("value")
                                                 + '" class="check-confirmWeight-'+tabChecked[i]['id']+'" /> g</td>\n\
                                            <td>\n\
                                                <select class="assurance_list-'+ tabChecked[i]['id']+'">\n\
                                                    <option value="0">' + $('#translate_MR_no_insurance').val() + '</option>\n\
                                                    <option value="1">' + $('#translate_MR_complementary_insurance').val() + '1 </option>\n\
                                                    <option value="2">' + $('#translate_MR_complementary_insurance').val() + '2 </option>\n\
                                                    <option value="3">' + $('#translate_MR_complementary_insurance').val() + '3 </option>\n\
                                                    <option value="4">' + $('#translate_MR_complementary_insurance').val() + '4 </option>\n\
                                                    <option value="5">' + $('#translate_MR_complementary_insurance').val() + '5 </option>\n\
                                                </select>\n\
                                            </td>\n\
                                        </tr>';
                    }
                // If command Expeditor, insurance policy won't be proposed but a non-standard size will be
                if (carrier === 'Expeditor')
                    {
                        tableContent += '<tr>\n\
                                            <td>&nbsp;'+tabChecked[i]['id']+'&nbsp;</td>\n\
                                            <td>\n\
                                                <input type="text" value="' + $('input[value="'+tabChecked[i]['id']+'"]').parent("td").parent("tr").find("input[name~='weight-carrier_id']").attr("value")
                                                        + '" class="check-confirmWeight-' + tabChecked[i]['id']+'" /> g</td>\n\
                                            <td>\n\
                                                <input type="checkbox" name="non-standard_size_'+tabChecked[i]['id']+'">&nbsp&nbsp&nbsp&nbsp'+$('#translate_non-standard_size').val()+
                                                '</input>\n\
                                            </td>\n\
                                        </tr>';
                    }
	}
	tableContent += '</table><p id="textLoading">...</p><div style="position: relative; text-align: center;" id="barreLoading"></div>';
	$('#dialog-confirmWeight-content').html(tableContent);

	// Progress bar won't be on some Prestashop versions
	if ($.isFunction($('#barreLoading').progressbar))
			$('#barreLoading').progressbar();
                    
	$("#dialog-confirmWeight").dialog ("open");   
}

var PDF = [];
var erreurs = '';

// Fetchhing checked orders and sending to all shippers (called by weight confirmation popup dialog)
function sendToCarrier () {

	// MONDIAL RELAY
	if ($('#MRToken').val() !== 'false')
        {
		
                var mrtoken = $('#MRToken').val();
		var weight_list = [];
                var assurance_list = [];
		numSelected = $("input[name='orderBox[]']:checked").length;
                
                var ids = [];
		for (var i in tabChecked)
                {
                    // Weights
                    weight_list.push($('.check-confirmWeight-'+tabChecked[i]['id']).val()+'-'+tabChecked[i]['id']);
                    
                    // Ids
                    ids.push(tabChecked[i]['id']);
                    
                    // Insurance
                    assurance_list.push($('.assurance_list-'+tabChecked[i]['id']+' option:selected').val()+'-'+tabChecked[i]['id']);
		}
                
		$.ajax({ // Ajax call to mondial relay
			 type : 'POST',
                        url: '../modules/mondialrelay/ajax.php',
			 data : {'order_id_list' : ids,
					 'numSelected' : numSelected,
					 'weight_list' : weight_list,
                                         'insurance_list': assurance_list,
					 'method' : 'MRCreateTickets',
					 'mrtoken' : mrtoken},
			 dataType: 'json',
			 success: function(json_success)
			 {
                             
                             var translate_mr_done  = $('#translate_mr_done').val();
				$("#textLoading").html(translate_mr_done);

				// Progress bar won't be on some Prestashop versions
				if ($.isFunction($('#barreLoading').progressbar))
					$('#barreLoading').progressbar({ value: $('#barreLoading').progressbar("option", "value") + 20});

				 var linksMR = '';
				 var deliveryNumbers = '';


                                 // COMMANDES OK
				 // Links to labels are concatenated separated by a spacebar
				 for (var i in json_success['success'])
                                 {

                                    if (json_success['success'][i] !== null)
                                    {
                                           successMR = json_success['success'][i];
                                           linksMR += 'http'+successMR['displayTicketURL'].split("http")[1]+' ';
                                           deliveryNumbers += successMR['expeditionNumber']+'-'+i+' ';
                                    }        
				 }

				 $('#etiquettesMR').val(linksMR);
				 $('#deliveryNumbersMR').val(deliveryNumbers);

				 
                                 // COMMANDS NOK
				 // Delete errored orders from tabChecked then an error is added
				 for (var i in json_success['error'])
                                 {
                                    if (json_success['error'][i] !== null)
                                    {
                                           tabChecked.splice((tabChecked.indexOf(i)),1);
                                           errorMR = json_success['error'][i];
                                           erreurs += $('#translate_order_num').val() + i + ' : '+errorMR[1]+', ' + errorMR[0] +'<br/>';
                                    }        
				 }
                                 for (var i in json_success['other'])
                                 {
                                    if (json_success['other'][i] !== null)
                                    {
                                        if (json_success['other']['error'][i] !== null)
                                        {
                                           tabChecked.splice((tabChecked.indexOf(i)),1);
                                           errorMR = json_success['other']['error'];
                                           erreurs += $('#translate_mr_wtf_error').val() + ' : '+errorMR[0]+'<br/>';
                                        }
                                    }        
				 }
				 
				 MRelayCheck = true;
				 if (IEexpeCheck === true) 
					callTnt();
			 },
			 error : function(json_failed)
                         {
                            // COMMANDS NOK
                            // Delete errored orders from tabChecked then an error is added
                            for (var i in json_failed['error'])
                            {
                               if (json_failed['error'][i] !== null)
                               {
                                    tabChecked.splice((tabChecked.indexOf(i)),1);
                               }        
                            }
                                 
                            erreurs += $('#translation_error_call_MR').val();
                            
                            $("#textLoading").html($('#transtation_mr_error1').val());
                       
                            // Progress bar won't be on some Prestashop versions
                            if ($.isFunction($('#barreLoading').progressbar))
                                  $('#barreLoading').progressbar({ value: $('#barreLoading').progressbar("option", "value") + 20});

                            MRelayCheck = true;
                            if (IEexpeCheck === true) callTnt();
			 }
		});
	}

	if (MRelayCheck && IEexpeCheck) callTnt();
        

	// EXPEDITOR INET
	if ($('#ExpeditorToken').val() !== 'false')
        {
            var order = [];
            for (var i in tabChecked){
                    // Define standard_size Expeditor property set to 0 or 1 whether the checked box or no for that command.
                    order[i] = {'id' : tabChecked[i]['id'], 
                        'weight': $('.check-confirmWeight-'+tabChecked[i]['id']).val(), 
                        'standard_size': ($('[name="non-standard_size_'+tabChecked[i]['id']+'"]').prop('checked'))?0:1};
            }

            $.ajax({ // Ajax call to expeditor inet
                    type : 'POST',
                    url : 'index.php?controller=AdminExpeditor&token='+$('#ExpeditorToken').val(),
                    data : 
                    {
                        'order' : order,
                        'generate' : 'Generate'
                    },
                     success: function(data)
                    {
                        var translate_ExpInet_done  = $('#translate_ExpInet_done').val();
                        $("#textLoading").html(translate_ExpInet_done);

                        // Progress bar won't be on some Prestashop versions
                        if ($.isFunction($('#barreLoading').progressbar))
                                $('#barreLoading').progressbar({ value: $('#barreLoading').progressbar("option", "value") + 20});

                        // If expeditor returns a CSV about processed orders
                        if (typeof (data) !== 'undefined')
                        {
                            var retour = [];

                            // Separating all the CSV lines
                            var csv = data.split('\n'); 
                            csv.splice(csv.length -1, 1);

                            // Processing each row
                            for (var i in csv)
                            { 
                                // Recovery of each value
                                var tmp = csv[i].split(";");

                                // Fetching expedition number
                                var id_exp = tmp[1].substring(4, tmp[1].length-1);

                                // Checking if the returned order is among those selected
                                for (var j=0; j< tabChecked.length; j++)
                                {
                                    if(id_exp == tabChecked[j]['id'])
                                    {
                                        // Saves the CSV line Expeditor to be returned
                                        retour.push(csv[i]);

                                        // Order processed, remove it from the list
                                        tabChecked.splice(j,1);
                                    }
                                }
                            }
                            
                            $('#expeditorCSV').val(retour);
                        } 
                        IEexpeCheck= true;
                        if (MRelayCheck === true)
                                callTnt();
                     },
                    error : function()
                    {
                        erreurs += $('#translation_error_call_exp').val();
                            
                            $("#textLoading").html($('#transtation_ex_error1').val());
                       
                            // Progress bar won't be on some Prestashop versions
                            if ($.isFunction($('#barreLoading').progressbar))
                                  $('#barreLoading').progressbar({ value: $('#barreLoading').progressbar("option", "value") + 20});

                            IEexpeCheck= true;
                            if (MRelayCheck === true)
                                    callTnt();
                    }
             });
	}

	function callTnt()
        {
            
            $("#textLoading").html($().val('#translate_other_done'));
            
            // Progress bar won't be on some Prestashop versions
            if ($.isFunction($('#barreLoading').progressbar))
                $('#barreLoading').progressbar({ value: 100}); 
            
            var ids = [];
            for (var i in tabChecked)
            {
                // Ids
                ids.push(tabChecked[i]['id']);
            }
            // orders unprocessed : trying to pass them to TNT after reloading : HOOK TNT
            $('#idOthers').val(ids);
            
            if (erreurs == '')
                erreurs = false;

            $('#hidden_form').prepend('<input type="hidden" id="linkPDF" name="linkPDF" value="'+ids+'"/>');
            $('#hidden_form').prepend('<input type="hidden" id="newState" name="newState" value="'+4+'"/>');
            $('#hidden_form').prepend('<input type="hidden" id="handle" name="handle" value="' + erreurs + '"/>');
            $('#hidden_form').submit();
	}
}

// Open to a blank
function _blank(url)
{
   window.open(url, '_blank');
}