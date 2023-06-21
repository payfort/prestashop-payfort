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
var apsPayment = (function () {
    return {
        redirectionCheckout : function (response) {
            $('<form id="frm_aps_payment" action="'+response.url+'" method="POST"><input type="submit"/></form>').appendTo('body');
            $.each(response.params, function(k, v){
                $('<input>').attr({
                    type: 'hidden',
                    id: k,
                    name: k,
                    value: v
                }).appendTo('#frm_aps_payment');
            });
            $( '#frm_aps_payment').submit();
        },
        standardCheckout : function (response, payment_method) {
            if ( ! ! response.redirect_url ) {
                setTimeout(function(){window.location.href = response.redirect_url},100);
                return;
            }

            var frame_selector = payment_method+'_aps_merchant_page';
            if($("#"+frame_selector).size()) {
                $( "#"+frame_selector ).remove();
            }

            $( '<form id="aps_merchant_payment_form" action="' + response.url + '" method="POST"><input type="submit"/></form>' ).appendTo( 'body' );
            $.each(
                response.params,
                function(k, v){
                    $( '<input>' ).attr(
                        {
                            type: 'hidden',
                            id: k,
                            name: k,
                            value: v
                        }
                    ).appendTo( '#aps_merchant_payment_form');
                }
            );
            var iFrame         = '#'+payment_method+'-div-aps-iframe';
            var iFrameContent  = 'pf_iframe_content';
            var paymentFormId  = '#aps_merchant_payment_form';

            $( '<iframe name="' + frame_selector + '" id="' + frame_selector + '" height="650px" frameborder="0" scrolling="no" style="display:none" onload="apsPayment.'+payment_method+'iframeLoaded(this)" ></iframe>' ).appendTo( $(iFrame).find( '#' + iFrameContent ) );
            $(iFrame).find( '.aps-iframe-spin' ).show();
            $(iFrame).find( '.aps-iframe-close' ).hide();
            $(iFrame).find( '#' + frame_selector ).attr( "src",  response.url );

            $( paymentFormId ).attr("action",response.url);
            $( paymentFormId ).attr( "target",frame_selector );
            $( paymentFormId ).submit();

            $(iFrame).show();
        },
        hostedCheckout: function( response, payment_method ) {
            if ( ! ! response.redirect_url ) {
                setTimeout(function(){window.location.href = response.redirect_url},100);
            }
            var payment_box = $( "#"+payment_method+'_form');
            $( '<form id="aps_'+payment_method+'" action="' + response.url + '" method="POST"><input type="submit"/></form>' ).appendTo( 'body' );
            var formParams = response.params;
            if ( 'amazonpaymentservices' === payment_method || 'amazonpaymentservices_installments' === payment_method ) {
                if (payment_box.find( '.aps_token_radio:checked' ).val() == "" || 1)
                {
                    formParams.card_number        = payment_box.find( '.aps_card_number' ).val().trim();
                    formParams.card_holder_name   = payment_box.find( '.aps_card_holder_name' ).val();
                    formParams.expiry_date        = payment_box.find( '.aps_expiry_year' ).val() + payment_box.find( '.aps_expiry_month' ).val();
                    formParams.card_security_code = payment_box.find( '.aps_card_security_code' ).val();
                    if(!(formParams.remember_me)){
                        if (payment_box.find( '.aps_card_remember_me' ).is( ':checked' ))
                        {
                            formParams.remember_me = "YES";
                        }
                        else{
                            formParams.remember_me = "NO";
                        }
                    }
                }
                else {
                    formParams.token_name           = payment_box.find( '.aps-radio:checked' ).val();
                    formParams.card_bin             = payment_box.find( '.aps-radio:checked' ).data("cardbin");
                    formParams.card_security_code   = payment_box.find( '.aps_card_security_code' ).val();
                }
            }

            $.each(
                formParams,
                function(k, v){
                    $( '<input>' ).attr(
                        {
                            type: 'hidden',
                            id: k,
                            name: k,
                            value: v
                        }
                    ).appendTo( '#aps_' + payment_method );
                }
            );
            $( '#aps_' + payment_method ).submit();
        },
        closePopup: function(payment_method) {
            $( ".div-aps-iframe" ).hide();
            if ( $('#'+payment_method+'_aps_merchant_page').size() ) {
                $('#'+payment_method+'_aps_merchant_page').remove();
            }
            window.location = aps_front_controller + '&action=merchantPageCancel';
        },
        amazonpaymentservicesiframeLoaded: function(){
            $('.aps-iframe-spin').hide();
            $('.aps-iframe-close').show();
            $('#amazonpaymentservices_aps_merchant_page').show();
        },
        amazonpaymentservices_installmentsiframeLoaded: function(){
            $('.aps-iframe-spin').hide();
            $('.aps-iframe-close').show();
            $('#amazonpaymentservices_installments_aps_merchant_page').show();
        },
        validatePayment: function ( payment_method ) {
            var status = true;
            if('amazonpaymentservices_visa_checkout' == payment_method) {
                return status;
            }
            if ( payment_method ) {
                var payment_box        = $( '#'+payment_method+'_form');
                var card_value         = payment_box.find( ".aps_card_number" ).val();
                var holdername_value   = payment_box.find( ".aps_card_holder_name" ).val();
                var cvv_value          = payment_box.find( ".aps_card_security_code" ).val();
                var expiry_month       = payment_box.find( ".aps_expiry_month" ).val();
                var expiry_year        = payment_box.find( ".aps_expiry_year" ).val();
                
                if ( payment_box.find( '.aps_hosted_form' ).is( ':visible' ) )
                {
                    var validateCard       = APSValidation.validateCard( card_value );
                    var validateHolderName = APSValidation.validateHolderName( holdername_value );
                    var validateExpiry     = APSValidation.validateCardExpiry( expiry_month, expiry_year );
                    var validateCardCVV    = APSValidation.validateSavedCVV( cvv_value, payment_box.find( ".aps_card_security_code" ).attr( 'maxlength' ) );

                    if ( validateCard.validity === false ) {
                        payment_box.find( ".aps_card_error" ).html( validateCard.msg );
                        status = false;
                    } else {
                        if ( ! $( '#amazon_ps_installments_form .aps_card_error' ).hasClass( 'installment_error' ) ) {
                            payment_box.find( ".aps_card_error" ).html( '' );
                        }
                    }
                    if ( validateHolderName.validity === false ) {
                        payment_box.find( ".aps_card_name_error" ).html( validateHolderName.msg );
                        status = false;
                    } else {
                        payment_box.find( ".aps_card_name_error" ).html( '' );
                    }
                    if ( validateCardCVV.validity === false ) {
                        status = false;
                        payment_box.find( ".aps_card_cvv_error" ).html( validateCardCVV.msg );
                    } else {
                        payment_box.find( ".aps_card_cvv_error" ).html( '' );
                    }

                    if ( validateExpiry.validity === false ) {
                        payment_box.find( ".aps_card_expiry_error" ).html( validateExpiry.msg );
                        status = false;
                    } else {
                        payment_box.find( ".aps_card_expiry_error" ).html( '' );
                    }
                }
                if( 'amazonpaymentservices_installments' === payment_method ) {
                    if( $( '.emi_box.selected' ).length >= 1 ) {
                        $( "#installment_plans .aps_plan_error" ).html( '' );
                    } else {
                        if( $.trim( $('#installment_plans .plans .emi_box').html() ).length ) {
                            $( "#installment_plans .aps_plan_error" ).html( APSValidation.translate('required_field') );
                            status = false;
                        }
                    }
                    if (!$('#installment_plans #installment_term').is(':checked')) {
                        $( '#installment_plans .aps_installment_terms_error' ).html(  APSValidation.translate('required_field')  );
                        status = false;
                    } else {
                        $( '#installment_plans .aps_installment_terms_error' ).html('');
                    }
                }else if ( 'amazonpaymentservices' === payment_method ) {
                    // check emi & procedded with full payment exist
                    if ( $( '#installment_plans .emi_box' ).attr( 'data-full-payment' ) == '1' ) {
                        if ( payment_box.find( '.emi_box.selected' ).length >= 1 ) {
                            payment_box.find( ".aps_plan_error" ).html( '' );
                        } else {
                            payment_box.find( ".aps_plan_error" ).html( APSValidation.translate('required_field') );
                            status = false;
                        }
                        if(! $( '#installment_plans .emi_box.selected' ).attr( 'data-full-payment' ) == '1' ){
                            if ( ! payment_box.find( 'input[name="installment_term"]' ).is( ':checked' ) ) {
                                payment_box.find( ".aps_installment_terms_error" ).html( APSValidation.translate('required_field') );
                                status = false;
                            } 
                        }else {
                            payment_box.find( ".aps_installment_terms_error" ).html( '' );
                        }
                    }
                }
            }
            return status;
        },
        valuOtpVerifyBox: function ( response ) {
            $( '.otp_generation_msg' ).html( response.message );
            $( '.valu_form.active' ).slideUp().removeClass( 'active' );
            $( '#verfiy_otp_sec' ).slideDown().addClass( 'active' );
        },
        valuTenureBox: function( response ) {
            //$( '.valu_form.active' ).slideUp().removeClass( 'active' );
            $( '#tenure_sec' ).slideDown().addClass( 'active' );
            $( '#tenure_sec .tenure' ).html( response.tenure_html );
            $( '#tenure_sec .tenure .tenure_carousel' ).slick(
                {
                    dots: false,
                    infinite: false,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    rtl: $( 'body' ).hasClass( 'lang-rtl ' ) ? true : false,
                    arrows: true,
                    prevArrow: '<i class="fa fa-chevron-left tenure-carousel-left-arr"></i>',
                    nextArrow: '<i class="fa fa-chevron-right tenure-carousel-right-arr"></i>'
                }
            );
        }
    }
})();

