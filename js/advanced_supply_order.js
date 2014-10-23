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

$('document').ready(function()
{
    
    /*
     * Blocage de la modification du fournisseur une fois que la commande fournisseur est crée
    **/
    if( $('#supply_order_form select#id_supplier').length > 0 && $('#supply_order_form input#id_supply_order').length > 0 )
    {
        //Récupération de l'id du fournisseur 
        var id_supply = $('#supply_order_form select#id_supplier').val();
        
        /*
         * Modification pour rendre le fournisseur inchangeable
         * Info : l'attribut readOnly n'est pas disponible 
         * Création d'un bouton hidden qui contient la valeur du fournisseur
         * Et changement des name et id du champs désactivés
        */
        $('#supply_order_form select#id_supplier').attr('disabled', true)
                                                  .attr('id', 'id_supplier_disabled')
                                                  .attr('name', 'id_supplier_disabled')
                                                  .after('<input type="hidden" name="id_supplier" id="id_supplier" value="'+id_supply+'" />');
    }
    
    /*
    *  Permet de récupérer l'escompte d'un fournisseur dans l'ecran de création de commande fournisseur
    */
    $('select#id_supplier').change( function(){
        
        $.ajax({
                type: 'GET',
                url: '../modules/erpillicopresta/ajax/ajax.php',
                async: true,
                cache: false,
                dataType : "json",
                data : { 
                    'task' : 'supplier',
                    'action' : 'getSupplier',
                    'id_supplier' :$(this).val(),
                    'token': token
                },
                success: function(jsonData)
                {
                    if( jsonData != null)
                    {
                        if(jsonData.error != undefined)
                             jAlert(jsonData.error);
                        else {
                            
                            //Récupération des données sinon zéro 
                            var escompte = $.isEmpty( jsonData.escompte ) ? '0.00' : parseFloat(jsonData.escompte).toFixed(2) ; 
                            var shipping_amount = $.isEmpty( jsonData.shipping_amount ) ? '0.00' : parseFloat(jsonData.shipping_amount).toFixed(2) ; 
                            var franco_amount = $.isEmpty( jsonData.franco_amount )?   '0.00' : parseFloat(jsonData.franco_amount).toFixed(2); 
                            var delivery_time = $.isEmpty( jsonData.delivery_time )?   '0' : parseInt(jsonData.delivery_time); 
                            var discount_amount = $.isEmpty( jsonData.discount_amount )?   '0' : parseFloat(jsonData.discount_amount); 
                          
                            //Récupération du total du prix d'achat HT des produits HT avec reduction
                            var total_product_price_te_with_discount = $('.txt_total_product_price').html();
                            total_product_price_te_with_discount = parseFloat(total_product_price_te_with_discount).toFixed(2);       
                            total_product_price_te_with_discount =  total_product_price_te_with_discount.replace(/ /g,'') !=  "" ? total_product_price_te_with_discount.replace(/ /g,'') : '0.00';
                            var amount_to_franco_with_produc_discount = (franco_amount - total_product_price_te_with_discount).toFixed(2);
                                                 
                            //get date delivery expected
                            var date_delivery_expected = getDateDelivery(delivery_time)
                                                 
                            //Set des valeurs pour le formule 
                            $('#escompte').val(escompte).addClass('updatedField');
                            $('#shipping_amount').val(shipping_amount).addClass('updatedField');
                            $('#global_discount_amount').val(discount_amount).addClass('updatedField');
                            $('input[name=date_delivery_expected]').val(date_delivery_expected).addClass('updatedField');
                            
                            //set des valeurs pour le franco
                            $('.txt_franco_amount').text(franco_amount).addClass('updatedField').css('padding', '2px 5px 2px 5px');
                            $('.txt_amount_to_franco').text(amount_to_franco_with_produc_discount).addClass('updatedField').css('padding', '2px 5px 2px 5px'); 
                        }
                    }
                    else
                        showErrorMessage( $('#transtation_no_data').val() );
                }
        });
    });
    
    /*
    *   Execution du sript au chargement de la page
    */
    if( $('input[name=global_discount_type]').length > 0)
    {
        var global_dicounr_type_checked = $('input[name=global_discount_type]:checked').val();
        toggleGobalDiscount(global_dicounr_type_checked); 
    }
    
    /*
    *  Permet de permuter entre type de remise global
    */
   $('input[name=global_discount_type]').change(function(){
       
        toggleGobalDiscount($(this).val());
   });
   
   
   //Affichage des popups utilisant le plugins Cluetip
   $('.cluetip').cluetip({
        showTitle: true,
        sticky: true,
        closePosition: 'title',
        ajaxCache:false,
        width: '850px',
        tracking: true,
        waitImage:true,
        arrows: true,
        closeText: '<img src="../img/admin/cross.png">'
   }); 
   
   //Affichage des popups utilisant le plugins Cluetip
   $('.cluetip-min').cluetip({
        showTitle: true,
        sticky: true,
        closePosition: 'title',
        ajaxCache:false,
        width: '450px',
        tracking: true,
        waitImage:true,
        arrows: true,
        closeText: '<img src="../img/admin/cross.png">'
   }); 
   
     
   //Popup pour l'affichage des prix par fournisseur
   $("body").delegate(".cluetip-supply-price:not(.hasTooltip)", "mouseover", function (event) {

        $('.cluetip-supply-price').cluetip({
            showTitle: true,
            closePosition: 'title',
            ajaxCache:false,
            sticky: false,
            width: '250px',
            tracking: true,
            waitImage:true,
            cluezIndex: 110, 
            closeText: '<img src="../modules/erpillicopresta/img/cross.png">'
        }).addClass("hasTooltip").trigger("mouseover");
        event.preventDefault();
   });
    
    
   //Si la div avec cet ID existe sur la page 
   if( $('#dialog_select_product').length > 0) 
   {
          $( "#dialog_select_product" ).dialog({
                autoOpen: false,
                width:'auto',
                buttons: 
                [
                    {
                        text: $('#transtation_add_to_so').val(),
                        id: "btn_multipleSelected",
                        click: function() {

                            var product_checked  = false;
                            
                            //On parcour la liste des produits en ne gardant que les produits coché
                            $('#dialog_select_product #content table tr').each( function(index){

                                 //Si le produit a été coché
                                 if( $(this).find('input.select_product').is(':checked'))
                                 {
                                      //On récupère le Json sous fomr de string
                                      var product_json = $(this).find('.product_json').html();

                                      //Transforme en objet Json
                                      product_infos = $.parseJSON(product_json);

                                      //On remplit de champs pour réutiliser la fonction existante
                                      $('#cur_product_name').val(product_infos.name);

                                      // call to addProduct function ( in form.tpl or list.php depending to the source)
                                      addProduct();

                                      product_checked = true;
                                 }   
                            })

                            if( product_checked )
                                $( this ).dialog( "close" ); //On ferme la popup
                            else
                                showErrorMessage( $('#transtation_select_one_product').val() );
                        }
                    },
                    {
                        text: $('#transtation_cancel').val(),
                        click: function() {$( this ).dialog( "close" );}
                    }
                ]
        });
   }
    
    
    /*
     * Selection multiple de produit
    */
    $('.multiple_selection').click(function(){
       
        var loader = $(this).next('.multiple_selection_loder'); 
                
        displayLegendeOnDialog();
        loader.show();
        
        $.ajax({
                type: 'GET',
                url: 'index.php',
                async: true,
                cache: false,
                data: 
                {
                    controller : 'AdminAdvancedSupplyOrder',
                    task : 'getProductsForSupplyOrder',
                    id_supplier : $('#id_supplier').val(),
                    id_currency : $('#id_currency').val(),
                    id_categorie : $('#id_categorie').val(),
                    id_manufacturer : $('#id_manufacturer').val(),
                    id_warehouse : $('#id_warehouse').val(),
                    ids : $('#product_ids').val(),
                    ajax: 1,
                    token:token
                }, 
                success: function(data)
                {
                     $( "#dialog_select_product #content table tbody" ).html(data);
                     $( "#dialog_select_product" ).dialog( "open" );
                     loader.hide();
                }
        });
        
        return false;
    });
    
    /*
     * Dans la selection multiple : permet de selectionner tous les produits de la liste
    */
    $('#select_all_product').live('click', function(){
          $(this).parent().parent().parent().find(':checkbox').attr('checked', this.checked);
    });
    
    
    /*
     * Gestion du produit poubelle si on tape "divers" dans le champs auto-completion
     *
    */
    $('#cur_product_name').keyup( function() {
        
        if( $(this).val() == 'divers')
        {
            addProductTrash();
        }        
    })
    
    // Sélection id 5 "order receive completely", affichage des champs de factu date et numéro
    $('#id_supply_order_state').change(function()
    {
        if($('#id_supply_order_state option:selected').val() == 5)
            $('#invoice').removeClass('invoice');
        else
            $('#invoice').addClass('invoice');
    });
    
    // Passage champs date factu en datepicker
    $('.date_to_invoice').datepicker(
    {
        defaultDate:new Date,
        dateFormat:'yy-mm-dd'
    });
	
    $("input[name=date_delivery_expected]").datepicker({        
                               dateFormat : 'yy-mm-dd',
                               minDate: '0d'
    });

    if ($("table.supply_order .datepicker").length > 0) {
            $("table.supply_order .datepicker").datepicker({
                    prevText: '',
                    nextText: '',
                    dateFormat: 'yy-mm-dd'
            });
    }
                        
    // Controle vérification numéro de facture et date rempli
    $('#_form').live('submit', function(e)
    {
        if($('#id_supply_order_state option:selected').val() == 5)
        {   
            if($('#invoice_number').val() == '' || $('#date_to_invoice').val() == '')
            {
                jAlert( $('#transtation_fill_invoice_number').val() );
                e.preventDefault();
                location.reload(); 
            }
        }
    });
    
    // Création  et gestion de la dialog popup
    $("#dialog-wholesale").dialog({
        autoOpen: false,
        width: "500"
        
    });
    
    // Affiche dialog de confirmation MAJ wolesale price
    $('.wholesale_update').live('click', function()
    {
        var tr = $(this).parent().parent().parent();
        
        $("#dialog-wholesale").dialog({ show: 'clip'});
        $("#dialog-wholesale").dialog({ hide: 'clip'});         
            
        $("#dialog-wholesale").dialog({ buttons: [
            {
                text: $('#transtation_cancel').val(),
                click: function() 
                {
                    $(this).dialog("close"); 
                }
            },
            {
                text: "Ok",
                click: function() 
                {
                    // prix d'achat en remplacant virgule par point, et on dégage le € en parsint
                    var wholesale_price = parseFloat(tr.find('td input.input_price').val().replace(',', '.'));
                    var ids = trim(tr.find('td.ids').text());
                   
                    // Maj price
                    apply_new_wholesalePrice(wholesale_price, ids);
                    $(this).dialog("close"); 
                }
            }
        ]});
        $("#dialog-wholesale").dialog().dialog("open");
    });
    
    // MAJ réception ou ANNULATION réception
    $('.receipt_update, .receipt_cancel').live('click', function()
    {        
        var _this = $(this);
        var action = $(this).attr('class');
        var tr = $(this).parent().parent().parent();
        
        var wholesale_price = tr.find('td input.input_price').val();
        var discount_rate = tr.find('td input.discount_rate_change').val();
        var quantity = tr.find('td input.quantity').val();
        var id_employee = tr.find('input.id_employee').val();
        var employee_lastname = tr.find('input.employee_lastname').val();
        var employee_firstname = tr.find('input.employee_firstname').val();
        var id_supply_order_state = tr.find('input.id_supply_order_state').val();
        var id_supply_order_receipt_history = tr.find('input.id_supply_order_receipt_history').val();
        var id_erpip_supply_order_receipt_history = tr.find('input.id_erpip_supply_order_receipt_history').val();
        var id_supply_order_detail = tr.find('input.id_supply_order_detail').val();	
        var id_stock_mvt = tr.find('input.id_stock_mvt').val();	
	
        $.ajax({
                type: 'GET',
                url: '../modules/erpillicopresta/ajax/ajax.php',
                async: true,
                cache: false,
                data: 
                {
                    task: 'supplier',
                    action : action,
                    wholesale_price : wholesale_price,
                    discount_rate : discount_rate,
                    quantity:quantity,
                    id_employee:id_employee,
                    employee_firstname:employee_firstname,
                    employee_lastname:employee_lastname,
                    id_supply_order_state:id_supply_order_state,
                    id_supply_order_receipt_history:id_supply_order_receipt_history,
                    id_erpip_supply_order_receipt_history:id_erpip_supply_order_receipt_history,
                    id_supply_order_detail:id_supply_order_detail,
                    id_stock_mvt:id_stock_mvt,
                    token:token
                }, 
                success: function(data)
                {
                    if(data == '1')
                    {
                        // Si annulation, on grise la ligne et supprime les boutons d'actions
                        if(action == 'receipt_cancel')
                        {
                            _this.parent().parent().parent().find('td input').attr('disabled','disabled');
                            _this.parent().parent().parent().find('td.action').append('<p><i>Annulée</i></p>');
                            _this.parent().parent().parent().find('img').remove();
                            jAlert($('#trad_receiptcanceled').val());
                        }
                        else
                            jAlert($('#trad_receiptupdated').val());
                    }
                    else
                    {
                        if(action == 'receipt_cancel')
                            jAlert( $('#transtation_error_receipt1').val() );
                        else
                            jAlert( $('#transtation_error_receipt2').val() );
                    }
                }
        });
    });
   
    
    // Création  et gestion de la dialog popup de création d'une image de stock
    $("#dialog-billing").dialog({
        autoOpen: false,
        width: "500"
    });
    
    // Facturation groupée
    $('#desc-supply_order-duplicate').click(function()
    {
        var isChecked = false;
        $('table tbody > tr').each(function()
        {
            //Si commande sélectionnée
            if($(this).find('td input.orderSelected').is(':checked'))
                isChecked = true;   
        });
        
        // Si au moins une commande est sélectionnée
        if(isChecked)
        {
            $("#dialog-billing").dialog({ show: 'clip'});
            $("#dialog-billing").dialog({ hide: 'clip'});         

            $("#dialog-billing").dialog({ buttons: [
                {
                    text: $('#transtation_cancel').val(),
                    click: function() 
                    {
                        $(this).dialog("close"); 
                    }
                },
                {
                    text: "Ok",
                    click: function() 
                    {
                        createBilling();
                        $(this).dialog("close"); 
                    }
                }
            ]});
            $("#dialog-billing").dialog().dialog("open");
        }
        else
            jAlert( $('#transtation_select_one_order').val() );
    });
    
    
    // MAJ du Montant total du prix d'achat HT des produits (avec réductions produit & taxe)
    $('.unit_price, .quantity_expected, .tax_rate').live('change',function()
    {
        // Maj total commande
        //majTotalOrder();
        
        majTotalPrice($(this).parent().parent());
    });
    
    
    // Change discount rate --> maj discount amount
    $('.discount_rate_product').live('change',function()
    {
        var unit_price = $(this).parent().parent().find('td input.unit_price').val();
        var quantity = $(this).parent().parent().find('td input.quantity_expected').val();
        var total_price = (unit_price * quantity);
        var discount_amount = (total_price * $(this).val()) / 100;
        var tax_rate = $(this).parent().parent().find('td input.tax_rate').val();
        
        // Total = quantité * prix * taxe
        total_price = ((total_price - discount_amount) * tax_rate)/100 + (total_price - discount_amount);
        
        // Maj discount amount
        $(this).parent().parent().find('td input.discount_amount_product').val(discount_amount.toFixed(2));
        
        // MAJ Total price
        $(this).parent().parent().find('td span.total_product').text(total_price);
        
        // Derniere moduif de type AMOUNT
        $('#last_discount_change').val('rate');
        
        // Maj total commande
        majTotalOrder();
    });
    
    //    
    
    // Change discount amount --> maj discount rate
    $('.discount_amount_product').live('change',function()
    {
        var unit_price = $(this).parent().parent().find('td input.unit_price').val();
        var quantity = $(this).parent().parent().find('td input.quantity_expected').val();
        var total_price = (unit_price * quantity);
		
        var discount_amount = $(this).val();
        var discount_rate = ($(this).val() / total_price) * 100;
        var tax_rate = $(this).parent().parent().find('td input.tax_rate').val();
        
        // Total = quantité * prix * taxe
        total_price = ((total_price - discount_amount) * tax_rate)/100 + (total_price - discount_amount);
        
        // Maj discount amount
        $(this).parent().parent().find('td input.discount_rate_product').val(discount_rate.toFixed(2));
        
        // MAJ Total price
        $(this).parent().parent().find('td span.total_product').text(total_price);
        
        // Derniere moduif de type RATE
        $('#last_discount_change').val('amount');
        
        // Maj total commande
        majTotalOrder();
    });
    
    
    
    // Sélection de produit avec une quantité --> maj total price ligne & commande
    $('#btn_multipleSelected').click(function()
    {   
        //majTotalOrder();
    });
    
    // Suppression d'une ligne de produit --> maj total price
    $('.removeProductFromSupplyOrderLink').live("click", function()
    {
        // Maj total
        setTimeout(function() 
        {
            majTotalOrder();
        }, 100);
    });
    
    
    // Popup sélection multiple, saisie quantité, enregistrement dans le json product_infos
    $('.quantity_ordered').live("change",function()
    {
        var json = $(this).parent().parent().find('td.product_json').text();
        
        //Transforme en objet Json
        json = $.parseJSON(json);0  
        json.quantity_expected = $(this).val();
        
        // Enregistre avec la modification
        $(this).parent().parent().find('td.product_json').text(JSON.stringify(json));
        
        // Sélection auto de la checkbox associée
        $(this).parent().parent().find('td input.select_product').prop('checked', true);
    });
	
	    // Popup sélection multiple, saisie quantité, enregistrement dans le json product_infos
    $('.comment').live("change",function()
    {
        var json = $(this).parent().parent().find('td.product_json').text();
        
        //Transforme en objet Json
        json = $.parseJSON(json);0  
        json.comment = $(this).val();
        
        // Enregistre avec la modification
        $(this).parent().parent().find('td.product_json').text(JSON.stringify(json));
        
        // Sélection auto de la checkbox associée
        $(this).parent().parent().find('td input.select_product').prop('checked', true);
    });
    
    
    // Réception de produits. Si aucun produit sélectionné, on bloque
    $('.form').submit(function(e)
    {
        if($('.supply_order_detail tbody >tr').length > 0)
        {
            var check = false;
            $('.supply_order_detail tbody >tr').each(function()
            {
                if($(this).find('td input.noborder').is(':checked'))
                    check = true;
           });

           if(!check)
               e.preventDefault();
        }
    });
    
    
    // Réception de produit, modification quantité, on coche la check associée
    $('input[name^="quantity_received_today_"]').change(function()
    {
        $(this).parent().parent().find('td input.noborder').prop('checked', true);
    });
    
    // appel ajax si le champ total_price existe 
    if( $('#total_price').length > 0 )
    {
        $.ajax({
            type: 'POST',
            url: '../modules/erpillicopresta/ajax/ajax.php',
            async: true,
            cache: false,
            data:  
            {
                task : 'supplier',
                action : 'getTotalPrice',
                token : token,
                id_supply_order : $('#id_supply_order').val()
            }, 
            success: function(data)
            {
                $('#total_price').html(data);
            }
        });
    }

    // Evenements pour mettre à jour le prix total de la réception si le prix ou la réduction sont modifiés
    $(document).on("change", "input.discount_rate_change", function()
    {
            calcTotalPrice($(this));
    });
    $(document).on("change", "input.input_price", function()
    {
            calcTotalPrice($(this));
    });

    // Affiche/masque l'input de commentaire dans le tableau de la selection multiple
    $(".writeComment").live('click', function()
    {
            if ($(this).parent().find(".comment").css("display") == "none")
                    $(this).parent().find(".comment").css("display", "block");
            else
                    $(this).parent().find(".comment").css("display", "none");
    });

    $('input.quantity_received_today').live('click', function() {
            /* checks checkbox when the input is clicked */
            $(this).parents('tr:eq(0)').find('input[type=checkbox]').attr('checked', true);
    });
    
    /* view description of supply order*/
    $('.selectUpdateSupplyOrderState').change( function(){
        
        var id_supply_order = $(this).parent().find('#id_supply_order').val();
        var supply_order_state = $(this).find('option:selected').text();
        var id_supply_order_state = $(this).val();
        
        $('#dialog-id-supply-order').text(id_supply_order)
        $('#dialog-name-supply-order-state').text(supply_order_state);
        
        $("#dialog-confirmUpdateSupplyOrderState").dialog({
            autoOpen: true,
            show: "clip",
            hide: "clip",
            width: "500",
        });
        
        $("#dialog-confirmUpdateSupplyOrderState").dialog({ buttons: [
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
                        updateSupplyOrderState(id_supply_order,id_supply_order_state );
                        $(this).dialog("close");
                    }
                }
        ]});
        
        
    });
    
    
    if(getPrestashopMailVersion() == '1.6')
    {
        $('.supply_order_detail [id^="details_details"]').click(function(){
            $(this).attr("href",'');
            var token = $('input[name=token]').val();
            var id_supply_order = $('input#id_supply_order').val();
            var id = $(this).attr("id").split('details_details_');                
            display_action_details_16(id[1], 'AdminAdvancedSupplyOrder', token , 'details',{"display_product_history":"1","action":"details"});
            return false;
        });
    }
    
});

