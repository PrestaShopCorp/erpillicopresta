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

// Récupération du Token pour sécuriser les requetes AJAX
var token = document.location.href.split("token=");
token = token[1].split("#");
token = token[0].split("&");
token = token[0];

// Variables globales
var quantity_changed = false;
var submited = false;
var error = '';
var values;
var localStore = new Array();
var csv = false;

// Affichage des dÃ©clinaisons d'un produit
function expandAll()
{
    $('.product tbody > tr').each(function()
    {
        // Expand all
        var id_product = trim($(this).find('td.id_product').text());
        var token = $('#token').val();

        // Uniquement au premier clic
        if (!submited) 
        {   
            if (getPrestashopMailVersion() == '1.6')
                display_action_details_16(id_product, 'AdminInventory', token, 'details', '');
            else
                display_action_details(id_product, 'AdminInventory', token, 'details', '');
        }
    });
}

// Enregistre les valeurs saisie pour un produit
function saveValues(product)
{
    // RECUPERATION DES DERNIERES VALEURS A JOUR DE LA LIGNE MODIFIEE
    var currentValues = $('input[name=inventory_values]');

    var id_product = 0;
    var id_product_attribute = 0;
    var physical_quantity = 0;
    var prefix = '';

    // RÃ©cupÃ©ration type de gestion de stock
    var advanced_stock_management = $('#advanced_stock_management').val();

    // Ajout du prefix si stock avancÃ©
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

    // QuantitÃ© saisie
    var found_quantity = product.find('td input.filled_quantity').val();

    // Si ';' --> id produit & declinaison
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

    // Si aucune raison choisie, on applique celle par dÃ©faut en fonction de la valeur saisie (+ ou -)
    if (id_reason == -1)
    {
        if (parseInt(trim(found_quantity)) >= parseInt(trim(physical_quantity)))
            id_reason = $('#reason_increase').val();
        else
            id_reason = $('#reason_decrease').val();
    }

    // Dans le cas d'un display produit / dÃ©clinaison, on ne traite pas les produits avec dÃ©clinaisons
    if (id_reason != undefined)
        var productLine = 'idproduct==' + id_product + '|' + 'idproductattribute==' + id_product_attribute + '|' +
            'idreason==' + id_reason + '|' + 'area==' + area + '|' + 'subarea==' +  subarea + '|' + 'location==' + location + '|' +
            'physicalquantity==' + physical_quantity + '|' + 'foundquantity==' + found_quantity + '_';

    // Maj values
    currentValues.val(currentValues.val() + productLine);
    currentValues.val(currentValues.val().replace('undefined',''));
}

// Retourne les valeurs enregistrÃ©es pour un id produit
function getSavedValues(productLine, id_product, id_product_attribute)
{
    if (id_product_attribute == '')
        id_product_attribute = 0;

    // Parcours des valeurs enregistrÃ©es pour trouver une Ã©quivalence
    for(var k=0; k<=values.length; k++)
    {
        var loop = false;

        if (values[k] != '' && values[k] != undefined)
        {
            //alert(id_product +'=='+ values[k]["idproduct"] + '&&' + id_product_attribute +'=='+ values[k]["idproductattribute"]);
            if (id_product == values[k]["idproduct"] && id_product_attribute == values[k]["idproductattribute"])
            {
                // Si dÃ©jÃ  traitÃ© une maj de valeur plus rÃ©cente, on passe
                for(var l=0; l<=localStore.length; l++)
                {
                    if (id_product + ';' +id_product_attribute == localStore[l])
                        loop = true;
                }

                // Seulement si on a pas dÃ©jÃ  traitÃ© cette ligne
                if (loop == false)
                {
                    // QuantitÃ©
                    productLine.find('td input.filled_quantity').val(values[k]["foundquantity"]);

                    // Emplacement
                    productLine.find('td input[name=location]').val(values[k]["location"]);

                    // Zone
                    productLine.find('td select[name=area] option')
                        .removeAttr('selected')
                        .filter('[value='+values[k]["area"]+']')
                        .attr('selected', true)

                    // Sous zone
                    productLine.find('td select[name=subarea] option')
                        .removeAttr('selected')
                        .filter('[value='+values[k]["subarea"]+']')
                        .attr('selected', true)

                    // Ajout de la ligne au tableau des lignes traitÃ©es
                    localStore.push(id_product + ';' +id_product_attribute);
                }
            }
        }
    }
}

