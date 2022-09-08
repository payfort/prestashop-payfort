<?php


class AmazonpaymentservicesConfig extends AmazonpaymentservicesSuper
{
    private static $instance;
    private $aps_status;
    private $merchant_identifier;
    private $access_code;
    private $request_sha_phrase;
    private $response_sha_phrase;
    private $sandbox_mode;
    private $command;
    private $sha_type;
    private $gateway_currency;
    private $debug;
    private $order_status_id;
    private $host_to_host_url;
    private $cc_status;
    private $cc_integration_type;
    private $cc_show_mada_branding;
    private $cc_show_meeza_branding;
    private $cc_mada_bins;
    private $cc_meeza_bins;
    private $tokenization;
    private $hide_delete_token;
    private $cc_sort_order;
    private $visa_checkout_status;
    private $visa_checkout_integration_type;
    private $visa_checkout_api_key;
    private $visa_checkout_profile_name;
    private $visa_checkout_sort_order;
    private $installments_status;
    private $installments_integration_type;
    private $installments_sar_order_min_value;
    private $installments_aed_order_min_value;
    private $installments_egp_order_min_value;
    private $installments_issuer_name;
    private $installments_issuer_logo;
    private $installments_sort_order;
    private $naps_status;
    private $naps_sort_order;
    private $knet_status;
    private $knet_sort_order;
    private $valu_status;
    private $valu_order_min_value;
    private $valu_sort_order;
    private $apple_pay_status;
    private $apple_pay_sha_type;
    private $apple_pay_btn_type;
    private $apple_pay_access_code;
    private $apple_pay_request_sha_phrase;
    private $apple_pay_response_sha_phrase;
    private $apple_pay_domain_name;
    private $apple_pay_display_name;
    private $apple_pay_supported_network;
    private $apple_pay_production_key;
    private $apple_pay_sort_order;
    private $apple_pay_product_page;
    private $apple_pay_cart_page;
    private $check_status_cron_url;
    private $check_status_cron_duration;