// Calcul le total réception
function calcTotalPrice(row)
{
	var wholesale_price = parseFloat(row.parent().parent().find('td input.input_price').val().replace(",", "."));
	var discount_percentage = parseFloat(row.parent().parent().find('td input.discount_rate_change').val());
        var wholesale_price_discount = wholesale_price - (wholesale_price * discount_percentage) / 100;
	var quantity = parseInt(row.parent().parent().find('td input.quantity').val());
	
	var old_total = parseFloat(row.parent().parent().find('td input.last_price').val());
	if (wholesale_price == "" || isNaN(wholesale_price))
		wholesale_price = 0;
 	if (discount_percentage == "" || isNaN(discount_percentage))
		discount_percentage = 0;
	if (quantity == "" || isNaN(quantity))
		quantity = 0;
	var total_price = (wholesale_price - (wholesale_price * discount_percentage / 100)) * quantity;
	if (total_price < 0)
		total_price = 0;
	
        // MAJ total ligne
	row.parent().parent().find('td.total_price').html('<input type="hidden" class="last_price" type="text" size="5" value="' +  total_price.toFixed(2) + '" />' + total_price.toFixed(2) + " €");
	
        // MAJ total réception
	$('#total_price').html(parseFloat($('#total_price').html()) + (total_price - old_total));	
        
        // MAJ Total NET avec remise
        row.parent().parent().find('td.wholesale_price_net').text(wholesale_price_discount + ' €');
}

