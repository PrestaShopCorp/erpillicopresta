{*
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
*}

<input type='hidden' name="id_supply_order"  id="id_supply_order" value="{$smarty.get.id_supply_order|intval}" />

{$header}
{$content}
{$footer}

{* include template to add new product on receipt *}
{if $is_1_6}
    {include file=$template_path|cat:'supply_orders_receipt_history/receipt_footer_1_6.tpl'}
{else}
     {include file=$template_path|cat:'supply_orders_receipt_history/receipt_footer_1_5.tpl'}
{/if}