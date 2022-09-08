<?php

class AmazonpaymentservicesPayment extends AmazonpaymentservicesSuper
{
    private static $instance;
    private $aps_helper;
    private $aps_config;
    private $aps_order;
    private $error_message;

    public function __construct()
    {
        parent::__construct();
        $this->aps_helper = AmazonpaymentservicesHelper::getInstance();
        $this->aps_config = AmazonpaymentservicesConfig::getInstance();
        $this->aps_order  = new AmazonpaymentservicesOrder();
    }

    /**
     * @return AmazonpaymentservicesConfig
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new AmazonpaymentservicesPayment();
        }
        return self::$instance;
    }

    public function getPaymentRequestParams($payment_method, $integrationType = ApsConstant::APS_INTEGRATION_TYPE_REDIRECTION, $extras = array())
    {
        $id_order = $this->aps_order->getSessionOrderId();
        $this->aps_order->loadOrder($id_order);

        $gateway_params = array(
            'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
            'access_code'         => $this->aps_config->getAccessCode(),
            'merchant_reference'  => $id_order,
            'language'            => $this->aps_config->getLanguage(),
        );
        if ($integrationType == ApsConstant::APS_INTEGRATION_TYPE_REDIRECTION) {
            $currency                        = $this->aps_helper->getGatewayCurrencyCode();
            $gateway_params['currency']       = strtoupper($currency);
            $gateway_params['amount']         = $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency);
            $gateway_params['customer_email'] = $this->aps_order->getEmail();
            $gateway_params['command']        = $this->aps_config->getCommand($payment_method);
            $gateway_params['order_description'] = 'Order#' . $id_order;
            $gateway_params['return_url']     = $this->aps_helper->getReturnUrl('responseOnline');
            if (isset($extras['aps_payment_token']) && ! empty($extras['aps_payment_token'])) {
                $gateway_params['token_name'] = $extras['aps_payment_token'];
            }
            if ($payment_method == ApsConstant::APS_PAYMENT_METHOD_KNET) {
                $gateway_params['payment_option'] = 'KNET';
            } elseif ($payment_method == ApsConstant::APS_PAYMENT_METHOD_NAPS) {
                $gateway_params['payment_option']    = 'NAPS';
            } elseif ($payment_method == ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS) {
                $gateway_params['installments'] = 'STANDALONE';
                $gateway_params['command']      = 'PURCHASE';
            } elseif ($payment_method ==  ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT) {
                $gateway_params['digital_wallet'] = 'VISA_CHECKOUT';
            }
            $plugin_params  = $this->aps_helper->plugin_params();
            $gateway_params = array_merge($gateway_params, $plugin_params);
        } else {
            if ($payment_method ==  ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT) {
                unset($gateway_params['service_command']);
                $currency                         = $this->aps_helper->getGatewayCurrencyCode();
                $gateway_params['currency']       = strtoupper($currency);
                $gateway_params['amount']         = $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency);
                $gateway_params['customer_email'] = $this->aps_order->getEmail();
                $gateway_params['command']        = $this->aps_config->getCommand($payment_method);
                $gateway_params['return_url']     = $this->aps_helper->getReturnUrl('responseOnline');
            } else {
                $gateway_params['service_command'] = ApsConstant::APS_COMMAND_TOKENIZATION; //'TOKENIZATION';
                $gateway_params['return_url']      = $this->aps_helper->getReturnUrl('merchantPageResponse');
                if ($payment_method == ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS && ($integrationType == ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT)) {
                    $currency                       = $this->aps_helper->getGatewayCurrencyCode();
                    $gateway_params['currency']     = strtoupper($currency);
                    $gateway_params['installments'] = 'STANDALONE';
                    $gateway_params['amount']       = $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency);
                }
                if (isset($extras['aps_payment_token']) && ! empty($extras['aps_payment_token'])) {
                    $gateway_params['token_name'] = trim($extras['aps_payment_token'], ' ');
                    
                    if (isset($extras['aps_card_bin']) && ! empty($extras['aps_card_bin'])) {
                        $gateway_params['card_bin'] = trim($extras['aps_card_bin'], ' ');
                    }
                    if (isset($extras['aps_payment_cvv']) && ! empty($extras['aps_payment_cvv'])) {
                        $gateway_params['card_security_code'] = trim($extras['aps_payment_cvv'], ' ');
                    }
                    $this->aps_helper->log("aps notify tokenization_purchase");
                    $host2HostParams = $this->merchantPageNotifyFort($gateway_params, $id_order, $payment_method, $integrationType);
                    $redirect_url =  $this->handleApsResponse($host2HostParams, 'online', 'cc_merchant_page_h2h', true);
                    if ($redirect_url === true) {
                        $objOrder = new Order($id_order);
                        $customer = new Customer($objOrder->id_customer);
                        $redirect_url = Context::getContext()->link->getPageLink(
                            'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                        );
                    } elseif ($redirect_url === false) {
                        $redirect_url = $redirectUrl = Context::getContext()->link->getPageLink(
                            'error&fc=module&module=amazonpaymentservices&action=displayError'
                        );
                    }
                    $this->aps_helper->log("tokenization_purchase".$redirect_url);
                    return array('url' => '', 'params' => '', 'redirect_url' => $redirect_url);
                }
            }
        }
        $signature   = $this->aps_helper->calculateSignature($gateway_params, 'request');
        $gateway_params['signature'] = $signature;

        $gateway_url = $this->aps_helper->getGatewayUrl();

        $this->aps_helper->log("APS Request Params ($payment_method)" . print_r($gateway_params, 1));

        return array('url' => $gateway_url, 'params' => $gateway_params);
    }


    /**
     *
     * @param array  $apsParams
     * @param string $responseMode (online, offline)
     * @retrun boolean
     */
    public function handleApsResponse($apsParams = array(), $responseMode = 'online', $integrationType = 'redirection', $tokenization_purchase = false)
    {
        $id_order = '';
        try {
            $responseParams  = $apsParams;
            $success         = false;
            $responseMessage = isset($responseParams['response_message']) && ! empty($responseParams['response_message']) ? $responseParams['response_message'] : $this->module->l('An error occurred while making the transaction. Please try again', 'amazonpaymentservicespayment');

            if (empty($responseParams)) {
                $this->aps_helper->log('Empty Aps response parameters (' . $responseMode . ')');
                throw new Exception($responseMessage);
            }

            if (!isset($responseParams['merchant_reference']) || empty($responseParams['merchant_reference'])) {
                $this->aps_helper->log("Invalid Aps response parameters. merchant_reference not found ($responseMode) \n\n" . print_r($responseParams, 1));
                throw new Exception($responseMessage);
            }
            $id_order = $responseParams['merchant_reference'];
            $this->aps_order->loadOrder($id_order);

            // get order id if webhook call for valu order and valu refund webhook
            $valu_order_id_by_reference = '';
            if ($this->aps_order->getOrderId() == null) {
                if ((isset($responseParams['command']) && in_array($responseParams['command'], array('REFUND', 'CAPTURE', 'VOID_AUTHORIZATION'))) || (isset($responseParams['payment_option']) && 'VALU' === $responseParams['payment_option'] && 'offline' === $responseMode)) {
                    $valu_order_id_by_reference = ApsOrder::getValuOrderIdByReference( $responseParams['merchant_reference'] );
                    $this->aps_helper->log("Valu orderId" . $valu_order_id_by_reference);

                }
            }

            $payment_method = '';
            if ($this->aps_order->getOrderId() != null) {
                $payment_method = ApsOrder::getApsMetaValue($this->aps_order->getOrderId(), 'payment_method');
                if ('cc_merchant_page_h2h' != $integrationType) {
                    $integrationType = ApsOrder::getApsMetaValue($this->aps_order->getOrderId(), 'integration_type');
                }
            }

            $responseType          = $responseParams['response_message'];
            $signature             = $responseParams['signature'];
            $responseOrderId       = $responseParams['merchant_reference'];
            $responseStatus        = isset($responseParams['status']) ? $responseParams['status'] : '';
            $responseCode          = isset($responseParams['response_code']) ? $responseParams['response_code'] : '';
            $responseStatusMessage = $responseType;
            $responseGatewayParams = $responseParams;

            $notIncludedParams = array('signature', 'fc', 'module', 'controller', 'action', 'integration_type', 'isolang', 'id_lang');
            foreach ($responseGatewayParams as $k => $v) {
                if (in_array($k, $notIncludedParams)) {
                    unset($responseGatewayParams[$k]);
                }
            }

            $signature_type     = isset($responseParams['digital_wallet']) && 'APPLE_PAY' === $responseParams['digital_wallet'] ? 'apple_pay' : 'regular';

            //check webhook call for apple pay
            if (isset($responseParams['command']) && in_array($responseParams['command'], array('REFUND', 'CAPTURE', 'VOID_AUTHORIZATION'))) {
                if (isset($responseParams['access_code']) && $responseParams['access_code'] == $this->aps_config->getApplePayAccessCode()) {
                    $signature_type = 'apple_pay';
                }
            }
            $calculateSignature = $this->aps_helper->calculateSignature($responseGatewayParams, 'response', $signature_type);

            //update order id if webhook call for valu refund
            if ($valu_order_id_by_reference != '' && ($this->aps_order->getOrderId() == null)) {
                $id_order = $valu_order_id_by_reference;
                $responseParams['merchant_reference'] = $id_order;
                $this->aps_order->loadOrder($id_order);
                $payment_method   = ApsOrder::getApsMetaValue($id_order, 'payment_method');
                if ('cc_merchant_page_h2h' != $integrationType) {
                    $integrationType = ApsOrder::getApsMetaValue($id_order, 'integration_type');
                }
            }

            // check the signature
            if (strtolower($calculateSignature) !== strtolower($signature)) {
                $responseMessage = $this->module->l('Invalid Singature');
                $this->aps_helper->log(sprintf('Invalid Signature. Calculated Signature: %1s, Response Signature: %2s', $calculateSignature, $signature));
                // There is a problem in the response we got
                $r = $this->aps_order->onHoldOrder($responseMessage);
                if ($r) {
                    throw new Exception($responseMessage);
                }
                return true;
            }

            if (ApsConstant::APS_PAYMENT_CANCEL_RESPONSE_CODE === $responseCode) {
                $responseMessage = isset($responseParams['response_message']) && ! empty($responseParams['response_message']) ? $responseParams['response_message'] : $this->module->l('Transaction Cancelled');

                $r = $this->aps_order->declineOrder($responseParams, $responseMessage);
                if ($r) {
                    throw new Exception($responseMessage);
                }
            }
            // standard & hosted checkout
            if ($integrationType == 'cc_merchant_page_h2h') {
                if (ApsConstant::APS_MERCHANT_SUCCESS_RESPONSE_CODE === $responseCode && isset($responseParams['3ds_url'])) {
                    if ($payment_method == ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT && $this->aps_config->getVisaCheckoutIntegrationType() == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT || $tokenization_purchase) {
                        $this->aps_helper->log("Visa 3DS_URL return ".$responseParams['3ds_url']);
                        return $responseParams['3ds_url'];
                    }
                    if ($integrationType == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT) {
                        $this->aps_helper->log("PHP 3DS_URL Called ".$responseParams['3ds_url']);
                        header('location:' . $responseParams['3ds_url']);
                    } else {
                        $this->aps_helper->log("JS 3DS_URL Called ".$responseParams['3ds_url']);
                        echo '<script>window.top.location.href = "'.$responseParams['3ds_url'].'"</script>';
                    }
                    exit;
                }
            }

            if (ApsConstant::APS_PAYMENT_SUCCESS_RESPONSE_CODE === $responseCode || ApsConstant::APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE === $responseCode) {
                $this->aps_order->successOrder($responseParams, $responseMode);
            } elseif (in_array($responseCode, ApsConstant::APS_ONHOLD_RESPONSE_CODES, true)) {
                $this->aps_order->onHoldOrder($responseMessage);
            } elseif (ApsConstant::APS_CAPTURE_SUCCESS_RESPONSE_CODE === $responseCode || ApsConstant::APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE === $responseCode) {
                $this->aps_order->capture_order($responseParams, $responseMode);
            } elseif (ApsConstant::APS_REFUND_SUCCESS_RESPONSE_CODE === $responseCode) {
                $this->aps_order->refund_order($responseParams, $responseMode);
            } elseif (ApsConstant::APS_AUTHORIZATION_VOIDED_SUCCESS_RESPONSE_CODE === $responseCode || ApsConstant::APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE === $responseCode) {
                $this->aps_order->void_order($responseParams, $responseMode);
            } elseif (ApsConstant::APS_TOKENIZATION_SUCCESS_RESPONSE_CODE === $responseCode || ApsConstant::APS_UPDATE_TOKENIZATION_SUCCESS_RESPONSE_CODE === $responseCode || ApsConstant::APS_SAFE_TOKENIZATION_SUCCESS_RESPONSE_CODE === $responseCode) {
                $this->aps_helper->log("merchant ". $payment_method. " == ". $integrationType);
                if (($payment_method == ApsConstant::APS_PAYMENT_METHOD_CC && ($integrationType == ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT || $integrationType == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT)) || ($payment_method == ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS && $integrationType == ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT) || ($payment_method == ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS && $integrationType == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT)) {
                    $host2HostParams = $this->merchantPageNotifyFort($responseParams, $id_order, $payment_method, $integrationType);
                    return $this->handleApsResponse($host2HostParams, 'online', 'cc_merchant_page_h2h');
                }
            } else {
                $capture_void_refund = array(
                    ApsConstant::APS_COMMAND_CAPTURE,
                    ApsConstant::APS_COMMAND_VOID_AUTHORIZATION,
                    ApsConstant::APS_COMMAND_REFUND,
                );
                if (isset($responseParams['command']) && in_array($responseParams['command'], $capture_void_refund)) {
                    $responseMessage = isset($responseParams['response_message']) && ! empty($responseParams['response_message']) ? $responseParams['response_message'] : '';

                    $this->aps_order->updateOrderHistory('Failed '.$responseParams['command']. ' Error : '.$responseMessage);
                } else {
                    $responseMessage = isset($responseParams['response_message']) && ! empty($responseParams['response_message']) ? $responseParams['response_message'] : $this->model->l('Response Unknown');
                    $r = $this->aps_order->declineOrder($responseParams, $responseMessage);
                    if ($r) {
                        throw new Exception($responseMessage);
                    }
                }
            }
        } catch (Exception $e) {
            $this->error_message = $e->getMessage();
            $this->aps_helper->log("ERROR : handleApsResponse : " . $e->getMessage());
            $this->aps_helper->setFlashMsg($e->getMessage(), ApsConstant::APS_FLASH_MSG_ERROR);
            // Don't cancelled order if already payment success
            if ( in_array($this->aps_order->getStatusId(),
                [
                    $this->aps_helper->processingOrderStatusId(),
                    $this->aps_helper->shippedOrderStatusId(),
                    $this->aps_helper->completeOrderStatusId(),
                    $this->aps_helper->refundedOrderStatusId(),
                    $this->aps_helper->voidedOrderStatusId(),
                    $this->aps_helper->processingProgressOrderStatusId()
                ]
                ) ) {
                return true;
            } else {
                $this->refillCart($id_order);
                Context::getContext()->cookie->__set('aps_error_msg', 'Technical error occurred : ' . $e->getMessage());
            }
            return false;
        }
        return true;
    }