// Mise a jour des totaux
function majTotalPrice(selector)
{
    // Valeurs du tableau
    var unit_price = parseFloat(selector.find('td input.unit_price').val());
    var quantity = parseInt(selector.find('td input.quantity_expected').val());
    var discount_amount = 0;
    var discount_rate = 0;
    var tax_rate = parseFloat(selector.find('td input.tax_rate').val());
    var shipping_amount = parseFloat($('#shipping_amount'));
    //var shipping_amount_backup = parseFloat($('#shipping_amount_backup'));    
  
    var total_price = (unit_price * quantity);
    
    var last_discount_change = $('#last_discount_change').val();
    
    // MAJ discount rate
    if(last_discount_change == 'amount')
    {
        discount_amount = parseFloat(selector.find('td input.discount_amount_product').val());
        discount_rate = (discount_amount / total_price) * 100;
        selector.find('td input.discount_rate_product').val(discount_rate.toFixed(2));
    }
    else
    {
        discount_rate = parseFloat(selector.find('td input.discount_rate_product').val());
        discount_amount = (total_price * discount_rate) / 100;
        selector.find('td input.discount_amount_product').val(discount_amount.toFixed(2));
    }
    
    // Calcul total produit
    var total = ((total_price - discount_amount) * tax_rate)/100 + (total_price - discount_amount);

    // MAJ total produit
    selector.find('td span.total_product').text(total.toFixed(2));
    
    // Seulement si le franco est différent de 0 car dans ce cas, 0 = pas de franco définit
    if($('.txt_franco_amount').text() != '0,00' && $('.txt_franco_amount').text() != '0,00 €')
    {
        var total_order = 0;
        
        // Calcul total commande en live
        $('#products_in_supply_order tbody > tr').each(function()
        {
            var unit_price = $(this).find('td input.unit_price').val();
            var quantity = $(this).find('td input.quantity_expected').val();
            var discount_amount = $(this).find('td input.discount_amount_product').val();

            // Cumul total commande
            var total_price = (unit_price * quantity);
            total_order += (total_price - discount_amount);
        });
    
        var franco = parseFloat($('.txt_franco_amount').text().replace(' ', ''));
        
        // Calcul Restant franco
        var total_franco = (franco - total_order).toFixed(2);
        total_franco = (total_franco == 1) ? 0 : total_franco; // Hack calcul, quand plus de produit, le résultat donne 1 au lieu de 0

        // MAJ Restant franco
		if (total_franco <= 0)
			$('.txt_amount_to_franco').html('<span style="color: green;">0.00 €</span>');
        else
			$('.txt_amount_to_franco').text(total_franco + ' €');
		
        // Si franco <= 0, frais de ports offerts
        if(total_franco <= 0)
        { 
            // Passage des frais de ports à 0
            if( $('#shipping_amount').data("oldValue") == undefined ||  $('#shipping_amount').data("oldValue") == 0)
            {
                console
                $('#shipping_amount').data("oldValue",$('#shipping_amount').val());
            }
            $('#shipping_amount').val('0');
            total_franco = 0.00;
        }
        else
        {
            // Restaure les frais de ports d'origine
            if( $('#shipping_amount').data("oldValue") != undefined &&  $('#shipping_amount').data("oldValue") != 0)
                $('#shipping_amount').val( $('#shipping_amount').data("oldValue"));
        }
    }
    
    majTotalOrder();
}


