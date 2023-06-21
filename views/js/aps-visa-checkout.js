/**
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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function(){
	var startTime = new Date().getTime();
	function everyTimeCheckHostedVisaCheckout() {
	    if($("#hosted_visa_checkout_img").length > 0){
			var sdk_url = $("#aps_visa_checkout_data").attr("data-vc_sdk_js_url");
			$.getScript(sdk_url);
			clearInterval(myInterval);
	    }
	    if(new Date().getTime() - startTime > 60000){
			clearInterval(myInterval);
	    }
	}
	aps_vc = aps_visa_checkout_params;
	if (typeof aps_vc != 'undefined' && 'vc_integration_type' in aps_vc && 'hosted_checkout' == aps_vc.vc_integration_type){
		var myInterval = setInterval(everyTimeCheckHostedVisaCheckout, 100);
	}
});
function onVisaCheckoutReady() {
	V.init(
		{
			apikey : $("#aps_visa_checkout_data").attr("data-api_key"), // This will be provided by Amazon Payment Services
			externalProfileId : $("#aps_visa_checkout_data").attr("data-profile_name"),
			settings : {
				locale : $("#aps_visa_checkout_data").attr("data-language"),
				countryCode : $("#aps_visa_checkout_data").attr("data-country_code"), // depends on ISO-3166-1 alpha-2 standard codes
				review : {
					message : $("#aps_visa_checkout_data").attr("data-merchant_message"), //
					buttonAction : 'Continue' // The button label
				},
				threeDSSetup : {
					threeDSActive : "false" // true to enable the 3ds false to disable it
				}
			},
			paymentRequest : {
				currencyCode : $("#aps_visa_checkout_data").attr("data-currency"), //depends on ISO 4217 standard alpha-3 code values
				subtotal : $("#aps_visa_checkout_data").attr("data-amount"), // Subtotal of the payment.
			}
		}
	);
	V.on(
		"payment.success",
		function(payment) {
			if (payment.callid) {
				document.getElementById( "aps_visa_checkout_callid" ).value = payment.callid;
				document.getElementById( "aps_visa_checkout_status" ).value = 'success';
			}
			$("#conditions-to-approve #conditions_to_approve[terms-and-conditions]").prop('checked', true);
			if ($('div.aps_ps16').length) {
				$("#amazonpaymentservices_visa_checkout_form #payment-confirmation .btn").click();
			} else {
				$("#amazonpaymentservices_visa_checkout_form").submit();
			}
		}
	);
	V.on(
		"payment.cancel",
		function(payment) {
			alert("You have cancelled the payment, please try again.");
			location.reload();
		}
	);
	V.on(
		"payment.error",
		function(payment, error) {
			alert(error);
			location.reload();
		}
	);
}
