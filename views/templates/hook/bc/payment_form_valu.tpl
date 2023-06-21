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

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  <div class="row"><div class="col-xs-12{if version_compare($smarty.const._PS_VERSION_, '1.6.0.11', '<')} col-md-6{/if}">
{/if}

<div class="payment_module aps_ps16">
	<div class="aps16" title="{$payment_title|escape:'html':'UTF-8'}">
		{if $logo_path != null }
			<img class="logo" src="{$logo_path|escape:'htmlall':'UTF-8'}">
		{/if}
		{$payment_title|escape:'html':'UTF-8'}
		<form style="" name="amazonpaymentservices_form" id={$payment_method|escape:'htmlall':'UTF-8'|cat:"_form"} class="aps_redirect_payment_form" method="post">
			<input type="hidden" name="aps_payment_method" id="aps_payment_method" value="{$payment_method|escape:'htmlall':'UTF-8'}">
			<input type="hidden" name="aps_integration_type" id="aps_integration_type" value="{$integration_type|escape:'htmlall':'UTF-8'}">

		<div id="request_otp_sec" class="valu_form active">
		   {if $allow_downpayment }
				<div class="valu-amount downpayment-field">
					<label class="col-sm-12 aps-pad-none">{l s='Enter valU downpayment amount (Optional):' mod='amazonpaymentservices'}</label>
					<input type="text" value="{$downpayment_value|escape:'htmlall':'UTF-8'}" autocomplete="off" maxlength="6" placeholder="Amount" value="0" class="input-text aps_valu_downpayment onlynum" />
				</div>
			{/if}
			<div class="valu-amount wallet_amount-field">
				<label class="col-sm-12 aps-pad-none">{l s='Enter valU wallet amount (Optional):' mod='amazonpaymentservices'}</label>
				<input type="text" value="" autocomplete="off" maxlength="6" placeholder="Amount" value="0" class="input-text aps_valu_wallet_amount onlynum" />
			</div>
			<div class="valu-amount cashback-field">
				<label class="col-sm-12 aps-pad-none">{l s='Enter valU cashback amount (Optional):' mod='amazonpaymentservices'}</label>
				<input type="text" value="" autocomplete="off" maxlength="6" placeholder="Amount" value="0" class="input-text aps_valu_cashback onlynum" />
			</div>
			<div class="aps-row">
				<div class="aps col-sm-1 aps-pad-none">
					<span class="country_code">{$country_code|escape:'htmlall':'UTF-8'}</span>
				</div>
				<div class="aps col-sm-3 aps-pad-none">
					<input type="text" value="" autocomplete="off" maxlength="19" placeholder="{l s='Enter your mobile number' mod='amazonpaymentservices'}" class="input-text aps_valu_mob_number onlynum" />
				</div>
				<div class="aps col-sm-4">
					<button type="button" class="valu_customer_verify btn btn-primary">{l s='Request OTP' mod='amazonpaymentservices'}</button>
				</div>
			</div>
		</div>

			<div id="verfiy_otp_sec" class="valu_form">
			    <div class="otp_generation_msg aps_success"></div>
			    <div class="aps-row">
			        <div class="aps col-sm-9 aps-pad-none">
			            <input type="password" class="form-control no-outline input-text aps_valu_otp" placeholder="{l s='Enter OTP' mod='amazonpaymentservices'}" onKeyPress="return keyLimit(this,10)" autocomplete="off"/>
			        </div>
			    </div>
			</div>

			<div id="tenure_sec" class="valu_form">
				<input type="hidden" id="aps_active_tenure" name="active_tenure" />
				<input type="hidden" id="aps_tenure_amount" name="tenure_amount" />
				<input type="hidden" id="aps_tenure_interest" name="tenure_interest" />
				<input type="hidden" id="aps_otp" name="aps_otp" />
				<div id="aps_valu_otp_field" class="form-row">
					<div class="install-line">{l s='Please select your Installment plan!' mod='amazonpaymentservices'}</div>
					<div class="tenure">
					</div>
					<div class="termRow mt-1">
						<input type="checkbox" name="valu_terms" id="valu_terms" checked />
						<span class="js-terms">
						{l s='I agree with the valU' mod='amazonpaymentservices'} <a href="{$term_url|escape:'htmlall':'UTF-8'}">{l s='terms and condition' mod='amazonpaymentservices'}</a> {l s='to proceed with the transaction' mod='amazonpaymentservices'}
						</span>
					</div>
					<div class="tenure_term_error aps_error"></div>
				</div>
			</div>

			<label class="valu_process_error aps_error"></label>

			<div class="aps-loader" id="div-aps-loader" style="display:none">
			    <div class="loader">
			         <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
			    </div>
			</div>
			<div id="payment-confirmation">
                <div class="ps-shown-by-js">
                  <button type="submit" class="btn btn-primary center-block">Pay & Place Order</button>
                </div>
            </div>
		</form>
	</div>
</div>
{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  </div></div>
{/if}