// MAJ total commande
function majTotalOrder()
{
    var total_order = 0;

    // Derniere suppressionn, on RAZ les compteurs
    if($('#products_in_supply_order tbody > tr').length == 0)
    {
        $('.txt_amount_to_franco').text($('.txt_franco_amount').text());
        $('.txt_total_product_price').text('0,00 €');
    }
    else
    {
        $('#products_in_supply_order tbody > tr').each(function()
        {
            var unit_price = $(this).find('td input.unit_price').val();
            var quantity = $(this).find('td input.quantity_expected').val();
            var discount_amount = $(this).find('td input.discount_amount_product').val();

            // Cumul total commande
            var total_price = (unit_price * quantity);
            total_order += (total_price - discount_amount);
        });
        
        // MAJ total commande
        $('.txt_total_product_price').text(total_order.toFixed(2) + ' €');
    }
}

// Accrochage de plusieurs commande à une facture
function createBilling()
{
    var orders = [];
    var id_supply_order = -1;
    
    var invoice_number = $('#invoice_number').val();
    var date_to_invoice = $('#date_to_invoice_group').val();

    $('table .supply_order tbody > tr').each(function()
    {
        //Si commande sélectionnée
        if($(this).find('td.id_supply_order input.orderSelected').is(':checked'))
        {
            var id_supply_order = $(this).find('td.id_supply_order input.id').val();
            orders.push(id_supply_order);
        }
    });

     $.ajax({
            type: 'GET',
            url: '../modules/erpillicopresta/ajax/ajax.php',
            async: false,
            cache: false,
            data: 
            {
                action : 'billing',
                orders:orders,
                invoice_number:invoice_number,
                date_to_invoice:date_to_invoice,
				token:token
            }, 
            success: function(data)
            {
                //if(data == '1')
                    showSuccessMessage( $('#transtation_order_linked_billing').val() );
//                else
//                    jAlert('Error while created a new billing');
            }
    });
    
    $('.form').submit();
}