// Supprime une valeur gap stock en hidden
function deleteGapValue(ids, name)
{
    $('#gap_values').val($('#gap_values').val().replace(ids+'|', ''));
    $('#gap_values').val($('#gap_values').val().replace(name+'__', ''));
}

// Inventaire des donnÃ©es enregistrÃ©es
function makeInventory()
{

    // Recuperation des valeurs necessaires
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
    // Sur le passage de la souris sur une ligne de produit, on stock l'id produit ciblÃ© dans un hidden
    $('.row_hover').mouseover(function()
    {
        var id_product = $($(this).find('td.id_product')).text();
        $('#selectedProductId').val(id_product);

        // RAZ id dÃ©clinaison
        $('#selectedProductAttributeId').val('0');
    });

    // Sur le passage de la souris sur une ligne de dÃ©clinaison, on stock l'id dÃ©clinaison (id_product) ciblÃ© dans un hidden
    $(".action_details").live('mouseover', function()
    {
        // RÃ©cupÃ©ration des id produit & dÃ©clinaison puis split
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
    
    // Passage de la souris sur une ref fournisseur principale, on affiche la liste de TOUTES les ref four du produit -- PRODUIT PRINCIPAL
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

    // Application de la quantitÃ© en masse
    $('#desc-product-duplicate,#page-header-desc-product-duplicate').click(function()
    {
        var advanced_stock_management = $('#advanced_stock_management').val();
        var jstring = (advanced_stock_management == 1) ? $("#trad_confirm").val() + '<br />' + $("#trad_advancedstock_warning").val() : $("#trad_confirm").val() + '<br />' + $("#trad_classic_warning").val();
        
        // Confirmation avant action
        jConfirm(jstring, 'Attention', function(event)
        {
            // Si Ok..
            if(event)
            {
                // Application de la quantitÃ©
                if (!quantity_changed)
                {
                    // Affiche les dÃ©clinaisons
                    expandAll();

                    $('.product tbody > tr').each(function()
                    {
                        // On ignore les lignes de sÃ©pÃ©rations vides ..
                        if ($(this).find('td').length > 1)
                        {
                            // Application quantitÃ©
                            var prefix = '';

                            // Si stock avancÃ©, on rÃ©cupÃ¨re la quantitÃ© physique
                            if (advanced_stock_management == 1)
                                prefix = 'physical_';

                            var physical_quantity = trim($(this).find('td.' + prefix + 'quantity').text());
                            var found_quantity = $(this).find('td input.filled_quantity');

                            // Si on trouve '', on est peut Ãªtre sur une dÃ©clinaison : on cherche dans un span
                            if (physical_quantity == '')
                                    physical_quantity = trim($(this).find('td span.' + prefix + 'quantity').text());

                                                if (physical_quantity == '')
                                                        physical_quantity = '0';
                            if(physical_quantity != '')
                                found_quantity.val((physical_quantity !='--') ? physical_quantity : '0');

                            // Sauvegarde valeur
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

    // CrÃ©ation  et gestion de la dialog popup de sÃ©lection d'un container
    $("#dialog-select_container").dialog({
        autoOpen: false,
        show: "clip",
        hide: "clip",
        width: "550",
        position: ['center', 'center'],
        buttons:
            [
                // Annulation
                {
                    text: $("#trad_cancel").val(),
                    click: function() {
                        $(this).dialog("close");
                    }
                },

                {
                        text: $("#trad_validate").val(),
                        // Nouvel inventaire
                        click: function()
                        {
                            // Si inventaire classique
                            if (!csv)
                            {
                                if (($('.selected td.name input').val() == undefined || $('.selected td.name input').val() == "") && $('.selected td.name').text() == "")
                                {
                                        jAlert($("#trad_emptyinventoryname").val());
                                }
                                else
                                {
                                        var gap_values = $('#gap_values');
                                        // Over gap! on demande confirmation
                                        if (gap_values.val() != '_')
                                        {
                                            // RAZ la liste des produits en depassement
                                            $("#dialog-confirm_inventory ul").empty();

                                            // On récupère le tableau des id|name
                                            gap_values = gap_values.val().split('_');
                                            //Pour chaque couple id|name
                                            for(var i = 0; i<= gap_values.length; i++)
                                            {
                                                //On vérifie que le couple n'est pas vide
                                                if (gap_values[i] != undefined && gap_values[i] != '')
                                                {
                                                     //On récupère le tableau id, name
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
                                            // Au moins un produit Ã  modifier
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

    // CrÃ©ation  et gestion de la dialog popup de confirmation d'inventaire'
    $("#dialog-confirm_inventory").dialog({
        autoOpen: false,
        show: "clip",
        hide: "clip",
        width: "500",
        buttons:
            [
				{
                // Annulation
					text: $("#trad_cancel").val(),
					click: function()
					{
						$(this).dialog("close");
					}
				},

                // Confirmation
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

    // Nouvel inventaire
    $('#desc-product-save, #desc-product-save-and-stay, #page-header-desc-product-save, #page-header-desc-product-save-and-stay').click(function(e)
    {
        // Affichage de la box de sÃ©lection d'un nouveau container
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

    // SÃ©lection d'un inventaire, ajoute la classe 'selected' sur la ligne pour le repÃ©rer
    $('input[name=id_inventory]').click(function()
    {
        // Parcours de la table et suppression des class selected
        $('#tbl_container tbody > tr').each(function()
        {
            $(this).removeClass('selected');
        });

        // Ajout de la classe sur celui sÃ©lectionnÃ©
        $(this).parent().parent().addClass('selected');
    });

    // VÃ©rification de l'Ã©cart de stock et enresitrement des valeurs saisies
    $(".filled_quantity").live(
    {
        focusout:function()
        {
            // Initialisation
            var prefix = '';
            var physical_quantity = 0;
             var name = '';

            // RÃ©cupÃ©ration type de gestion de stock
            var advanced_stock_management = $('#advanced_stock_management').val();

            // Ajout du prefix si stock avancÃ©
            if (advanced_stock_management == 1)
                prefix = 'physical_';

            // Valeur d'Ã©cart de stock max en conf
            var gap_stock = $('#gap_stock').val();

            // QuantitÃ© saisie
            var found_quantity = $(this).parent().parent().find('td input.filled_quantity').val();

            // RÃ©cupÃ©ration quantitÃ© physique
            // Si pas d'id produit trouvÃ©, on est sur une dÃ©clinaison'
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
            
            // VERIRICATION DE L'ECART DE STOCK
            // Si entier
            if (isInt(found_quantity) && Number(found_quantity) >= 0)
            {
                // Si Ã©cart plus important que celui parametrÃ©, enregistrement, encadrÃ© rouge et  alert
				if (gap_stock != '' && gap_stock != 0)
				{
					if (Math.abs((found_quantity - physical_quantity)) >= gap_stock)
					{
						jAlert($('#trad_quantityerror').val() + ' ' + gap_stock);
						
						$(this).addClass('overGap');
						
						deleteGapValue(ids, name);
						$('#gap_values').val($('#gap_values').val() + ids + '|' + name + '__');
					}
					else // Sinon suppression valeur et retrait class encadrÃ© rouge
					{
						deleteGapValue(ids, name);
						$(this).removeClass('overGap');
					}
				}
            }
            else
            {
				// => y a des lettres
                if (found_quantity != '')
                    jAlert($("#trad_onlyinteger").val());
				else // => c'est vide
				{
                                            // On recupere l'id produit et l'id declinaison s'il y en a une

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

						// On recupere "l'historique" de inventory_values pour supprimer les entrees de l'id product et id product atribute concernes
						values  = $('#inventory_values').val().split("_");
						
						// Creation du tableau qui va contenir les chaines
						last_values = new Array();
						for(var i=0; i <= values.length; i++)
						{
							if (values[i] != undefined && values[i] != '')
								if (id_product_attribute != -1 && values[i].indexOf('idproduct=='+id_product+'|idproductattribute=='+id_product_attribute) == -1) // Si ce n'est pas la bonne declinaison du bon produit
									last_values.push(values[i]); // On peut garder cette chaine
								else if (id_product_attribute == -1 && values[i].indexOf('idproduct=='+id_product+'|idproductattribute==0') == -1) // Si ce n'est pas le bon produit
									last_values.push(values[i]);
						}
						
						// Retourne la chaine complete separeee par des "/"
						last_values = last_values.join('_');
						// On ne garde donc que les chaines de valeur des autres produits et on remplace ce qui se trouvait la avant
						$('#inventory_values').val(last_values);
						
						deleteGapValue(ids, name);
						$(this).removeClass('overGap');
				}
				$(this).parent().parent().find('td input.filled_quantity').val('')
            }

            // ENREGISTREMENT DE LA VALEUR SAISIE
            if (found_quantity != '')
            {
                saveValues($(this).parent().parent());
            }
        }

    });

    // Changement de l'emplacement : vÃ©rification qu'il ne soit pas dÃ©jÃ  pris
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

        // ENREGISTREMENT DE LA VALEUR SAISIE
        saveValues($(this).parent().parent());
    });

    // Changement de raison de mouvement : on save
    $('td select[name=reason]').live("change", function()
    {
        saveValues($(this).parent().parent());
    });

    // Affichage des dÃ©clinaisons d'un produits, rÃ©cupÃ©ration des valeurs dÃ©jÃ  enregistrÃ©s
    $('a[id^="details_details_"]').click(function()
    {
        var id_product = trim($(this).parent().parent().find('td.id_product').text());

        // Recherche de toutes les lignes de dÃ©clinaisons
        $('table.product tbody > tr.details_details_'+id_product).each(function()
        {
            // On ne prends pas en compte les lignes de sÃ©paration
            if ($(this).find('td').length > 1)
            {
                var ids = $(this).find('td span.id_product').text();
                var id_product_attribute = ids.split(';')[1];

                // Si on a une valeur de gap enregistrÃ©, on encadre le champs quanitÃ© en rouge
                var gap_values = $('#gap_values').val();

                if (gap_values.indexOf(ids) != -1)
                    $(this).find('td input.filled_quantity').addClass('overGap');

                // Enregistrement des valeurs saisis sur le produit
                getSavedValues($(this), id_product, id_product_attribute);
            }
        });
    });

    // AprÃ¨s filtre ou pagination, rÃ©cupÃ©ration des valeurs saisies et enregistrÃ©es
    $('table.product').ready(function()
    {
        // RÃ©cupÃ©ration des lignes d'inventaires
        values  = $('#inventory_values').val().split("_");
        values = values.reverse();

        for(var i=0; i<= values.length; i++)
        {
            if (values[i] != '' && values[i] != undefined)
            {
                // RÃ©cupÃ©ration des colonnes
                values[i] = values[i].split("|");

                for(var j=0; j<=values[i].length; j++)
                {
                    if (values[i][j] != '' && values[i][j] != undefined)
                    {
                        // RÃ©cupÃ©ration du contenu de chaque celulle
                        var attributes = values[i][j].split("==");

                        // values[ligne][colonnes] = valeur
                        values[i][attributes[0]] = attributes[1];
                    }
                }
            }
        }

        // Parcours du tableau pour afficher les valeurs enregistrÃ©es
        $('table.product tbody > tr').each(function()
        {
            var ids = trim($(this).find('td.id_product').text());

            var id_product = 0;
            var id_product_attribute = 0;

            // Si ';' --> id produit & declinaison
            if (ids.indexOf(';') != -1)
            {
                ids = ids.split(';');
                id_product = ids[0];
                id_product_attribute = ids[1];
            }
            else
                id_product = ids;

            var gap_values = $('#gap_values').val();

            // Si on a une valeur de gap enregistrÃ©, on encadre le champs quanitÃ© en rouge
            if (gap_values.indexOf('_' + trim($(this).find('td.id_product').text()) + '|') != -1)
                $(this).find('td input.filled_quantity').addClass('overGap');

            // Enregistrement des valeurs saisis sur le produit
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
