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

class ErpStockMvtReason extends StockMvtReasonCore
{

	public static function existsByName($name)
	{
            $query = new DbQuery();
            $query->select('smr.id_stock_mvt_reason');
            $query->from('stock_mvt_reason_lang', 'smr');
            $query->where('smr.name = "'.pSQL($name).'"');

            $rst = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

            if (!empty($rst))
                return (int)$rst;
            else
                return false;
	}

	public static function getIdsByName($name)
	{
            $query = new DbQuery();
            $tab = array();
            $query->select('smr.id_stock_mvt_reason');
            $query->from('stock_mvt_reason_lang', 'smr');
            $query->where('smr.name = "'.pSQL($name).'"');

            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            foreach ($res as $row)
            {
                    $tab[] = $row['id_stock_mvt_reason'];
            }
            return $tab;
	}

	public static function deleteByIds($ids)
	{
            foreach ($ids as $id)
            {
                $query = new DbQuery();
                $query->delete('stock_mvt_reason_lang', 'id_stock_mvt_reason = "'.pSQL($id['id_stock_mvt_reason']).'"');
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);

                $query = null;

                $query = new DbQuery();
                $query->delete('stock_mvt_reason', 'id_stock_mvt_reason = "'.pSQL($id['id_stock_mvt_reason']).'"');
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);
            }
            return true;
	}
}