/*
* Permet de cacher ou afficher les remise global selon choix de l'utilisateur
*/
function toggleGobalDiscount(global_dicounr_type)
{   
   $('input#discount_rate').parent().show('slow');
   $('input#discount_rate').parent().prev('label').show('slow');

   $('input#global_discount_amount').parent().show('slow');
   $('input#global_discount_amount').parent().prev('label').show('slow');

   if( global_dicounr_type == 'rate')
   {
       $('input#global_discount_amount').val('0');
       $('input#global_discount_amount').parent().hide('slow');
       $('input#global_discount_amount').parent().prev('label').hide('slow');
   }
   else if( global_dicounr_type == 'amount')
   {
        $('input#discount_rate').val('0');
        $('input#discount_rate').parent().hide('slow');
        $('input#discount_rate').parent().prev('label').hide('slow');
   }
}


/*
 * Permet savoir si l'URL courante contient un paramètre
 * 
*/
$.hasUrlParam = function(name){
    var results = new RegExp('[\\?&amp;]' + name).exec(window.location.href);
    return ( results != null) ? true : false;
}

/*
* Permet de vérifier si une variable est vide
*
*/
$.isEmpty = function(variable){
    
    variable = $.trim(variable);
    if( variable == null || variable == '' || variable == undefined || variable == 'undefined' || variable == '0' || variable == 0)
        return true;
    else
        return false;
}

