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

<div id="extruderLeft" class="{literal}{title:'ERP'}{/literal} prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'}" style="display: none;">
    {if $erp_feature}
        {foreach from=$erp_feature item=feature}
        <div class="voice">
            {if $feature['active']}
            <a class="label" href="index.php?controller={$feature.controller|escape:'htmlall'}&token={$feature.token|escape:'htmlall'}">
                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall'}erpillicopresta/img/features/{$feature.picture|escape:'htmlall'}" style="width:32px;height:32px;"/>
                {$feature.name|escape:'htmlall'}
            </a>
            {else}
                <a class="label" href="javascript:void();">
                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall'}erpillicopresta/img/features/none.png" style="width:32px;height:32px;"/>
                <i>{$feature.name|escape:'htmlall'}</i>
                </a>
            {/if}
        </div>
        {/foreach}
    {/if}
</div>