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

	// Stock transfer, Auto calculation of the quantity remaining after transfer
	$('.qte_transfer').keyup(function()
	{
		if ($(this).val() != '')
		{
			// Quantity to transfer
			var qte_transfer = parseInt(trim($(this).val()));
			
			var tr_parent = $(this).parent().parent();
			
			// Current quantity stock A
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
			
			// Quantity after transfer stock A
			var Aquantity_after = Aphysical_quantity - qte_transfer;

			// Cell quantity after
			var container_qte_after = tr_parent.find('td.quantity_after')
			
			// If positive future stock A
			if (Aquantity_after >= 0)
			{
				// Apply new quantity into stock A
				container_qte_after.text(Aquantity_after);
				container_qte_after.addClass('stockAImpact');
		
				// Current quantity stock B
                                var Bphysical_quantity = 0;
				if (trim(tr_parent.find('td.physical_quantity2').text()) != '--')
                                    Bphysical_quantity = parseInt(tr_parent.find('td.physical_quantity2').text());

				// Quantity after transfer stock B
				var Bquantity_after = parseInt(Bphysical_quantity) + parseInt(qte_transfer);

				// Apply new quantity into le stock B
				tr_parent.find('td.new_stock').text(Bquantity_after);
				tr_parent.find('td.new_stock').addClass('stocBAImpact');
				
				// Saving value in case of reloading page (filter or pagination)
				$('.transfers').val($('.transfers').val() + '_' + trim(Aids) + '|' + qte_transfer + '|' + id_stock_s1 + '|' + id_stock_s2);
                                
				$(this).val(qte_transfer);
				
				// Updating transfer list
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
		// Only if a quantity at least is filled
		if ($('.transfers').val().length > 0)
		{
			
			// Initialization variables
			var id_stockA = $('#warehouse_id_stockA').val();
			var id_stockB = $('#warehouse_id_stockB').val();
			var lastname = $('#lastname').val();
			var firstname = $('#firstname').val();
			var id_employee = $('#id_employee').val();
			var ids_mvt = '';
                        
                        // Stock values already stored
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

	// Displaying source stock table, adding values already processed before reloading (filtee or pagination)
	$('#stockA').ready(function()
	{
		// Each stock A product not in stock B will be in gray
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
				
			// Gray default
			_this.find('td input.qte_transfer').prop('disabled', true);

			$.ajax({
				type: 'GET',
				data: {
					id_product:id_product,
					id_product_attribute:id_product_attribute,
					id_warehouse:etpB,
					//token:token,
					//task:'getPresenceWarehouseB'
					},
				cache:false,
				async: true,
				url: 'index.php?controller=AdminStockTransfer&ajax=1&task=getPresenceWarehouseB&token='+token,
				success: function(data)
				{
					// If product not found in stock B, gray
					if (data == 'true')
						_this.find('td input.qte_transfer').prop('disabled', false);
				}
			});

		});

		// Stock values already stored
		if ($('.transfers').val() != '' && $('.transfers').val() != undefined)
		{
			var values = $('.transfers').val().split("_");

			// UPDATING STOCK A
			$('#stockA tbody > tr').each(function()
			{
				// Id line
				var Aids =  trim($(this).find('td.ids').text());

				// Value after updating stock
				var container_qte_after =  $(this).find('td.quantity_after');

				// Physical quantity before update
				var Aphysical_quantity =  trim($(this).find('td.physical_quantity').text());

				// Browse stored values
				for(var i=0;i<=values.length-1;i++)
				{
					// Get id and values about stock
					var value = values[i].split("|");

					// If same id, fill up stock
					if (Aids == value[0])
					{
						// Apply filled values 
						$(this).find('td input.qte_transfer').val(value[1]);

						// Application de la nouvelle quantité dans le stock A
						var Aquantity_after = Aphysical_quantity - value[1];
						container_qte_after.text(Aquantity_after);
						container_qte_after.addClass('stockAImpact');

						// UPDATING STOCK B
				
						$(this).find('td.new_stock').text(value[1]);

						// Current quantity stock  B
						var Bphysical_quantity = $(this).find('td.physical_quantity2').text();

						// Quantity after stock B
						var Bquantity_after = parseInt(Bphysical_quantity) + parseInt(value[1]);

						// Apply new quantity into stock B
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
                    // Updating list
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

            // Updationg transfer field minus string corresponding to the transfer to be deleted
            $('.transfers').val(values.replace(reg, ''));

            // Beware field including ids (id optionally with id_attribute concatenated with an ;) : replace of ';' by a '_' because ';' is not allowed in
            // a class name (it is replaced on display in the template in the same way to identify the TR tag)
            var ligne = $('input#products_ids_' + toRemove.replace(';','_')).parent().parent();

            // Reset values
            ligne.find('.qte_transfer').val('0');
            ligne.find('td.quantity_after').removeClass('stockAImpact');
            ligne.find('td.quantity_after').text(trim(ligne.find('td.physical_quantity').text()));    	
            ligne.find('td.new_stock').removeClass('stocBAImpact');
            ligne.find('td.new_stock').text(trim(ligne.find('td.physical_quantity2').text()));
	}


	function createListeTranfert()
	{
            // Stock values already stored
            var values = $('.transfers').val();

            // display/hide process button 
            if(values == undefined || values == '')
                $('#submitTransfers').hide();
            else
                $('#submitTransfers').show();

            if (values == undefined)
                    return;

            // Request
            $.ajax({
                    type: 'GET',
                    data: {
                        values:values ,
                        //token:token,
                        //task: 'updateListeTransfert'
                    },
                    cache:false,
                    async: false,
                    url: 'index.php?controller=AdminStockTransfer&ajax=1&task=updateListeTransfert&token='+token,
                    success: function(data) {

                            if (data != 'false')
                                    $('#transfert_attente').html(data);
                    }
            });

	}