/*
* Retourne la date de livraison calculé à partir du delai de livraison
*
*/
function getDateDelivery(delivery_time)
{            
    //int var 
    var date_delivery_expected = '';
    
    //Calcul date de livraison
    if( delivery_time > 0)
    {
        //Get date now
        var deliveryDate = new Date();
        
        //Add delevery time 
        deliveryDate.setDate(deliveryDate.getDate() + delivery_time);
        
        //month in javascript start with zero
        var month = deliveryDate.getMonth() + 1 ; 
        
        //Get delevery date
        date_delivery_expected = deliveryDate.getFullYear()+ "-" + ( month < 10 ? '0'+month : month ) + "-" + deliveryDate.getDate();
    }
    
    return date_delivery_expected;
}


/*
 * Fonction pour la gestion du produit "Poubelle"
*/

function addProductTrash()
{                        
    
    var id_supplier = $('#id_supplier').val();
    var id_currency = $('#id_currency').val();
    
    $('#dialog_add_product').load('../modules/erpillicopresta/ajax/addProduct.php', { id_supplier: id_supplier, token:token } );
    $( "#dialog_add_product" ).dialog({
                autoOpen: true,
                width:900,
                buttons: 
                [
                    {
                        text: $('#trad_add').val(),
                        click: function() { 
                            
                            // action a réaliser si soumission du formulaire
                            $(this).find('form').submit( function() {

                                  //Récupération des données
                                  var datas = $(this).serialize();

                                  if(datas != '')
                                  {
                                     $.ajax({
                                       type: "POST",
                                       async: "false",
                                       cache:"false",
                                       url: $(this).attr('action'),
                                       data: datas,
                                       dataType : "json",
                                       success: function(data_new_product){
                                              
                                            $.ajax({
                                                   type: "POST",
                                                   async: "false",
                                                   cache:"false",
                                                   url: '../modules/erpillicopresta/ajax/ajax.php',
                                                   dataType : "json",
                                                   data: {
                                                        id_product: data_new_product.id_product,
                                                        id_supplier: id_supplier,
                                                        id_currency: id_currency,
                                                        action : 'getProduct',
                                                        token : token
                                                   },
                                                   success: function(newProductTrash){

                                                      // remplissage de la variable pour ajouter le nouveau produit
                                                      product_infos = newProductTrash;
                                                      
                                                      //Ajout des nouvelles valeur saisie pour le produit
                                                      product_infos.quantity_expected = data_new_product.quantity_expected;
                                                      product_infos.discount_rate = data_new_product.discount_rate;
                                                      product_infos.tax_rate = data_new_product.tax_rate;

                                                      // on remplit de champs pour réutiliser la fonction existante
                                                      $('#cur_product_name').val(product_infos.name);

                                                      // on appel de la fonction de Prestashop (fichier form.tpl)
                                                      addProduct();
                                                      
                                                      $( "#dialog_add_product" ).dialog("close");
                                                      
                                                    }
                                                 });
                                        }
                                     });
                                  }
                                  
                                  return false;
                            }); 
                            
                            // soumission du formulaire d'ajout de nouveau produit
                            $(this).find('form').submit();
                        }
                    },
                    {
                        text: $('#trad_cancel').val(),
                        click: function() {$( this ).dialog( "close" );}
                    }
                ]
        });
}

