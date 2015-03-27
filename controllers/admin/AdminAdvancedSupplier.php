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

require_once _PS_MODULE_DIR_.'erpillicopresta/controllers/admin/IPAdminController.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/erpillicopresta.php';
require_once _PS_MODULE_DIR_.'erpillicopresta/config/control.php';

class AdminAdvancedSupplierController extends IPAdminController
{
	public $bootstrap = true ;
	public function __construct()
	{
		$this->table = 'supplier';
		$this->className = 'Supplier';

		$this->addRowAction('view');
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->allow_export = true;

                // template path for avdanced supply ordr
                $this->template_path = _PS_MODULE_DIR_.'erpillicopresta/views/templates/admin/';

                $this->toolbar_title = $this->l('1 Click ERP ILLICOPRESTA');
                
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selection'), 'confirm' => $this->l('Delete selected items?')));

		$this->_select = 'COUNT(DISTINCT ps.`id_product`) AS products, email';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product_supplier` ps ON (a.`id_supplier` = ps.`id_supplier`)';
		$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'erpip_supplier` es ON (a.`id_supplier` = es.`id_supplier`)';
		$this->_group = 'GROUP BY a.`id_supplier`';

		$this->fieldImageSettings = array('name' => 'logo', 'dir' => 'su');

		$this->fields_list = array(
			'id_supplier' => array(
							'title' => $this->l('ID'),
							'align' => 'center',
							'width' => 25
						),
			'logo' => array(
							'title' => $this->l('Logo'),
							'width' => 150,
							'align' => 'center',
							'image' => 'su',
							'orderby' => false,
							'search' => false
						),
			'name' => array(
							'title' => $this->l('Name'),
							'width' => 'auto'
						),
			'email' => array(
							'title' => $this->l('Email'),
							'width' => 'auto'
						),
			'products' => array(
							'title' => $this->l('Number of products'),
							'width' => 70,
							'align' => 'right',
							'filter_type' => 'int',
							'tmpTableFilter' => true
						),
			'active' => array(
							'title' => $this->l('Enabled'),
							'width' => 70,
							'align' => 'center',
							'active' => 'status',
							'type' => 'bool',
							'orderby' => false
						)
		);

		// get controller status
        $this->controller_status = Configuration::get(ErpIllicopresta::getControllerStatusName('AdminAdvancedOrder'));

		parent::__construct();

	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryUi('ui.widget');
		$this->addJqueryPlugin('tagify');
	}

		public function renderView()
		{
			$supplier = new SupplierCore((int)Tools::getValue('id_supplier'));
			$products = $supplier->getProductsLite($this->context->language->id);
			$total_product = count($products);

			$comb_array = array();

		for ($i = 0; $i < $total_product; $i++)
		{
			$products[$i] = new Product($products[$i]['id_product'], false, $this->context->language->id);
			$products[$i]->loadStockData();
			// Build attributes combinations
			$combinations = $products[$i]->getAttributeCombinations($this->context->language->id);
			foreach ($combinations as $combination)
			{
				$comb_infos = Supplier::getProductInformationsBySupplier($this->object->id,
																		$products[$i]->id,
																		$combination['id_product_attribute']);
				$comb_array[$combination['id_product_attribute']]['product_supplier_reference'] = $comb_infos['product_supplier_reference'];
				$comb_array[$combination['id_product_attribute']]['product_supplier_price_te'] = Tools::displayPrice($comb_infos['product_supplier_price_te'], new Currency($comb_infos['id_currency']));
				$comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
				$comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
				$comb_array[$combination['id_product_attribute']]['upc'] = $combination['upc'];
				$comb_array[$combination['id_product_attribute']]['quantity'] = $combination['quantity'];
				$comb_array[$combination['id_product_attribute']]['attributes'][] = array(
					$combination['group_name'],
					$combination['attribute_name'],
					$combination['id_attribute']
				);
			}

			if (isset($comb_array))
			{
				foreach ($comb_array as $key => $product_attribute)
				{
					$list = '';
					foreach ($product_attribute['attributes'] as $attribute)
						$list .= $attribute[0].' - '.$attribute[1].', ';
					$comb_array[$key]['attributes'] = rtrim($list, ', ');
				}
				isset($comb_array) ? $products[$i]->combination = $comb_array : '';
				unset($comb_array);
			}
			else
			{
				$product_infos = Supplier::getProductInformationsBySupplier($this->object->id,
																			$products[$i]->id,
																			0);
				$products[$i]->product_supplier_reference = $product_infos['product_supplier_reference'];
				$products[$i]->product_supplier_price_te = Tools::displayPrice($product_infos['product_supplier_price_te'], new Currency($product_infos['id_currency']));
			}
		}

		$this->tpl_view_vars = array(
			'supplier' => $this->object,
			'products' => $products,
			'stock_management' => Configuration::get('PS_STOCK_MANAGEMENT'),
			'shopContext' => Shop::getContext(),
		);

		return parent::renderView();
	}

