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
*  @copyright 2007-2014 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{$is_1_6 = $smarty.const._PS_VERSION_|substr:0:3 == '1.6'}

{if $show_toolbar && !$is_1_6}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
{/if}

{if isset($erp_feature)}
{include file=$template_path|cat:'common/erp_sidebar.tpl' erp_feature=$erp_feature}
{/if}

<br/>

<div style="width:600px; margin:auto;">
    
    <h2>
        <a href="{$link->getAdminLink('AdminAdvancedSupplyOrder',true)|escape:'htmlall'}" class="button">
             <i class="icon-arrow-left"></i>
            {l s='Return to supply orders list' mod='erpillicopresta'}
        </a>
    </h2>

    <h2>
        <a href="{$link->getAdminLink('AdminGenerateSupplyOrders',true)|escape:'htmlall'}&submitAction=generateSupplyOrderFormPDF&print_pdf_bulk=true&supply_order_created={$supply_order_created|escape:'htmlall'}" class="button">
            <i class="icon-print"></i>
            {l s='Print supply orders' mod='erpillicopresta'}
        </a>
    </h2>

</div>

{$content}