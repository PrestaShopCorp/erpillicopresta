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


<!-- DESCRIPTION BLOC -->

<div class="erp-configuration-page prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'}">

    <div class="row header">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12" id="logoLeft">
            <img width="100%" src="../modules/erpillicopresta/img/1click_en.png" alt="{l s='1 Click ERP' mod='erpillicopresta'}" title="{l s='1 Click ERP' mod='erpillicopresta'}" />
        </div>

        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
            <h1> {l s='The first advanced-version Prestashop ERP available for free.' mod='erpillicopresta'}  </h1>
            <h2>{l s='Your personalised store management accelerator.' mod='erpillicopresta'} </h2> 
        </div>

        <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12" id="logoRight">            
            <img src="../modules/erpillicopresta/img/certified.png" alt="{l s='Certified by prestashop' mod='erpillicopresta'}" title="{l s='Certified by prestashop' mod='erpillicopresta'}" />
        </div>
    </div>

    <br />

    <div class="row bloks">

        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">

            <h2 class="configuration_heading">{l s='Time-saving module' mod='erpillicopresta'}</h2>

            <div class="blok_content first">

                <p>
                    <span class="bold">{l s='Save up to 2h per day*' mod='erpillicopresta'}</span>
                </p>
           
                <h4 class="red">
                    {l s='6 functional areas' mod='erpillicopresta'} <br/>
                    {l s='30 features' mod='erpillicopresta'}
                </h4>
            
                <div class="row">
                    
                        <ul class="features_list col-lg-9 col-lg-offset-3 col-md-8 col-md-offset-4 col-sm-9 col-sm-offset-3 col-xs-11 col-xs-offset-1">
                            <li>
                                - {l s='Optimise the management of' mod='erpillicopresta'}
                                <span class="bold">{l s='client orders' mod='erpillicopresta'}</span>
                            </li>
                            <li>
                                - {l s='Improve' mod='erpillicopresta'}
                                <span class="bold">{l s='supplier sheet data' mod='erpillicopresta'}</span>
                            </li>
                            <li>
                                - {l s='Facilitate' mod='erpillicopresta'}
                                <span class="bold">{l s='ordering from suppliers' mod='erpillicopresta'}</span>
                            </li>
                            <li>
                                - {l s='Manage your' mod='erpillicopresta'}
                                <span class="bold">{l s='stock' mod='erpillicopresta'}</span>
                                {l s='efficiently' mod='erpillicopresta'}
                            </li>
                            <li>
                                - {l s='Manage' mod='erpillicopresta'}
                                <span class="bold">{l s='inventories online and offline' mod='erpillicopresta'}</span>
                            </li>
                            <li>
                                - {l s='Restock' mod='erpillicopresta'}
                                <span class="bold">{l s='automatically' mod='erpillicopresta'}</span>
                                {l s=' ' mod='erpillicopresta'}
                            </li>
                        </ul>
                  
                </div>

                <br/>
                
                <p>{l s='Compatible with Prestashop 1.5 et 1.6' mod='erpillicopresta'}</p>

            </div>

        </div>

        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">

            <h2 class="configuration_heading">{l s='A personalised module' mod='erpillicopresta'}</h2>

            <div class="row blok_content">
                <div>
                    
                    <h4 class="red">
                        {l s='The 1ST free, adaptable ERP' mod='erpillicopresta'}
                    </h4>

                    <div class="version">   
                        <p>
                            <span class="bold">
                                {l s='Free version' mod='erpillicopresta'}
                            </span>
                            {l s=' > Activate 1-Click ERP' mod='erpillicopresta'}
                        </p>
                        
                        <br/>
                        
                        <p class="row">
                            <span class="bold col-lg-2">
                                {l s='Advanced version' mod='erpillicopresta'}
                            </span>
                            <span class="col-lg-10">
                                {l s=' > Available for purchase or as a subscription**' mod='erpillicopresta'}   <br/>
                                {l s=' > A number of packs are available to adapt the module to your needs' mod='erpillicopresta'}
                            </span>
                        </p>
                        
                        <p></p>
                    </div>
                    
                </div>
                            
                <div>
                    <a href="{if $erp_iso_code == 'fr'} {$smarty.const.ERP_URL_VIDEO_FR} {else} {$smarty.const.ERP_URL_VIDEO_EN} {/if}" target="_blank" id="video_link">
                        {l s='Read more' mod='erpillicopresta'}
                    </a>
                </div>
                
                <div class="row blok_button">
                    <div class="col-lg-4 col-lg-offset-2 button1 col-md-3 col-md-offset-3 col-sm-4 col-sm-offset-2 col-xs-12">
                        <a href="{if $erp_iso_code == 'fr'} {$smarty.const.ERP_URL_DOC_FR} {else} {$smarty.const.ERP_URL_DOC_EN} {/if}" target="_blank">{l s='Download documentation' mod='erpillicopresta'}</a>
                    </div>
                    
                    <div class="visible-xs"> &nbsp; </div>
                    
                    <div class="col-lg-4 button2 col-md-3 col-sm-4 col-xs-12">
                       <a href="{if $erp_iso_code == 'fr'} {$smarty.const.ERP_URL_CONTACT_FR} {else} {$smarty.const.ERP_URL_CONTACT_EN} {/if}" target="_blank">{l s='Contact us' mod='erpillicopresta'}</a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <br/>
    
    <p>
        {l s='*Amount of time saved is an estimate by the merchants who use 1-Click ERP.' mod='erpillicopresta'}
        {l s='**Subscription without any duration obligation. Cancellation is possible at any time.' mod='erpillicopresta'}
    </p>
    <br/>

    
    {if Configuration::get('ERP_LICENCE_INSTALL_ERROR') == '0' || $blockLicence == false }
    
        <div class="row bloks blok_licence">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <h2 class="configuration_heading red">

                    {if Configuration::get('ERP_LICENCE_VALIDITY') == '0'}
                        {l s='Activation form' mod='erpillicopresta'}
                        <span>{l s='For any free or paid activation, you must first fill out the form below' mod='erpillicopresta'}</span>
                    {else}
                        {l s='Activation information' mod='erpillicopresta'}
                    {/if}
                </h2>
            </div>
        </div>
        
        
        {if $isDevelopper && Configuration::get('ERP_LICENCE_VALIDITY') == '0' && Configuration::get('ERP_NEW_LICENCE') != '' }
            
            <div class="{if {$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'} == '1_6' }alert alert-info{else}hint clear{/if}" style="display: block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <ul id="infos_block" class="list-unstyled">
                    <li><b>{l s='Carefull your license would be attached to non routable IP' mod='erpillicopresta'} {Configuration::get('PS_SHOP_DOMAIN')}</b>.<br></li>
                    <li><b>{l s='When your store would be launched in production, you will have to process to a free migration of your license.' mod='erpillicopresta'}</b><br></li>
                </ul>
            </div>
        
            <br/>
        
        {/if}
    
       
        {if isset($forms) && !empty($forms)}
            {foreach from=$forms item=unique_form}
                {$unique_form}
            {/foreach}
        {/if}
    {/if}

</div>

<!-- END DESCRIPTION BLOC -->