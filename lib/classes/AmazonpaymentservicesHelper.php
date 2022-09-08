<?php

class AmazonpaymentservicesHelper extends AmazonpaymentservicesSuper
{
    private static $instance;
    private $aps_config;
    private $log;

    public function __construct()
    {
        parent::__construct();
        $this->aps_config = AmazonpaymentservicesConfig::getInstance();
    }

    /**
     * @return AmazonpaymentservicesConfig
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new AmazonpaymentservicesHelper();
        }
        return self::$instance;
    }

    public function getBaseCurrency()
    {
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        return $currency->iso_code;
    }

    public function getFrontCurrency()
    {
        $currency = $this->context->currency;
        return $currency->iso_code;
    }

    public function getGatewayCurrencyCode($base_currency_code = null, $current_currency_code = null)
    {
        $gateway_currency = $this->aps_config->getGatewayCurrency();
        
        if ($base_currency_code == null) {
            $base_currency_code   = $this->getBaseCurrency();
        }
        
        if ($current_currency_code == null) {
            $current_currency_code = $this->getFrontCurrency();
        }

        $currency_code    = $base_currency_code;
        if ($gateway_currency == 'front') {
            $currency_code = $current_currency_code;
        }
        return $currency_code;
    }

    public function getReturnUrl($path)
    {
        return $this->context->link->getModuleLink(
            'amazonpaymentservices',
            'validation',
            array('action' => $path),
            Configuration::get('PS_SSL_ENABLED')
        );
    }

    /**
     * Convert Amount with decimal points
     * @param decimal $amount
     * @param decimal $currency_value
     * @param string  $currency_code
     * @return decimal
     */
    public function convertGatewayAmount($amount, $currency_value, $currency_code, $iso = false)
    {
        $gateway_currency = $this->aps_config->getGatewayCurrency();
        $new_amount       = 0;
        
        $decimal_points   = $this->getCurrencyDecimalPoints($currency_code);
        if ($gateway_currency == 'front') {
            $new_amount = round($amount, $decimal_points);
        } else {
            $new_amount = round($amount / $currency_value, $decimal_points);
        }
        if (0 !== $decimal_points) {
            $new_amount = $new_amount * (pow(10, $decimal_points));
        }
        if( true === $iso ) {
            $new_amount = $this->convertIntToDecimalAmount( $new_amount, $currency_code );
        }
        $new_amount = number_format($new_amount, 0 , '.' , '');
        return $new_amount;
    }

    public function convertedAdminOrderToGatewayAmount($amount, $currency_code, $currency_value)
    {
        $gateway_currency = $this->aps_config->getGatewayCurrency();
        $decimal_points   = $this->getCurrencyDecimalPoints($currency_code);
        if ($gateway_currency != 'front') {
            $amount = round($amount / $currency_value, (int)$decimal_points);
        }

        if ( 0 !== $decimal_points ) {
            $amount = $amount * (pow(10, $decimal_points));
        }
        $amount = number_format($amount, 0 , '.' , '');
        return $amount;
    }

    /**
     * Convert decimal amount to int amount
     * example 100.00 to 10000
    */
    public function convertDecimalToIntAmount($amount, $currency_code)
    {
        $decimal_points   = $this->getCurrencyDecimalPoints($currency_code);
        if (0 !== $decimal_points) {
            $amount = $amount * (pow(10, $decimal_points));
        }
        return $amount;
    }

    /**
     * convert int to decimal amount
     * example USD 10075 = 100.75
    */
    public function convertIntToDecimalAmount($amount, $currency_code)
    {
        $new_amount     = 0;
        $decimal_points = $this->getCurrencyDecimalPoints($currency_code);
        $divide_by      = intval(str_pad(1, $decimal_points + 1, 0, STR_PAD_RIGHT));
        if (0 === $decimal_points) {
            $new_amount = $amount;
        } else {
            $new_amount = $amount / $divide_by;
        }
        return $new_amount;
    }

    /**
     * Convert int to decimal amount
     * Example order amount 100.75 passed to APS 10075
     * Now 10075 convert to order amount 100.75
    */

