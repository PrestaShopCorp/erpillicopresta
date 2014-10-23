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

{extends file="helpers/list/list_footer.tpl"}
{block name=after}
    <div class="legende_nstock">
            <span class="rupture"></span> {l s='Out of stock' mod='erpillicopresta'}
            <span class="alerte"></span>  {l s='Alert' mod='erpillicopresta'}                    
            <span class="normal"></span> {l s='Normal' mod='erpillicopresta'}
            <span class="surstock"></span> {l s='Overstock' mod='erpillicopresta'}
            <span class="niveau_nr"></span> {l s='Levels not informed' mod='erpillicopresta'}
    </div>
{/block}