//Validation control
var APSValidation  = (function () {
    return {
        validateCard: function ( card_number ) {
            var card_type     = "";
            var card_validity = true;
            var message       = '';
            var card_length   = 0;
            if ( card_number ) {
                card_number = card_number.replace( / /g,'' ).replace( /-/g,'' );
                // Visa
                var visa_regex = new RegExp( '^4[0-9]{0,15}$' );
                
                // MasterCard
                var mastercard_regex = new RegExp( '^5$|^5[0-5][0-9]{0,16}$' );
                
                // American Express
                var amex_regex = new RegExp( '^3$|^3[47][0-9]{0,13}$' );
                
                //mada
                var mada_regex = new RegExp( '/^' + mada_bins + '/', 'm' );
                var mada_regex = new RegExp( mada_bins, 'm' );
                
                //meeza
                var meeza_regex = new RegExp( meeza_bins, 'gm' );
                
                if ( card_number.match( mada_regex ) ) {
                    //todo check recurring condition required
                    if (0 && has_recurring_products != '0') {
                        card_validity = false;
                        message       = APSValidation.translate('invalid_card');
                    } else {
                        card_length = 19;
                    }
                    card_type   = 'mada';
                } else if ( card_number.match( meeza_regex ) ) {
                    //todo check recurring condition required
                    if (0 && has_recurring_products != '0') {
                        card_validity = false;
                        message       = APSValidation.translate('invalid_card');
                    } else {
                        card_length = 19;
                    }
                    card_type   = 'meeza';
                } else if( card_number.match( visa_regex ) ) {
                    card_type = 'visa';
                    card_length = 16;
                } else if ( card_number.match( mastercard_regex ) ) {
                    card_type = 'mastercard';
                    card_length = 16;
                } else if ( card_number.match( amex_regex ) ) {
                    card_type = 'amex';
                    card_length = 15;
                } else {
                    card_validity = false;
                    message       = APSValidation.translate('invalid_card');
                }
                
                if ( card_number.length < 15 ) {
                    card_validity = false;
                    message       = APSValidation.translate('invalid_card_length');
                }
            } else {
                message       = APSValidation.translate('card_empty');
                card_validity = false;
            }
            return {
                card_type,
                validity: card_validity,
                msg: message,
                card_length
            }
        },
        validateHolderName: function ( card_holder_name ) {
            var validity     = true;
            var message      = '';
            card_holder_name = card_holder_name.trim();
            if (card_holder_name.length > 255 ) {
                validity = false;
                message  = APSValidation.translate('invalid_card_holder_name');
            }
            return {
                validity,
                msg: message
            }
        },
        validateCVV: function( card_cvv,  ) {
            var validity = true;
            var message  = '';
            card_cvv     = card_cvv.trim();
            if (card_cvv.length > 4 || card_cvv.length == 0) {
                validity = false;
                message  = APSValidation.translate('invalid_card_cvv');
            }
            return {
                validity,
                msg: message
            }
        },
        validateSavedCVV: function( card_cvv, length ) {
            var validity = true;
            var message  = '';
            card_cvv     = card_cvv.trim();
            if ( card_cvv.length != length || card_cvv.length == 0 || card_cvv == '000' ) {
                validity = false;
                message  = APSValidation.translate('invalid_card_cvv');
            }
            return {
                validity,
                msg: message
            }
        },
        validateCardExpiry: function( card_expiry_month, card_expiry_year ) {
            var validity = true;
            var message  = '';
            if ( card_expiry_month === '' || ! card_expiry_month ) {
                validity = false;
                message  = APSValidation.translate('invalid_expiry_month');
            } else if ( card_expiry_year === '' || ! card_expiry_year ) {
                validity = false;
                message  = APSValidation.translate('invalid_expiry_year');;
            } else if ( parseInt( card_expiry_month ) <= 0 || parseInt( card_expiry_month ) > 12  ) {
                validity = false;
                message  = APSValidation.translate('invalid_expiry_month');
            } else {
                var cur_date, exp_date;
                card_expiry_month = ('0' + parseInt( card_expiry_month - 1 )).slice( -2 );
                cur_date          = new Date();
                exp_date          = new Date( parseInt( '20' + card_expiry_year ), card_expiry_month, 30 );
                if (exp_date.getTime() < cur_date.getTime()) {
                    message  = APSValidation.translate('invalid_expiry_date');
                    validity = false;
                }
            }
            return {
                validity,
                msg: message
            }
        },
        validateHostedSavedCVV: function(){
            if ( $( '.aps-radio' ).is( ':checked' ) ) {
                var aps_cvv = $( '.aps-radio:checked' ).parents( '.aps_token_row' ).find( '.aps_saved_card_security_code' );
                if ( ! APSValidation.validateSavedCVV( aps_cvv.val(), aps_cvv.attr( 'maxlength' ) ).validity ) {
                    $( '.field-error' ).removeClass( 'field-error' );
                    aps_cvv.addClass( 'field-error' );
                    $( 'html, body' ).animate(
                        {
                            scrollTop: $( '.field-error' ).offset().top - 50
                        },
                        1000
                    );
                    return false;
                } else {
                    $('.aps_hosted_form .aps_card_security_code').val(aps_cvv.val().trim());
                    aps_cvv.removeClass( 'field-error' );
                }
            }
            return true;
        },
        translate: function(key, category) {
            var message = aps_js_messages[key];
            return message;
        },
    };
})();

