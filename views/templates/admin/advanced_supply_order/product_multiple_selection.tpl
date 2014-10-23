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

<!-- ui-dialog -->
<div id="dialog_select_product" title="{l s='Multiple Selection' mod='erpillicopresta'}">

        <!-- Traduction des niveaux de stock-->
        <p>{l s='Please select the products for this order' mod='erpillicopresta'}</p>
        <div id="content" style="margin-left:0;">
          <table class="table table_grid" id="product_select_table">
                <thead>
                <tr>
                  <th><input type="checkbox" id="select_all_product" name="select_all_product" /></th>
                  <th>{l s='Supp ref.' mod='erpillicopresta'}</th>
                  <th>{l s='Intern ref.' mod='erpillicopresta'}</th>
                  <th>{l s='Label' mod='erpillicopresta'}</th>
                  {if $controller_status == true}
                    <th>{l s='Stock level' mod='erpillicopresta'}</th>
                  {/if}
                  <th>{l s='Shop Usa. Qty' mod='erpillicopresta'}</th>
                  {if Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') == '1'}
                      <th>{l s='Shop Phy. Qty' mod='erpillicopresta'}</th>
                      <th>{l s='Real Qty' mod='erpillicopresta'}</th>
                  {/if} 
                  {if $controller_status == true}
                        <th title="{l s='Quantity sold for %d rolling month' sprintf={Configuration::get('ERP_ROLLING_MONTHS_NB_SO')} mod='erpillicopresta'}">{l s='Sold quantity' mod='erpillicopresta'}</th>     
                        <th title="{l s='Sales progression between m and m-1' mod='erpillicopresta'}">{l s='Sales prog' mod='erpillicopresta'}</th>     
                        {if Configuration::get('ERP_SALES_FORECAST_CHOICE') != 0}
                            <th title="{l s='Projected sales over the next 30 days (calculated with six rolling month)' mod='erpillicopresta'}">{l s='Proj sales' mod='erpillicopresta'}</th>   
                        {/if}
                        {* else 
                            <th title="{l s='Projected sales on %d days (calculated by period)' sprintf={Configuration::get ('ERP_PROJECTED_PERIOD')|escape:'htmlall'} mod='erpillicopresta'}">{l s='Proj sales' mod='erpillicopresta'}</th>   
                        *}
                   {/if}
                   <th>{l s='Quantity' mod='erpillicopresta'}</>
                   <th>{l s='Comment' mod='erpillicopresta'}</>
               </tr>
               </thead>
                <tbody>
                    <!-- body rempli avec les valeurs de retour de l'AJAX -->
                </tbody>
         </table>

         <div class="legende_nstock">
            {if $controller_status == true}
                <span class="rupture"></span> {l s='Out of stock' mod='erpillicopresta'}
                <span class="alerte"></span>  {l s='Alert' mod='erpillicopresta'}                    
                <span class="normal"></span> {l s='Normal' mod='erpillicopresta'}
                <span class="surstock"></span> {l s='Overstock' mod='erpillicopresta'}
                <span class="niveau_nr"></span> {l s='Levels not informed' mod='erpillicopresta'}
            {/if}
        </div>
        </div>    
</div>

<!-- ui-dialog -->
<div id="dialog_add_product" title="Add product" class="hide">
        <div id="content"></div>    
</div>

