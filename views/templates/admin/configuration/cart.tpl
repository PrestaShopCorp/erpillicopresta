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
 

<div class="erp-configuration-page prestashop_{$smarty.const._PS_VERSION_|substr:0:3|replace:'.':'_'}">
   
    <div id="cart_features">

        <h2 class="configuration_heading">
            {l s='I create my custom ERP' mod='erpillicopresta'}
        </h2>

        <div>
            
            {if isset($containers)}
            
            <form method="post">
                
                {if !empty($current_basket)}
                    <input type="hidden" name="submitUpdateBasket" value="1" />
                    <input type="hidden" name="current_basket_ids" value="{','|implode:$basket_ids}" />
                {/if}
                               
                <div class="row">
                    
                    <div class="col-lg-6 blok-economiser">
                                                
                        <h4> 
                            {l s='Save up with the pack : ' mod='erpillicopresta'} 
                        </h4>
                        
                        <p class="bold">
                            {l s='You do not have enough time and do not wish to create a personalised ERP?' mod='erpillicopresta'} <br/>
                            {l s='Choose a pre-configured pack!' mod='erpillicopresta'}
                        </p> 
                        
                        
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                <div class="slider global"></div>
                            </div>
                            <div class="col-lg-7 col-lg-offset-1 col-md-5 col-md-offset-1 col-sm-5 col-sm-offset-1 col-xs-12">
                                
                                <div class="row price_slider">
                                
                                    {* ----GET GLOBAL PRICE FOR GLOBAL SLIDER--- *}
                                    
                                    {assign var="global_price_light_purchasse" value=0}
                                    {assign var="global_price_light_sub" value=0}
                                    
                                    {assign var="global_price_pro_purchasse" value=0}
                                    {assign var="global_price_pro_sub" value=0}
                                    
                                    {assign var="free_feature_ids" value=''}
                                    
                                    {foreach item=container from=$containers}
                                        {foreach item="levels" from=$container}
                                            {foreach item=level from=$levels}
                                                {if isset($level->features)}
                                                
                                                    {* if free *}
                                                    {if $level->id == '1'}
                                                    {foreach item=feature from=$level->features}
                                                        {$free_feature_ids = $free_feature_ids|cat:$feature->id|cat:','} 
                                                    {/foreach}
                                                    
                                                    {* if light *}
                                                    {elseif $level->id == '2'}
                                                        
                                                        {$global_price_light_purchasse = $global_price_light_purchasse + $level->purchase_price}
                                                        {$global_price_light_sub = $global_price_light_sub + $level->subscription_price}

                                                    {* if pro *}    
                                                    {elseif $level->id == '3' }

                                                        {$global_price_pro_purchasse = $global_price_pro_purchasse + $level->purchase_price}
                                                        {$global_price_pro_sub = $global_price_pro_sub + $level->subscription_price}

                                                    {/if}
                                                    
                                                {/if}
                                            {/foreach}
                                        {/foreach}
                                    {/foreach}
                                    
                                    <input type="hidden" class="free_feature_ids" name="free_feature_ids" value="{$free_feature_ids}" />
                                    <input type="hidden" class="light_purchasse" name="light_purchasse" value="{$global_price_light_purchasse}" disabled />
                                    <input type="hidden" class="light_sub" name="light_sub" value="{$global_price_light_sub}" disabled />
                                    <input type="hidden" class="pro_purchasse" name="pro_purchasse" value="{$global_price_pro_purchasse}" disabled />
                                    <input type="hidden" class="pro_sub" name="pro_sub" value="{$global_price_pro_sub}" disabled />
                                    
                                    {* Global Price Light *}
                                    <input type="hidden" id="global_price_purchasse_2" value="250" disabled />
                                    <input type="hidden" id="global_price_sub_2" value="19.99" disabled />
                                    
                                    {* Global Price Pro *}
                                    <input type="hidden" id="global_price_purchasse_3" value="450" disabled />
                                    <input type="hidden" id="global_price_sub_3" value="39.99" disabled />
                                    
                                    {* Global diff price *}
                                    <input type="hidden" id="global_price_purchasse_diff" value="200" disabled />
                                    <input type="hidden" id="global_price_sub_diff" value="20" disabled />
                                    
                                    <input type="hidden" id="globa_level_selected" value="{if !empty($globa_level_selected)}{$globa_level_selected}{else}0{/if}" disabled />                                     
                                    
                                    <div class="arrow-right col-lg-1 col-md-1 col-sm-1 col-xs-1"> 
                                    </div>
                                    
                                    <div class="col-lg-10 col-md-11 col-sm-10 col-xs-10"> 
                                        
                                        <span class="bold">{l s='Purchase' mod='erpillicopresta'}</span> : 
                                        <span class="price_purchasse">0</span> € {l s='OOT' mod='erpillicopresta'}
                                        <span class="price_old">
                                            ( <span class="price_purchasse_old"></span> € {l s='OOT' mod='erpillicopresta'} ) 
                                        </span>
                                        
                                        <br/>
                                        
                                        <span class="bold">{l s='Subscription' mod='erpillicopresta'}</span> : 
                                        <span class="price_sub">0</span> € / {l s='Month' mod='erpillicopresta'} 
                                        <span class="price_old">
                                        ( <span class="price_sub_old"></span> € {l s='OOT' mod='erpillicopresta'} / {l s='Month' mod='erpillicopresta'}  ) 
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <div class="col-lg-offset-1 col-lg-5 blok-cart-price">
                        
                        <div class="row">
                            <div class="block-left col-lg-6 col-md-6 col-sm-6">
                                <ul>
                                </ul>
                            </div>
                            <div class="block-right col-lg-6 col-md-6 col-sm-6">
                                
                                <div class="price">
                                    <span id="total_price">0</span> <span class="devise">€</span> <span class="tax">{l s='OOT' mod='erpillicopresta'}</span>  
                                    <div id="total_price_sub">{l s='Or' mod='erpillicopresta'} <span>0</span> € {l s='OOT' mod='erpillicopresta'} {l s='/ Month' mod='erpillicopresta'} </div>
                                </div>
                                                                
                                <button type="submit" name="submitActivateLicence" class="purchasse">
                                    {l s='VALIDATE MY CART' mod='erpillicopresta'}
                                </button>
                                
                                {if Configuration::get('ERP_LICENCE_VALIDITY') == '0'}
                                    <button type="submit" name="submitActivateLicence" class="free">
                                        {l s='ACTIVATE MY FREE VERSION' mod='erpillicopresta'}
                                    </button>
                                 {/if}
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                
                <br/>
                <br/>
               
                <div class="row">

                        {* ---- GET FEATURES LIST --- *}
                        
                        {foreach item=container from=$containers}
                        
                            <div class="row features_list">

                                <div class="col-lg-2 col-md-4 col-sm-4">
                                    
                                    <h4>{$container->container}</h4>
                                    
                                    <div class="slider"></div>
                                    
                                    <div class="row price_slider">
                                        <div class="arrow-right col-lg-1 col-md-1 col-sm-1 col-xs-1"> 
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 feature_price_arrow">
                                            <p class="price_s">
                                                <span class="bold">{l s='Purchase' mod='erpillicopresta'}</span> : <span class="price_purchasse">0</span> €<br/>
                                                <span class="bold">{l s='Subscription' mod='erpillicopresta'}</span> : <span class="price_sub">0</span> € / {l s='Month' mod='erpillicopresta'}
                                            </p>
                                            <p class="not-available" style="display: none;">
                                                <span class="bold">{l s="Not available" mod="erpillicopresta"}</span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                </div>
                                                                
                                <div class="features-desc col-lg-10 col-md-8 col-sm-8">

                                    <ul class="freature_block">
                                        {foreach item="levels" from=$container}
                                        
                                            {foreach item=level from=$levels}
                                            
                                                {if isset($level->features)}
                                                
                                                    {* if light *}
                                                    {if $level->id == '2'}

                                                        <input type="hidden" class="light_price_purchasse" name="light_price_purchasse" value="{$level->purchase_price}" disabled />
                                                        <input type="hidden" class="light_price_sub" name="light_price_sub" value="{$level->subscription_price}" disabled />

                                                    {* if pro *}    
                                                    {elseif $level->id == '3' }

                                                        <input type="hidden" class="pro_price_purchasse" name="pro_price_purchasse" value="{$level->purchase_price}" disabled />
                                                        <input type="hidden" class="pro_price_sub" name="pro_price_sub" value="{$level->subscription_price}" disabled />

                                                    {/if}
                                                        
                                                    {foreach item=feature from=$level->features}
                                                        
                                                        {* INIT VAR *}
                                                        {$input_level_status = 'disabled'} 
                                                        {$in_basket = 'false'} 
                                                    
                                                        {* if no current basket and level = 1 (free) the input is automatically selected *}
                                                        {if empty($current_basket) && $level->id == 1} {$input_level_status = 'enabled'} {/if}
                                                        
                                                        {* if feature is in basket*}
                                                        {if $feature->id|in_array:$current_basket} {$in_basket = 'true'}{/if}
                                                        
                                                        <input type="hidden" class="selected_feature"
                                                               level_id="{$level->id}"
                                                               id="selected_feature_{$level->id}"
                                                               name="selected_feature[]"
                                                               in_basket="{$in_basket}"
                                                               value="{$feature->id}" {$input_level_status} />
                                                    
                                                        {foreach item=controller_details from=$feature->controller_details}
                                                            <li class="{$level->name|lower}_feature {if $level->id == '1'}enable{/if}">
                                                               {$controller_details->name}
                                                            </li>
                                                        {/foreach}

                                                    {/foreach}
                                                    
                                                {/if}
                                                
                                            {/foreach}
                                            
                                        {/foreach}
                                    </ul>
                                    
                                </div>

                            </div>
                        {/foreach}
                        
                </div>
                
            </form>

            {else}

                <div class="alert alert-danger">
                    {l s="Error while getting features. Please contact the customer service." mod="erpillicopresta"}
                </div>

            {/if}

        </div>

    </div>