var amazonpaymentservices = (function () {
	return {
		submitPlaceOrder: function(payment_method, integration_type = 'redirection') {
            var isValid = true;
            if ( 'amazonpaymentservices_valu' === payment_method ) {
                if ( $( '.tenureBox.selected' ).length === 1 ) {
                    $( ".valu_process_error" ).html( "" );
                } else {
                    if ($(" #tenure_sec .tenure").children().length===0) {
                        $( ".valu_process_error" ).html( APSValidation.translate('valu_pending_msg') );
                    } else {
                        $( ".valu_process_error" ).html( APSValidation.translate('valu_select_plan') );
                    }
                    $( '.aps-loader' ).hide();
                    $("#payment-confirmation button[type='submit']").removeAttr('disabled');
                    return false;
                }
                if ( !($( "#valu_terms" ).is( ':checked' ))) {
                    $( ".tenure_term_error" ).html( APSValidation.translate('valu_terms_msg') );
                    $( '.aps-loader' ).hide();
                    $("#payment-confirmation button[type='submit']").removeAttr('disabled');
                    return false;
                }
                $( '.aps-loader' ).show();
            }
            if('hosted_checkout' == integration_type){
                isValid = apsPayment.validatePayment(payment_method);
                if(!APSValidation.validateHostedSavedCVV()){
                    $("#payment-confirmation button[type='submit']").removeAttr('disabled');
                    return false;
                }
            }
            if(! isValid){
                $("#payment-confirmation button[type='submit']").removeAttr('disabled');
                return false;
            }

            // filter card number from request
            var fdata = $("#"+payment_method+"_form :input")
                .filter(function(index, element) {
                    return $(element).attr('id') != 'aps_card_number';
                })
                .serialize() + '&action=checkout';

			$.ajax({
                url : aps_front_controller,
                type: 'POST',
                dataType: 'json',
                async: true,                
                data: fdata,
                success: function(data)
                {
                    if (data.success) {
                        response = data.data;
                        if ('amazonpaymentservices_valu' === payment_method) {
                            if ( ! ! response.redirect_url ) {
                                setTimeout(function(){window.location.href = response.redirect_url},100);
                                return;
                            }
                        } else if ('redirection' === integration_type) {
                            apsPayment.redirectionCheckout(response);
                        } else if ( integration_type === 'standard_checkout' ) {
                            apsPayment.standardCheckout( response, payment_method );
                        } else if ( integration_type === 'hosted_checkout' ) {
                            apsPayment.hostedCheckout( response, payment_method );
                        }
                    }
                    else if (data.success == false ) {
                        $("#payment-confirmation button[type='submit']").removeAttr('disabled');
                        $('#installment_plans .plans').append('<div class="alert alert-danger alert-dismissible">' + data.error_message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                    else {
                        if (data.url) {
                            window.location = response.url;
                        }
                    }
                },
                error: function () {
                    alert("An error occurred while placing order. Please try again.");
                    window.location.href=window.location.href
                }
            });
		}
	};
})();

jQuery(document).ready(function() {

$("form[name='amazonpaymentservices_form']").submit(function(e) {
    e.preventDefault();
    payment_method   = $(this).find("#aps_payment_method").val();
    integration_type = $(this).find("#aps_integration_type").val();
    amazonpaymentservices.submitPlaceOrder(payment_method, integration_type);
});

$( document.body ).on(
    'keypress paste',
    '.onlynum',
    function(e) {
        var key = e.which || e.keyCode;
        if ( key >= 48 && key <= 57 ) {
            return true;
        }
        return false;
    }
);

$( document.body ).on(
    'keypress paste drop',
    '.aps_card_holder_name',
    function(e) {
        var key = e.which || e.keyCode;
        if ( ( key >= 65 && key <= 90 ) || ( key >= 97 && key <= 122 ) || key == 32 ) {
            return true;
        }
        return false;
    }
);
$( document.body ).on(
    'paste drop',
    '.aps_card_number',
    function(e) {
        return false;
    }
);

$( document.body ).on(
    'keyup',
    '.aps_hosted_payment_form .aps_card_number',
    function(e) {
        var cardnumber = $( this ).val().trim();
        $( this ).parents( '.aps_hosted_payment_form' ).find( '.card-icon.active' ).removeClass( 'active' );
        if ( cardnumber.length >= 4 ) {
            $( this ).parents( '.aps_hosted_payment_form' ).find( '.aps_card_error' ).html( '' );
            var validateCard = APSValidation.validateCard( cardnumber );
            if ( validateCard.card_type ) {
                $( this ).parents( '.aps_hosted_payment_form' ).find( '.card-' + validateCard.card_type + '.card-icon' ).addClass( 'active' );
                if ( 'amex' === validateCard.card_type ) {
                    $( this ).parents( '.aps_hosted_payment_form' ).find( '.aps_card_security_code' ).attr( 'maxlength', 4 );
                } else {
                    $( this ).parents( '.aps_hosted_payment_form' ).find( '.aps_card_security_code' ).attr( 'maxlength', 3 );
                }
                if ( validateCard.card_length >= 1 ) {
                    $( this ).attr( 'maxlength', validateCard.card_length );
                }
                if ( validateCard.validity === true ) {
                    $( this ).parents( '.aps_hosted_payment_form' ).find( '.aps_card_error' ).html( '' );
                }
            }
        }
    }
);

$( document.body ).on(
    'blur',
    '.aps_hosted_payment_form .aps_saved_card_security_code, .aps_hosted_payment_form .aps_card_number',
    function(e) {
        var ele        = $( this );
        var frm_aps    =  ele.parents('.aps_hosted_payment_form');
        payment_method = frm_aps.find("#aps_payment_method").val();

        /*Installments plans need only when embedded hosted checkout
          with amazonpaymentservices payment method
        */
        if( payment_method === 'amazonpaymentservices' && is_embedded_hosted_checkout === 0) {
            return;
        }
        var card_bin      = '';
        var bin_valid     = 0;
        var token_request = 0;
        var cardnumber    = frm_aps.find('.aps_card_number').val().trim();
        if( cardnumber.length >= 15 ) {
            card_bin  = cardnumber.substring( 0,6 );
            bin_valid = 1;
        } else {
            var cvv      = frm_aps.find('.aps_saved_card_security_code').val().trim()
            card_bin = $( this ).parents( '.aps_token_row' ).find( '.aps-radio:checked' ).attr("data-cardbin");
            if ( card_bin.length >= 6 && cvv.length >= 3 ) {
                bin_valid     = 1;
                token_request = 1;
            }
        }
        $( '#installment_plans .plans' ).html( '' );
        $( '#installment_plans .plan_info' ).html( '' );
        $( '#installment_plans .issuer_info' ).html( '' );
        $( '#aps_installment_confirmation_en' ).val( '' );
        $( '#aps_installment_confirmation_ar' ).val( '' );
        $( '#aps_installment_plan_code' ).val( '' );
        $( '#aps_installment_issuer_code' ).val( '' );
        $( '#aps_installment_interest' ).val( '' );
        $( '#aps_installment_amount' ).val( '' );
        if ( bin_valid == 1) {
            ele.parents( 'div.aps_token_group' ).find( '.aps_install_token_error' ).html('');
            frm_aps.find('.aps-loader').show();
            $.ajax(
                {
                    url:aps_front_controller,
                    data:{
                        card_bin : card_bin,
                        action   : 'getInstallmentPlans',
                        embedded_hosted_checkout : is_embedded_hosted_checkout
                    },
                    type:'POST',
                    success:function( response ) {
                        frm_aps.find('.aps-loader').hide();
                        response = JSON.parse( response );
                        if ( 'success' === response.status ) {
                            frm_aps.find('.aps_card_error.installment_error' ).removeClass( 'installment_error' );
                            frm_aps.find('.aps_card_error' ).html( "" );
                            frm_aps.find( '#installment_plans .plans' ).html( response.plans_html );
                            frm_aps.find( '#installment_plans .plan_info' ).html( response.plan_info );
                            if (is_embedded_hosted_checkout != 1) {
                                frm_aps.find('#installment_plans .plan_info').removeClass('validation-off');
                            } else {
                                frm_aps.find('#installment_plans .plan_info').addClass('validation-off');
                            }

                            frm_aps.find( '#installment_plans .issuer_info' ).html( response.issuer_info );
                            frm_aps.find( '#installment_plans .plans .emi_carousel' ).slick(
                                {
                                    dots: false,
                                    infinite: false,
                                    slidesToShow: 3,
                                    slidesToScroll: 1,
                                    rtl: $( 'body' ).hasClass( 'lang-rtl ' ) ? true : false,
                                    arrows: true,
                                    prevArrow: '<i class="fa fa-chevron-left emi-carousel-left-arr"></i>',
                                    nextArrow: '<i class="fa fa-chevron-right emi-carousel-right-arr"></i>'
                                }
                            );
                            frm_aps.find( '#aps_installment_confirmation_en' ).val( response.confirmation_en );
                            frm_aps.find( '#aps_installment_confirmation_ar' ).val( response.confirmation_ar );
                            frm_aps.find( ".with_full_payment" ).parents(".emi_box").height($(".emi_box:not([data-full-payment])").height());
                        } else {
                            if (is_embedded_hosted_checkout != 1) {
                                if (token_request) {
                                    ele.parents('.aps_token_group').find( '.aps_install_token_error' ).html( response.message );
                                }else{
                                    frm_aps.find('.aps_card_error' ).addClass( 'installment_error' );
                                    frm_aps.find('.aps_card_error' ).html( response.message );
                                }
                            }
                            frm_aps.find( '#installment_plans .plans' ).html( response.plans_html );
                            frm_aps.find( '#installment_plans .plan_info' ).html( response.plan_info );
                        }
                    }
                }
            );
        }
    }
);

$( document.body ).on(
    'click',
    '.valu_customer_verify',
    function(e) {
        var mobile_number = $( '.aps_valu_mob_number' ).val().trim();
        var down_payment  = $( '.aps_valu_downpayment' ).val();
        var wallet_amount = $( '.aps_valu_wallet_amount' ).val();
        var cashback_amount = $( '.aps_valu_cashback' ).val();
		down_payment = down_payment >= 0 ? down_payment : 0 ;
        wallet_amount = wallet_amount >= 0 ? wallet_amount : 0 ;
        cashback_amount = cashback_amount >= 0 ? cashback_amount : 0 ;

        $( ".valu_process_error" ).html( "" );
        if(mobile_number.length == 0){
            $( ".valu_process_error" ).html( APSValidation.translate('required_field') );
        } else if (mobile_number.length >= 11 && mobile_number.length <= 19 && mobile_number.match(/^\d+$/) ) {
            $( ".valu_process_error" ).html( "" );
            $( '.aps-loader' ).show();
            $.ajax(
                {
                    url:aps_front_controller,
                    type:'POST',
                    data:{
                        mobile_number : mobile_number,
                        down_payment : down_payment,
                        wallet_amount : wallet_amount,
                        cashback_amount : cashback_amount,
                        action   : 'valu_customer_verify'
                    },
                    beforeSend: function () {
                        $('.valu_customer_verify').attr('disabled', true);
                    },
                    success: function(response) {
                        response = JSON.parse( response );
                        if ( 'success' === response.status ) {
                            $( '.aps-loader' ).hide();
                            apsPayment.valuOtpVerifyBox( response );
                            apsPayment.valuTenureBox( response );
                        } else if ('genotp_error' === response.status) {
                            $( '.aps-loader' ).hide();
                            $( '.valu_process_error' ).html( response.message );
                            $( "#request_otp_sec" ).hide();

                        } else {
                            $( '.aps-loader' ).hide();
                            $( '.aps_valu_otp_verfiy_error' ).html( response.message );
                            $( '.valu_process_error' ).html( response.message );
                            $('.valu_customer_verify').attr('disabled', false);
                        }
                    }
                }
            );
        } else {
            $( ".valu_process_error" ).html( APSValidation.translate('valu_invalid_mobile') );
        }
    }
);

$( document.body ).on(
    'click',
    '.valu_otp_verify',
    function(e) {
        var otp     = $( '.aps_valu_otp' ).val();
        $( ".valu_process_error" ).html( "" );
        $( '.aps-loader' ).show();
        $.ajax(
            {
                url:aps_front_controller,
                type:'POST',
                data:{
                    otp      : otp,
                    action   : 'valu_otp_verify'
                },
                success: function(response) {
                    $( '.aps-loader' ).hide();
                    $( ".valu_process_error" ).html( "" );
                    response = JSON.parse( response );
                    if ( 'success' === response.status ) {
                        apsPayment.valuTenureBox( response );
                    } else {
                        $( '.valu_process_error' ).html( response.message );
                    }
                }
            }
        )
    }
);

$( document.body ).on(
    'click',
    '#tenure_sec .tenureBox',
    function(e) {
        var ele     = $( this );
        var tenure  = ele.attr( 'data-tenure' );
        var tenure_amount   = ele.attr( 'data-tenure-amount' );
        var tenure_interest = ele.attr( 'data-tenure-interest' );
        var otp = $( '.aps_valu_otp').val();
        $( '#aps_otp').val(otp);
        $( '#aps_active_tenure' ).val( tenure );
        $( '#aps_tenure_amount' ).val( tenure_amount );
        $( '#aps_tenure_interest' ).val( tenure_interest );
        $( '.tenureBox.selected' ).removeClass( 'selected' );
        ele.addClass( 'selected' );
    }
);
$(document).ready(function(){
    $(window).resize(function(){
        clearTimeout(window.resizedFinished);
        window.resizedFinished = setTimeout(function(){
        if ( $( '.with_full_payment' ).length >= 1 ) {
                $(".with_full_payment").parents(".emi_box").height($(".emi_box:not([data-full-payment])").height());
            }
        }, 250);
  });
});

$( document.body ).on(
    'click',
    '.emi_box',
    function(e) {
        $( '.emi_box.selected' ).removeClass( 'selected' );
        $( this ).addClass( 'selected' );
        var plan_code   = $( this ).attr( 'data-plan-code' );
        var issuer_code = $( this ).attr( 'data-issuer-code' );
        var interest_text = $( this ).attr( 'data-interest' );
        var interest_amount = $( this ).attr( 'data-amount' );
        var frm_aps =  $( this ).parents('.aps_hosted_payment_form');

        if($( this ).attr( 'data-full-payment' ) == '1'){
            if(!frm_aps.find('#installment_plans .plan_info').hasClass('validation-off')){
                frm_aps.find('#installment_plans .plan_info').addClass('validation-off');
            }
        }else{
            frm_aps.find('#installment_plans .plan_info').removeClass('validation-off');
        }

        frm_aps.find( '#aps_installment_plan_code' ).val( plan_code );
        frm_aps.find( '#aps_installment_issuer_code' ).val( issuer_code );
        frm_aps.find( '#aps_installment_interest' ).val( interest_text );
        frm_aps.find( '#aps_installment_amount' ).val( interest_amount );
        frm_aps.find( '.aps_plan_error').html( '' );
    }
);

$( document.body ).on(
    'blur',
    '.saved_cvv.aps_saved_card_security_code',
    function(e) {
        if ( ! APSValidation.validateSavedCVV( $( this ).val(), $( this ).attr( 'maxlength' ) ).validity ) {
            $( '.field-error' ).removeClass( 'field-error' );
            $( this ).addClass( 'field-error' );
            $( 'html, body' ).animate(
                {
                    scrollTop: $( '.field-error' ).offset().top - 10
                },
                1000
            );
            return false;
        } else {
            $( this ).removeClass( 'field-error' );
        }
    }
);

/*Add new card button on change*/
$('.aps_token_radio').change(function () {
    var frm_aps =  $( this ).parents('.aps_hosted_payment_form');
    if (!(frm_aps.length > 0)) {
        frm_aps =  $( this ).parents('.aps_standard_payment_form');
    }
    frm_aps.find('.token-box input[type="text"]').remove();
    frm_aps.find('.aps_token_radio:checked').attr("required","required");
    frm_aps.find('.aps-radio').removeAttr("required");
    frm_aps.find('.aps_hosted_form').show();
    resetFiledAndValidation(frm_aps);
});
/* Save cards on change*/
$('.aps-radio').change(function () {
    var frm_aps =  $( this ).parents('.aps_hosted_payment_form');
    if (!(frm_aps.length > 0)) {
        frm_aps =  $( this ).parents('.aps_standard_payment_form');
    }
    frm_aps.find('.token-box input[type="text"]').remove();
    var card_type = frm_aps.find('.aps-radio:checked').data("cardtype");
    cvv_length = 3;
    if(card_type == 'amex'){
        cvv_length = 4;
    }
    frm_aps.find('.aps-radio:checked').parents('.aps_token_row').append('<input type="text" id="aps_saved_card_security_code" name="aps_saved_card_security_code" class="aps_saved_card_security_code saved_cvv onlynum" autocomplete="off" maxlength="'+cvv_length+'" required placeholder="CVV">');
    frm_aps.find('.aps_saved_card_security_code').focus();
    frm_aps.find('.aps-radio:checked').attr("required","required");
    frm_aps.find('.aps_token_radio').removeAttr("required");
    frm_aps.find('.aps_hosted_form').hide();
    resetFiledAndValidation(frm_aps);
});

function resetFiledAndValidation(frm_aps){
     frm_aps.find(".issuer_info").html('');
     frm_aps.find(".plan_info").html('');
     frm_aps.find(".plans").html('');
     frm_aps.find(".aps_error").html('');
     if( ! frm_aps.find(".plan_info").hasClass('validation-off') ) {
        frm_aps.find(".plan_info").addClass('validation-off');
     }

    $('.aps_hosted_form .aps_card_number').val('');
    $('.aps_hosted_form .aps_card_holder_name').val('');
    $('.aps_hosted_form .aps_expiry_month').val('');
    $('.aps_hosted_form .aps_expiry_year').val('');
    $('.aps_hosted_form .aps_card_security_code').val('');
}
});