    /**
     * Array of keys contain origin Db keys
     *
     * @var array config fields
     */
    public static $aps_config_fields = array(
        'aps_status' => 'AMAZONPAYMENTSERVICES_STATUS',
        'merchant_identifier' => 'AMAZONPAYMENTSERVICES_MERCHANT_IDENTIFIER',
        'access_code' => 'AMAZONPAYMENTSERVICES_ACCESS_CODE',
        'request_sha_phrase' => 'AMAZONPAYMENTSERVICES_REQUEST_SHA_PHRASE',
        'response_sha_phrase' => 'AMAZONPAYMENTSERVICES_RESPONSE_SHA_PHRASE',
        'sandbox_mode' => 'AMAZONPAYMENTSERVICES_SANDBOX_MODE',
        'command' => 'AMAZONPAYMENTSERVICES_COMMAND',
        'sha_type' => 'AMAZONPAYMENTSERVICES_SHA_TYPE',
        'gateway_currency' => 'AMAZONPAYMENTSERVICES_GATEWAY_CURRENCY',
        'debug' => 'AMAZONPAYMENTSERVICES_DEBUG',
        'order_status_id' => 'AMAZONPAYMENTSERVICES_ORDER_STATUS_ID',
        'host_to_host_url' => 'AMAZONPAYMENTSERVICES_HOST_TO_HOST_URL',
        'cc_status' => 'AMAZONPAYMENTSERVICES_CC_STATUS',
        'cc_integration_type' => 'AMAZONPAYMENTSERVICES_CC_INTEGRATION_TYPE',
        'cc_show_mada_branding' => 'AMAZONPAYMENTSERVICES_CC_SHOW_MADA_BRANDING',
        'cc_show_meeza_branding' => 'AMAZONPAYMENTSERVICES_CC_SHOW_MEEZA_BRANDING',
        'cc_mada_bins' => 'AMAZONPAYMENTSERVICES_CC_MADA_BINS',
        'cc_meeza_bins' => 'AMAZONPAYMENTSERVICES_CC_MEEZA_BINS',
        'tokenization' => 'AMAZONPAYMENTSERVICES_TOKENIZATION',
        'hide_delete_token' => 'AMAZONPAYMENTSERVICES_HIDE_DELETE_TOKEN',
        'cc_sort_order' => 'AMAZONPAYMENTSERVICES_CC_SORT_ORDER',
        'visa_checkout_status' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_STATUS',
        'visa_checkout_integration_type' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_INTEGRATION_TYPE',
        'visa_checkout_api_key' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_API_KEY',
        'visa_checkout_profile_name' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_PROFILE_NAME',
        'visa_checkout_sort_order' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_SORT_ORDER',
        'installments_status' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_STATUS',
        'installments_integration_type' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_INTEGRATION_TYPE',
        'installments_sar_order_min_value' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_SAR_ORDER_MIN_VALUE',
        'installments_aed_order_min_value' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_AED_ORDER_MIN_VALUE',
        'installments_egp_order_min_value' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_EGP_ORDER_MIN_VALUE',
        'installments_issuer_name' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_ISSUER_NAME',
        'installments_issuer_logo' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_ISSUER_LOGO',
        'installments_sort_order' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_SORT_ORDER',
        'naps_status' => 'AMAZONPAYMENTSERVICES_NAPS_STATUS',
        'naps_sort_order' => 'AMAZONPAYMENTSERVICES_NAPS_SORT_ORDER',
        'knet_status' => 'AMAZONPAYMENTSERVICES_KNET_STATUS',
        'knet_sort_order' => 'AMAZONPAYMENTSERVICES_KNET_SORT_ORDER',
        'valu_status' => 'AMAZONPAYMENTSERVICES_VALU_STATUS',
        'valu_order_min_value' => 'AMAZONPAYMENTSERVICES_VALU_ORDER_MIN_VALUE',
        'valu_sort_order' => 'AMAZONPAYMENTSERVICES_VALU_SORT_ORDER',
        'apple_pay_status' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_STATUS',
        'apple_pay_sha_type' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_SHA_TYPE',
        'apple_pay_btn_type' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_BTN_TYPE',
        'apple_pay_access_code' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_ACCESS_CODE',
        'apple_pay_request_sha_phrase' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_REQUEST_SHA_PHRASE',
        'apple_pay_response_sha_phrase' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_RESPONSE_SHA_PHRASE',
        'apple_pay_domain_name' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_DOMAIN_NAME',
        'apple_pay_display_name' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_DISPLAY_NAME',
        'apple_pay_supported_network' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_SUPPORTED_NETWORK',
        'apple_pay_production_key' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_PRODUCTION_KEY',
        'apple_pay_sort_order' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_SORT_ORDER',
        'apple_pay_product_page' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_PRODUCT_PAGE',
        'apple_pay_cart_page' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_CART_PAGE',
        'check_status_cron_url' => 'AMAZONPAYMENTSERVICES_CHECK_STATUS_CRON_URL',
        'check_status_cron_duration' => 'AMAZONPAYMENTSERVICES_CHECK_STATUS_CRON_DURATION'
    );

    public function __construct()
    {
        parent::__construct();
        $this->gatewayProductionHostUrl    = ApsConstant::GATEWAY_PRODUCTION_URL;
        $this->gatewaySandboxHostUrl       = ApsConstant::GATEWAY_SANDBOX_URL;
        $this->gatewayProductionNotiApiUrl = ApsConstant::GATEWAY_PRODUCTION_NOTIFICATION_API_URL;
        $this->gatewaySandboxNotiApiUrl    = ApsConstant::GATEWAY_SANDBOX_NOTIFICATION_API_URL;
        ;
       
        foreach (self::$aps_config_fields as $key => $dbKey) {
            $this->$key = Configuration::get($dbKey, null);
        }
    }

