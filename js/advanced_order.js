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
	// Initialisation info bulle
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
	
	// Bloquage de l'infobulle sur les stocks pas en orange
	var tabLinkStock = $("a#info-stock");
	for (i in  tabLinkStock){
		if (((tabLinkStock.eq(i)).children("img")).attr('alt') !== '2'){
			(tabLinkStock.eq(i)).unbind('mouseover');
		}
	};

	// Initialisation dialog confirmation changement d'un statut
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
    
	// Initialisation dialog confirmation des poids 
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
        
	// Initialisation dialog chgement statuts en masse
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
                        //On vérifie si certaines commandes sont en alerte de stock
                        if (tabCheckedAlert.length > 0){

                                // Remplir dialog
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
    
	// Initialisation dialog stock alert
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
                        // On prends toutes les commandes non chékés, on les supprimes de tabChecked
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

	//Variables pour l'expédition
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

	// On remplis le tableau des commandes selectionnés
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
	// Si aucune commande séléctionnée
	if (tabChecked.length === 0){
                jAlert ($('#transtation_select_least_one_order').val(), $('#transtation_alert').val());
		return false;
	}
	  
	$("#dialog-updateStates").dialog("open");
}

function updateOrderState (){

	var idOrder  = (listChange.attr('class')).substring(23);
	var idState = ($('.selectUpdateOrderState-'+idOrder+' option:selected').attr('class')).substring(19);

	$.ajax({
		type: 'POST',
		url: '../modules/erpillicopresta/ajax/ajax.php',
		dataType:'json',
		data: {
                    'idOrder' : idOrder, 
                    'idState' : idState, 
                    'token': token, 
                    'action': 'unique', 
                    'task':'updateOrderStatus', 
                    'id_employee' : $('input#id_employee').val() 
                },
		success: (function (retour) {
			if (retour['res']) {
				// POPUP DE CONFIRMATION
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

function updateStates (dialog_element) {

        $('#dialog-updateStates img.loader-update-states').show('slow');
	var idState = ($('.selectUpdateStates option:selected').attr('class')).substring(19);
	$.ajax({
		type: 'POST',
		url: '../modules/erpillicopresta/ajax/ajax.php',
		dataType:'json',
		data: {
                    'idOrder' : tabChecked,
                    'idState' : idState,
                    'token': token,
                    'action': 'masse',
                    'task' : 'updateOrderStatus',
                    'id_employee' : $('input#id_employee').val()
                },
		success: (function (retour) {
                        //dialog_element.dialog("close");
                        $('#content form').prepend('<input type="hidden" id="linkPDF" name="linkPDF" value="'+retour['ordersWithoutError']+'"/>');
                        $('#content form').prepend('<input type="hidden" id="newState" name="newState" value="'+idState+'"/>');
                        $('#content form').prepend('<input type="hidden" id="handle" name="handle" value="'+retour['message']+'"/>');
			$('#content form').submit();
		})
	});

}

function printOrders () {
        
	// On récupère toutes les commandes séléctionnées 
	tabChecked = [];
        var id_order_checked;
        var checkBox = $("input[name='orderBox[]']");
	for (var i = 0; i < checkBox.length; i += 1){
            if ((checkBox.eq(i)).attr("checked")){
                    id_order_checked = checkBox.eq(i).val(); 
                    tabChecked.push(id_order_checked);
            }
	}
	// Si aucune commande séléctionnée
	if (tabChecked.length === 0){
                jAlert ($('#transtation_select_least_one_order').val(), $('#transtation_alert').val());
		return false;
	}
        $('#content form').prepend('<input type="hidden" id="linkPDFPrint" name="linkPDFPrint" value="'+tabChecked+'"/>');
	$('#linkPDFPrint').val(tabChecked);
	$('#content form').submit();


}

// --------------------------------------
//          EXPEDITIONS
// --------------------------------------

// On remplis la fenetre de dialog avec les poids pour laisser l'utilisateur les modifier
function openDialogWeight (){
    
	// On remplis le tableau des commandes selectionnés
	tabChecked = [];
        
	numSelected = $("input[name='orderBox[]']:checked").length;
        
        var expeditor_status = $('#expeditor_status').val();
        //var MR_status = $('#MR_status').val();
	$("input[name='orderBox[]']:checked").each(function()
	{
            order = [];
            order['id'] = $(this).val();
            
            
            //VERRUE :  Cas d'une commande mal passée dont le statut s'affiche "--"
            //On teste l'existence de la balise de sélection des statuts pour cette commande
            //On ne met pas cette commande dans le tabchecked
            if (($("select[class='selectUpdateOrderState-" + $(this).val() + "']")).length)
                {
                    tabChecked.push(order);
                    
                     // on va chercher le statut de la commande en question
                    // ne pas ommettre le option:first dans la sélection pour le cas où l'utilsateur a fait afficher un autre statut sans le valider (le statut reste affiché mais il n'est pas correct)
                    var current_order_status = $("select[class='selectUpdateOrderState-" + $(this).val() + "'] option:first").prop('class').match(/\d+$/)[0];
                    //var reference = $(this).parent().parent().children('td').eq(3).text().trim();
                    var carrier = $(this).parent().parent().children('td').eq(5).find('img').data('carrier');

                    // Si commande expeditor avec un statut différent de celui paramétré dans expeditor : on enregitre l'erreur
                    if(carrier == 'Expeditor' && (expeditor_status != current_order_status))
                        {
                        erreurs += $('#translate_order_status_error').val() + $(this).val() + ' : ' + $('#translate_order_status_error_EXP').val() + ' : ' + $("select[class='selectUpdateOrderState-" + $(this).val() + "'] option[class='selectedOrderState-" + expeditor_status + "']").text() + '<br/>';
                        }
                    // Si commande MR avec un statut différent de celui paramétré dans MR : on enregitre l'erreur
//                    if(carrier == 'MR' && (MR_status != current_order_status))
//                        {
//                        erreurs += $('#translate_order_status_error').val() + $(this).val() + ' : ' + $('#translate_order_status_error_MR').val() + ' : ' + $("select[class='selectUpdateOrderState-" + $(this).val() + "'] option[class='selectedOrderState-" + expeditor_status + "']").text() + '<br/>';
//                        }
                }
	});

	// Si aucune commande séléctionnée
	if (tabChecked.length === 0){
		jAlert ($('#transtation_select_least_one_order').val(), $('#transtation_alert').val());
		return false;
	}

	// On remplis la fenetre
	var tableContent = '<table style="text-align: center;" class="table_popup">\n\
                                <tr>\n\
                                    <th>ID</th>\n\
                                    <th>' + $('#transtation_weight').val() + '</th>\n\
                                    <th id="insurance_column">' + $('#translation_other_parameter').val() + '</th>\n\
                                </tr>';
        for (var i in tabChecked)
        {
            
		var carrier =  $('input[value="'+tabChecked[i]['id']+'"]').parent("td").parent("tr").find("img.carrier_image").attr("alt");
		
                //Pour une commande MR, on propose le choix de la police d'assurance.
                if(carrier === 'MR')
                    {
                        // Les assurances de mondial relay peuvent avoir 6 niveaux, dont le 0 qui correspond à aucune assurance.
                        // Voir mondialrelay\views\templates\admin\generate_tickets.tpl lignes 90 à 96.
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
                //Pour une commande Expeditor, on ne propose pas le choix de la police d'assurance mais on propose de choisir non-standard size
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

	// Progress bar n'est pas sur certaines versions de Prestahsop
	if ($.isFunction($('#barreLoading').progressbar))
			$('#barreLoading').progressbar();
                    
	$("#dialog-confirmWeight").dialog ("open");   
}

var PDF = [];
var erreurs = '';

// Récupération des commandes cochés et envoi à tous les expéditeurs (appellé par la popup dialog de confirmation des poids)
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
                    // Poids
                    weight_list.push($('.check-confirmWeight-'+tabChecked[i]['id']).val()+'-'+tabChecked[i]['id']);
                    
                    // Ids
                    ids.push(tabChecked[i]['id']);
                    
                    // Assurance
                    assurance_list.push($('.assurance_list-'+tabChecked[i]['id']+' option:selected').val()+'-'+tabChecked[i]['id']);
		}
                
		$.ajax({ // Appel ajax à mondial relay
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
//                             console.log('Ok');
//                             console.log(json_success);
                             
                             var translate_mr_done  = $('#translate_mr_done').val();
				$("#textLoading").html(translate_mr_done);

				// Progress bar n'est pas sur certaines versions de Prestahsop
				if ($.isFunction($('#barreLoading').progressbar))
					$('#barreLoading').progressbar({ value: $('#barreLoading').progressbar("option", "value") + 20});

				 var linksMR = '';
				 var deliveryNumbers = '';


                                 // COMMANDES OK
				 // On concatene les liens vers les etiquettes séparés par un espace
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

				 
                                 // COMMANDES NOK
				 //On supprime les commandes en erreur de tabChecked et on ajoute l'erreur
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
                                           //console.log(errorMR);
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
                            // COMMANDES NOK
                            //On supprime les commandes en erreur de tabChecked et on ajoute l'erreur
                            for (var i in json_failed['error'])
                            {
                               if (json_failed['error'][i] !== null)
                               {
                                    tabChecked.splice((tabChecked.indexOf(i)),1);
                               }        
                            }
                                 
                            erreurs += $('#translation_error_call_MR').val();
                            
                            $("#textLoading").html($('#transtation_mr_error1').val());
                       
                            // Progress bar n'est pas sur certaines versions de Prestahsop
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
                    //On définit la propriété standard_size de Expeditor Inet qu'on met à 0 ou à 1 selon si la case a été cochée pour cette commande ou non.
                    order[i] = {'id' : tabChecked[i]['id'], 
                        'weight': $('.check-confirmWeight-'+tabChecked[i]['id']).val(), 
                        'standard_size': ($('[name="non-standard_size_'+tabChecked[i]['id']+'"]').prop('checked'))?0:1};
            }

            $.ajax({ // Appel ajax à expeditor inet
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

                        // Progress bar n'est pas sur certaines versions de Prestahsop
                        if ($.isFunction($('#barreLoading').progressbar))
                                $('#barreLoading').progressbar({ value: $('#barreLoading').progressbar("option", "value") + 20});

                        // SI expeditor retourne bien un CSV de commande traitées
                        if (typeof (data) !== 'undefined')
                        {
                            var retour = [];

                            // On sépare toutes les lignes du CSV
                            var csv = data.split('\n'); 
                            csv.splice(csv.length -1, 1);

                            // Traitement de chaque ligne
                            for (var i in csv)
                            { 
                                // Récupération de chaque valeur
                                var tmp = csv[i].split(";");

                                // Récup numéro expé
                                var id_exp = tmp[1].substring(4, tmp[1].length-1);

                                // Vérification si la commande retournée fait partie de celles sélectionnées
                                for (var j=0; j< tabChecked.length; j++)
                                {
                                    if(id_exp == tabChecked[j]['id'])
                                    {
                                        // Enregistre la ligne de CSV expéditor à retourner
                                        retour.push(csv[i]);

                                        // Commande traitée, on la supprime de la liste
                                        tabChecked.splice(j,1);
                                    }
                                }
                            }
                            //console.log(erreurs);
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
                       
                            // Progress bar n'est pas sur certaines versions de Prestahsop
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
            PDF = tabChecked.slice(0, tabChecked.length);
            
            $("#textLoading").html($().val('#translate_other_done'));
            
            // Progress bar n'est pas sur certaines versions de Prestahsop
            if ($.isFunction($('#barreLoading').progressbar))
                $('#barreLoading').progressbar({ value: 100}); 
            
            var ids = [];
            for (var i in tabChecked)
            {
                // Ids
                ids.push(tabChecked[i]['id']);
            }
            // Commandes non traitées : on essaye de les passer à TNT après rechargement : HOOK TNT
            $('#idOthers').val(ids);
            
            if (erreurs == '')
                erreurs = false;

            //console.log(erreurs);

            $('#hidden_form').prepend('<input type="hidden" id="linkPDF" name="linkPDF" value="'+PDF+'"/>');
            $('#hidden_form').prepend('<input type="hidden" id="newState" name="newState" value="'+4+'"/>');
            $('#hidden_form').prepend('<input type="hidden" id="handle" name="handle" value="' + erreurs + '"/>');
            $('#hidden_form').submit();
	}
}

// Ouverture  en blank
function _blank(url)
{
   window.open(url, '_blank');
}