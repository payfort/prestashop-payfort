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

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your checkout functionality placed here.
	 * should reside in this file.
	 */
	var debug = false;
	jQuery(document).ready(function() {
		if (window.ApplePaySession) {
			if (ApplePaySession.canMakePayments) {
				setTimeout(function(){
					$('#amazonpaymentservices_apple_pay_form').parents('div.js-payment-option-form').prev().show();
				},2000);
			} else {
	            $('#amazonpaymentservices_apple_pay_form').parents('div.js-payment-option-form').prev().hide();
	            $('#amazonpaymentservices_apple_pay_form').parents('div.payment_module').hide();
			}
		} else {
	        $('#amazonpaymentservices_apple_pay_form').parents('div.js-payment-option-form').prev().hide();
	        $('#amazonpaymentservices_apple_pay_form').parents('div.payment_module').hide();
		}
	});

	function initApplePayment( apple_order, evt ) {
		var apple_ele =$('#amazonpaymentservices_apple_pay_form').find("#aps_apple_vars");
		var apple_vars = [];
		apple_vars.supported_networks = apple_ele.attr("data-supported_networks").split(",");
		apple_vars.country_code = apple_ele.attr("data-country_code");
		apple_vars.currency_code = apple_ele.attr("data-currency_code");
		apple_vars.display_name = apple_ele.attr("data-display_name");

		var runningAmount  = parseFloat( apple_order.grand_total );
		var runningPP      = parseFloat( 0 );
		var runningTotal   = function() { return parseFloat( runningAmount + runningPP ).toFixed( 2 ); }
		var shippingOption = "";

		var cart_array         = [];
		var x                  = 0;
		var subtotal           = apple_order.sub_total;
		var tax_total          = apple_order.tax_total;
		var shipping_total     = apple_order.shipping_total;
		var discount_total     = apple_order.discount_total;
		var supported_networks = [];
		apple_vars.supported_networks.forEach(
			function (network) {
				supported_networks.push( network );
			}
		);
		cart_array[x++] = {type: 'final',label: 'Subtotal', amount: parseFloat( subtotal ).toFixed( 2 ) };
		cart_array[x++] = {type: 'final',label: 'Shipping fees', amount: parseFloat( shipping_total ).toFixed( 2 ) };
		if ( parseFloat( discount_total ) >= 1 ) {
			cart_array[x++] = {type: 'final',label: 'Discount', amount: parseFloat( discount_total ).toFixed( 2 ) };
		}
		cart_array[x++] = {type: 'final',label: 'Tax', amount: parseFloat( tax_total ).toFixed( 2 ) };

		function getShippingOptions(shippingCountry){
			if ( shippingCountry.toUpperCase() == apple_vars.country_code ) {
				shippingOption = [{label: 'Standard Shipping', amount: getShippingCosts( 'domestic_std', true ), detail: '3-5 days', identifier: 'domestic_std'},{label: 'Expedited Shipping', amount: getShippingCosts( 'domestic_exp', false ), detail: '1-3 days', identifier: 'domestic_exp'}];
			} else {
				shippingOption = [{label: 'International Shipping', amount: getShippingCosts( 'international', true ), detail: '5-10 days', identifier: 'international'}];
			}
			return shippingOption;
		}

		function getShippingCosts(shippingIdentifier, updateRunningPP ){

			var shippingCost = 0;

			switch (shippingIdentifier) {
				case 'domestic_std':
					shippingCost = 0;
			break;
				case 'domestic_exp':
					shippingCost = 0;
			break;
				case 'international':
					shippingCost = 0;
			break;
				default:
					shippingCost = 0;
			}

			if (updateRunningPP == true) {
				runningPP = shippingCost;
			}

			return shippingCost;

		}
		var paymentRequest = {
			currencyCode: apple_vars.currency_code,
			countryCode: apple_vars.country_code,
			//requiredShippingContactFields: ['postalAddress'],
			lineItems: cart_array,
			total: {
				label: apple_vars.display_name,
				amount: runningTotal()
			},
			supportedNetworks: supported_networks,
			merchantCapabilities: [ 'supports3DS' ]
		};
		var supported_networks_level = 3;
		if($.inArray('mada', supported_networks) != -1){
			supported_networks_level = 5;
		}
		console.log("supported_networks_level" + supported_networks_level);
		console.log(paymentRequest);
		var session = new ApplePaySession( supported_networks_level, paymentRequest );

		// Merchant Validation
		session.onvalidatemerchant = function (event) {
			var promise = performValidation( event.validationURL );
			promise.then(
				function (merchantSession) {
					session.completeMerchantValidation( merchantSession );
				}
			);
		}

		function performValidation(apple_url) {
			return new Promise(
				function(resolve, reject) {
					$.ajax({
		                url : aps_front_controller,
		                type: 'POST',
		                dataType: 'json',
		                data: {
							action: 'validate_apple_url',
							apple_url
						},
		                success: function (data){
							if ( ! data) {
								reject;
							} else {
								data = JSON.parse( data );
								resolve( data );
							}
						},
						error: function( ) {
							reject;
						}
		            });
				}
			);
		}

		session.onpaymentmethodselected = function(event) {
			var newTotal     = { type: 'final', label: apple_vars.display_name, amount: runningTotal() };
			var newLineItems = cart_array;

			session.completePaymentMethodSelection( newTotal, newLineItems );

		}

		session.onpaymentauthorized = function (event) {
			var promise = sendPaymentToken( event.payment.token );
			promise.then(
				function (success) {
					var status;
					if (success) {
						document.getElementById( "applePay" ).style.display = "none";
						status = ApplePaySession.STATUS_SUCCESS;
						sendPaymentToAps( event.payment.token );
					} else {
						status = ApplePaySession.STATUS_FAILURE;
					}

					session.completePayment( status );
				}
			);
		}

		function sendPaymentToken(paymentToken) {
			return new Promise(
				function(resolve, reject) {
					resolve( true );
				}
			);
		}

		function sendPaymentToAps(data) {
			var formId = 'frm_aps_fort_apple_payment';
			if (jQuery( "#" + formId ).length > 0) {
				jQuery( "#" + formId ).remove();
			}

			$( '<form id="' + formId + '" action="#" method="POST"></form>' ).appendTo( 'body' );
			var response  = {};
			response.data = JSON.stringify( { "data" : data} );
			response.action = 'send_apple_payment_aps';
			$.each(
				response,
				function (k, v) {
					$( '<input>' ).attr(
						{
							type: 'hidden',
							id: k,
							name: k,
							value: v
						}
					).appendTo( $( '#' + formId ) );
				}
			);

			$( '#' + formId ).attr( 'action', aps_front_controller );
			$( '#' + formId ).submit();
		}

		session.oncancel = function(event) {
			if (prestashop_version == '1.7') {
				window.location.href = aps_front_controller + '?action=merchantPageCancel';
			} else {
				window.location.href = aps_front_controller + '&action=merchantPageCancel';
			}
		}

		session.begin();
	}
jQuery(document).ready(function() {
	$( document.body ).on(
		'click',
		'#applePay',
		function(evt) {
			// filter card number from request
            var fdata = $("#amazonpaymentservices_apple_pay_form")
                .serialize() + '&action=checkout';
			$.ajax({
                url :      aps_front_controller,
                type:      'POST',
                data:      fdata,
                dataType:  'json',
                async:      false,
                success: function (response){
				},
				complete:	function( response ) {
				},
				error:	function( jqXHR, textStatus, errorThrown ) {
				}
            }).done(function(response){
				if ( response.success ) {
					$( '.apple_pay_process_error' ).html( '' );
					initApplePayment( response.apple_order, evt );
				} else {
					$( '.apple_pay_process_error' ).html( response.messages );
				}
			});
		}
	);
})
})( jQuery );