    public function visaCheckoutHosted($visa_checkout_call_id, $payment_method, $id_order)
    {
        $responseParams['visa_checkout_call_id'] = $visa_checkout_call_id;
        $responseParams['merchant_reference']    = $id_order;
        $host2HostParams = $this->merchantPageNotifyFort($responseParams, $id_order, $payment_method, ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT);
        return $this->handleApsResponse($host2HostParams, 'online', 'cc_merchant_page_h2h');
    }

    private function merchantPageNotifyFort($apsParams, $id_order, $payment_method, $integrationType)
    {
        //send host to host
        $this->aps_order->loadOrder($id_order);

        $baseCurrency  = $this->aps_helper->getBaseCurrency();
        $orderCurrency = $this->aps_order->getCurrencyCode();
        $currency      = $this->aps_helper->getGatewayCurrencyCode($baseCurrency, $orderCurrency);
        $language      = $this->aps_config->getLanguage();
        $command = ApsConstant::APS_COMMAND_PURCHASE;
        if (isset($apsParams['card_bin']) && ! empty($apsParams['card_bin'])) {
            $command = $this->aps_config->getCommand($payment_method, $apsParams['card_bin']);
        } elseif (isset($apsParams['card_number']) && ! empty($apsParams['card_number'])) {
            $command = $this->aps_config->getCommand($payment_method, substr($apsParams['card_number'], 0, 6));
        } else {
            $command = $this->aps_config->getCommand($payment_method);
        }

        if (isset($apsParams['token_name']) && ! empty($apsParams['token_name'])) {
            $card_type = ApsToken::getTokenCardType($apsParams['token_name']);
            if (! empty($card_type)) {
                $command = $this->aps_config->getCommand($payment_method, null, strtoupper($card_type));
            }
        }

        $postData      = array(
            'merchant_reference'  => $apsParams['merchant_reference'],
            'access_code'         => $this->aps_config->getAccessCode(),
            'command'             => $command,
            'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
            'customer_ip'         => $this->aps_helper->getCustomerIp(),
            'amount'              => $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency),
            'currency'            => strtoupper($currency),
            'customer_email'      => $this->aps_order->getEmail(),
            'language'            => $language,
            'return_url'          => $this->aps_helper->getReturnUrl('responseOnline')
        );
        
        if ($payment_method == ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT) {
            $postData['digital_wallet'] = 'VISA_CHECKOUT';
            $postData['call_id']   = $apsParams['visa_checkout_call_id'];
        } else {
            $postData['token_name']    = $apsParams['token_name'];
            if (isset($apsParams['card_security_code'])) {
                $postData['card_security_code'] = $apsParams['card_security_code'];
            }
        }
        