    public function convertGatewayToOrderAmount($amount, $currency_code, $value)
    {
        //convert int amount to decimal amount
        $amount = $this->convertIntToDecimalAmount($amount, $currency_code);
        $decimal_points = $this->getCurrencyDecimalPoints($currency_code);

        $gateway_currency = $this->aps_config->getGatewayCurrency();
        if ($gateway_currency == 'front') {
            $amount = round($amount, (int)$decimal_points);
        } else {
            $amount = round($amount * $value, (int)$decimal_points);
        }
        return $amount;
    }

    public function getConversionRate($currency_iso_code)
    {
        $id_currency = Currency::getIdByIsoCode($currency_iso_code);
        $currency    = new Currency($id_currency);
        return $currency->conversion_rate;
    }

    /**
     *
     * @param string $currency
     * @param integer
     */
    public function getCurrencyDecimalPoints($currency)
    {
        $decimalPoint  = 2;
        $arrCurrencies = array(
            'JOD' => 3,
            'KWD' => 3,
            'OMR' => 3,
            'TND' => 3,
            'BHD' => 3,
            'LYD' => 3,
            'IQD' => 3,
        );
        if (isset($arrCurrencies[$currency])) {
            $decimalPoint = $arrCurrencies[$currency];
        }
        return $decimalPoint;
    }

    /**
     * calculate  signature
     * @param array $arrData
     * @param sting $signType request or response
     * @param sting $type regular or applepay
     * @return string  signature
     */
    public function calculateSignature($arrData, $signType = 'request', $type = 'regular')
    {
        $signature = '';
        try {
            $shaString = '';
            $hmac_key = '';
            $hash_algorithm = '';

            ksort($arrData);
            foreach ($arrData as $k => $v) {
                if ('products' === $k) {
                    $productString = '[{';
                    foreach ($v as $next_key => $next_value) {
                        $productString.= "$next_key=$next_value, ";
                    }
                    $productString = rtrim($productString, ', ');
                    $productString .= '}]';
                    $shaString .= "$k=" . $productString;
                } elseif ('apple_header' === $k || 'apple_paymentMethod' === $k) {
                    $shaString .= $k . '={';
                    foreach ($v as $i => $j) {
                        $shaString .= $i . '=' . $j . ', ';
                    }
                    $shaString  = rtrim($shaString, ', ');
                    $shaString .= '}';
                } else {
                    $shaString .= "$k=$v";
                }
            }

            if ('apple_pay' === $type) {
                $hash_algorithm = $this->aps_config->getApplePayShaType();
            } else {
                $hash_algorithm = $this->aps_config->getShaType();
            }
            if ('apple_pay' === $type) {
                if ($signType == 'request') {
                    $shaString = $this->aps_config->getApplePayRequestShaPhrase() . $shaString . $this->aps_config->getApplePayRequestShaPhrase();
                    $hmac_key  = $this->aps_config->getApplePayRequestShaPhrase();
                } else {
                    $shaString = $this->aps_config->getApplePayResponseShaPhrase() . $shaString . $this->aps_config->getApplePayResponseShaPhrase();
                    $hmac_key  = $this->aps_config->getApplePayResponseShaPhrase();
                }
            } else {
                if ($signType == 'request') {
                    $shaString = $this->aps_config->getRequestShaPhrase() . $shaString . $this->aps_config->getRequestShaPhrase();
                    $hmac_key  = $this->aps_config->getRequestShaPhrase();
                } else {
                    $shaString = $this->aps_config->getResponseShaPhrase() . $shaString . $this->aps_config->getResponseShaPhrase();
                    $hmac_key  = $this->aps_config->getResponseShaPhrase();
                }
            }

            if (in_array($hash_algorithm, array( 'sha256', 'sha512' ), true)) {
                $signature = hash($hash_algorithm, $shaString);
            } elseif ('hmac256' === $hash_algorithm) {
                $signature = hash_hmac('sha256', $shaString, $hmac_key);
            } elseif ('hmac512' === $hash_algorithm) {
                $signature = hash_hmac('sha512', $shaString, $hmac_key);
            }
        } catch ( Exception $e ) {
            $this->log("calculateSignature error " . $e->getMessage());
        }
        return $signature;
    }