	public function renderForm()
	{
		// loads current warehouse
		if (!($obj = $this->loadObject(true)))
			return;

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Suppliers'),
				'image' => '../img/admin/suppliers.gif'
			),
			'input' => array(
				array(
					'type' => 'hidden',
					'name' => 'id_address',
				),
				array(
					'type' => 'hidden',
					'name' => 'id_erpip_supplier',
				),
				array(
					'type' => 'text',
					'label' => $this->l('Name'),
					'name' => 'name',
					'size' => 40,
					'required' => true,
					'hint' => $this->l('Invalid characters :').' <>;=#{}',
				),
				array(
					'type' => 'text',
					'label' => $this->l('Email'),
					'name' => 'email',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Fax'),
					'name' => 'fax',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Franco Amount'),
					'name' => 'franco_amount',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Discount Amount'),
					'name' => 'discount_amount',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Shipping Amount'),
					'name' => 'shipping_amount',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Discounting'),
					'name' => 'escompte',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Delivery Time'),
					'name' => 'delivery_time',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Account Number Accounting'),
					'name' => 'account_number_accounting',
					'size' => 40,
					'required' => false,
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Description:'),
					'name' => 'description',
					'cols' => 60,
					'rows' => 10,
					'lang' => true,
					'hint' => $this->l('Invalid characters :').' <>;=#{}',
					'desc' => $this->l('Will appear in the suppliers list'),
					'autoload_rte' => 'rte' //Enable TinyMCE editor for short description
				),
				array(
					'type' => 'text',
					'label' => $this->l('Phone :'),
					'name' => 'phone',
					'size' => 15,
					'maxlength' => 16,
					'desc' => $this->l('Phone number of this supplier')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Address :'),
					'name' => 'address',
					'size' => 100,
					'maxlength' => 128,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Address :').' (2)',
					'name' => 'address2',
					'size' => 100,
					'maxlength' => 128,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Postal Code/Zip Code :'),
					'name' => 'postcode',
					'size' => 10,
					'maxlength' => 12,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('City :'),
					'name' => 'city',
					'size' => 20,
					'maxlength' => 32,
					'required' => true,
				),
				array(
					'type' => 'select',
					'label' => $this->l('Country :'),
					'name' => 'id_country',
					'required' => true,
					'default_value' => (int)$this->context->country->id,
					'options' => array(
						'query' => Country::getCountries($this->context->language->id, false),
						'id' => 'id_country',
						'name' => 'name',
					),
				),
				array(
					'type' => 'select',
					'label' => $this->l('State :'),
					'name' => 'id_state',
					'options' => array(
						'id' => 'id_state',
						'query' => array(),
						'name' => 'name'
					)
				),
				array(
					'type' => 'file',
					'label' => $this->l('Logo :'),
					'name' => 'logo',
					'display_image' => true,
					'desc' => $this->l('Upload a supplier logo from your computer')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta title :'),
					'name' => 'meta_title',
					'lang' => true,
					'hint' => $this->l('Forbidden characters :').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta description :'),
					'name' => 'meta_description',
					'lang' => true,
					'hint' => $this->l('Forbidden characters :').' <>;=#{}'
				),
				array(
					'type' => 'tags',
					'label' => $this->l('Meta keywords :'),
					'name' => 'meta_keywords',
					'lang' => true,
					'hint' => $this->l('Forbidden characters :').' <>;=#{}',
					'desc' => $this->l('To add "tags" click in the field, write something and then press "Enter"')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Enable :'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					)
				)
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				//'class' => 'button'
			)
		);

		// loads current address for this supplier - if possible
		$address = null;
		if (isset($obj->id))
		{
			$id_address = Address::getAddressIdBySupplierId($obj->id);

			if ($id_address > 0)
				$address = new Address((int)$id_address);
		}

		// force specific fields values (address)
		if ($address != null)
		{
			$this->fields_value = array(
				'id_address' => $address->id,
				'phone' => $address->phone,
				'address' => $address->address1,
				'address2' => $address->address2,
				'postcode' => $address->postcode,
				'city' => $address->city,
				'id_country' => $address->id_country,
				'id_state' => $address->id_state,
			);
		}
		else
			$this->fields_value = array(
				'id_address' => 0,
				'id_country' => Configuration::get('PS_COUNTRY_DEFAULT')
			);

		// loads current erp_supplier` informationfor this supplier - if possible
		$erp_supplier = null;
		if (isset($obj->id))
		{
			$id_erpip_supplier = ErpSupplier::getErpSupplierIdBySupplierId($obj->id);
			if ($id_erpip_supplier > 0)
					$erp_supplier = new ErpSupplier( (int)$id_erpip_supplier);
		}

		// force specific fields values (erp_supplier)
		if ($erp_supplier != null)
		{
					$this->fields_value = array_merge($this->fields_value,
						array(
								'id_erpip_supplier' => $erp_supplier->id,
								'email' => $erp_supplier->email,
								'fax' => $erp_supplier->fax,
								'franco_amount' => $erp_supplier->franco_amount,
								'discount_amount' => $erp_supplier->discount_amount,
								'shipping_amount' => $erp_supplier->shipping_amount,
								'escompte' => $erp_supplier->escompte,
								'delivery_time' => $erp_supplier->delivery_time,
								'account_number_accounting' => $erp_supplier->account_number_accounting,
					)
				);
		}
		else
					$this->fields_value = array_merge($this->fields_value,
							array(
									'id_erpip_supplier' => 0,
						)
				);

		if (Shop::isFeatureActive())
		{
					$this->fields_form['input'][] = array(
							'type' => 'shop',
							'label' => $this->l('Shop association :'),
							'name' => 'checkBoxShopAsso',
				);
		}

		// set logo image
		$image = ImageManager::thumbnail(_PS_SUPP_IMG_DIR_.'/'.$this->object->id.'.jpg', $this->table.'_'.(int)$this->object->id.'.'.$this->imageType, 350, $this->imageType, true);
		$this->fields_value['image'] = $image ? $image : false;
		$this->fields_value['size'] = $image ? filesize(_PS_SUPP_IMG_DIR_.'/'.$this->object->id.'.jpg') / 1000 : false;

		return parent::renderForm();
	}

	public function initContent()
	{
		parent::initContent();
	}

	/**
	 * AdminController::postProcess() override
	 * @see AdminController::postProcess()
	 */
	public function postProcess()
	{
		require_once _PS_MODULE_DIR_.'erpillicopresta/models/ErpFeature.php';
		$this->context->smarty->assign(array(
			'erp_feature' => ErpFeature::getFeaturesWithToken($this->context->language->iso_code),
			'template_path' => $this->template_path,
		));

		if(Tools::isSubmit('export_csv'))
			$this->renderCSV();

		// checks access
		if (Tools::isSubmit('submitAdd'.$this->table) && !($this->tabAccess['add'] === '1'))
		{
			$this->errors[] = Tools::displayError($this->l('You do not have permission to add suppliers.'));
			return parent::postProcess();
		}

		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			if (Tools::isSubmit('id_supplier') && !($obj = $this->loadObject(true)))
				return;

			// updates/creates address if it does not exist
			if (Tools::isSubmit('id_address') && (int)Tools::getValue('id_address') > 0)
				$address = new Address((int)Tools::getValue('id_address')); // updates address
			else
				$address = new Address(); // creates address

			$address->alias = Tools::getValue('name', null);
			$address->lastname = 'supplier'; // skip problem with numeric characters in supplier name
			$address->firstname = 'supplier'; // skip problem with numeric characters in supplier name
			$address->address1 = Tools::getValue('address', null);
			$address->address2 = Tools::getValue('address2', null);
			$address->postcode = Tools::getValue('postcode', null);
			$address->phone = Tools::getValue('phone', null);
			$address->id_country = Tools::getValue('id_country', null);
			$address->id_state = Tools::getValue('id_state', null);
			$address->city = Tools::getValue('city', null);

			$validation = $address->validateController();

			// checks address validity
			if (count($validation) > 0)
			{
				foreach ($validation as $item)
					$this->errors[] = $item;
				$this->errors[] = Tools::displayError($this->l('The address is not correct. Please make sure all of the required fields are completed.'));
			}
			else
			{
				if (Tools::isSubmit('id_address') && Tools::getValue('id_address') > 0)
					$address->update();
				else
				{
					$address->save();
					$_POST['id_address'] = $address->id;
				}
			}

			//--ERP informations
			// updates/creates erp_supplier if it does not exist
			if (Tools::isSubmit('id_erpip_supplier') && (int)Tools::getValue('id_erpip_supplier') > 0)
				$erp_supplier = new ErpSupplier((int)Tools::getValue('id_erpip_supplier')); // updates erp_supplier
			else
				$erp_supplier = new ErpSupplier(); // creates erp_supplier

			$erp_supplier->email = Tools::getValue('email', null);
			$erp_supplier->fax = Tools::getValue('fax', null);
			$erp_supplier->franco_amount = Tools::getValue('franco_amount', null);
			$erp_supplier->discount_amount = Tools::getValue('discount_amount', null);
			$erp_supplier->shipping_amount = Tools::getValue('shipping_amount', null);
			$erp_supplier->escompte = Tools::getValue('escompte', null);
			$erp_supplier->delivery_time = Tools::getValue('delivery_time', null);
			$erp_supplier->account_number_accounting = Tools::getValue('account_number_accounting', null);

			$validation2 = $erp_supplier->validateController();
			//print_r($validation2);
			// checks erp_supplier validity
			if (count($validation2) > 0)
			{
				foreach ($validation2 as $item)
						$this->errors[] = $item;
                                
				$this->errors[] = Tools::displayError($this->l('The ErpIllicopresta Supplier is not correct. Please make sure all of the required fields are completed.'));

			}
			else
			{
				if (Tools::isSubmit('id_erpip_supplier') && Tools::getValue('id_erpip_supplier') > 0)
						$erp_supplier->update();
				else
				{
						$erp_supplier->save();
						$_POST['id_erpip_supplier'] = $erp_supplier->id;
				}
			}

			return parent::postProcess();
		}
		else if (Tools::isSubmit('delete'.$this->table))
		{
			if (!($obj = $this->loadObject(true)))
				return;
			else if (SupplyOrder::supplierHasPendingOrders($obj->id))
				$this->errors[] = $this->l('It is not possible to delete a supplier if there are pending supplier orders.');
			else
			{
				//delete all product_supplier linked to this supplier
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'product_supplier` WHERE `id_supplier`='.(int)$obj->id);

				$id_address = Address::getAddressIdBySupplierId($obj->id);
				$address = new Address($id_address);
				if (Validate::isLoadedObject($address))
				{
					$address->deleted = 1;
					$address->save();
				}

				//delete erp supplier
				$id_erpip_supplier = ErpSupplier::getErpSupplierIdBySupplierId($obj->id);
				$erp_supplier = new ErpSupplier($id_erpip_supplier);
				if (Validate::isLoadedObject($erp_supplier))
					$erp_supplier->delete();

				return parent::postProcess();
			}
		}
		else
			return parent::postProcess();
	}

	public function ajaxProcess()
	{

	}

	public function InitToolbar()
	{
		if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true)
		{
			switch ($this->display)
			{
				default:
					parent::initToolbar();
					$this->toolbar_btn['import'] = array(
						'href' => $this->context->link->getAdminLink('AdminImport', true).'&import_type=suppliers',
						'desc' => $this->l('Import')
					);
			}
			return parent::initToolbar();
		}
	}

	public function initToolBarTitle()
	{
		//$this->toolbar_title[] = $this->l('Administration');
		//$this->toolbar_title[] = $this->l('Merchant Expertise');
	}


	public function initPageHeaderToolbar()
	{
		if (empty($this->display))
			$this->page_header_toolbar_btn['new_supplier'] = array(
				'href' => self::$currentIndex.'&addsupplier&token='.$this->token,
				'desc' => $this->l('Add a new supplier'),
				'icon' => 'process-icon-new'
			);

			$this->page_header_toolbar_btn['save'] = array(
				'href' => self::$currentIndex.'&export_csv&token='.$this->token,
				'desc' => $this->l('Export suppliers'),
				'icon' => 'process-icon-save'
			);

		parent::initPageHeaderToolbar();
	}



	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
	}

	public function renderList()
	{
            $this->toolbar_title = $this->l('Suppliers');
		return  parent::renderList();
	}

	/**
	 * @see AdminController::afterAdd()
	 */
	protected function afterAdd($object)
	{
		$id_address = (int)Tools::getValue('id_address');
		$address = new Address($id_address);
		if (Validate::isLoadedObject($address))
		{
			$address->id_supplier = $object->id;
			$address->save();
		}

		//--ERP informations
		// bind erp_supplier_order to supply_order
		if (Tools::isSubmit('id_erpip_supplier') && (int)Tools::getValue('id_erpip_supplier') > 0)
		{
					$id_erpip_supplier = (int)Tools::getValue('id_erpip_supplier');
					$erp_supplier = new ErpSupplier($id_erpip_supplier);
					if (Validate::isLoadedObject($erp_supplier))
					{
							$erp_supplier->id_supplier = $object->id;
							$erp_supplier->save();
					}
		}

		return true;
	}

	/**
	* @see AdminController::afterUpdate()
	*/
	protected function afterUpdate($object)
	{
		$id_address = (int)Tools::getValue('id_address');
		$address = new Address($id_address);
		if (Validate::isLoadedObject($address))
		{
			if ($address->id_supplier != $object->id)
			{
				$address->id_supplier = $object->id;
				$address->save();
			}
		}
		return true;
	}

	protected function afterImageUpload()
	{
		$return = true;
		/* Generate image with differents size */
		if (($id_supplier = (int)Tools::getValue('id_supplier'))
				&& isset($_FILES) && count($_FILES) && file_exists(_PS_SUPP_IMG_DIR_.$id_supplier.'.jpg'))
		{
			$images_types = ImageType::getImagesTypes('suppliers');
			foreach ($images_types as $image_type)
			{
				$file = _PS_SUPP_IMG_DIR_.$id_supplier.'.jpg';
				if (!ImageManager::resize($file, _PS_SUPP_IMG_DIR_.$id_supplier.'-'.Tools::stripslashes($image_type['name']).'.jpg', (int)$image_type['width'], (int)$image_type['height']))
					$return = false;
			}

			$current_logo_file = _PS_TMP_IMG_DIR_.'supplier_mini_'.$id_supplier.'_'.$this->context->shop->id.'.jpg';

			if (file_exists($current_logo_file))
				unlink($current_logo_file);
		}
		return $return;
	}

	public function renderCSV()
	{
	    if (Tools::isSubmit('export_csv'))
	    {
	        // header
	        header('Content-type: text/csv; charset=utf-8');
	        header('Cache-Control: no-store, no-cache');
	        header('Content-disposition: attachment; filename="suppliers.csv"');

	        // write headers column
	        $keys = array(
	                'Name',
	                'Email',
	                'Company',
	                'Firstname',
	                'Lastname',
	                'Address 1',
	                'Address 2',
	                'Post code',
	                'City',
	                'Phone',
	                'GSM',
	                'Fax',
	                'Franco amount',
	                'Discount amount',
	                'Escompte',
	                'Delivery time',
	                'Account number accounting',
	                'Adding date',
	                'Updating date',
	                'activate'
	        );

	        echo sprintf("%s\n", implode(';', $keys));

	        $query = null;
	        $query = new DbQuery();
	        $query->select('s.*, erpips.*, a.company, a.firstname, a.lastname, a.address1, a.address2, a.postcode, a.city, a.phone, a.phone_mobile');

	        $query->from('supplier', 's');
	        $query->leftjoin('erpip_supplier', 'erpips', 'erpips.id_supplier = s.id_supplier');
	        $query->leftjoin('address', 'a', 'a.id_supplier = s.id_supplier');

	        // Execute query
	        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

	        // write datas
	        foreach ($res as $supplier)
	        {
	            $content_csv = array( 
	                self::transformText($supplier['name']),
	                $supplier['email'],
	                self::transformText($supplier['company']),
	                self::transformText($supplier['firstname']),
	                self::transformText($supplier['lastname']),
	                self::transformText($supplier['address1']),
	                self::transformText($supplier['address2']),
	                $supplier['postcode'],
	                $supplier['city'],
	                $supplier['phone'],
	                $supplier['phone_mobile'],
	                $supplier['fax'],
	                $supplier['franco_amount'],
	                $supplier['discount_amount'],
	                $supplier['escompte'],
	                $supplier['delivery_time'],
	                $supplier['account_number_accounting'],
	                $supplier['date_add'],
	                $supplier['date_upd'],
	                $supplier['active'],
	                PHP_EOL
	            );
	           
	            echo implode(';', $content_csv);
	        }
	        die();
	    }
	}

	

		/* RJMA
         * Help to translate AdminAdvancedSupplier controller
	*/
	protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = false)
	{
			if (!empty($class))
			{
				$str = ErpIllicopresta::findTranslation('erpillicopresta', $string, 'AdminAdvancedSupplier');
				$str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
				return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : Tools::stripslashes($str)));
			}
	}
}