        if ($payment_method == ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS && $integrationType == ApsConstant::APS_INTEGRATION_TYPE_STANDARD_CHECKOUT) {
            $postData['installments']            = 'YES';
            $postData['plan_code']               = $apsParams['plan_code'];
            $postData['issuer_code']             = $apsParams['issuer_code'];
            $postData['command']                 = 'PURCHASE';
        } elseif ($payment_method == ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS && $integrationType == ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT) {
            $plan_code = ApsOrder::getApsMetaValue($id_order, 'installment_plan_code');
            $issuer_code = ApsOrder::getApsMetaValue($id_order, 'installment_issuer_code');
            $postData['installments']            = 'HOSTED';
            $postData['plan_code']               = $plan_code;
            $postData['issuer_code']             = $issuer_code;
            $postData['command']                 = 'PURCHASE';
        }
        $embedded_order = ApsOrder::getApsMetaValue($id_order, 'embedded_hosted_order');
        if (isset($embedded_order) && $embedded_order == 1) {
            $plan_code = ApsOrder::getApsMetaValue($id_order, 'installment_plan_code');
            $issuer_code = ApsOrder::getApsMetaValue($id_order, 'installment_issuer_code');
            $postData['installments'] = 'HOSTED';
            $postData['plan_code']    = $plan_code;
            $postData['issuer_code']  = $issuer_code;
            $postData['command']      = 'PURCHASE';
        }
        
        $customerName  = $this->aps_order->getCustomerName();
        if (!empty($customerName)) {
            $postData['customer_name'] = $this->aps_order->getCustomerName();
        }

        $postData['eci'] = ApsConstant::APS_COMMAND_ECOMMERCE;

        if (isset($apsParams['remember_me']) && ! isset($apsParams['card_security_code']) && ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT !== $payment_method) {
            $postData['remember_me'] = isset($apsParams['remember_me']) ? $apsParams['remember_me'] : 'NO';
        }

        $plugin_params  = $this->aps_helper->plugin_params();
        $postData       = array_merge($postData, $plugin_params);

        //calculate request signature
        $signature             = $this->aps_helper->calculateSignature($postData, 'request');
        $postData['signature'] = $signature;

        $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
        $this->aps_helper->log($gateway_url.'Merchant Page Notify Api Request Params : ' . print_r($postData, 1));

        $response = $this->callApi($postData, $gateway_url);

        $this->aps_helper->log('Merchant Page Notify Api Response Params : ' . print_r($response, 1));