// Applique le nouveau prix d'achat
function apply_new_wholesalePrice(wholesale_price, ids)
{
    
    ids = ids.split(';');
    var id_product = ids[0];
    var id_product_attribute = ids[1];
    var id_supplier = $('#id_supplier').val();
    
    $.ajax({
            type: 'GET',
            url: '../modules/erpillicopresta/ajax/ajax.php',
            async: true,
            cache: false,
            data: 
            {
                action : 'majWholesalePrice',
                wholesale_price : wholesale_price,
                id_product : id_product,
                id_product_attribute : id_product_attribute,
                id_supplier:id_supplier,
                task:'supplier',
                token : token
            }, 
            success: function(data)
            {
                if(data == '1')
                    jAlert($('#trad_wholesalepriceok').val());
                else
                    jAlert($('#trad_wholesalepriceko').val());
            }
    });
}

function updateSupplyOrderState (id_supply_order,id_supply_order_state ){

	$.ajax({
            type: 'POST',
            url: '../modules/erpillicopresta/ajax/ajax.php',
            dataType:'json',
            data: {
                'id_supply_order' : id_supply_order,
                'id_supply_order_state' : id_supply_order_state,
                'token': token,
                'action': 'updateSupplyOrderStatus',
                'task':'supplier',
                'id_employee' : $('input#id_employee').val() 
            },
            success: (function (data) {
                 
                 if (data.error == undefined )
                    showSuccessMessage(data.message);
                 else
                     showErrorMessage(data.error);
            })
	});
}

function displayLegendeOnDialog()
{
    var legende = $('.legende_nstock').hide().html();
    $('.ui-dialog-buttonpane').prepend('<div class="legende_nstock">'+legende+'</div>');   
}