    /**
     * Log the error on the disk
     */
    public function log($messages, $forceDebug = false)
    {
        $debugMode = $this->aps_config->isDebugMode();
        if (!$debugMode && !$forceDebug) {
            return;
        }
        $logger = new FileLogger();
        $logger->setFilename($this->aps_config->getLogFileDir());
        $logger->logInfo($messages);
    }

    public function getCustomerIp()
    {
        return Tools::getRemoteAddr();
    }

    public function getGatewayHost()
    {
        if ($this->aps_config->isSandboxMode()) {
            return $this->getGatewaySandboxHost();
        }
        return $this->getGatewayProdHost();
    }

    public function getGatewayUrl($type = 'redirection')
    {
        $testMode = $this->aps_config->isSandboxMode();
        if ($type == 'notificationApi') {
            $gatewayUrl = $testMode ?  'https://sbpaymentservices.payfort.com/FortAPI/paymentApi' :  'https://paymentservices.payfort.com/FortAPI/paymentApi';
        } else {
            $gatewayUrl = $testMode ? $this->aps_config->getGatewaySandboxHostUrl() : $this->aps_config->getGatewayProdHostUrl();
        }

        return $gatewayUrl;
    }

    public function setFlashMsg($message, $status = APS_FLASH_MSG_ERROR, $title = '')
    {
        return;
    }

    public function pendingOrderStatusId()
    {
        return Configuration::get('APS_OS_PENDING');
    }
    
    public function processingOrderStatusId()
    {
        return Configuration::get('PS_OS_PAYMENT');
    }

    public function onHoldOrderStatusId()
    {
        return Configuration::get('APS_OS_ONHOLD');
    }

    public function shippedOrderStatusId()
    {
        Configuration::get('PS_OS_SHIPPING');
    }

    public function completeOrderStatusId()
    {
        return Configuration::get('PS_OS_DELIVERED');
    }

    public function cancelOrderStatusId()
    {
        return Configuration::get('PS_OS_CANCELED');
    }

    public function failedOrderStatusId()
    {
        return Configuration::get('PS_OS_ERROR');
    }

    public function refundedOrderStatusId()
    {
        return Configuration::get('PS_OS_REFUND');
    }
    
    public function voidedOrderStatusId()
    {
        return Configuration::get('APS_OS_VOIDED');
    }

    public function processingProgressOrderStatusId()
    {
        return Configuration::get('PS_OS_PREPARATION');
    }

