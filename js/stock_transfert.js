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

	$('document').ready(function()
	{

	$('a.filter_link').live('click', function(){

		var filter_orderby = $(this).find('.filter_orderby').text();
		var filter_orderway = $(this).find('.filter_orderway').text();
		
		$('input[name="stockOrderway"]').val(filter_orderway);
		$('input[name="stockOrderby"]').val(filter_orderby);
		
		$('#filterForm').submit();
		
		return false;	
	});

	$('input[name="name_or_ean"]').live('change', function(){
		
		$('input.name_or_ean').val($(this).val());
	})

	// Transfert de stock, calcul auto de la quantité restant après transfert
	$('.qte_transfer').keyup(function()
	{
		if ($(this).val() != '')
		{
			// Quantité à transférer
			var qte_transfer = parseInt(trim($(this).val()));
			
			var tr_parent = $(this).parent().parent();
			
			// Quantité actuelle stock A
			var Aphysical_quantity = tr_parent.find('td.physical_quantity').text();
			
			// Id(s) stock A
			var Aids = tr_parent.find('td.ids').text();
                        
                        // get id_stock
			var id_stock_s1 = tr_parent.find('input.id_stock_s1').val();
			var id_stock_s2 = tr_parent.find('input.id_stock_s2').val();
			
			if (qte_transfer == 0)
			{
				setToZero(trim(Aids));
				createListeTranfert();
				return;
			}
			else if  (qte_transfer < 0)
			{
				jAlert($('#trad_negativetransfert').val());
				
                                var new_stock = 0;
                                var Bphysical_quantity = 0;
                                
                                if (trim(tr_parent.find('td.new_stock').text()) != '--')
                                    new_stock = parseInt(trim(tr_parent.find('td.new_stock').text()));
				
                                if (trim(tr_parent.find('td.physical_quantity2').text()) != '--')
                                    Bphysical_quantity = parseInt(tr_parent.find('td.physical_quantity2').text());
                                
				var valeur_precedente = new_stock - Bphysical_quantity;
				
				$(this).val(valeur_precedente);
				return;
			}
			
			// Quantité après transfert stock A
			var Aquantity_after = Aphysical_quantity - qte_transfer;

			// Celulle quantité après
			var container_qte_after = tr_parent.find('td.quantity_after')
			
			// Si futur stock A positif
			if (Aquantity_after >= 0)
			{
				// Application de la nouvelle quantité dans le stock A
				container_qte_after.text(Aquantity_after);
				container_qte_after.addClass('stockAImpact');
		
				// Quantité actuelle stock B
                                var Bphysical_quantity = 0;
				if (trim(tr_parent.find('td.physical_quantity2').text()) != '--')
                                    Bphysical_quantity = parseInt(tr_parent.find('td.physical_quantity2').text());

				// Quantité après transfert stock B
				var Bquantity_after = parseInt(Bphysical_quantity) + parseInt(qte_transfer);

				// Application de la nouvelle quantité dans le stock B
				tr_parent.find('td.new_stock').text(Bquantity_after);
				tr_parent.find('td.new_stock').addClass('stocBAImpact');
				
				// Enregistrement de la valeur pour le cas du rechargement de page (filtre ou pagination)
				$('.transfers').val($('.transfers').val() + '_' + trim(Aids) + '|' + qte_transfer + '|' + id_stock_s1 + '|' + id_stock_s2);
                                
				$(this).val(qte_transfer);
				
				// Mise à jour liste transfert
				createListeTranfert();
			}
			else
			{
                                showErrorMessage($('#trad_notstockenough').val());
				
                                var new_stock = 0;
                                var Bphysical_quantity = 0;
                                
                                if (trim(tr_parent.find('td.new_stock').text()) != '--')
                                    new_stock = parseInt(trim(tr_parent.find('td.new_stock').text()));
				
                                if (trim(tr_parent.find('td.physical_quantity2').text()) != '--')
                                    Bphysical_quantity = parseInt(tr_parent.find('td.physical_quantity2').text());
				                                
				var valeur_precedente = new_stock - Bphysical_quantity;
				
				$(this).val(valeur_precedente);
			}
		}
	});

	// Validation des transferts de stock
	$('#validate_transfer').click(function()
	{
		// Seulement si au moins une quantité a été saisie
		if ($('.transfers').val().length > 0)
		{
			
			// Initialisation des variables
			var id_stockA = $('#warehouse_id_stockA').val();
			var id_stockB = $('#warehouse_id_stockB').val();
			var lastname = $('#lastname').val();
			var firstname = $('#firstname').val();
			var id_employee = $('#id_employee').val();
			var ids_mvt = '';
                        
                        // Valeurs de stock déjà enregistrées
			var values = $('.transfers').val();
                        
                        $('#submitTransfers #id_stockA').val(id_stockA);
                        $('#submitTransfers #id_stockB').val(id_stockB);
                        $('#submitTransfers #lastname').val(lastname);        
                        $('#submitTransfers #firstname').val(firstname);
                        $('#submitTransfers #id_employee').val(id_employee);
                        $('#submitTransfers #ids_mvt').val(ids_mvt);
                        $('#submitTransfers #values').val(values);
                        $('#submitTransfers #deleteCookie').val('true');
                        $('#submitTransfers #ids_mvt_csv').val($('.transfers').val());
                        $('#submitTransfers #id_warehouse_src').val(id_stockA);
                        $('#submitTransfers #id_warehouse_dst').val(id_stockB);
		}
		else
		{
			jAlert($('#trad_noquantityfilled').val());
		}
		
	});

	// Affichage tableau de stock source, ajout des valeurs déjà traitée avant un rechargement (filtre ou pagination)
	$('#stockA').ready(function()
	{
		// Grise chaque produit du stock A non présent dans le stock B
		$('#stockA tbody > tr').each(function()
		{
			// Id warehouse entrepot B
			var etpB = $('#warehouse_id_stockB').val();
			var _this = $(this);

			// Id(s) stock A
			var Aids = $(this).find('td.ids').text();
			var id_product = 0;
			var id_product_attribute = 0;

			if (Aids.indexOf(';') != -1)
			{
				Aids = Aids.split(';');
				id_product = trim(Aids[0]);
				id_product_attribute = trim(Aids[1]);
			}
			else
				id_product = trim(Aids);
				
			// Par défaut on grise tout
			_this.find('td input.qte_transfer').prop('disabled', true);

			$.ajax({
				type: 'GET',
				data: {
					id_product:id_product,
					id_product_attribute:id_product_attribute,
					id_warehouse:etpB,
					token:token,
					task:'getPresenceWarehouseB'
					},
				cache:false,
				async: true,
				url: '../modules/erpillicopresta/ajax/ajax.php',
				success: function(data)
				{
					// SI produit non trouvé ds stock B, on grise
					if (data == 'true')
						_this.find('td input.qte_transfer').prop('disabled', false);
				}
			});

		});

		// Valeurs de stock déjà enregistrées
		if ($('.transfers').val() != '' && $('.transfers').val() != undefined)
		{
			var values = $('.transfers').val().split("_");

			// MAJ STOCK A
			$('#stockA tbody > tr').each(function()
			{
				// Id ligne
				var Aids =  trim($(this).find('td.ids').text());

				// Valeur arès mise à jour de stock
				var container_qte_after =  $(this).find('td.quantity_after');

				// Quantité physique avt mise a jour
				var Aphysical_quantity =  trim($(this).find('td.physical_quantity').text());

				// Parcours des valeurs enregistrées
				for(var i=0;i<=values.length-1;i++)
				{
					// Récup id et valeur de stock
					var value = values[i].split("|");

					// Si on a une égalité d'id, on rempli le stock
					if (Aids == value[0])
					{
						// Application valeur saisie
						$(this).find('td input.qte_transfer').val(value[1]);

						// Application de la nouvelle quantité dans le stock A
						var Aquantity_after = Aphysical_quantity - value[1];
						container_qte_after.text(Aquantity_after);
						container_qte_after.addClass('stockAImpact');

						// MAJ STOCK B
				
						$(this).find('td.new_stock').text(value[1]);

						// Quantité actuelle stock B
						var Bphysical_quantity = $(this).find('td.physical_quantity2').text();

						// Quantité après transfert stock B
						var Bquantity_after = parseInt(Bphysical_quantity) + parseInt(value[1]);

						// Application de la nouvelle quantité dans le stock B
						$(this).find('td.new_stock').text(Bquantity_after);
						$(this).find('td.new_stock').addClass('stocBAImpact');
	
					}
				}
			});
		}
		
		createListeTranfert();
		
		
	});


            $('.deleteAwaitinTft').live('click',function(){
                    var toRemove = $(this).find('input[type=hidden]').val();  	

                    setToZero(toRemove);
                    // Mise à jour de la liste
                    createListeTranfert();
            });

	});


	function setToZero(toRemove)
	{	
            //regex to get part of transfer value to delete 
            // patern : _[IDS]|qty|id_stock_1|id_stock_2
            // exemple : _1;5|52|4|7
            var reg = new RegExp("_" + toRemove + "\\\|[0-9]+\\\|[0-9]+\\\|[0-9]+", "g");

            var values = $('.transfers').val();

            // Mise à jour du champ transfert en enlevant la chaine correspondant au transfert à supprimer
            $('.transfers').val(values.replace(reg, ''));

            // Attention le champ comprenant les ids (id avec eventuellement id_attribute concaténé avec un ;) : replace de ';' par '_' car ';' n'est pas licite dans un
            // nom de classe (il est remplacé à l'affichage dans le template de la même manière pour idenitifer la balise TR)
            var ligne = $('input#products_ids_' + toRemove.replace(';','_')).parent().parent();

            // Reset valeurs
            ligne.find('.qte_transfer').val('0');
            ligne.find('td.quantity_after').removeClass('stockAImpact');
            ligne.find('td.quantity_after').text(trim(ligne.find('td.physical_quantity').text()));    	
            ligne.find('td.new_stock').removeClass('stocBAImpact');
            ligne.find('td.new_stock').text(trim(ligne.find('td.physical_quantity2').text()));
	}


	function createListeTranfert()
	{
            // Valeurs de stock déjà enregistrées
            var values = $('.transfers').val();

            // display/hide process button 
            if(values == undefined || values == '')
                $('#submitTransfers').hide();
            else
                $('#submitTransfers').show();

            if (values == undefined)
                    return;

            // Requête
            $.ajax({
                    type: 'GET',
                    data: {
                        values:values ,
                        token:token,
                        task: 'updateListeTransfert'
                    },
                    cache:false,
                    async: false,
                    url: '../modules/erpillicopresta/ajax/ajax.php',
                    success: function(data) {

                            if (data != 'false')
                                    $('#transfert_attente').html(data);
                    }
            });

	}