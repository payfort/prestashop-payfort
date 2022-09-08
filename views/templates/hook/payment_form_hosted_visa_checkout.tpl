{*
* 2007-2021 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<form style="" name="amazonpaymentservices_form" id={$payment_method|escape:'htmlall':'UTF-8'|cat:"_form"} class="aps_redirect_payment_form" method="post">
	<input type="hidden" name="aps_payment_method" id="aps_payment_method" value="{$payment_method|escape:'htmlall':'UTF-8'}">
	<input type="hidden" name="aps_integration_type" id="aps_integration_type" value="{$integration_type|escape:'htmlall':'UTF-8'}">
	<input type="hidden" id="aps_visa_checkout_status" name="aps_visa_checkout_status" value="" />
	<input type="hidden" id="aps_visa_checkout_callid" name="aps_visa_checkout_callid" value="" />
	<img id="hosted_visa_checkout_img" alt="Visa Checkout" class="v-button" role="button" src={$visa_checkout_button_url|escape:'htmlall':'UTF-8'|cat:"?cardBrands=VISA,MASTERCARD,DISCOVER,AMEX"}/>

	<div id="aps_visa_checkout_data" data-language={$language|escape:'htmlall':'UTF-8'} data-amount={$amount|escape:'htmlall':'UTF-8'} data-currency={$currency|escape:'htmlall':'UTF-8'} data-api_key={$api_key|escape:'htmlall':'UTF-8'} data-profile_name={$profile_name|escape:'htmlall':'UTF-8'} data-country_code={$country_code|escape:'htmlall':'UTF-8'}
	data-merchant_message="{$merchant_message|escape:'htmlall':'UTF-8'}"
	data-vc_sdk_js_url={$hosted_visa_checkout_sdk_url|escape:'htmlall':'UTF-8'} >
	</div>
	<div class="aps-loader" id="div-aps-loader" style="display:none">
	    <div class="loader">
	         <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
	    </div>
	</div>
</form>