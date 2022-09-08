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

<form name="amazonpaymentservices_form" id={$payment_method|escape:'htmlall':'UTF-8'|cat:"_form"} class="aps_hosted_payment_form" method="post">
	<input type="hidden" name="aps_payment_method" id="aps_payment_method" value="{$payment_method|escape:'htmlall':'UTF-8'}"/>
	<input type="hidden" name="aps_integration_type" id="aps_integration_type" value="{$integration_type|escape:'htmlall':'UTF-8'}"/>

	{include file='module:amazonpaymentservices/views/templates/hook/payment_tokens.tpl'}

	<div class="aps_hosted_form">
		<div class="form-group">
			<label class="card-label" for="aps_card_number"> 
				{l s='Card Number' mod='amazonpaymentservices'}
				<span class="required">*</span>
			</label>
			<div class="card-row">
				<input type="text" id="aps_card_number" name="card_number" value="" placeholder="{l s='Card Number' mod='amazonpaymentservices'}" class="form-control aps_card_number onlynum" autocomplete="off" maxlength="19"/>
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/mada-logo.png" alt="{l s='mada' mod='amazonpaymentservices'}" class="card-mada card-icon" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/visa-logo.png" alt="{l s='Visa' mod='amazonpaymentservices'}" class="card-visa card-icon" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/mastercard-logo.png" alt="{l s='mastercard' mod='amazonpaymentservices'}" class="card-mastercard card-icon" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/amex-logo.png" alt="{l s='amex' mod='amazonpaymentservices'}" class="card-amex card-icon" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/meeza-logo.jpg" alt="{l s='meeza' mod='amazonpaymentservices'}" class="card-meeza card-icon" />

				<label class="aps_error aps_card_error"></label>
			</div>
		</div>
		<div class="form-group">
			<label class="card-label" for="aps_card_holder_name"> 
				{l s='Card Holder Name' mod='amazonpaymentservices'}
			</label>
			<div class="card-row">
				<input type="text" id="aps_card_holder_name" name="card_holder_name" value="" placeholder="{l s='Card Holder Name' mod='amazonpaymentservices'}" class="form-control aps_card_holder_name" autocomplete="off" maxlength="50"/>
				<label class="aps_error aps_card_name_error"></label>
			</div>
		</div>
		<div class="form-group">
			<label class="card-label" for="aps_expiry_month"> 
				{l s='Expiry Date' mod='amazonpaymentservices'}
				<span class="required">*</span>
			</label>
			<div class="card-row row">
				<div class="col-md-6">
					<input type="text" id="aps_expiry_month" name="aps_expiry_month" value="" placeholder="{l s='MM' mod='amazonpaymentservices'}" class="form-control aps_expiry_month onlynum " autocomplete="off" size="2" maxlength="2"/>
				</div>
				<div class="col-md-6">
					<input type="text" id="aps_expiry_year" name="aps_expiry_year" value="" placeholder="{l s='YY' mod='amazonpaymentservices'}" class="form-control aps_expiry_year onlynum" autocomplete="off" size="2" maxlength="2"/>
				</div>
			</div>
			<label class="aps_error aps_card_expiry_error"></label>
		</div>
		<div class="form-group">
			<label class="card-label" for="aps_card_security_code"> 
				{l s='CVV' mod='amazonpaymentservices'}
				<span class="required">*</span>
			</label>
			<div class="card-row">
				<input type="text" id="aps_card_security_code" name="aps_card_security_code" value="" placeholder="{l s='CVV' mod='amazonpaymentservices'}" class="form-control aps_card_security_code onlynum" autocomplete="off" size="3" maxlength="4"/>
				<label class="aps_error aps_card_cvv_error"></label>
			</div>
		</div>
		<div class="form-group">
	        <div class="card-row">
	           {if (isset($aps_tokens) && $aps_tokens.is_enabled_tokenization )}
	            <div class="radio">
	                <label>
	                    <input type="checkbox" name="aps_card_remember_me" class="aps_card_remember_me input-checkbox" checked/>
	                    {l s='Save My Card' mod='amazonpaymentservices'}
	                </label>
	            </div>
	            {/if}
	        </div>
	    </div>
	</div>
	<input type="hidden" id="aps_installment_plan_code" name="aps_installment_plan_code" />
    <input type="hidden" id="aps_installment_issuer_code" name="aps_installment_issuer_code" />
    <input type="hidden" id="aps_installment_confirmation_en" name="aps_installment_confirmation_en" />
    <input type="hidden" id="aps_installment_confirmation_ar" name="aps_installment_confirmation_ar" />

    <input type="hidden" id="aps_installment_interest" name="aps_installment_interest" />
    <input type="hidden" id="aps_installment_amount" name="aps_installment_amount" />

    <div id="installment_plans">
        <div class="issuer_info"></div>
        <div class="plans"></div>
        <label class="aps_error aps_plan_error"></label>
        <div class="plan_info"></div>
    </div>
    <div class="aps-loader" id="div-aps-loader" style="display:none">
	    <div class="loader">
	         <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
	    </div>
	</div>
</form>