<?php
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
*/
class ApsConstant
{

    //version
    const APS_VERSION                         = '2.2.0';
    //Payment methods
    const APS_PAYMENT_METHOD_CC               = 'amazonpaymentservices';
    const APS_PAYMENT_METHOD_NAPS             = 'amazonpaymentservices_naps';
    const APS_PAYMENT_METHOD_KNET             = 'amazonpaymentservices_knet';
    const APS_PAYMENT_METHOD_VALU             = 'amazonpaymentservices_valu';
    const APS_PAYMENT_METHOD_VISA_CHECKOUT    = 'amazonpaymentservices_visa_checkout';
    const APS_PAYMENT_METHOD_INSTALLMENTS     = 'amazonpaymentservices_installments';
    const APS_PAYMENT_METHOD_APPLE_PAY        = 'amazonpaymentservices_apple_pay';

    // Integration Types Values
    const APS_INTEGRATION_TYPE_REDIRECTION       = 'redirection';
    const APS_INTEGRATION_TYPE_STANDARD_CHECKOUT = 'standard_checkout';
    const APS_INTEGRATION_TYPE_HOSTED_CHECKOUT   = 'hosted_checkout';
    const APS_INTEGRATION_TYPE_EMBEDDED_HOSTED_CHECKOUT   = 'embedded_hosted_checkout';

    const APS_RETRY_PAYMENT_OPTIONS    = array(
        'VISA',
        'MASTERCARD',
        'AMEX',
        'MADA',
        'MEEZA',
    );
    const APS_RETRY_DIGITAL_WALLETS    = array(
        'VISA_CHECKOUT',
        'APPLE_PAY',
    );

    //Payment response coder
    const APS_PAYMENT_SUCCESS_RESPONSE_CODE               = '14000';
    const APS_TOKENIZATION_SUCCESS_RESPONSE_CODE          = '18000';
    const APS_SAFE_TOKENIZATION_SUCCESS_RESPONSE_CODE     = '18062';
    const APS_UPDATE_TOKENIZATION_SUCCESS_RESPONSE_CODE   = '18063';
    const APS_PAYMENT_CANCEL_RESPONSE_CODE                = '00072';
    const APS_MERCHANT_SUCCESS_RESPONSE_CODE              = '20064';
    const APS_GET_INSTALLMENT_SUCCESS_RESPONSE_CODE       = '62000';
    const APS_VALU_CUSTOMER_VERIFY_SUCCESS_RESPONSE_CODE  = '90000';
    const APS_VALU_CUSTOMER_VERIFY_FAILED_RESPONSE_CODE   = '00160';
    const APS_VALU_OTP_GENERATE_SUCCESS_RESPONSE_CODE     = '88000';
    const APS_VALU_OTP_VERIFY_SUCCESS_RESPONSE_CODE       = '92182';
    const APS_REFUND_SUCCESS_RESPONSE_CODE                = '06000';
    const APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE = '02000';
    const APS_TOKEN_SUCCESS_RESPONSE_CODE                 = '52062';
    const APS_TOKEN_SUCCESS_STATUS_CODE                   = '52';
    const APS_CAPTURE_SUCCESS_RESPONSE_CODE               = '04000';
    const APS_AUTHORIZATION_VOIDED_SUCCESS_RESPONSE_CODE  = '08000';
    const APS_CHECK_STATUS_SUCCESS_RESPONSE_CODE          = '12000';
    const APS_CHECK_STATUS_ORDER_NOT_FOUND_RESPONSE_CODE  = '12036';
    const APS_PAYMENT_TOKEN_UPDATE_RESPONSE_CODE          = '58000';
    const APS_ONHOLD_RESPONSE_CODES                       = array(
        '15777',
        '15778',
        '15779',
        '15780',
        '15781',
        '00006',
        '01006',
        '02006',
        '03006',
        '04006',
        '05006',
        '06006',
        '07006',
        '08006',
        '09006',
        '11006',
        '13006',
        '17006',
    );
    const APS_FAILED_RESPONSE_CODES                       = array(
        '13666',
        '00072',
    );

    //flash messages constant
    const APS_FLASH_MSG_ERROR          ='E';
    const APS_FLASH_MSG_SUCCESS        ='S';
    const APS_FLASH_MSG_INFO           ='I';
    const APS_FLASH_MSG_WARNING        ='W';