</div>

<script>

    // JS translation
    var alert_check_all = "{l s='Dear merchant, please complete fully the form above to finalise your order.' mod='erpillicopresta' js=1}";
    var alert_invalid_email = "{l s='Your contact mail address is not valid. Please write a valid mail address.' mod='erpillicopresta' js=1}";
    var alert_invalid_name = "{l s='Your name is not valid. Please write a valid name (no numbers).' mod='erpillicopresta' js=1}";
    
    // current basket converted to javascript array
    var current_basket = {if !empty($current_basket)}{$current_basket|@json_encode}{else}[]{/if};
                        
    var feature_options = ["{l s='Free' mod='erpillicopresta'}", "{l s='Light' mod='erpillicopresta'}", "{l s='Pro' mod='erpillicopresta'}"];
    
    $(".slider")
        .slider({ 
            range: 'min', 
            value: 0,
            min: 0, 
            max: feature_options.length-1, 
            orientation: "horizontal"
        })
        .slider("pips", {
            rest: "label",
            labels: feature_options,
        })
        .slider("float", {
            labels: feature_options
        })
        .on("slidechange", function( e, ui ) {
            
                
            var price_purchasse = price_sub = 0;
            var price_purchasse_old = price_sub_old = 0;
            var features_list = $(this).closest('.features_list');
            var selected_level = parseInt(ui.value) + 1;
            
            // if is not global slider
            if( !$(this).hasClass('global'))
            {                
                features_list.find('ul li.light_feature, ul li.pro_feature').removeClass('enable').removeClass('disable')
                features_list.find('.selected_feature').attr('disabled', true);

                // if free selected
                if( ui.value == 0)
                {
                    features_list.find('ul li.light_feature, ul li.pro_feature').addClass('disable');
                    features_list.find('#selected_feature_'+selected_level).attr('disabled', false);
                }

                // if light selected
                else if( ui.value == 1) 
                {
                    features_list.find('ul li.light_feature').addClass('enable');
                    features_list.find('ul li.pro_feature').addClass('disable');
                    features_list.find('#selected_feature_'+selected_level).attr('disabled', false);

                    // manage price
                    price_purchasse = features_list.find('.light_price_purchasse').val();
                    price_sub = features_list.find('.light_price_sub').val();       
                }

                // if pro selected
                else if( ui.value == 2)
                {
                    features_list.find('ul li.light_feature, ul li.pro_feature').addClass('enable');
                    features_list.find('#selected_feature_'+selected_level).attr('disabled', false);

                    // manage price
                    price_purchasse = features_list.find('.pro_price_purchasse').val();
                    price_sub = features_list.find('.pro_price_sub').val();
                }
                
                if( typeof price_purchasse == "undefined" && ui.value > 0)
                {
                    features_list.find('.feature_price_arrow .price_s').hide();
                    features_list.find('.feature_price_arrow .not-available').show();
                }
                else {
                    features_list.find('.feature_price_arrow .price_s').show();
                    features_list.find('.feature_price_arrow .not-available').hide();
                }
                
                features_list.find('.price_purchasse').text(price_purchasse); 
                features_list.find('.price_sub').text(price_sub); 
                
            }
            
            // if is global slider
            else 
            {
                // set vavlue to feature slider
                $('.features_list').find('.slider').slider('value', ui.value);
                
                // init by show old price
                $(this).closest('.blok-economiser').find('.price_old').show();
                
                // if free selected
                if( ui.value == 0)
                {
                   // hide old price if free selected
                   $(this).closest('.blok-economiser').find('.price_old').hide();
                }
                // if light selected
                else if( ui.value == 1)
                {
                    price_purchasse_old = $(this).closest('.blok-economiser').find('.light_purchasse').val();
                    price_sub_old = $(this).closest('.blok-economiser').find('.light_sub').val();
                    
                    price_purchasse = $(this).closest('.blok-economiser').find('input#global_price_purchasse_2').val();
                    price_sub = $(this).closest('.blok-economiser').find('input#global_price_sub_2').val();
                }
                
                // if pro selected
                else if( ui.value == 2)
                {
                    price_purchasse_old = $(this).closest('.blok-economiser').find('.pro_purchasse').val();
                    price_sub_old = $(this).closest('.blok-economiser').find('.pro_sub').val();
                    
                    price_purchasse = $(this).closest('.blok-economiser').find('input#global_price_purchasse_3').val();
                    price_sub = $(this).closest('.blok-economiser').find('input#global_price_sub_3').val();
                }
                                
                $(this).closest('.blok-economiser').find('span.price_purchasse').text(price_purchasse);
                $(this).closest('.blok-economiser').find('span.price_sub').text(price_sub);
                
                price_purchasse_old = parseFloat(price_purchasse_old) ;
                price_sub_old = parseFloat(price_sub_old);  
                    
                $(this).closest('.blok-economiser').find('span.price_purchasse_old').text(price_purchasse_old.toFixed(2));
                $(this).closest('.blok-economiser').find('span.price_sub_old').text(price_sub_old.toFixed(2));
            }
            
            refreshErpCart();
            
        });

        refreshErpCart();
        
        // set value to global slider if global level selected
        {if !empty($globa_level_selected)}
            $('.slider.global').slider('value', ({$globa_level_selected} - 1) );
            
        // set value to slider with value of current basket if current basket is not empty 
        {else if !empty($current_basket)}
        
            //for each selected feature input 
            $('input.selected_feature').each(function(){
                
                // feature id 
                var selected_feature_feature_id = $(this).val();
                                
                // if feature id in array, slider is setted t this value
                if( $.inArray(selected_feature_feature_id, current_basket) >=0 )
                {
                    var level_id = parseInt($(this).attr('level_id')) - 1 ;
                    $(this).closest('.features_list').find('.slider').slider('value', level_id);
                }
            });
            
        {/if}
        
</script>