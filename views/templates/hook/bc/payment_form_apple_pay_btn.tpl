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
<div id="aps_apple_pay_product_buy_btn">
	<div id={$payment_method|escape:'htmlall':'UTF-8'|cat:"_"}{$btn_page|escape:'htmlall':'UTF-8'}>
		<div class="buttons apple_pay_option hide-me">
			{if ($btn_page == 'product')}
				<button type="button" value="Apple pay" id="applePay_{$btn_page|escape:'htmlall':'UTF-8'}_psbc" class="{$button_type|escape:'htmlall':'UTF-8'}"></button>
			{else}
				<button type="button" value="Apple pay" id="applePay_{$btn_page|escape:'htmlall':'UTF-8'}" class="{$button_type|escape:'htmlall':'UTF-8'}"></button>
			{/if}
		</div>
		<label class="apple_pay_process_error aps_error"></label>

		<div id="aps_apple_vars" data-payment_method="{$payment_method|escape:'htmlall':'UTF-8'}" data-integration_type="{$integration_type|escape:'htmlall':'UTF-8'}" data-merchant_identifier="{$merchant_identifier|escape:'htmlall':'UTF-8'}" data-country_code="{$country_code|escape:'htmlall':'UTF-8'}" data-currency_code="{$currency_code|escape:'htmlall':'UTF-8'}" data-display_name="{$display_name|escape:'htmlall':'UTF-8'}" data-supported_networks="{$supported_networks|escape:'htmlall':'UTF-8'}" >
		</div>
		<div class="aps-loader" id="div-aps-loader" style="display:none">
		    <div class="loader">
		         <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
		    </div>
		</div>
	</div>
<div>