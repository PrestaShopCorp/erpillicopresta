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
*  @copyright 2007-2014 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ErpOrder extends Order
{

	/*
	* check if is mondialrelay order
	*/
	public static function isMROrder ($id_order)
	{
		if (!Module::isEnabled('mondialrelay'))
			return false;

		$query = '
		SELECT COUNT(*)
		FROM '._DB_PREFIX_.'orders
		WHERE id_order = "'.(int)$id_order.'" AND
		id_carrier IN (SELECT id_carrier
		FROM '._DB_PREFIX_.'mr_method)';

		$tab = Db::getInstance()->getValue($query);
		if ($tab == 0)
			return false;

		return true;
	}

	/*
	* check if is TNT order
	*/
	public static function isTntOrder ($id_order)
	{
		if (!Module::isEnabled('tntcarrier'))
			return false;

		$query = 'SELECT COUNT(*)
		FROM '._DB_PREFIX_.'orders
		WHERE id_order = "'.(int)$id_order.'" AND
		id_carrier IN (SELECT id_carrier
		FROM '._DB_PREFIX_.'tnt_carrier_option)';

		if ((int)Db::getInstance()->getValue($query) == 0)
			return false;
		return true;
	}

	/*
	* check if is Expeditor carrier
	*/
	public static function isExpeditorCarrier ($id_carrier)
	{
		if (!Module::isEnabled('expeditor'))
			return false;

		foreach (explode(',', ConfigurationCore::get('EXPEDITOR_CARRIER')) as $carrier)
			if ($carrier == $id_carrier)
				return true;

		return false;
	}

	/*
	* getDocuments
	*/
	public function getDocuments()
	{
		$invoices = $this->getInvoicesCollection()->getResults();
		$delivery_slips = $this->getDeliverySlipsCollection()->getResults();
		// @TODO review
		foreach ($delivery_slips as $delivery)
		{
			$delivery->is_delivery = true;
			$delivery->date_add = $delivery->delivery_date;
		}
		$order_slips = $this->getOrderSlipsCollection()->getResults();

		$documents = array_merge($invoices, $order_slips, $delivery_slips);
		usort($documents, array('ErpOrder', 'erpSortDocuments'));
		return $documents;
	}


	public static function erpSortDocuments($a, $b)
	{
		if ($a->date_add == $b->date_add)
				return 0;
		return ($a->date_add < $b->date_add) ? -1 : 1;
	}
	/*
	* return orders with the specified produit_id and product_attribute_id
	*/
	public static function getOrdersByProductAndAttribute($id_product, $id_attribute = 0)
	{
		return Db::getInstance()->executeS('
			SELECT o.id_order
			FROM '._DB_PREFIX_.'orders o
			LEFT JOIN '._DB_PREFIX_.'order_detail od ON (o.id_order = od.id_order)
			WHERE od.product_id = '.(int)$id_product.' AND od.product_attribute_id = '.(int)$id_attribute.'
			ORDER BY o.date_add DESC
		');
	}


	/*
	*	get product of order
	*/
	public function getListOfProducts($action_type = null)
	{
		// get products of customer order
		if (is_null($action_type) || $action_type == 'customer')
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT od.product_id, od.product_attribute_id
				FROM `'._DB_PREFIX_.'order_detail` od
				WHERE od.`id_order` = '.(int)($this->id));

		// get products of supplier order
		elseif ($action_type == 'supplier')
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT sod.product_id, sod.product_attribute_id
				FROM `'._DB_PREFIX_.'supply_order_detail` sod
				WHERE sod.`id_supply_order` = '.(int)($this->id));
	}


	/*
	* List product order ordered quantity
	*/
	public function getListOfProductsWithQuantity()
	{
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT od.product_id, od.product_attribute_id, od.product_quantity
				FROM `'._DB_PREFIX_.'order_detail` od
				WHERE od.`id_order` = '.(int)($this->id));
	}


	public static function getIdCarrierbyIdOrder ($id_order)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT id_order_carrier
			FROM `'._DB_PREFIX_.'order_carrier` o
			WHERE o.`id_order` = '.(int)($id_order));
	}

	public static function getIdStateByIdOrder ($id_order)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT current_state
			FROM `'._DB_PREFIX_.'orders` o
			WHERE o.`id_order` = '.(int)($id_order));
	}
        
        // Gestionnaire d'erreurs
        public static function ErpOrdersAjaxErrorHandler($errno, $errstr, $errfile, $errline)
        {
           if (!(error_reporting() & $errno)) {
                // Ce code d'erreur n'est pas inclus dans error_reporting()
                return;
            }
            
            $context = Context::getContext();
            $context->cookie->__unset('errorOrderAjaxHandler');
            
            switch ($errno) {
                case E_USER_ERROR:
                    $context->cookie->__set('errorOrderAjaxHandler', 'Error : '.$errstr.' - ligne '.$errline.' file '.$errfile);
                    break;
                case E_USER_WARNING:
                case E_USER_NOTICE:
                default:
                    $context->cookie->__set('errorOrderAjaxHandler', 'Error : '.$errstr);
                    break;
            }
        }

}