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

class HTMLTemplateErpSupplyOrderForm extends HTMLTemplate
{
	public $supply_order;
	public $warehouse;
	public $address_warehouse;
	public $address_supplier;
	public $context;

	public function __construct($supply_order, $smarty)
	{
		$this->supply_order = $supply_order;
		$this->order = $supply_order;
		$this->smarty = $smarty;
		$this->context = Context::getContext();
		$this->warehouse = new Warehouse((int)$supply_order->id_warehouse);
		$this->address_warehouse = new Address((int)$this->warehouse->id_address);
		$this->address_supplier = new Address(Address::getAddressIdBySupplierId((int)$supply_order->id_supplier));

		// header informations
		$this->date = Tools::displayDate($supply_order->date_add);
		$this->title = (Tools::getValue('submitAction') == 'generateSupplyOrderFormPDF') ?
		HTMLTemplateErpSupplyOrderForm::l('Supply order form') : HTMLTemplateSupplyOrderForm::l('Receiving slip form');
	}

	/**
	 * @see HTMLTemplate::getContent()
	 */
	public function getContent()
	{
		$final_pdf = '';

		require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrderCustomer.php';
		require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplier.php';
		require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpSupplyOrder.php';

		// Retrieval of the link between supplier order AND customer order if exists
		$erp_supply_order_customer = ErpSupplyOrderCustomer::getSupplyOrdersCustomer(  (int)$this->supply_order->id);

		$supply_order_details = $this->supply_order->getEntriesCollection((int)$this->supply_order->id_lang);
		$this->roundSupplyOrderDetails($supply_order_details);

		$supply_order_receipt_history = array();
		foreach ($supply_order_details as $supply_order_detail)
			$supply_order_receipt_history[$supply_order_detail->id] = $this->getSupplyOrderReceiptHistoryCollection($supply_order_detail->id);

		$this->roundSupplyOrder($this->supply_order);

		$tax_order_summary = $this->getTaxOrderSummary();
		$currency = new Currency((int)$this->supply_order->id_currency);

		//-ERP information
		// get additional supplier information
		$erp_supplier = null;
		$erp_supplier_fax = '';
		if (isset($this->supply_order->id_supplier) && (int)$this->supply_order->id_supplier > 0)
		{
			$id_erpip_supplier = ErpSupplier::getErpSupplierIdBySupplierId((int)$this->supply_order->id_supplier);
			if ((int)$id_erpip_supplier > 0)
				$erp_supplier = new ErpSupplier( (int)$id_erpip_supplier);
		}

		if ($erp_supplier != null)
		   $erp_supplier_fax = $erp_supplier->fax;

		//-ERP information
		// get additional supply order information
		$erp_supply_order = null;
		if (isset($this->supply_order->id) && (int)$this->supply_order->id > 0)
		{
			$id_erpip_supply_order = ErpSupplyOrder::getErpSupplierOrderIdBySupplierOrderId((int)$this->supply_order->id);
			if ((int)$id_erpip_supply_order > 0)
					$erp_supply_order = new ErpSupplyOrder( (int)$id_erpip_supply_order);
		}

		// get shipping address
		$adresse_livraison = self::getStoreByName('Adresse livraison');


		$this->smarty->assign(array(
			'warehouse' => $this->warehouse,
			'address_warehouse' => $this->address_warehouse,
			'address_supplier' => $this->address_supplier,
			'supply_order' => $this->supply_order,
			'erp_supply_order' => $erp_supply_order,
			'supply_order_details' => $supply_order_details,
						'supply_order_receipt_history' => $supply_order_receipt_history,
			'tax_order_summary' => $tax_order_summary,
			'currency' => $currency,
						'fax' => $erp_supplier_fax,
						'action' => Tools::getValue('submitAction'),
						'shop_name' => Configuration::get('PS_SHOP_NAME'),
						'shop_addr' => Configuration::get('PS_SHOP_ADDR1'),
						'shop_addr2' => Configuration::get('PS_SHOP_ADDR2'),
						'shop_code' => Configuration::get('PS_SHOP_CODE'),
						'shop_city' => Configuration::get('PS_SHOP_CITY'),
						'shop_country' => Configuration::get('PS_SHOP_COUNTRY'),
						'adresse_livraison' => $adresse_livraison
		));

		// if there is an supply order generated : display a PDF page by customer
		if (!empty ( $erp_supply_order_customer))
		{
			// distribution by customer : one page per customer
			$final_item = array();
			foreach ($erp_supply_order_customer as $item)
				$final_item[ $item['id_customer'] ][] = $item;

			$pdf_customer = '';
			// Create page per customer
			foreach ( $final_item as $id_customer => $datas)
			{
				$customer = new Customer( (int)$id_customer);

				$order_detail = array();
				foreach ($datas as $data)
				{
					$order_detail_line = new OrderDetailCore( (int)$data['id_order_detail']);
					$order_detail_line->tax_rate = Tax::getProductTaxRate( $order_detail_line->product_id);
					$order_detail[] = $order_detail_line;
				}
				$this->smarty->assign(array('is_customer_page' => 'true'));
				$this->smarty->assign(array('supply_order_details' =>  $order_detail));
				$this->smarty->assign(array('customer_name' =>   $customer->lastname.' '.$customer->firstname));

				$pdf_customer .= '<div style="page-break-before:always"><div>';
				$pdf_customer .= $this->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/pdf/erp-supply-order-customer.tpl');
			}

			$this->smarty->assign( array('is_customer_page' => 'false'));
			$this->smarty->assign( array('supply_order_details' => $supply_order_details));
			$final_pdf = $this->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/pdf/erp-supply-order.tpl').$pdf_customer;
		}
		else
			$final_pdf = $this->smarty->fetch(_PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/pdf/erp-supply-order.tpl');

		return $final_pdf;
	}

	/**
	 * @see HTMLTemplate::getBulkFilename()
	 */
	public function getBulkFilename()
	{
		return 'supply_order.pdf';
	}

	/**
	 * @see HTMLTemplate::getFileName()
	 */
	public function getFilename()
	{
		if (Tools::getValue('submitAction') == 'generateSupplyOrderFormPDF')
			return self::l('SupplyOrderForm').sprintf('_%s', $this->supply_order->reference).'.pdf';
		else
			return self::l('SupplyOrderReceivingSlip').sprintf('_%s', $this->supply_order->reference).'.pdf';
	}

	protected function getTaxOrderSummary()
	{
		$query = new DbQuery();
		$query->select('
			SUM(price_with_order_discount_te) as base_te,
			tax_rate,
			SUM(tax_value_with_order_discount) as total_tax_value
		');
		$query->from('supply_order_detail');
		$query->where('id_supply_order = '.(int)$this->supply_order->id);
		$query->groupBy('tax_rate');

		$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		foreach ($results as &$result)
		{
			$result['base_te'] = Tools::ps_round($result['base_te'], 2);
			$result['tax_rate'] = Tools::ps_round($result['tax_rate'], 2);
			$result['total_tax_value'] = Tools::ps_round($result['total_tax_value'], 2);
		}
		unset($result); // remove reference

		return $results;
	}

	/**
	 * @see HTMLTemplate::getHeader()
	 */
	public function getHeader()
	{         
		$shop_name = Configuration::get('PS_SHOP_NAME');
		$path_logo = $this->getLogo();
		$width = $height = 0;

		if (!empty($path_logo))
			list($width, $height) = getimagesize($path_logo);

		$this->smarty->assign(array(
			'logo_path' => $path_logo,
			'img_ps_dir' => 'http://'.Tools::getMediaServer(_PS_IMG_)._PS_IMG_,
			'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
			'title' => $this->title,
			'reference' => $this->supply_order->reference,
			'date' => $this->date,
			'shop_name' => $shop_name,
			'width_logo' => $width,
			'height_logo' => $height
		));

		return $this->smarty->fetch($this->getTemplate('supply-order-header'));
	}

	/**
	 * @see HTMLTemplate::getFooter()
	 */
	public function getFooter()
	{
		$this->address = $this->address_warehouse;
		$free_text = array();
		$free_text[] = HTMLTemplateSupplyOrderForm::l('DE: Discount excluded ');
		$free_text[] = HTMLTemplateSupplyOrderForm::l(' DI: Discount included');

		$this->smarty->assign(array(
			'shop_address' => $this->getShopAddress(),
			'shop_fax' => Configuration::get('PS_SHOP_FAX'),
			'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
			'shop_details' => Configuration::get('PS_SHOP_DETAILS'),
			'free_text' => $free_text,
		));
		return $this->smarty->fetch($this->getTemplate('supply-order-footer'));
	}

	/**
	 * Rounds values of a SupplyOrderDetail object
	 * @param array $collection
	 */
	protected function roundSupplyOrderDetails(&$collection)
	{
		foreach ($collection as $supply_order_detail)
		{
			$supply_order_detail->unit_price_te = Tools::ps_round($supply_order_detail->unit_price_te, 2);
			$supply_order_detail->price_te = Tools::ps_round($supply_order_detail->price_te, 2);
			$supply_order_detail->discount_rate = Tools::ps_round($supply_order_detail->discount_rate, 2);
			$supply_order_detail->price_with_discount_te = Tools::ps_round($supply_order_detail->price_with_discount_te, 2);
			$supply_order_detail->tax_rate = Tools::ps_round($supply_order_detail->tax_rate, 2);
			$supply_order_detail->price_ti = Tools::ps_round($supply_order_detail->price_ti, 2);
		}
	}

	/**
	 * Rounds values of a SupplyOrder object
	 * @param SupplyOrder $supply_order
	 */
	protected function roundSupplyOrder(SupplyOrder &$supply_order)
	{
		$supply_order->total_te = Tools::ps_round($supply_order->total_te, 2);
		$supply_order->discount_value_te = Tools::ps_round($supply_order->discount_value_te, 2);
		$supply_order->total_with_discount_te = Tools::ps_round($supply_order->total_with_discount_te, 2);
		$supply_order->total_tax = Tools::ps_round($supply_order->total_tax, 2);
		$supply_order->total_ti = Tools::ps_round($supply_order->total_ti, 2);
	}

	private function getSupplyOrderReceiptHistoryCollection($supply_order_detail_id)
	{
		$results = Db::getInstance()->executeS('
				SELECT
				sorh.`id_supply_order_receipt_history`,
				sorh.`id_supply_order_detail`,
				sorh.`id_employee`,
				sorh.`employee_lastname`,
				sorh.`employee_firstname`,
				sorh.`id_supply_order_state`,
				sorh.`quantity`,
				esorh.`id_erpip_supply_order_receipt_history`,
				esorh.`id_supply_order_receipt_history`,
				esorh.`unit_price`,
				esorh.`discount_rate`,
				esorh.`is_canceled`
				FROM `'._DB_PREFIX_.'supply_order_receipt_history` sorh
				inner JOIN `'._DB_PREFIX_.'erpip_supply_order_receipt_history` esorh ON esorh.`id_supply_order_receipt_history` = sorh.`id_supply_order_receipt_history` AND esorh.`is_canceled` = 0 
				WHERE sorh.`id_supply_order_detail` = '.(int)$supply_order_detail_id);

		return $results;
	}

	// Get a store by its name
	static public function getStoreByName($name)
	{
		$query = ' SELECT s.*, cl.name country, st.iso_code state
		FROM '._DB_PREFIX_.'store s
		'.Shop::addSqlAssociation('store', 's').'
		LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = s.id_country)
		LEFT JOIN '._DB_PREFIX_.'state st ON (st.id_state = s.id_state)
		WHERE s.active = 1 AND s.name =\''.pSQL($name).'\' ';

		$stores = Db::getInstance()->getRow( $query);

		if (empty($stores))
				return array();
		else
				return $stores;
	}
        
        /**
	 * Returns the invoice logo
	 */
	protected function getLogo()
	{
		$logo = '';

		$physical_uri = Context::getContext()->shop->physical_uri.'img/';

		if (Configuration::get('PS_LOGO_INVOICE') != false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE')))
			$logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE');
		elseif (Configuration::get('PS_LOGO') != false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO')))
			$logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO');
		return $logo;
	}
}