    public function clean_string($string)
    {
        $string = str_replace(array( ' ', '-' ), array( '', '' ), $string);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    /**
     * Get Plugin params
     *
     * @return plugin_params array
     */
    public function plugin_params()
    {
        return array(
            'app_programming'    => 'PHP',
            'app_framework'      => 'PrestaShop',
            'app_ver'            => 'v' . _PS_VERSION_,
            'app_plugin'         => 'PrestaShop',
            'app_plugin_version' => 'v' . APSConstant::APS_VERSION,
        );
    }

    public function checkOrderEligibleForKnet()
    {
        $supported_currencies = ['KWD'];
        if (! in_array($this->getGatewayCurrencyCode(), $supported_currencies)) {
            return false;
        }
        return true;
    }

    public function checkOrderEligibleForNaps()
    {
        $supported_currencies = ['QAR'];
        if (! in_array($this->getGatewayCurrencyCode(), $supported_currencies)) {
            return false;
        }
        return true;
    }

    public function checkOrderEligibleForInstallments($total, $embedded_hosted_checkout = 0)
    {
        if ($this->aps_config->getInstallmentsIntegrationType() == ApsConstant::APS_INTEGRATION_TYPE_EMBEDDED_HOSTED_CHECKOUT && !($embedded_hosted_checkout)) {
            return false;
        }

        $validated_currencies = array( 'AED', 'SAR', 'EGP' );
        $is_min_total_limit = true;
        $front_currency = $this->getFrontCurrency();

        if (in_array(strtoupper($front_currency), $validated_currencies, true)) {
            $min_limit = 0;
            if ('SAR' === $front_currency) {
                $min_limit = $this->aps_config->getInstallmentsSAROrderMinValue();
            } elseif ('AED' === $front_currency) {
                $min_limit = $this->aps_config->getInstallmentsAEDOrderMinValue();
            } elseif ('EGP' === $front_currency) {
                $min_limit = $this->aps_config->getInstallmentsEGPOrderMinValue();
            }
            $currency  = $this->getGatewayCurrencyCode();
            $conversion_rate = $this->getConversionRate($currency);

            $gateway_currency = $this->aps_config->getGatewayCurrency();
            if ('front' == $gateway_currency) {
                $amount      = $this->convertGatewayAmount($total, $conversion_rate, $currency);
            } else {
                $amount  = $this->convertDecimalToIntAmount($total, $front_currency);
            }

            $min_limit  = $this->convertDecimalToIntAmount($min_limit, $front_currency);
            if ($amount < $min_limit) {
                $is_min_total_limit = false;
            }
        }
        return $is_min_total_limit;
    }

    public function checkOrderEligibleForValu($total)
    {
        $supported_currencies = array( 'EGP' );
        $is_min_total_limit = false;
        $front_currency = $this->getFrontCurrency();

        if (in_array(strtoupper($front_currency), $supported_currencies, true)) {
            $is_min_total_limit = true;
            $min_limit = $this->aps_config->getValuOrderMinValue();
            $currency  = $this->getGatewayCurrencyCode();
            $conversion_rate = $this->getConversionRate($currency);

            $gateway_currency = $this->aps_config->getGatewayCurrency();
            if ('front' == $gateway_currency) {
                $amount      = $this->convertGatewayAmount($total, $conversion_rate, $currency);
            } else {
                $amount  = $this->convertDecimalToIntAmount($total, $front_currency);
            }

            $min_limit  = $this->convertDecimalToIntAmount($min_limit, $front_currency);
            if ($amount < $min_limit) {
                $is_min_total_limit = false;
            }
        }
        return $is_min_total_limit;
    }

    public function getTokensData($payment_method)
    {
        // if customer not logged in then return without tokens
        if (! $this->context->customer->isLogged()) {
            return;
        }

        // check tokenization is enable
        if (! $this->aps_config->isEnabledTokenization()) {
            return;
        }

        $id_customer = $this->context->customer->id;
        $display_add_new_card = 'style="display:none"';

        $tokens = ApsToken::getApsTokens($id_customer);

        if (ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS == $payment_method) {
            // installment only support visa & mastercard cards
            $tokens = array_filter(
                $tokens,
                function ($token_row) {
                    if (in_array($token_row['card_type'], array( 'visa', 'mastercard'), true)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            );
        }

        if (! (empty($tokens))) {
            $display_add_new_card = '';
        }

        $data['display_add_new_card'] = $display_add_new_card;
        $data['tokens'] = $tokens;
        $data['is_enabled_tokenization'] = $this->aps_config->isEnabledTokenization();
        return $data;
    }

    public function getPaymentMethodTitle($payment_method)
    {
        switch ($payment_method) {
            case ApsConstant::APS_PAYMENT_METHOD_CC:
                    return $this->module->l("Credit / Debit card");
                break;
            case ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS:
                    return $this->module->l("Installments");
                break;
            case ApsConstant::APS_PAYMENT_METHOD_NAPS:
                    return $this->module->l("NAPS");
                break;
            case ApsConstant::APS_PAYMENT_METHOD_KNET:
                    return $this->module->l("KNET");
                break;
            case ApsConstant::APS_PAYMENT_METHOD_VALU:
                    return $this->module->l("Buy Now, Pay Monthly");
                break;
            case ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT:
                    return $this->module->l("VISA Checkout");
                break;
            case ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY:
                    return $this->module->l("Apple Pay");
                break;
            default:
                return $this->module->l("Credit / Debit card");
                break;
        }
    }
}