    /**
     * @return Amazonpaymentservices_Config
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new AmazonpaymentservicesConfig();
        }
        return self::$instance;
    }

    public static function getConfigKeys()
    {
        return self::$aps_config_fields;
    }

    private function _getConfig($key)
    {
        $key = strtoupper($key);
        return Configuration::get('AMAZONPAYMENTSERVICES_STATUS' . $key);
    }

    public function getLanguage()
    {
        $language = $this->context->language->iso_code;
        if ($language != 'ar') {
            $language = 'en';
        }
        return $language;
    }

    public function getStatus()
    {
        return $this->aps_status;
    }

    public function getMerchantIdentifier()
    {
        return $this->merchant_identifier;
    }

    public function getAccessCode()
    {
        return $this->decodeValue($this->access_code);
    }

    public function getRequestShaPhrase()
    {
        return $this->decodeValue($this->request_sha_phrase);
    }

    public function getResponseShaPhrase()
    {
        return $this->decodeValue($this->response_sha_phrase);
    }

    public function getSandboxMode()
    {
        return $this->sandbox_mode;
    }

    public function decodeValue($value)
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    public function getCommand($paymentMethod =ApsConstant::APS_PAYMENT_METHOD_CC, $card_number = null, $card_type = null)
    {
        $mada_regex  = '/^' . $this->getMadaBins() . '/';
        $meeza_regex = '/^' . $this->getMeezaBins() . '/';

        $command            = $this->command;
        $authorized_methods = array(
            ApsConstant::APS_PAYMENT_METHOD_CC,
            ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT,
            ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY,
        );
        if ('AUTHORIZATION' === $command && ! in_array($paymentMethod, $authorized_methods, true)) {
            $command = 'PURCHASE';
        }
        if ('AUTHORIZATION' === $command && ApsConstant::APS_PAYMENT_METHOD_CC === $paymentMethod) {
            if (! empty($card_number)) {
                if (preg_match($mada_regex, $card_number) || preg_match($meeza_regex, $card_number)) {
                    $command = 'PURCHASE';
                }
            } elseif (! empty($card_type)) {
                if ('MADA' === $card_type || 'MEEZA' === $card_type) {
                    $command = 'PURCHASE';
                }
            }
        }
        //todo
        /*if($this->cart->hasRecurringProducts()){
            $command = 'PURCHASE';
        }*/
        return $command;
    }

    public function getShaType()
    {
        return $this->sha_type;
    }

    public function getGatewayCurrency()
    {
        return $this->gateway_currency;
    }
      
    public function getDebugMode()
    {
        return $this->debug;
    }

    public function getHostUrl()
    {
        return $this->hostUrl;
    }

    public function getCcStatus()
    {
        return $this->cc_status;
    }

    public function getCcIntegrationType()
    {
        return $this->cc_integration_type;
    }

    public function getCcShowMeezaBranding()
    {
        return $this->cc_show_meeza_branding;
    }

    public function getCcShowMadaBranding()
    {
        return $this->cc_show_mada_branding;
    }
    
    public function isEnabledTokenization()
    {
        if ($this->tokenization) {
            return true;
        }
        return false;
    }
    
    public function isHideDeleteToken()
    {
        if ($this->hide_delete_token) {
            return true;
        }
        return false;
    }
    
    public function getEnabledTokenization()
    {
        return ! empty($this->tokenization) ? $this->tokenization : 1;
    }
    
    public function getHideDeleteToken()
    {
        return ! empty($this->hide_delete_token) ? $this->hide_delete_token : 0;
    }

    public function getCcSortOrder()
    {
        return $this->cc_sort_order;
    }

    public function getNapsStatus()
    {
        return $this->naps_status;
    }

    public function getNapsSortOrder()
    {
        return $this->naps_sort_order;
    }

    public function getInstallmentsStatus()
    {
        return $this->installments_status;
    }
  
    public function getInstallmentsIntegrationType()
    {
        return $this->installments_integration_type;
    }
    
    public function getInstallmentsSAROrderMinValue()
    {
        return ! empty($this->installments_sar_order_min_value) ? $this->installments_sar_order_min_value : 1000;
    }

    public function getInstallmentsAEDOrderMinValue()
    {
        return ! empty($this->installments_aed_order_min_value) ? $this->installments_aed_order_min_value : 1000;
    }

    public function getInstallmentsEGPOrderMinValue()
    {
        return ! empty($this->installments_egp_order_min_value) ? $this->installments_egp_order_min_value : 1000;
    }

    public function getInstallmentsIssuerName()
    {
        return $this->installments_issuer_name;
    }

    public function getInstallmentsIssuerLogo()
    {
        return $this->installments_issuer_logo;
    }

    public function getInstallmentsSortOrder()
    {
        return $this->installments_sort_order;
    }
    
    public function getKnetStatus()
    {
        return $this->knet_status;
    }

    public function getKnetSortOrder()
    {
        return $this->knet_sort_order;
    }

    public function getValuStatus()
    {
        return $this->valu_status;
    }

    public function getValuOrderMinValue()
    {
        return $this->valu_order_min_value;
    }

    public function getValuSortOrder()
    {
        return $this->valu_sort_order;
    }

    public function getVisaCheckoutStatus()
    {
        return $this->visa_checkout_status;
    }

    public function getVisaCheckoutIntegrationType()
    {
        return $this->visa_checkout_integration_type;
    }
    
    public function getVisaCheckoutApiKey()
    {
        return $this->decodeValue($this->visa_checkout_api_key);
    }

    public function getVisaCheckoutProfileName()
    {
        return $this->visa_checkout_profile_name;
    }

    public function getVisaCheckoutSortOrder()
    {
        return $this->visa_checkout_sort_order;
    }

    public function getVisaCheckoutButton()
    {
        if ($this->isSandboxMode()) {
            return ApsConstant::VISA_CHECKOUT_BUTTON_SANDBOX;
        }
        return ApsConstant::VISA_CHECKOUT_BUTTON_PRODUCTION;
    }

    public function getVisaCheckoutJS()
    {
        if ($this->isSandboxMode()) {
            return ApsConstant::VISA_CHECKOUT_JS_SANDBOX;
        }
        return ApsConstant::VISA_CHECKOUT_JS_PRODUCTION;
    }

    public function getApplePayStatus()
    {
        return $this->apple_pay_status;
    }

    public function getApplePayShaType()
    {
        return $this->apple_pay_sha_type;
    }

    public function getApplePayButtonType()
    {
        return $this->apple_pay_btn_type;
    }

    public function getApplePayAccessCode()
    {
        return $this->decodeValue($this->apple_pay_access_code);
    }

    public function getApplePayRequestShaPhrase()
    {
        return $this->decodeValue($this->apple_pay_request_sha_phrase);
    }

    public function getApplePayResponseShaPhrase()
    {
        return $this->decodeValue($this->apple_pay_response_sha_phrase);
    }

    public function getApplePayDomainName()
    {
        return $this->apple_pay_domain_name;
    }

    public function getApplePayDisplayName()
    {
        return $this->apple_pay_display_name;
    }

    public function getApplePaySupportedNetwork()
    {
        return $this->apple_pay_supported_network;
    }

    public function getApplePayProductionKey()
    {
        return $this->apple_pay_production_key;
    }

    public function getApplePaySortOrder()
    {
        return $this->apple_pay_sort_order;
    }

    public function isActive()
    {
        if ($this->status) {
            return true;
        }
        return false;
    }

    public function isSandboxMode()
    {
        if ($this->sandbox_mode) {
            return true;
        }
        return false;
    }

    public function isDebugMode()
    {
        if ($this->debug) {
            return true;
        }
        return false;
    }

    public function isCcActive()
    {
        if ($this->cc_status) {
            return true;
        }
        return false;
    }

    public function isCcStandardCheckout()
    {
        if ($this->cc_integration_type == ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT) {
            return true;
        }
        return false;
    }

    public function isCcHostedCheckout()
    {
        if ($this->cc_integration_type == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT) {
            return true;
        }
        return false;
    }

    public function isNapsActive()
    {
        if ($this->naps_status) {
            return true;
        }
        return false;
    }

    public function isInstallmentsActive()
    {
        if ($this->installments_status) {
            return true;
        }
        return false;
    }

    public function isInstallmentsStandardCheckout()
    {
        if ($this->installments_integration_type == ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT) {
            return true;
        }
        return false;
    }

    public function isInstallmentsHostedCheckout()
    {
        if ($this->installments_integration_type == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT) {
            return true;
        }
        return false;
    }

    public function isEmbeddedHostedCheckout()
    {
        if ($this->installments_integration_type == ApsConstant::APS_INTEGRATION_TYPE_EMBEDDED_HOSTED_CHECKOUT) {
            return true;
        }
        return false;
    }

    public function isKnetActive()
    {
        if ($this->knet_status) {
            return true;
        }
        return false;
    }

    public function isValuActive()
    {
        if ($this->valu_status) {
            return true;
        }
        return false;
    }

    public function isVisaCheckoutActive()
    {
        if ($this->visa_checkout_status) {
            return true;
        }
        return false;
    }

    public function isMeezaBranding()
    {
        if ($this->cc_show_meeza_branding) {
            return true;
        }
        return false;
    }

    public function isMadaBranding()
    {
        if ($this->cc_show_mada_branding) {
            return true;
        }
        return false;
    }

    public function isApplePayActive()
    {
        if ($this->apple_pay_status) {
            return true;
        }
        return false;
    }

    public function getApplePayCertificateFileName()
    {
        return Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_CRT_FILE', null);
    }

    public function getApplePayCertificateKeyFileName()
    {
        return Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_KEY_FILE', null);
    }

    public function isEnabledApplePayProductPage()
    {
        if ($this->apple_pay_product_page) {
            return true;
        }
        return false;
    }
    public function isEnabledApplePayCartPage()
    {
        if ($this->apple_pay_cart_page) {
            return true;
        }
        return false;
    }

    public function getGatewayProdHostUrl()
    {
        return $this->gatewayProductionHostUrl;
    }

    public function getGatewaySandboxHostUrl()
    {
        return $this->gatewaySandboxHostUrl;
    }

    public function getGatewayProductionNotiApiUrl()
    {
        return $this->gatewayProductionNotiApiUrl;
    }

    public function getGatewaySandboxNotiApiUrl()
    {
        return $this->gatewaySandboxNotiApiUrl;
    }

    public function getMadaBins()
    {
        return $this->cc_mada_bins;
    }

    public function getMeezaBins()
    {
        return $this->cc_meeza_bins;
    }

    public function getCheckStatusCronDuration()
    {
        return $this->check_status_cron_duration;
    }

    public function getLogFileDir()
    {
        $logs_dir = _PS_ROOT_DIR_ . '/var/logs/';
            if (! file_exists($logs_dir)) {
                $logs_dir = _PS_ROOT_DIR_ . '/app/logs/';
                if (! file_exists($logs_dir)) {
                    $logs_dir = _PS_ROOT_DIR_ . '/log/';
            }
        }
        $logFileDir  = $logs_dir . 'amazon_ps_'.date('Y-m-d').'.log';
        return $logFileDir;
    }

    /*public function getPaymentMethodIntegrationType($paymentMethod)
    {
        switch ($paymentMethod) {
            case ApsConstant::APS_PAYMENT_METHOD_CC:
                return $this->getCcIntegrationType();
                break;
            case ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS:
                return $this->getInstallmentsIntegrationType();
                break;
            case ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT:
                return $this->getVisaCheckoutIntegrationType();
                break;
            default:
                return ApsConstant::APS_INTEGRATION_TYPE_REDIRECTION;
                break;
        }
    }*/
}
