<?php
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

require_once (dirname(__FILE__).'/../../../config/config.inc.php');
require_once (dirname(__FILE__).'/../../../init.php');
include_once (dirname(__FILE__).'/../../../config/settings.inc.php');
require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStock.php');
require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/stock/ErpStockMvt.php');
require_once (_PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php');
require_once _PS_MODULE_DIR_.'erpillicopresta/config/Licence.php';

$cookie 			= new Cookie ('psAdmin');
$context 			= Context::getContext ();
$employee 			= new Employee ($cookie->id_employee);
$context->employee 	= $employee;
$erpip = new ErpIllicopresta();

$token = Tools::getValue('token');

	/* check Token */
	if (!Tools::isSubmit ('token') || (
			$token != Tools::getAdminToken ('AdminAdvancedStock'.(int)(Tab::getIdFromClassName('AdminAdvancedStock')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminSupplyOrders'.(int)(Tab::getIdFromClassName('AdminSupplyOrders')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminStockTransfer'.(int)(Tab::getIdFromClassName('AdminStockTransfer')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminInventory'.(int)(Tab::getIdFromClassName('AdminInventory')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminStockGap'.(int)(Tab::getIdFromClassName('AdminStockGap')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminAdvancedOrder'.(int)(Tab::getIdFromClassName('AdminAdvancedOrder')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminAdvancedSupplyOrder'.(int)(Tab::getIdFromClassName('AdminAdvancedSupplyOrder')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)$cookie->id_employee)
			&& $token != Tools::getAdminToken('AdminERP'.(int)(Tab::getIdFromClassName('AdminERP')).(int)$cookie->id_employee)
		)
		|| Tools::getValue ('task') === false)
	exit ('ERROR');

	switch (Tools::getValue ('task'))
	{

	case 'updateOrderStatus' :
                               
                if( Configuration::get($erpip->getControllerStatusName('ADVANCEDORDER')) == STATUS1 
                        && Tools::getValue('action') == 'masse' 
                        && count(Tools::getValue('idOrder')) > ERP_ORDERFR )
                {
                    $erp_orderfr = array(
                        'free_limitation_msg' => sprintf($erpip->l('You are using a free version of 1-Click ERP which limits the order change state to %d orders.'), ERP_ORDERFR)
                    );
                    print Tools::jsonEncode($erp_orderfr); exit();
                }
                
		else if (Tools::isSubmit('idOrder') && Tools::isSubmit('idState') && Tools::isSubmit('action') && Tools::isSubmit('id_employee'))
		{
			$retour = null;
			$id_employee = (int)Tools::getValue('id_employee');

			require_once _PS_MODULE_DIR_.'erpillicopresta/classes/order/ErpOrder.php';
                        
                        set_error_handler(array('ErpOrder', 'ErpOrdersAjaxErrorHandler'));
                        
			switch (Tools::getValue('action'))
			{
				case 'unique' :
					$retour = array('res' => false, 'newColor' => null);
					$currOrder = new ErpOrder( (int)Tools::getValue('idOrder'));
					$currOrder->setCurrentState( (int)Tools::getValue('idState'), (int)$id_employee);
					$currOrder = new ErpOrder( (int)Tools::getValue('idOrder')); /* Recreate object because the prvious one do not update after modification */
					$currOrderState = ($currOrder->getCurrentOrderState()); /* Get new state (no builder, need to pass by order) */
					$retour['newColor'] = $currOrderState->color;
					$retour['res'] = true;
                                        
                                        if (isset($context->cookie->errorOrderAjaxHandler) && !empty($context->cookie->errorOrderAjaxHandler))
                                        {
                                            $retour['message'] .= $context->cookie->errorOrderAjaxHandler;
                                        }
                                                                                
				break;

				case 'masse' :
					$retour = array('message' => 'false', 'ordersWithoutError' => array ());
					foreach (Tools::getValue('idOrder') as $order)
					{
                                            try
                                            {
                                                $currOrder = new ErpOrder($order);
                                                $currOrder->setCurrentState(Tools::getValue('idState'), (int)$id_employee);
                                                $retour['ordersWithoutError'][] = $order;
                                            }

                                            catch(Exception $e)
                                            {
                                                if ($retour['message'] == 'false')
                                                        $retour['message'] = '';

                                                $retour['message'] .= $erpip->l('Error for the order #').$order.': '.$e->getMessage().'<br/>';
                                            }
                                            
                                            if ($retour['message'] == 'false' && !empty($context->cookie->errorOrderAjaxHandler))
                                                        $retour['message'] = '';
                                            
                                            if (!empty($context->cookie->errorOrderAjaxHandler))
                                                $retour['message'] .= $context->cookie->errorOrderAjaxHandler.'<br/>';                                            
					}
				break;
			}
                        
			print Tools::jsonEncode($retour);
                        $context->cookie->__unset('errorOrderAjaxHandler');
			exit();
		}

	break;

	case 'updateListeTransfert' :

		if (Tools::isSubmit('values'))
		{
			echo '<tr>
                                <td></td>
                                <td width="60"></td>
				<td width="80"></td>
                            </tr>';

			require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProduct.php');
                        require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/helpers/StockTransferHelper.php');
		
			$data = StockTransferHelper::getTransfertDataAsArray(Tools::getValue ('values'));

			/*  Get addition information */
			foreach ($data as $key => &$val)
			{
				$val['product_name'] = ErpProduct::getProductName ($val['id_product'], $val['id_product_attribute']);

				echo '<tr>
                                        <td>'.$val['product_name'].'</td>
                                        <td>'.$val['quantity'].'</td>
                                        <td style="cursor: pointer;" class="deleteAwaitinTft"><input type="hidden" value="'.$key.'" />
                                        <img src="../img/admin/delete.gif" />
                                        </td>
				</tr>';
			}
		}
		else
			echo '<tr> <td colspan="3">No tranfert</td> </tr>';
		break;

	case 'getPresenceWarehouseB' :

		if (Tools::isSubmit('id_product') && Tools::isSubmit('id_product_attribute') && Tools::isSubmit('id_warehouse'))
		{
			$presence = ErpStock::getPresenceInStock(Tools::getValue('id_product'), Tools::getValue('id_product_attribute'), Tools::getValue('id_warehouse'));

			if ((int)$presence > 0)
				echo 'true';
			else
				echo 'false';
		}
		else
			echo $erpip->l('Parameters missing !');

		break;

	case 'products' :

		if (Tools::isSubmit ('id_order'))
		{
			$objOrder = new ErpOrder (Tools::getValue ('id_order'));
			$produits = $objOrder->getListOfProductsWithQuantity ();
			$message = '<table class="table_popup">
							<tr>
								<th>'.$erpip->l('SKU').'</th>
								<th>'.$erpip->l('Description').'</th>
								<th>'.$erpip->l('Quantity').'</th>';

			if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) /* Si la gestion avancée est activée */
			{
				$message .= '<th>'.$erpip->l('Physical Stock').'</th>
							 <th>'.$erpip->l('Usable Stock').'</th>
							 <th>'.$erpip->l('Real Stock').'</th>';
			}
			else
				$message .= '<th>'.$erpip->l('Stock').'</th>';

			$message .= '</tr>';

			foreach ($produits as &$prod)
			{
				$objProd = new Product($prod['product_id']);

				$message .= '<tr>';
				/*  If order neither sent nor cancelled nor current order */
				$message .= '<td>'.$objProd->reference.'</td><td>'.$objProd->getProductName($prod['product_id'], $prod['product_attribute_id']).'</td><td>'.$prod['product_quantity'].'</td>';

				if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) /* Si la gestion avancée est activée */
				{
					$manager = StockManagerFactory::getManager();
					$message .= '<td>'.$manager->getProductPhysicalQuantities($prod['product_id'], $prod['product_attribute_id']).'</td>'
								.'<td>'.$manager->getProductPhysicalQuantities	($prod['product_id'], $prod['product_attribute_id'], null, true).'</td>'
								.'<td>'.$manager->getProductRealQuantities	($prod['product_id'], $prod['product_attribute_id']).'</td>';
				}
				else
					$message .= '<td>'.StockAvailable::getQuantityAvailableByProduct($prod['product_id'], $prod['product_attribute_id']).'</td>';

				$message .= '</tr>';
			}

			$message .= '</table>';

			print $message;
		}

		break;

	case 'productSupplierPrice' :

		/*  If we have called the script with a term to search */
		if (Tools::isSubmit ('id_product') && Tools::isSubmit ('id_product_attribute'))
		{
                    $id_product 			= Tools::getValue ('id_product');
                    $id_product_attribute 	= Tools::getValue ('id_product_attribute');
                    $id_currency 			= Tools::getValue ('id_currency', false) ? Tools::getValue ('id_currency') : Configuration::get('PS_CURRENCY_DEFAULT');

                    /* Prices of all suppliers for the product */
                    $supplier_prices = ErpProductSupplier::getAllProductSupplierPrice ($id_product, $id_product_attribute, true);

                    if (!empty ($supplier_prices))
                    {
                        echo '<table class="table">';
                        foreach ($supplier_prices as $price)
                        {
                            /*  If supplier price = 0 we get the basic one */
                            if ($price['product_supplier_price_te'] == '0.000000')
                                    $wholesale_price = Stock::getWholesalePrice ($id_product, $id_product_attribute);
                            else
                                    $wholesale_price = $price['product_supplier_price_te'];

                            /*  Write of the HTML table */
                            echo  '<tr>
                                    <td>'.$price['supplier_name'].'</td>
                                    <td>'.number_format($wholesale_price , 2, '.', ' ').'€</td>
                            </tr>';
                        }
                        echo '</table>';
                    }
                    else
                        echo $erpip->l('No price found for this product');
		}

	break;

	case 'getSupplierReference' :

		/* If we called the script with a search term */
		if (Tools::isSubmit ('id_product'))
		{
			$id_product = Tools::getValue ('id_product');

			/*  Get id attribute. */
			/*  If get in get */
			if (Tools::isSubmit ('id_product_attribute'))
				$id_product_attribute = (int)Tools::getValue ('id_product_attribute');
			else
			{
				/*  Else concat with id product */
				/** Note NDE : Should be in strstr($id_product,';') ? */
				if (strpos($id_product, ';') !== false)
				{
                                    $ids = explode (';', $id_product);
                                    $id_product = $ids[0];
                                    $id_product_attribute 	= $ids[1];
				}
				else
                                    $id_product_attribute 	= 0;
			}

			/** Note NDE : à enlever ?? on perd la récupération faites au dessus si dans le cas de la concaténation dans id_produt */
			$id_product = Tools::getValue ('id_product');

			/*  For each supplier */
			$suppliers = Supplier::getSuppliers ();

			$supplier_ref_output = array();

			if (!empty($suppliers))
			{
				foreach ($suppliers as $supplier)
				{
					$supplier_ref 	= ErpProductSupplier::getProductSupplierReference ($id_product, $id_product_attribute, (int)$supplier['id_supplier']);
					if ($supplier_ref != false)
						$supplier_ref_output[(int)$supplier['id_supplier']] = array('name' => $supplier['name'], 'ref' => $supplier_ref);
				}

				if (!empty($supplier_ref_output))
				{
					echo '<table style="text-align:left" class="table">';
					foreach ($supplier_ref_output as $ref_output)
					{
						echo '<tr>';
						echo '<td>'.$ref_output['name'].' : </td>';
						echo '<td> &nbsp; '.$ref_output['ref'].'</td>';
						echo '</tr>';
					}
					echo '</table>';
				}
				else
					echo $erpip->l('No suppliers references found');
			}
		}

	break;
	        
	case 'supplier':
	default:

		/* Execute AJAX requests */
		if (Tools::isSubmit('action'))
		{
			switch(Tools::getValue('action'))
			{
				/* get supplier info */
				case 'getSupplier':
					if (Tools::isSubmit('id_supplier') && (int)Tools::getValue('id_supplier') > 0)
					{
						require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplier.php';

						$id_supplier = (int)Tools::getValue('id_supplier');

						//-ERP information
						$erp_supplier = null;
						$id_erpip_supplier = ErpSupplier::getErpSupplierIdBySupplierId($id_supplier);
						if ($id_erpip_supplier > 0)
								$erp_supplier = new ErpSupplier( (int)$id_erpip_supplier);

						if (!is_null($erp_supplier))
							echo Tools::jsonEncode($erp_supplier);
					}
					else
						echo Tools::jsonEncode(array('error' => $erpip->l('Error : no supplier found !')));
				break;

				case 'getSupplyOrderDescription':
					if (Tools::isSubmit('id_supplier_order'))
					{
						require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrder.php';

						$id_supplier_order = (int)(Tools::getValue('id_supplier_order'));
						if ($id_supplier_order > 0)
						{
							/*-ERP get association */
							$id_erpip_supply_order = ErpSupplyOrder::getErpSupplierOrderIdBySupplierOrderId($id_supplier_order);

							if ((int)$id_erpip_supply_order > 0)
							{
								$erp_supply_order = new ErpSupplyOrder((int)$id_erpip_supply_order);

								if ($erp_supply_order)
									echo $erp_supply_order->description;
							}
						}
					}
				break;

				/*  Dash product*/
				case 'getProduct':
					$id_product = Tools::getValue('id_product');
					$id_supplier = Tools::getValue('id_supplier');
					$id_currency = (Tools::isSubmit('id_currency')) ? Tools::getValue('id_currency') : Context::getContext()->id_currency;

					/*  get lang from context */
					$id_lang = (int)Context::getContext()->language->id;

					$query = new DbQuery();
					$query->select('
						CONCAT(p.id_product, \'_\', IFNULL(pa.id_product_attribute, \'0\')) as id,
						ps.product_supplier_reference as supplier_reference,
						IFNULL(pa.reference, IFNULL(p.reference, \'\')) as reference,
						IFNULL(pa.ean13, IFNULL(p.ean13, \'\')) as ean13,
						IFNULL(pa.upc, IFNULL(p.upc, \'\')) as upc,
						md5(CONCAT(\''._COOKIE_KEY_.'\', p.id_product, \'_\', IFNULL(pa.id_product_attribute, \'0\'))) as checksum,
						IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.name, \' - \', al.name SEPARATOR \', \')), pl.name) as name
					');

					$query->from('product', 'p');
					$query->innerJoin('product_lang', 'pl', 'pl.id_product = p.id_product AND pl.id_lang = '.(int)$id_lang);
					$query->leftJoin('product_attribute', 'pa', 'pa.id_product = p.id_product');
					$query->leftJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
					$query->leftJoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
					$query->leftJoin('attribute_lang', 'al', 'al.id_attribute = atr.id_attribute AND al.id_lang = '.(int)$id_lang);
					$query->leftJoin('attribute_group_lang', 'agl', 'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = '.(int)$id_lang);
					$query->leftJoin('product_supplier', 'ps', 'ps.id_product = p.id_product AND ps.id_product_attribute = IFNULL(pa.id_product_attribute, 0)');
					$query->where('p.id_product NOT IN (SELECT pd.id_product FROM `'._DB_PREFIX_.'product_download` pd WHERE (pd.id_product = p.id_product))');
					$query->where('p.is_virtual = 0 AND p.cache_is_pack = 0');
							$query->where('p.id_product = '.(int)$id_product.'');

					$query->groupBy('p.id_product, pa.id_product_attribute');

					$item = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

							$ids = explode('_', $item['id']);

					if ($item)
						die(Tools::jsonEncode($item));
					die(1);
				//break;

				/*  Update wholesaleprice */
				case 'majWholesalePrice':
					if(Tools::isSubmit('id_product') && Tools::isSubmit('id_product_attribute') && Tools::isSubmit('wholesale_price') && Tools::isSubmit('id_supplier'))
					{
						require_once (_PS_MODULE_DIR_.'erpillicopresta/classes/ErpProductSupplier.php');
						$id_product = Tools::getValue('id_product');
						$id_product_attribute = Tools::getValue('id_product_attribute');
						$wholesale_price = Tools::getValue('wholesale_price');
						$id_supplier = Tools::getValue('id_supplier');

						/* If we have a price for this supplier update for the supplier
						 * At any case we update principal price for product or attribute
						 */

						$query = 'SELECT COUNT(id_product_supplier) as nb_products, id_product_supplier
		                                            FROM '._DB_PREFIX_.'product_supplier
		                                            WHERE id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute
		                                            .' '. 'AND id_supplier = '.(int)$id_supplier.' AND product_supplier_price_te >0.000000';

						$nbProducts = DB::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

						/* If price for this supplier, update */
						if ((int)$nbProducts['nb_products'] > 0)
						{
		                                    $product_supplier = new ErpProductSupplier($nbProducts['id_product_supplier']);
		                                    $product_supplier->product_supplier_price_te = $wholesale_price;
		                                    $product_supplier->save();
						}

						/* If not global update of the product in any case */
						/*  Product */
						if ($id_product_attribute == '0')
						{
		                                    $product = new Product($id_product);
		                                    $product->wholesale_price = $wholesale_price;
		                                    $product->save();
						}
						/*  Attribute */
						else
						{
		                                    $combination = new Combination($id_product_attribute);
		                                    $combination->id_product = $id_product;
		                                    $combination->wholesale_price = $wholesale_price;
		                                    $combination->save();
						}

						$update = DB::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);

						echo $update;
					}
				break;

				/*  update delivery or cancel delivery  */
				case 'receipt_update':
				case 'receipt_cancel':
					$is_canceled = (Tools::getValue('action') == 'receipt_update') ? 0 : 1;

					/*  update table ps_supply_order_receipt_history */
					$supply_order_receipt_history = new SupplyOrderReceiptHistory();
					$supply_order_receipt_history->id_supply_order_receipt_history = (int)Tools::getValue('id_supply_order_receipt_history');
					$supply_order_receipt_history->id = (int)Tools::getValue('id_supply_order_receipt_history');
					$supply_order_receipt_history->id_supply_order_detail = Tools::getValue('id_supply_order_detail');
					$supply_order_receipt_history->id_employee = Tools::getValue('id_employee');
					$supply_order_receipt_history->employee_firstname = Tools::getValue('employee_firstname');
					$supply_order_receipt_history->employee_lastname = Tools::getValue('employee_lastname');
					$supply_order_receipt_history->id_supply_order_state = Tools::getValue('id_supply_order_state');
					$supply_order_receipt_history->quantity = Tools::getValue('quantity');
					$supply_order_receipt_history->date_add = Date('Y-m-d h:s:i');

					/*  UPdate purchase price and quantity in the stock mvt */
					if ($supply_order_receipt_history->update())
					{

						/*--ERP information */
						// updates/creates ErpSupplyOrderReceiptHistory if it does not exist
						require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrderReceiptHistory.php';

						if (Tools::isSubmit('id_erpip_supply_order_receipt_history') && (int)Tools::getValue('id_erpip_supply_order_receipt_history') > 0)
	                                            $erp_supply_order_receipt_history = new ErpSupplyOrderReceiptHistory((int)Tools::getValue('id_erpip_supply_order_receipt_history'));
						else
	                                            $erp_supply_order_receipt_history = new ErpSupplyOrderReceiptHistory(); // creates erp_supplier_order_detail

						$erp_supply_order_receipt_history->id_supply_order_receipt_history = $supply_order_receipt_history->id;
						$erp_supply_order_receipt_history->unit_price = Tools::getValue('wholesale_price');
						$erp_supply_order_receipt_history->discount_rate = Tools::getValue('discount_rate');
						$erp_supply_order_receipt_history->is_canceled = $is_canceled;

						$validation_esorh = $erp_supply_order_receipt_history->validateController();
						// checks erp_supplier_receipt_history validity
						if (count($validation_esorh) > 0)
						{
	                                            echo $erpip->l('The ErpIllicopresta Supplier Receipt History is not correct. Please make sure all of the required fields are completed.');
	                                            echo '<ul>';
	                                                foreach ($validation_esorh as $item)
	                                                    echo '<li>'.$item.'</li>';
	                                            echo '</ul>';
						}
						else
						{
	                                            if (Tools::isSubmit('id_erpip_supply_order_receipt_history') && Tools::getValue('id_erpip_supply_order_receipt_history') > 0)
	                                                $erp_supply_order_receipt_history->save();
						}

						/*  In cancel case 
						*   We need to update received quantity and generate stock mvt opposite
						*/
						if ($is_canceled)
						{
							/* Get info mvt_stock initial */
							$query = 'SELECT * FROM '._DB_PREFIX_.'stock_mvt WHERE id_stock_mvt = '.(int)Tools::getValue('id_stock_mvt');
							$initial_stock_mvt = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	                                                                                                        
							/*  If we have informations about the wtock mvt to cancel */
							if (count($initial_stock_mvt) > 0 )
							{                                                            
								/* Update Quantity  supply_order_detail */
								$query = 'UPDATE '._DB_PREFIX_.'supply_order_detail
										 SET quantity_received = (quantity_received - '.intval(Tools::getValue('quantity')).')
										 WHERE id_supply_order_detail = '.intval(Tools::getValue('id_supply_order_detail'));

								$update_supply_order = DB::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);

								/* If supply_order_detail successfully updated */
								if ($update_supply_order)
								{
									/* stock update */
									$stock = new ErpStock($initial_stock_mvt[0]['id_stock']);
									$stock->physical_quantity -= Tools::getValue('quantity');
									$stock->usable_quantity -= Tools::getValue('quantity');

									/* If stock  update ok .. */
									if ($stock->update())
									{
										/*  Synchro of stocks */
										StockAvailable::synchronize($stock->id_product);

										/*  Add stock mvt reason if not exists (lang non installed at the module installation) */
										$query = 'SELECT * FROM '._DB_PREFIX_.'stock_mvt_reason_lang WHERE id_stock_mvt_reason = '.(int)Configuration::get('ERP_RECEPTION_CANCELING_ID').' AND id_lang = '.(int)Context::getContext()->language->id;
										$results = Db::getInstance()->ExecuteS($query);
										if ($results <= 0)
											Db::getInstance()->insert(_DB_PREFIX_.'stock_mvt_reason_lang', array('id_stock_mvt_reason' => Configuration::get('ERP_RECEPTION_CANCELING_ID'), 'id_lang' => (int)Context::getContext()->language->id, 'name' => $erpip->l('Reception cancelling')));

										/* Opposite stock Mvt creation */
										$stock_mvt = new ErpStockMvt();
										$stock_mvt->id_stock = $initial_stock_mvt[0]['id_stock'];
										$stock_mvt->id_order = 0;
										$stock_mvt->id_supply_order = $initial_stock_mvt[0]['id_supply_order'];
										$stock_mvt->id_stock_mvt_reason = Configuration::get('ERP_RECEPTION_CANCELING_ID');
										$stock_mvt->id_employee = Tools::getValue('id_employee');;
										$stock_mvt->employee_lastname = Tools::getValue('employee_lastname');
										$stock_mvt->employee_firstname = Tools::getValue('employee_firstname');
										$stock_mvt->physical_quantity = Tools::getValue('quantity');
										$stock_mvt->date_add = Date('Y-m-d h:s:i');
										$stock_mvt->sign = -1;
										$stock_mvt->price_te = $initial_stock_mvt[0]['price_te'];
										$stock_mvt->last_wa = $initial_stock_mvt[0]['last_wa'];
										$stock_mvt->current_wa = $initial_stock_mvt[0]['current_wa'];
										$stock_mvt->referer = 0;
										if ($stock_mvt->add())
											echo '1';
										else
											echo $erpip->l('Error while saving reverse stock movement.');
									}
								}
							}
							else
								echo $erpip->l('Error while getting id_supply_order or id_stock');
						}
						else
							echo '1';
					}
					else
						echo $erpip->l('Error saving receipt history : ');
				break;

				/* get total price of the products received in supply order*/
				case 'getTotalPrice':
					$id_supply_order = (int)Tools::getValue('id_supply_order');

					/*  With order Id, we get all the product id of the order */
					$query = 'SELECT id_supply_order_detail FROM '._DB_PREFIX_.'supply_order_detail '
							.'WHERE id_supply_order = '.(int)$id_supply_order;

					$idsTab = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

					$idTab = array();

					/*  reading this list */
					if (count($idsTab) > 0)
					{
						foreach ($idsTab as $id)
							$idTab[] = (int)$id['id_supply_order_detail'];

						$ids = implode(',', $idTab);

						/*  We get receipt historic for each product (receipt not deleted) */
						$query = 'SELECT sorh.id_supply_order_detail, sorh.quantity, esorh.unit_price, esorh.discount_rate
									FROM '._DB_PREFIX_.'supply_order_receipt_history sorh
									LEFT JOIN '._DB_PREFIX_.'erpip_supply_order_receipt_history esorh ON esorh.id_supply_order_receipt_history = sorh.id_supply_order_receipt_history
									WHERE sorh.id_supply_order_detail IN ('.$ids.') AND ( esorh.is_canceled = 0 OR esorh.is_canceled IS NULL) ';

						$res = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

						$total = 0;
						if (count($res) > 0)
						{
							/* We read the list and sum prices */
							foreach ($res as $row)
							{
								if ($row['unit_price'] == NULL || $row['discount_rate'] == NULL)
								{
									$query = 'SELECT unit_price_te, discount_rate FROM '._DB_PREFIX_.'supply_order_detail '
											. 'WHERE id_supply_order_detail = '.(int)$row['id_supply_order_detail'];

									$price_discount = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

									$row['unit_price'] = $price_discount[0]['unit_price_te'];
									$row['discount_rate'] = $price_discount[0]['discount_rate'];
								}
								$total += $row['quantity'] * ($row['unit_price'] - ($row['unit_price'] * $row['discount_rate'] / 100));
							}
						}
						else
							$total = '0';
					}
					else
						$total = '0';

					$total = number_format($total, 2, '.', ' ');
					$total = str_replace(',', '', $total);
					echo $total;
				break;

				/*  Group facturation */
				case 'billing':
					$orders = Tools::getValue('orders');
					$invoice_number = Tools::getValue('invoice_number');
					$date_to_invoice = Tools::getValue('date_to_invoice');

					foreach ($orders as $id_supply_order)
					{
						$order = new SupplyOrder($id_supply_order);
						$order->invoice_number = $invoice_number;
						$order->date_to_invoice = $date_to_invoice;
						$order->id_supply_order_state = 5;

						$order->save();
					}
				break;

				/* change supply order state */
				case 'updateSupplyOrderStatus':
					// get state ID
					$id_state = (int)Tools::getValue('id_supply_order_state', 0);
					if ($id_state <= 0)
					{
						echo Tools::jsonEncode(array('error' => $erpip->l('The selected supply order status is not valid.')));
						exit();
					}

					// get supply order ID
					$id_supply_order = (int)Tools::getValue('id_supply_order', 0);
					if ($id_supply_order <= 0)
					{
						echo Tools::jsonEncode(array('error' => $erpip->l('The supply order ID is not valid.')));
						exit();
					}

					// try to load supply order
					$supply_order = new SupplyOrder($id_supply_order);

					if (Validate::isLoadedObject($supply_order))
					{
						// get valid available possible states for this order
						$states = SupplyOrderState::getSupplyOrderStates($supply_order->id_supply_order_state);

						foreach ($states as $state)
						{
							// if state is valid, change it in the order
							if ($id_state == $state['id_supply_order_state'])
							{
								$new_state = new SupplyOrderState($id_state);
								$old_state = new SupplyOrderState($supply_order->id_supply_order_state);
	                                                        
								// special case of validate state - check if there are products in the order and the required state is not an enclosed state
								if ($supply_order->isEditable() && !$supply_order->hasEntries() && !$new_state->enclosed)
									echo Tools::jsonEncode(array('error' => $erpip->l('It is not possible to change the status of this order because you did not order any product.','erpillicopresta')));
								else
								{
									$supply_order->id_supply_order_state = $state['id_supply_order_state'];
									if ($supply_order->save())
									{
										// if pending_receipt,
										// or if the order is being canceled,
										// synchronizes StockAvailable
										if (($new_state->pending_receipt && !$new_state->receipt_state) ||
												($old_state->receipt_state && $new_state->enclosed && !$new_state->receipt_state))
										{
												$supply_order_details = $supply_order->getEntries();
												$products_done = array();
												foreach ($supply_order_details as $supply_order_detail)
												{
														if (!in_array($supply_order_detail['id_product'], $products_done))
														{
																StockAvailable::synchronize($supply_order_detail['id_product']);
																$products_done[] = $supply_order_detail['id_product'];
														}
												}
										}

										echo Tools::jsonEncode(array('message' => $erpip->l('Supply order state updated successfully !')));
									}
								}
							}
						}
					}
				break;

				default:
					echo $erpip->l('Error : the requested action does not exist (parameter "action" is invalide) : ').Tools::getValue('action');
				break;

			}
		}
		else
			echo Tools::jsonEncode(array('error' => $erpip->l('Error : no action found (parameter "action" missing) !')));

	break;
}
die(1);