    // API Command
    const APS_COMMAND_GET_INSTALLMENT_PLANS = 'GET_INSTALLMENTS_PLANS';
    const APS_COMMAND_TOKENIZATION          = 'TOKENIZATION';
    const APS_COMMAND_STANDALONE            = 'STANDALONE';
    const APS_COMMAND_PURCHASE              = 'PURCHASE';
    const APS_COMMAND_AUTHORIZATION         = 'AUTHORIZATION';
    const APS_COMMAND_VISA_CHECKOUT_WALLET  = 'VISA_CHECKOUT';
    const APS_COMMAND_REFUND                = 'REFUND';
    const APS_COMMAND_RECURRING             = 'RECURRING';
    const APS_COMMAND_CAPTURE               = 'CAPTURE';
    const APS_COMMAND_VOID_AUTHORIZATION    = 'VOID_AUTHORIZATION';
    const APS_COMMAND_ECOMMERCE             = 'ECOMMERCE';
    const APS_COMMAND_CHECK_STATUS          = 'CHECK_STATUS';

    // Generic Constants
    const APS_VALU_EG_COUNTRY_CODE = '+20';

    //API urls
    const GATEWAY_PRODUCTION_URL                  = 'https://checkout.payfort.com/FortAPI/paymentPage';
    const GATEWAY_SANDBOX_URL                     = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';

    const GATEWAY_PRODUCTION_NOTIFICATION_API_URL = 'https://paymentservices.payfort.com/FortAPI/paymentApi/';
    const GATEWAY_SANDBOX_NOTIFICATION_API_URL    = 'https://sbpaymentservices.payfort.com/FortAPI/paymentApi/';

    const VISA_CHECKOUT_BUTTON_PRODUCTION         = "https://assets.secure.checkout.visa.com/wallet-services-web/xo/button.png";
    const VISA_CHECKOUT_BUTTON_SANDBOX            = "https://sandbox-assets.secure.checkout.visa.com/wallet-services-web/xo/button.png";

    const VISA_CHECKOUT_JS_PRODUCTION             = 'https://assets.secure.checkout.visa.com/checkout-widget/resources/js/integration/v1/sdk.js';
    const VISA_CHECKOUT_JS_SANDBOX                = 'https://sandbox-assets.secure.checkout.visa.com/checkout-widget/resources/js/integration/v1/sdk.js';

    // order recurring status
    const RECURRING_ACTIVE = 1;
    const RECURRING_INACTIVE = 2;
    const RECURRING_CANCELLED = 3;
    const RECURRING_SUSPENDED = 4;
    const RECURRING_EXPIRED = 5;
    const RECURRING_PENDING = 6;

    // order recurring transaction status
    const TRANSACTION_DATE_ADDED = 0;
    const TRANSACTION_PAYMENT = 1;
    const TRANSACTION_OUTSTANDING_PAYMENT = 2;
    const TRANSACTION_SKIPPED = 3;
    const TRANSACTION_FAILED = 4;
    const TRANSACTION_CANCELLED = 5;
    const TRANSACTION_SUSPENDED = 6;
    const TRANSACTION_SUSPENDED_FAILED = 7;
    const TRANSACTION_OUTSTANDING_FAILED = 8;
    const TRANSACTION_EXPIRED = 9;

    //Bins
    const MADA_BINS = '440647|440795|446404|457865|968208|457997|474491|636120|417633|468540|468541|468542|468543|968201|446393|409201|458456|484783|462220|455708|410621|455036|486094|486095|486096|504300|440533|489318|489319|445564|968211|410685|406996|432328|428671|428672|428673|968206|446672|543357|434107|407197|407395|412565|431361|604906|521076|529415|535825|543085|524130|554180|549760|968209|524514|529741|537767|535989|536023|513213|520058|558563|588982|589005|531095|530906|532013|968204|422817|422818|422819|428331|483010|483011|483012|589206|968207|419593|439954|530060|531196|420132|421141|588845|403024|968205|406136|42689700';
    const MEEZA_BINS = '507803[0-6][0-9]|507808[3-9][0-9]|507809[0-9][0-9]|507810[0-2][0-9]';
      
    public function __construct()
    {
    }
}