        return $response;
    }

    public function merchantPageCancel()
    {
        $id_order = $this->aps_order->getSessionOrderId();
        $this->aps_order->loadOrder($id_order);

        if ($id_order) {
            $this->aps_order->cancelOrder();
        }
        $this->aps_helper->setFlashMsg($this->module->l('You have canceled the payment, please try again.', 'amazonpaymentservicespayment'));
        return true;
    }

    public function callApi($postData, $gateway_url)
    {
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        $useragent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;charset=UTF-8',
                //'Accept: application/json, application/*+json',
                //'Connection:keep-alive'
        ));
        curl_setopt($ch, CURLOPT_URL, $gateway_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "compress, gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect
        //curl_setopt($ch, CURLOPT_TIMEOUT, Yii::app()->params['apiCallTimeout']); // timeout in seconds
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            $this->aps_helper->log('Api Curl Call error : '.$error_msg);
        }
        curl_close($ch);

        $array_result = json_decode($response, true);

        if (!$response || empty($array_result)) {
            return false;
        }
        return $array_result;
    }

    /**
     * Find bin in plans
     *
     * @return issuer_key int
     */
    private function findBinInPlans($card_bin, $issuer_data)
    {
        $issuer_key = null;
        if (! empty($issuer_data)) {
            foreach ($issuer_data as $key => $row) {
                $card_regex  = '';
                $issuer_bins = array_column($row['bins'], 'bin');
                if (! empty($issuer_bins)) {
                    $card_regex = '/^' . implode('|', $issuer_bins) . '/';
                    if (preg_match($card_regex, $card_bin)) {
                        $issuer_key = $key;
                        break;
                    }
                }
            }
        }
        return $issuer_key;
    }

    private function getInstallmentPlanCall($cardnumber, $cartTotal)
    {
        $retarr = array(
            'status'           => 'success',
            'installment_data' => array(),
            'code'             => 200,
            'message'          => 'List of plans',
        );
        try {
            $gateway_params = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getAccessCode(),
                'language'            => $this->aps_config->getLanguage(),
                'query_command'       => 'GET_INSTALLMENTS_PLANS'
            );

            $currency                  = $this->aps_helper->getGatewayCurrencyCode();
            $gateway_params['currency'] = strtoupper($currency);
            $conversion_rate           = $this->aps_helper->getConversionRate($currency);
            $gateway_params['amount']   = $this->aps_helper->convertGatewayAmount($cartTotal, $conversion_rate, $currency);

            //calculate request signature
            $signature             = $this->aps_helper->calculateSignature($gateway_params, 'request');
            $gateway_params['signature'] = $signature;

            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('Get installment plan Request Params : '.$gateway_url . print_r($gateway_params, 1));

            $response = $this->callApi($gateway_params, $gateway_url);

            if (ApsConstant::APS_GET_INSTALLMENT_SUCCESS_RESPONSE_CODE == $response['response_code']) {
                $response['installment_detail']['issuer_detail'] = array_filter(
                    $response['installment_detail']['issuer_detail'],
                    function ($row) {
                        return ! empty($row['plan_details']) ? true : false;
                    }
                );
                if (empty($response['installment_detail']['issuer_detail'])) {
                    throw new Exception($this->module->l('No plans found', 'amazonpaymentservicespayment'));
                }
                $issuer_key = $this->findBinInPlans($cardnumber, $response['installment_detail']['issuer_detail']);
                if (empty($issuer_key) && ! isset($response['installment_detail']['issuer_detail'][ $issuer_key ])) {
                    throw new Exception($this->module->l('There is no installment plan available', 'amazonpaymentservicespayment'));
                }
                $retarr['installment_data'] = $response['installment_detail']['issuer_detail'][ $issuer_key ];
            } else {
                throw new Exception($response['response_message']);
            }
        } catch (Exception $e) {
            $retarr['status']  = 'error';
            $retarr['code']    = '400';
            $retarr['message'] = $e->getMessage();
            $this->aps_helper->log("ERROR : installment_plans response \n\n" . $e->getMessage());
        }
        $this->aps_helper->log("Aps response installment_plans " . json_encode($response));
        $this->aps_helper->log("Aps retarr installment_plans" . print_r($retarr, 1));
        return $retarr;
    }

    /**
     * Get Installment plans ajax handler
     */
    public function getInstallmentPlanHandler($cardnumber, $embedded_hosted_checkout)
    {
        $cardnumber = str_replace(' ', '', $cardnumber);
        $cartTotal  = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        /*embedded hosted checkout check minimum cart total*/
        $pay_full_payment = '';
        if ($embedded_hosted_checkout == 1) {
            $installment_min_limit = $this->aps_helper->checkOrderEligibleForInstallments($cartTotal, $embedded_hosted_checkout);
            if (! $installment_min_limit) {
                $retarr['status']  = 'error';
                $retarr['message'] = $this->module->l('Order amount is less than currency minimum limit.', 'amazonpaymentservicespayment');
                return $retarr;
            }
            $pay_full_payment = "<div class='slide'>
                <div class='emi_box' data-interest ='' data-amount='' data-plan-code='' data-issuer-code='' data-full-payment='1'>
                    <p class='with_full_payment'>" . $this->module->l('Proceed with full amount', 'amazonpaymentservicespayment') . "</p>
                </div>
            </div>";
        }

        $response   = $this->getInstallmentPlanCall($cardnumber, $cartTotal);
        $retarr     = array(
            'status'          => 'success',
            'plans_html'      => '',
            'plan_info'       => '',
            'issuer_info'     => '',
            'message'         => '',
            'confirmation_en' => '',
            'confirmation_ar' => '',
        );
        if ('success' === $response['status'] && ! empty($response['installment_data'])) {
            $all_plans  = $response['installment_data']['plan_details'];
            $banking_system = $response['installment_data']['banking_system'];
            $interest_text  = 'Non Islamic' === $banking_system ? $this->module->l('Interest', 'amazonpaymentservicespayment') : $this->module->l('Profit Rate', 'amazonpaymentservicespayment');
            $months_text    = $this->module->l('Months', 'amazonpaymentservicespayment');
            $month_text     = $this->module->l('Month', 'amazonpaymentservicespayment');

            $plans_html = "<div class='emi_carousel'>";
            if (! empty($all_plans)) {
                $plans_html .= $pay_full_payment;

                $currency   = $this->aps_helper->getGatewayCurrencyCode();
                $currency   = strtoupper($currency);

                foreach ($all_plans as $key => $plan) {
                    $interest    = $this->aps_helper->convertIntToDecimalAmount($plan['fee_display_value'], $currency);
                    $interest_info = $interest . ('Percentage' === $plan['fees_type'] ? '%' : '') . ' ' . $interest_text;

                    $plans_html .= "<div class='slide'>
                        <div class='emi_box' data-interest ='" . $interest_info . "' data-amount='" . $plan['amountPerMonth'] . "' data-plan-code='" . $plan['plan_code'] . "' data-issuer-code='" . $response['installment_data']['issuer_code'] . "' >
                            <p class='installment'>" . $plan['number_of_installment'] .$months_text ."</p>
                            <p class='emi'><strong>" . ($plan['amountPerMonth']) . '</strong> ' . $plan['currency_code'] . "/".$month_text."</p>
                            <p class='int_rate'>" . $interest . ('Percentage' === $plan['fees_type'] ? '%' : '') . ' ' . $interest_text . '</p>
                        </div>
                    </div>';
                }
            }
            $plans_html .= '</div>';
            //Plan info
            $terms_url          = $response['installment_data'][ 'terms_and_condition_' . $this->aps_config->getLanguage() ];
            $processing_content = $response['installment_data'][ 'processing_fees_message_' . $this->aps_config->getLanguage() ];
            $issuer_text        = $response['installment_data'][ 'issuer_name_' . $this->aps_config->getLanguage() ];
            $issuer_logo        = $response['installment_data'][ 'issuer_logo_' . $this->aps_config->getLanguage() ];

            $terms_text         = '';
            if ($this->aps_config->getInstallmentsIssuerLogo()) {
                $terms_text .= "<img src='" . $issuer_logo . "' class='issuer-logo'/>";
            }

            $terms_text        .= $this->module->l('I agree with the installment', 'amazonpaymentservicespayment');
            $terms_text        .= ' <a target="_blank" href="' . $terms_url . '">';
            $terms_text        .= $this->module->l('terms and condition', 'amazonpaymentservicespayment');
            $terms_text        .= '</a> ';
            $terms_text        .= $this->module->l('to proceed with the transaction', 'amazonpaymentservicespayment');

            //$terms_text        .= $this->module->l('I agree with the installment <a target="_blank" href="%s">terms and condition</a> to proceed with the transaction');
            $plan_info          = '<input type="checkbox" name="installment_term" id="installment_term"/>' . $terms_text;
            $plan_info         .= '<div><label class="aps_installment_terms_error aps_error"></label></div>';
            $plan_info         .= '<p> ' . $processing_content . '</p>';

            $issuer_info = '';
            if ($this->aps_config->getInstallmentsIssuerName()) {
                $issuer_info .= "<div class='issuer_info'> <p> " .$this->module->l('Issuer Name', 'amazonpaymentservicespayment')." : ". $issuer_text . '</p> </div>';
            }

            $retarr['plans_html'] = $plans_html;
            $retarr['plan_info']  = $plan_info;
            $retarr['issuer_info']     = $issuer_info;
            $retarr['confirmation_en'] = $response['installment_data']['confirmation_message_en'];
            $retarr['confirmation_ar'] = $response['installment_data']['confirmation_message_ar'];
        } else {
            $retarr['status']  = 'error';
            $retarr['message'] = $response['message'];
        }
        return $retarr;
    }

    /**
    * Valu verify customer
    *
    * @return array
    */
    public function valuVerifyCustomer($mobile_number)
    {
        $status  = 'success';
        $message = $this->module->l('Customer Verfied', 'amazonpaymentservicespayment');
        try {
            $reference_id                = Context::getContext()->cart->id . bin2hex(openssl_random_pseudo_bytes(7));
            $gateway_params              = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getAccessCode(),
                'language'            => $this->aps_config->getLanguage(),
                'service_command'     => 'CUSTOMER_VERIFY',
                'payment_option'      => 'VALU',
                'merchant_reference'  => $reference_id,
                'phone_number'        => $mobile_number,
            );
            $signature = $this->aps_helper->calculateSignature($gateway_params, 'request');
            $gateway_params['signature'] = $signature;
            //execute post
            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('Customer verfiy Request Params : '.$gateway_url . print_r($gateway_params, 1));

            $response = $this->callApi($gateway_params, $gateway_url);
            $this->aps_helper->log("Valu verfiy customer response " . json_encode($response));

            $valuapi_stop_message = $this->module->l('VALU API failed. Please try again later.', 'amazonpaymentservicespayment');
            if (isset($response['status']) && ApsConstant::APS_VALU_CUSTOMER_VERIFY_SUCCESS_RESPONSE_CODE === $response['response_code']) {
                Context::getContext()->cookie->__set('aps_valu_reference_id', $reference_id);
                Context::getContext()->cookie->__set('aps_valu_mobile_number', $mobile_number);
            } elseif (isset($response['response_code']) && ApsConstant::APS_VALU_CUSTOMER_VERIFY_FAILED_RESPONSE_CODE === $response['response_code']) {
                $status  = 'error';
                $message = isset($response['response_message']) && ! empty($response['response_message']) ? $this->module->l('Customer does not exist.', 'amazonpaymentservicespayment') : $valuapi_stop_message;
                if (Context::getContext()->cookie->__isset('aps_valu_reference_id')) {
                    Context::getContext()->cookie->__unset('aps_valu_reference_id');
                    Context::getContext()->cookie->__unset('aps_valu_mobile_number');
                }
            } else {
                $status  = 'error';
                $message = isset($response['response_message']) && ! empty($response['response_message']) ? $response['response_message'] : $valuapi_stop_message;
                if (Context::getContext()->cookie->__isset('aps_valu_reference_id')) {
                    Context::getContext()->cookie->__unset('aps_valu_reference_id');
                    Context::getContext()->cookie->__unset('aps_valu_mobile_number');
                }
            }
        } catch (Exception $e) {
            $status  = 'error';
            $message = $this->module->l('Technical error occurred', 'amazonpaymentservicespayment');
        }
        $response_arr = array(
            'status'  => $status,
            'message' => $message,
        );
        return $response_arr;
    }

    /**
     * Valu generate OTP
     *
     * @return array
     */
    public function valuOtpGenerate($mobile_number, $reference_id)
    {
        $status  = 'success';
        $message = $this->module->l('OTP Generated', 'amazonpaymentservicespayment');
        try {
            $id_order                  = $this->aps_order->getSessionOrderId();
            $this->aps_order->loadOrder($id_order);
            $products                 = $this->getValuProductsData();
            $currency                 = $this->aps_helper->getGatewayCurrencyCode();
            $language                 = $this->aps_config->getLanguage();
            $gateway_params            = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getAccessCode(),
                'language'            => $language,
                'merchant_reference'  => $reference_id,
                'payment_option'      => 'VALU',
                'service_command'     => 'OTP_GENERATE',
                'merchant_order_id'   => $id_order,
                'phone_number'        => $mobile_number,
                'amount'              => $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency),
                'currency'            => $currency,
                'products'            => $products[0],
            );

            $signature = $this->aps_helper->calculateSignature($gateway_params, 'request');
            $gateway_params['signature'] = $signature;
            $gateway_params['products'] = $products;
            //execute post
            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('valu OTP generate Request : '.$gateway_url . print_r($gateway_params, 1));

            $response = $this->callApi($gateway_params, $gateway_url);
            $this->aps_helper->log("valu OTP generate response " . json_encode($response));
            $valuapi_stop_message = $this->module->l('VALU API failed. Please try again later.', 'amazonpaymentservicespayment');


            if (isset($response['response_code']) && ApsConstant::APS_VALU_OTP_GENERATE_SUCCESS_RESPONSE_CODE === $response['response_code']) {
                $status   = 'success';

                $mobile_number  = ApsConstant::APS_VALU_EG_COUNTRY_CODE.$mobile_number;
                if ($language == 'ar') {
                    $mobile_number = str_replace("+", "", $mobile_number)."+";
                }

                $message  = sprintf($this->module->l('OTP has been sent to you on your mobile number %s', 'amazonpaymentservicespayment'), $mobile_number);
                Context::getContext()->cookie->__set('aps_valu_id_order', $id_order);
                Context::getContext()->cookie->__set('aps_valu_transaction_id', $response['transaction_id']);
            } else {
                $status  = 'genotp_error';
                $message = isset($response['response_message']) && ! empty($response['response_message']) ? $response['response_message'] : $valuapi_stop_message;
                if (Context::getContext()->cookie->__isset('aps_valu_id_order')) {
                    Context::getContext()->cookie->__unset('aps_valu_id_order');
                    Context::getContext()->cookie->__unset('aps_valu_transaction_id');
                }
            }
        } catch (Exception $e) {
            $status  = 'error';
            $message = $this->module->l('Technical error occurred', 'amazonpaymentservicespayment').$e->getMessage();
        }
        $response_arr = array(
            'status'  => $status,
            'message' => $message,
        );
        return $response_arr;
    }

    /**
     * Valu verify OTP
     *
     * @return array
     */
    public function valuVerfiyOtp($mobile_number, $reference_id, $otp, $id_order)
    {
        $status      = '';
        $message     = '';
        $tenure_html = '';
        try {
            $this->aps_order->loadOrder($id_order);
            $currency                    = $this->aps_helper->getGatewayCurrencyCode();

            $gateway_params              = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getAccessCode(),
                'language'            => $this->aps_config->getLanguage(),
                'service_command'     => 'OTP_VERIFY',
                'payment_option'      => 'VALU',
                'merchant_reference'  => $reference_id,
                'phone_number'        => $mobile_number,
                'amount'              => $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency),
                'merchant_order_id'   => $id_order,
                'currency'            => $currency,
                'otp'                 => $otp,
                'total_downpayment'   => 0,
            );
            $signature = $this->aps_helper->calculateSignature($gateway_params, 'request');
            $gateway_params['signature'] = $signature;
            //execute post
            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('valu OTP verify Request Params : '.$gateway_url . print_r($gateway_params, 1));

            $response = $this->callApi($gateway_params, $gateway_url);
            $this->aps_helper->log("Valu OTP verify response " . json_encode($response));

            $valuapi_stop_message = $this->module->l('VALU API failed. Please try again later.', 'amazonpaymentservicespayment');

            if (isset($response['response_code']) && ApsConstant::APS_VALU_OTP_VERIFY_SUCCESS_RESPONSE_CODE === $response['response_code']) {
                Context::getContext()->cookie->__set('aps_valu_otp', $otp);
                $status                          = 'success';
                $message                         = $this->module->l('OTP Verified successfully', 'amazonpaymentservicespayment');
                $tenure_html                     = "<div class='tenure_carousel'>";
                if (isset($response['tenure']['TENURE_VM'])) {
                    foreach ($response['tenure']['TENURE_VM'] as $key => $ten) {
                        $tenure_html .= "<div class='slide'>
                                <div class='tenureBox' data-tenure='" . $ten['TENURE'] . "' data-tenure-amount='" . $ten['EMI'] . "' data-tenure-interest='" . $ten['InterestRate'] . "' >
                                    <p class='tenure'>" . $ten['TENURE'] ." ".$this->module->l('Months', 'amazonpaymentservicespayment')."</p>
                                    <p class='emi'><strong>" . ($ten['EMI']) . "</strong> EGP/".$this->module->l('Month', 'amazonpaymentservicespayment')."</p>
                                    <p class='int_rate'>" . $ten['InterestRate'] . "% ".$this->module->l('Interest', 'amazonpaymentservicespayment')."</p>
                                </div>
                            </div>";
                    }
                }
                $tenure_html .= '</div>';
            } else {
                $status  = 'error';
                $message = isset($response['response_message']) && ! empty($response['response_message']) ? $response['response_message'] : $valuapi_stop_message;
            }
        } catch (Exception $e) {
            $status  = 'error';
            $message = $this->module->l('Technical error occurred', 'amazonpaymentservicespayment');
        }
        return array(
            'status'      => $status,
            'message'     => $message,
            'tenure_html' => $tenure_html,
        );
    }

    /**
     * Valu generate OTP
     *
     * @return array
     */
    public function valuExecutePurchase($mobile_number, $reference_id, $otp, $transaction_id, $active_tenure, $id_order)
    {
        $status  = 'success';
        $message = '';
        $order   = '';
        try {
            $this->aps_order->loadOrder($id_order);
            $currency                    = $this->aps_helper->getGatewayCurrencyCode();

            $gateway_params = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getAccessCode(),
                'language'            => $this->aps_config->getLanguage(),
                'command'              => 'PURCHASE',
                'payment_option'       => 'VALU',
                'merchant_reference'   => $reference_id,
                'phone_number'         => $mobile_number,
                'amount'               => $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency),
                'merchant_order_id'    => $id_order,
                'currency'             => strtoupper($currency),
                'otp'                  => $otp,
                'tenure'               => $active_tenure,
                'total_down_payment'   => 0,
                'customer_code'        => $mobile_number,
                'customer_email'       => $this->aps_order->getEmail(),
                'purchase_description' => 'Order' . $id_order,
                'transaction_id'       => $transaction_id,
            );

            $plugin_params  = $this->aps_helper->plugin_params();
            $gateway_params  = array_merge($gateway_params, $plugin_params);

            $signature = $this->aps_helper->calculateSignature($gateway_params, 'request');
            $gateway_params['signature'] = $signature;
            //execute post
            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('valu purchase Request : '.$gateway_url . print_r($gateway_params, 1));

            $response = $this->callApi($gateway_params, $gateway_url);
            $this->aps_helper->log("Valu purchase response " . json_encode($response));

            $valuapi_stop_message = $this->module->l('VALU API failed. Please try again later.', 'amazonpaymentservicespayment');
            if (isset($response['response_code']) && ApsConstant::APS_PAYMENT_SUCCESS_RESPONSE_CODE === $response['response_code']) {
                $status  = 'success';
                $message = $this->module->l('Transaction Verified successfully', 'amazonpaymentservicespayment');
                $this->aps_order->successOrder($response, 'online');
            } else {
                $status  = 'error';
                $message = isset($response['response_message']) && ! empty($response['response_message']) ? $response['response_message'] : $valuapi_stop_message;
                $this->aps_order->declineOrder($response, $message);
                throw new Exception($message);
            }
            Context::getContext()->cookie->__unset('aps_valu_reference_id');
            Context::getContext()->cookie->__unset('aps_valu_mobile_number');
            Context::getContext()->cookie->__unset('aps_valu_id_order');
            Context::getContext()->cookie->__unset('aps_valu_transaction_id');
        } catch (Exception $e) {
            $status  = 'error';
            $message = $this->module->l('Technical error occurred', 'amazonpaymentservicespayment')." : ".  $e->getMessage();
            // Don't cancelled order if already payment success
            if ( in_array($this->aps_order->getStatusId(),
                [
                    $this->aps_helper->processingOrderStatusId(),
                    $this->aps_helper->shippedOrderStatusId(),
                    $this->aps_helper->completeOrderStatusId(),
                    $this->aps_helper->refundedOrderStatusId(),
                    $this->aps_helper->voidedOrderStatusId(),
                    $this->aps_helper->processingProgressOrderStatusId()
                ]
                ) ) {
                $status   = 'success';
            }
        }
        return array(
            'status'  => $status,
            'message' => $message,
        );
    }

    private function getValuProductsData()
    {
        $products      = array();
        $product_name  = '';
        $category_name = '';

        $id_order            = $this->aps_order->getSessionOrderId();
        $this->aps_order->loadOrder($id_order);
        $cart_products      = $this->context->cart->getProducts();
        $currency           = $this->aps_helper->getGatewayCurrencyCode();
        foreach ($cart_products as $product) {
            $product_name       = $this->aps_helper->clean_string($product['name']);
            $id_product         = $product['id_product'];
            $category = new Category($id_product, $this->context->language->id);
            if ($category) {
                $category_name = $this->aps_helper->clean_string($category->name);
            }
            break;
        }
        $amount = $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency);

        if (count($cart_products) > 1) {
            $products[] = array(
                'product_name'     => 'MutipleProducts',
                'product_price'    => $amount,
                'product_category' => $category_name,
            );
        } else {
            $products[] = array(
                'product_name'     => $product_name,
                'product_price'    => $amount,
                'product_category' => $category_name,
            );
        }
        return $products;
    }

    /**
     * Get apple pay order
     */
    public function getApplePayOrderData()
    {
        $apple_order = array(
            'sub_total'      => 0.00,
            'tax_total'      => 0.00,
            'shipping_total' => 0.00,
            'discount_total' => 0.00,
            'grand_total'    => 0.00,
            'order_items'    => array(),
        );

        $id_order        = $this->aps_order->getSessionOrderId();
        $this->aps_order->loadOrder($id_order);
        $currency       = $this->aps_helper->getGatewayCurrencyCode();
        $currency_value = $this->aps_order->getCurrencyValue();

        $apple_order['sub_total'] = $this->aps_helper->convertGatewayAmount($this->aps_order->getSubTotal(), $currency_value, $currency, true);

        $apple_order['discount_total'] = $this->aps_helper->convertGatewayAmount($this->aps_order->getDiscountTotal(), $currency_value, $currency, true);

        $apple_order['shipping_total'] = $this->aps_helper->convertGatewayAmount($this->aps_order->getShippingTotal(), $currency_value, $currency, true);

        $apple_order['grand_total'] = $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $currency_value, $currency, true);

        return $apple_order;
    }

    /**
     * Get apple pay order
     */
    public function getApplePayCartData()
    {
        $status      = 'success';
        $currency    = $this->aps_helper->getGatewayCurrencyCode();

        $id_country  = Context::getContext()->shop->getAddress()->id_country;
        $country_iso = Country::getIsoById($id_country);
        $country_iso = (isset($country_iso) ? $country_iso : 'US');

        $apple_order = array(
            'sub_total'      => 0.00,
            'tax_total'      => 0.00,
            'shipping_total' => 0.00,
            'discount_total' => 0.00,
            'grand_total'    => 0.00,
            'order_items'    => array(),
            'address_exist'  => 0,
            'country_code'   => $country_iso,
            'currency_code'  => Tools::strtoupper($currency),
            'display_name'   => $this->aps_config->getApplePayDisplayName(),
            'supported_networks' => $this->aps_config->getApplePaySupportedNetwork()
        );
        try{
            $grand_total    = Context::getContext()->cart->getOrderTotal(true, Cart::BOTH);
            $sub_total      = Context::getContext()->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
            $discount_total = Context::getContext()->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
            $shipping_total = Context::getContext()->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
            $currency       = $this->aps_helper->getGatewayCurrencyCode();
            $currency_value = $this->aps_helper->getConversionRate($currency);

            $apple_order['sub_total'] = $this->aps_helper->convertGatewayAmount($sub_total, $currency_value, $currency, true);

            $apple_order['discount_total'] = $this->aps_helper->convertGatewayAmount($discount_total, $currency_value, $currency, true);

            $apple_order['shipping_total'] = $this->aps_helper->convertGatewayAmount($shipping_total, $currency_value, $currency, true);

            $apple_order['grand_total'] = $this->aps_helper->convertGatewayAmount($grand_total, $currency_value, $currency, true);

            if (Context::getContext()->cart->id_address_delivery || Context::getContext()->cart->id_address_invoice) {
                $apple_order['address_exist'] = 1;
            }
        } catch ( \Exception $e ) {
            $status = 'error';
        }
        $result = array(
            'status'      => $status,
            'apple_order' => $apple_order,
        );
        return $result;
    }

    public function validateShippingAddress($address_data, $checkoutSession = null){
        $status         = 'success';
        $error_msg      = '';
        $postalCode     = '';
        $city           = '';
        $id_country     = '';
        $id_state       = '';

        try {
            if ( isset( $address_data ) ) {
                $this->aps_helper->log( 'APS address data for validate\n\n' . json_encode( $address_data, true ) );

                if ( isset( $address_data['countryCode'] ) && ! empty( $address_data['countryCode'] ) ) {
                    $id_country = Country::getByIso($address_data['countryCode']);
                }
                if ( isset( $address_data['administrativeArea'] ) && ! empty( $address_data['administrativeArea'] ) ) {

                    $id_state = State::getIdByIso($address_data['administrativeArea'], $id_country);
                }
                if ( isset( $address_data['postalCode'] ) && ! empty( $address_data['postalCode'] ) ) {
                    $postalCode = $address_data['postalCode'];
                }
                if ( isset( $address_data['locality'] ) && ! empty( $address_data['locality'] ) ) {
                    $city  = $address_data['locality'];
                }
            }

            if ( isset( $address_data ) ) {
                $id_address = $this->insertNewAddress($id_country, $id_state, $city, $postalCode);
                if ( !Context::getContext()->cart->isVirtualCart() ) {
                    if ($checkoutSession != null){
                        $checkoutSession->setIdAddressDelivery($id_address);
                        $checkoutSession->setIdAddressInvoice($id_address);
                    } else {
                        Context::getContext()->cart->id_address_delivery = $id_address;
                        Context::getContext()->cart->id_address_invoice = $id_address;
                        Context::getContext()->cart->update();
                        Context::getContext()->cart->setNoMultishipping();
                    }

                    $delivery_option = Context::getContext()->cart->getDeliveryOption();
                    Context::getContext()->cart->setDeliveryOption($delivery_option);
                    Context::getContext()->cart->update();

                    if (empty($delivery_option)) {
                        $status = 'error';
                        $error_msg = $this->module->l('No Shipping options are available.', 'amazonpaymentservicespayment');
                    }
                }
            }
        } catch ( \Exception $e ) {
            $status    = 'error';
            $error_msg = $e->getMessage();
        }
        $result = array(
            'status'    => $status,
            'error_msg' => $error_msg,
        );
        $apple_order = $this->getApplePayCartData();
        $result['apple_order']  = $apple_order['apple_order'];
        $this->aps_helper->log( 'APS validate apple pay address data \n\n' . json_encode( $result, true ) );
        return $result;
    }

    public function updateCustomerAndAddress($address_data){
        $status         = 'success';
        $error_msg      = '';

        try {
            $this->aps_helper->log( 'APS applepay update address data \n\n' . json_encode( $address_data, true ) );

            $address_1 = $address_2 = '';
            $firstname = $lastname = $email = $telephone = '';
            if (isset($address_data)) {
                if ( isset( $address_data['addressLines'] ) && ! empty( $address_data['addressLines'] ) ) {
                    if ( isset( $address_data['addressLines'][0] ) && ! empty( $address_data['addressLines'][0] ) ) {
                        $address_1 = $address_data['addressLines'][0];
                    }
                    if ( isset( $address_data['addressLines'][1] ) && ! empty( $address_data['addressLines'][1] ) ) {
                        $address_2 = $address_data['addressLines'][1];
                    }
                }
                if ( isset( $address_data['givenName'] ) && ! empty( $address_data['givenName'] ) ) {
                    $firstname = $address_data['givenName'];
                }
                if ( isset( $address_data['familyName'] ) && ! empty( $address_data['familyName'] ) ) {
                    $lastname = $address_data['familyName'];
                }
                if ( isset( $address_data['emailAddress'] ) && ! empty( $address_data['emailAddress'] ) ) {
                    $email = $address_data['emailAddress'];
                }
                if ( isset( $address_data['phoneNumber'] ) && ! empty( $address_data['phoneNumber'] ) ) {
                    $telephone = $address_data['phoneNumber'];
                }
                $this->aps_helper->log( 'APS apple pay customer id ' . (int)Context::getContext()->cart->id_customer);
                if (0 == (int)Context::getContext()->cart->id_customer) {
                    $id_customer = Customer::customerExists($email, true, false);
                    $customer = '';
                    if ($id_customer) {
                        $customer = new Customer($id_customer);
                        $this->aps_helper->log( 'APS apple pay customer exist ' . $id_customer);

                    } else {
                        $customer = new Customer();
                        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                            $crypto   = new \PrestaShop\PrestaShop\Core\Crypto\Hashing();
                            $customer->passwd    = $crypto->hash(Tools::passwdGen());
                        } else {
                            $password = Tools::passwdGen(8, 'RANDOM');
                            $customer->passwd    = Tools::encrypt($password);
                        }
                        $customer->email     = $email;
                        $customer->lastname  = $lastname;
                        $customer->firstname = $firstname;
                        $customer->is_guest  = 1;
                        $customer->add();
                        $id_customer = Customer::customerExists($email, true, false);
                        $this->aps_helper->log( 'APS apple pay customer added ' . $id_customer );
                    }
                    // Login Customer
                    if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                        Context::getContext()->updateCustomer($customer);
                    } else {
                        $this->updateContext($customer);
                        $this->aps_helper->log( 'APS apple pay customer added updateContext'  );
                    }
                    Context::getContext()->cart->id_customer = $id_customer;
                    Context::getContext()->cart->update();
                    $this->aps_helper->log( 'APS apple pay cart update ' . $id_customer );
                }
                $this->aps_helper->log( 'APS apple cart customer id ' . Context::getContext()->customer->id );

                $invoice_address = new Address((int)Context::getContext()->cart->id_address_invoice);
                if (isset($invoice_address)) {
                    if (empty(trim($invoice_address->firstname))){
                        $this->aps_helper->log( 'APS apple pay in address update ' .(int)Context::getContext()->cart->id_address_invoice );
                        $invoice_address->id_customer  =  (int)Context::getContext()->cart->id_customer;

                        $invoice_address->firstname    = $firstname;
                        $invoice_address->lastname     = $lastname;
                        $invoice_address->address1     = $address_1;
                        $invoice_address->address2     = $address_2;
                        $invoice_address->phone_mobile = $telephone;
                        $invoice_address->update();
                    }
                }
            }

        } catch ( \Exception $e ) {
            $status    = 'error';
            $error_msg = $e->getMessage();
        }
        $result = array(
            'status'    => $status,
            'error_msg' => $error_msg,
        );
        $this->aps_helper->log( 'APS apple pay update address response \n\n' . json_encode( $result, true ) );
        return $result;
    }

    public function updateContext(Customer $customer)
    {
        Context::getContext()->customer = $customer;
        Context::getContext()->smarty->assign('confirmation', 1);
        Context::getContext()->cookie->id_customer = (int)$customer->id;
        Context::getContext()->cookie->customer_lastname = $customer->lastname;
        Context::getContext()->cookie->customer_firstname = $customer->firstname;
        Context::getContext()->cookie->passwd = $customer->passwd;
        Context::getContext()->cookie->logged = 1;
        // if register process is in two steps, we display a message to confirm account creation
        if (!Configuration::get('PS_REGISTRATION_PROCESS_TYPE')) {
            Context::getContext()->cookie->account_created = 1;
        }
        $customer->logged = 1;
        Context::getContext()->cookie->email = $customer->email;
        Context::getContext()->cookie->is_guest = !Tools::getValue('is_new_customer', 1);
        // Update cart address
        Context::getContext()->cart->secure_key = $customer->secure_key;
    }

    public function insertNewAddress($id_country, $id_state, $city, $postcode){
        $addressObj = new Address();
        $addressObj->id_customer = Context::getContext()->customer->id;
        $addressObj->firstname   = pSQL(' ');
        $addressObj->lastname    = pSQL(' ');
        $addressObj->address1    = pSQL(' ');
        $addressObj->alias       = pSQL('My Address');
        $addressObj->postcode    = pSQL($postcode);
        $addressObj->city        = pSQL($city);
        $addressObj->id_country  = (int)$id_country;
        $addressObj->id_state    = (int)$id_state;
        $addressObj->add();
        $this->aps_helper->log('APS apple pay address added' . $addressObj->id);
        return $addressObj->id;
    }

    /**
     * Call apple pay api
     *
     * @return json
     */
    public function validateApplePayUrl($apple_url)
    {
        $ch                            = curl_init();
        $domain_name                   = $this->aps_config->getApplePayDomainName();
        $apple_pay_display_name        = $this->aps_config->getApplePayDisplayName();
        $production_key                = $this->aps_config->getApplePayProductionKey();

        $certificate_path              = _PS_UPLOAD_DIR_ . 'aps_certificate/' . $this->aps_config->getApplePayCertificateFileName();
        $apple_pay_merchant_identifier = openssl_x509_parse(Tools::file_get_contents($certificate_path))['subject']['UID'];
        $certificate_key               = _PS_UPLOAD_DIR_ . 'aps_certificate/' . $this->aps_config->getApplePayCertificateKeyFileName();
        $data                          = '{"merchantIdentifier":"' . $apple_pay_merchant_identifier . '", "domainName":"' . $domain_name . '", "displayName":"' . $apple_pay_display_name . '"}';
        $this->aps_helper->log('Init apple_url' . $apple_url . 'certificate_path' . $certificate_path);
        curl_setopt($ch, CURLOPT_URL, $apple_url);
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate_path);
        curl_setopt($ch, CURLOPT_SSLKEY, $certificate_key);
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $production_key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            $this->aps_helper->log('apple Api Curl Call error : '.$error_msg);
        }
        curl_close($ch);
        $this->aps_helper->log('Apple curl response ' . json_encode($response));
        return $response;
    }

    /**
     * Init apple pay payment
     */
    public function initApplePayPayment($response_params)
    {
        $status   = 'success';
        $id_order = 0;
        $message   = '';
        try {
            $id_order        = $this->aps_order->getSessionOrderId();
            if (! $id_order || null == $id_order || empty($id_order)) {
                $id_order = Context::getContext()->cookie->__get('aps_apple_order_id');
                $this->aps_order->setOrderId($id_order);
            }
            $this->aps_order->loadOrder($id_order);
            $currency       = $this->aps_order->getCurrencyCode();
            $gateway_params = array(
                'digital_wallet'      => 'APPLE_PAY',
                'command'             => $this->aps_config->getCommand(ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY),
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getApplePayAccessCode(),
                'merchant_reference'  => $id_order,
                'language'            => $this->aps_order->getLanguageCode(),
                'amount'              => $this->aps_helper->convertGatewayAmount($this->aps_order->getTotal(), $this->aps_order->getCurrencyValue(), $currency),
                'currency'            => strtoupper($currency),
                'customer_email'      => $this->aps_order->getEmail(),
                'apple_data'          => $response_params->data->paymentData->data,
                'apple_signature'     => $response_params->data->paymentData->signature,
                'customer_ip'         => $this->aps_helper->getCustomerIp(),
            );
            foreach ($response_params->data->paymentData->header as $key => $value) {
                $gateway_params['apple_header'][ 'apple_' . $key ] = $value;
            }
            foreach ($response_params->data->paymentMethod as $key => $value) {
                $gateway_params['apple_paymentMethod'][ 'apple_' . $key ] = $value;
            }
            $signature                   = $this->aps_helper->calculateSignature($gateway_params, 'request', 'apple_pay');
            $gateway_params['signature'] = $signature;

            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('Apple payment request ' . json_encode($gateway_params));

            $response = $this->callApi($gateway_params, $gateway_url);

            $this->aps_helper->log('Apple payment response ' . json_encode($response));
            if (ApsConstant::APS_PAYMENT_SUCCESS_RESPONSE_CODE === $response['response_code'] || ApsConstant::APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE === $response['response_code']) {
                $this->aps_order->successOrder($response, 'online');
                $status = 'success';
            } elseif (in_array($response['response_code'], ApsConstant::APS_ONHOLD_RESPONSE_CODES, true)) {
                $this->aps_order->onHoldOrder($response['response_message']);
                $aps_error_log = "APS apple pay on hold stage : \n\n" . json_encode($response, true);
                $this->aps_helper->log($aps_error_log);
                $status = 'success';
            } else {
                $result = $this->aps_order->declineOrder($response, $response['response_message']);
                $status = 'error';
                if ($result) {
                    throw new Exception($response['response_message']);
                }
            }
        } catch (\Exception $e) {
            $status    = 'error';
            $message   = $e->getMessage();
            $this->aps_helper->log("apple pay to aps error: " . $message);
            // Don't cancelled order if already payment success
            if ( in_array($this->aps_order->getStatusId(),
                [
                    $this->aps_helper->processingOrderStatusId(),
                    $this->aps_helper->shippedOrderStatusId(),
                    $this->aps_helper->completeOrderStatusId(),
                    $this->aps_helper->refundedOrderStatusId(),
                    $this->aps_helper->voidedOrderStatusId(),
                    $this->aps_helper->processingProgressOrderStatusId()
                ]
                ) ) {
                $status   = 'success';
            }
        }
        return array(
            'status'   => $status,
            'order_id' => $id_order,
            'message'  => $message
        );
    }

    public function refillCart($id_order)
    {
        $this->aps_helper->log("refillCart called #" . $id_order);
        if (!$id_order) {
            return false;
        }
        $oldCart     = new Cart(Order::getCartIdStatic($id_order, $this->context->customer->id));
        $duplication = $oldCart->duplicate();
        if (!$duplication || !Validate::isLoadedObject($duplication['cart'])) {
            //$this->errors[] = Tools::displayError('Sorry. We cannot renew your order.');
            return false;
        } elseif (!$duplication['success']) {
            //$this->errors[] = Tools::displayError('Some items are no longer available, and we are unable to renew your order.');
            return false;
        } else {
            $this->context->cookie->id_cart = $duplication['cart']->id;
            $context                        = $this->context;
            $context->cart                  = $duplication['cart'];
            CartRule::autoAddToCart($context);
            $this->context->cookie->write();
            if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                //Tools::redirect('index.php?controller=order-opc');
            }
            //Tools::redirect('index.php?controller=order');
            return true;
        }
    }
    public function refundRequest($id_order, $amount){
        $result = array();
        $refund_response = $this->doRefund($id_order, $amount);
        if ($refund_response['status'] == 'success') {
            $result['error'] = false;
            $result['msg']   = $this->module->l('Refund is successfully', 'amazonpaymentservicespayment');
        } else {
            $result['error'] = true;
            $result['msg']   = isset($refund_response['message']) && !empty($refund_response['message']) ? (string)$refund_response['message'] : $this->module->l('Unable to refund', 'amazonpaymentservicespayment');
        }
        return $result;
    }

    public function doRefund($id_order, $amount){
        $response_arr = array(
            'status'  => 'success',
            'message' => '',
            'data' => array(),
        );
        try {
            $this->aps_order->loadOrder($id_order);
            $currency      = $this->aps_order->getCurrencyCode();
            $payment_method = ApsOrder::getApsMetaValue($this->aps_order->getOrderId(), 'payment_method');
            if($payment_method == ApsConstant::APS_PAYMENT_METHOD_VALU || $payment_method == ApsConstant::APS_PAYMENT_METHOD_NAPS){
                $total         = $this->aps_order->getTotal();
                $total         = $this->aps_helper->convertDecimalToIntAmount($total, $currency);

                $req_amount = $this->aps_helper->convertDecimalToIntAmount($amount, $currency);
                if((float)$req_amount < (float)$total || (float)$req_amount > (float)$total){
                    throw new \Exception('Partial refund is not available in this payment method');
                }
            }

            $merchant_reference = $id_order;
            if($payment_method == ApsConstant::APS_PAYMENT_METHOD_VALU ){
                $merchant_reference = ApsOrder::getApsMetaValue( $id_order, 'valu_reference_id' );
            }

            $access_code    = $this->aps_config->getAccessCode();
            $signature_type = 'regular';
            if($payment_method == ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY){
                $access_code = $this->aps_config->getApplePayAccessCode();
                $signature_type = 'apple_pay';
            }

            $aps_payment_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_payment_response'), true);
            $aps_check_status_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_check_status_response'), true);
            $amazon_ps_data = array_merge((array)$aps_payment_response, (array)$aps_check_status_response);

            $gateway_params  = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $access_code,
                'merchant_reference'  => $merchant_reference,
                'language'            => $this->aps_order->getLanguageCode()
            );

            $total_amount               = $this->aps_helper->convertedAdminOrderToGatewayAmount( $amount, strtoupper($currency), $this->aps_order->getCurrencyValue());
            $gateway_params['amount']   = $total_amount;
            $gateway_params['currency'] = $this->aps_helper->getGatewayCurrencyCode(null, $currency);
            $gateway_params['command']  = ApsConstant::APS_COMMAND_REFUND;
            $gateway_params['order_description'] = 'Order#' . $id_order;


            $signature = $this->aps_helper->calculateSignature($gateway_params, 'request', $signature_type);

            $gateway_params['signature']         = $signature;

            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('APS Refund request \n\n' . $gateway_url . json_encode( $gateway_params, true ) );

            $response = $this->callApi( $gateway_params, $gateway_url );
            $this->aps_helper->log( 'APS refund response \n\n' . json_encode( $response, true ) );
            if ( ApsConstant::APS_REFUND_SUCCESS_RESPONSE_CODE === $response['response_code'] ) {
                $this->aps_helper->log( 'APS refund response success\n\n');
            } else {
                throw new \Exception( $response['response_message'] );
            }
        } catch ( Exception $e ) {
            $this->aps_helper->log( 'Submit refund Error \n\n' . $e->getMessage() );
            $response_arr['status']  = 'error';
            $response_arr['message'] = $e->getMessage();
        }
        return $response_arr;
    }

    public function doCaptureVoid($id_order, $amount, $capture_void)
    {
        $response_arr = array(
            'status'  => 'success',
            'message' => '',
            'data' => array(),
        );
        try {
            $this->aps_order->loadOrder($id_order);
            $currency      = $this->aps_order->getCurrencyCode();
            $payment_method = ApsOrder::getApsMetaValue($this->aps_order->getOrderId(), 'payment_method');


            $access_code    = $this->aps_config->getAccessCode();
            $signature_type = 'regular';
            if($payment_method == ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY){
                $access_code = $this->aps_config->getApplePayAccessCode();
                $signature_type = 'apple_pay';
            }

            $aps_payment_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_payment_response'), true);
            $aps_check_status_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_check_status_response'), true);
            $amazon_ps_data = array_merge((array)$aps_payment_response, (array)$aps_check_status_response);

            $gateway_params  = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $access_code,
                'merchant_reference'  => $id_order,
                'language'            => $this->aps_order->getLanguageCode()
            );

            $total_amount               = $this->aps_helper->convertedAdminOrderToGatewayAmount( $amount, strtoupper($currency), $this->aps_order->getCurrencyValue());
            if($capture_void == ApsConstant::APS_COMMAND_CAPTURE){
                $gateway_params['amount']   = $total_amount;
                $gateway_params['currency'] = $this->aps_helper->getGatewayCurrencyCode(null, $currency);
            }
            $gateway_params['command']  = $capture_void;
            $gateway_params['order_description'] = 'Order#' . $id_order;


            $signature = $this->aps_helper->calculateSignature($gateway_params, 'request', $signature_type);

            $gateway_params['signature']         = $signature;

            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('APS Capture Void request \n\n' . $gateway_url . json_encode( $gateway_params, true ) );

            $response = $this->callApi( $gateway_params, $gateway_url );
            $this->aps_helper->log( 'APS Capture Void response \n\n' . json_encode( $response, true ) );
            if ( ApsConstant::APS_CAPTURE_SUCCESS_RESPONSE_CODE === $response['response_code'] || ApsConstant::APS_AUTHORIZATION_VOIDED_SUCCESS_RESPONSE_CODE === $response['response_code']) {
                $this->aps_helper->log( 'APS Capture Void response success\n\n');
            } else {
                throw new \Exception( $response['response_message'] );
            }
        } catch ( Exception $e ) {
            $this->aps_helper->log( 'Submit Capture Error \n\n' . $e->getMessage() );
            $response_arr['status']  = 'error';
            $response_arr['message'] = $e->getMessage();
        }
        return $response_arr;
    }

    public function captureVoidRequest($id_order, $amount, $capture_void){
        $result = array();
        $refund_response = $this->doCaptureVoid($id_order, $amount, $capture_void);
        if ($refund_response['status'] == 'success') {
            $result['error'] = false;
            $result['msg']   = $this->module->l($capture_void . ' is successfully');
        } else {
            $result['error'] = true;
            $result['msg']   = isset($refund_response['message']) && !empty($refund_response['message']) ? (string)$refund_response['message'] : $this->module->l('Unable to ') . $capture_void;
        }
        return $result;
    }

    public function CheckPaymentStatusForPendingOrder() {
        $db = Db::getInstance();

        $duration_mins = $this->aps_config->getCheckStatusCronDuration();
        $current_datetime = date("Y-m-d H:i:s");
        $order_datetime = date("Y-m-d H:i:s", strtotime("-{$duration_mins} minutes", strtotime($current_datetime)));
        $order_from_datetime = date("Y-m-d H:i:s", strtotime("- 7 days", strtotime($current_datetime)));

        $check_for_status = [Configuration::get('APS_OS_PENDING', null), $this->aps_helper->failedOrderStatusId()];

        $sql = 'SELECT id_order FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `current_state` IN (' . implode(', ', $check_for_status) . ') AND `module` = "amazonpaymentservices"';
        $sql .= ' AND `date_add` <"' . $order_datetime . '"';
        $sql .= ' AND `date_add` >"' . $order_from_datetime . '"';

        if (Context::getContext()->shop->getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        } else {
            $sql .= ' AND id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        }

        $results =  $db->executeS($sql);
        foreach ($results as $key => $value) {
            $this->doCheckPaymentStatus($value['id_order']);
        }
        $this->aps_helper->log('check status run #'.$order_from_datetime.'  : '.$order_datetime);
    }

    public function doCheckPaymentStatus($id_order){
        $response = $this->aps_payment_status_checker($id_order);
        $this->aps_order->loadOrder($id_order);
        if ( ! empty( $response ) && isset( $response['response_code'] ) ) {
            $response_code    = $response['response_code'];

            $transaction_code = isset($response['transaction_code'])?$response['transaction_code'] : '';

            $meta_id =  ApsOrder::saveApsPaymentMetaData($id_order, 'aps_check_status_response', Tools::jsonEncode($response));
            $this->aps_helper->log('check status order #' . $id_order . ' meta updated id' . $meta_id);

            if ( ApsConstant::APS_CHECK_STATUS_SUCCESS_RESPONSE_CODE === $response_code && (ApsConstant::APS_PAYMENT_SUCCESS_RESPONSE_CODE === $transaction_code || ApsConstant::APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE === $transaction_code) )
            {
                $status = $this->aps_helper->processingOrderStatusId();
                if ($this->aps_order->getStatusId() != $status) {
                    $order_note = 'Payment complete by Amazon Payment Services Check status';
                    $this->aps_order->changeOrderStatus($status);
                    $this->aps_helper->log('check success order #'.$id_order.' '.$order_note);

                }
            }else {
                $status = Configuration::get('PS_OS_CANCELED') ? Configuration::get('PS_OS_CANCELED') : _PS_OS_CANCELED_;
                if ($this->aps_order->getStatusId() != $status) {
                    $order_note = 'Payment cancelled by Amazon Payment Services Check status';
                    $this->aps_order->changeOrderStatus($status);
                    $this->aps_helper->log('check success order #'.$id_order.' '.$order_note);
                }
            }
        }
    }

    public function aps_payment_status_checker($id_order){
        try {
            $this->aps_order->loadOrder($id_order);
            $currency      = $this->aps_order->getCurrencyCode();
            $payment_method = ApsOrder::getApsMetaValue($this->aps_order->getOrderId(), 'payment_method');

            $merchant_reference = $id_order;
            if($payment_method == ApsConstant::APS_PAYMENT_METHOD_VALU ){
                $merchant_reference = ApsOrder::getApsMetaValue( $id_order, 'valu_reference_id' );
                $this->aps_helper->log('Check Status valu reference : '.$id_order."--".$merchant_reference );
            }

            $access_code    = $this->aps_config->getAccessCode();
            $signature_type = 'regular';
            if($payment_method == ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY){
                $access_code = $this->aps_config->getApplePayAccessCode();
                $signature_type = 'apple_pay';
            }


            $gateway_params = array(
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $access_code,
                'language'            => $this->aps_order->getLanguageCode(),
                'query_command'       => ApsConstant::APS_COMMAND_CHECK_STATUS,
                'merchant_reference'  => $merchant_reference
            );

            //calculate request signature
            $signature             = $this->aps_helper->calculateSignature($gateway_params, 'request', $signature_type);
            $gateway_params['signature'] = $signature;

            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            $this->aps_helper->log('Check Status Request Params : '.$gateway_url . print_r($gateway_params, 1));

            $response = $this->callApi($gateway_params, $gateway_url);

            $this->aps_helper->log('Check Status Response Params : '. print_r($response, 1));
            return $response;
        } catch (Exception $e) {
            $this->aps_helper->log('Check Status error for #: '. $id_order . ' : ' . $e->getMessage());
        }
    }

    /**
     * APS Delete Token
     */
    public function delete_aps_token($token ) {
        $status  = 'success';
        $message = '';
        try{
            $random_key = bin2hex(openssl_random_pseudo_bytes(7));
            $gateway_params              = array(
                'service_command'     => 'UPDATE_TOKEN',
                'merchant_identifier' => $this->aps_config->getMerchantIdentifier(),
                'access_code'         => $this->aps_config->getAccessCode(),
                'merchant_reference'  => $random_key,
                'language'            => $this->aps_config->getLanguage(),
                'token_name'          => $token,
                'token_status'        => 'INACTIVE',
            );
            $signature                   = $this->aps_helper->calculateSignature( $gateway_params, 'request' );
            $gateway_params['signature'] = $signature;

            $gateway_url = $this->aps_helper->getGatewayUrl('notificationApi');
            //Delete token request log
            $this->aps_helper->log( 'APS Delete token request ' . json_encode( $gateway_params ) );
            $response = $this->callApi($gateway_params, $gateway_url);

            $this->aps_helper->log( 'APS delete token \n\n' . json_encode( $response, true ) );

            if ( isset( $response['response_code'] ) && ApsConstant::APS_PAYMENT_TOKEN_UPDATE_RESPONSE_CODE === $response['response_code'] ) {
                $status  = 'success';
                $message = $response['response_message'];
            } else {
                $status  = 'error';
                $message = $response['response_message'];
                throw new Exception( $message );

            }
        } catch ( \Exception $e ) {
            $status   = 'error';
            $message  = $e->getMessage();
        }
        return array(
            'status'   => $status,
            'message'  => $message,
        );
    }

}
