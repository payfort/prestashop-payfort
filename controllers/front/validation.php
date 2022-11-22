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

//include_once dirname(__FILE__) . '/../../lib/init.php';

class AmazonpaymentservicesValidationModuleFrontController extends ModuleFrontController
{
    private $aps_helper;
    private $aps_config;
    private $aps_payment;
    private $aps_order;

    public function __construct()
    {
        parent::__construct();
        $this->aps_config  = AmazonpaymentservicesConfig::getInstance();
        $this->aps_helper  = AmazonpaymentservicesHelper::getInstance();
        $this->aps_payment = AmazonpaymentservicesPayment::getInstance();
        $this->aps_order   = new AmazonpaymentservicesOrder();
    }

    public function initContent()
    {
        //$aps_params = array_merge($_GET, $_POST);
        $aps_params = Tools::getAllValues();
        if (isset($aps_params['content_only'])) {
            if (1 == $aps_params['content_only']) {
                $this->context->smarty->assign('nobots', true);
                $this->setTemplate('module:amazonpaymentservices/views/templates/hook/payment_valu_term.tpl');
            }
        }
        parent::initContent();
    }

    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
        $aps_params = Tools::getAllValues();
        if (isset($aps_params['action'])) {
            if ($aps_params['action'] == 'checkout') {
                $this->postApsPayment();
            } elseif ($aps_params['action'] == 'offline_response') {
                $this->responseOffline();
            } elseif ($aps_params['action'] == 'responseOnline') {
                $this->responseOnline();
            } elseif ($aps_params['action'] == 'merchantPageResponse') {
                $this->merchantPageResponse();
            } elseif ($aps_params['action'] == 'merchantPageCancel') {
                $this->merchantPageCancel();
            } elseif ($aps_params['action'] == 'getInstallmentPlans') {
                $this->getInstallmentPlans();
            } elseif ($aps_params['action'] == 'valu_customer_verify') {
                $this->valuCustomerVerify();
            } elseif ($aps_params['action'] == 'valu_generate_otp') {
                $this->valuGenerateOtp();
            } elseif ($aps_params['action'] == 'valu_otp_verify') {
                $this->valuOtpVerify();
            } elseif ($aps_params['action'] == 'validate_apple_url') {
                $this->validateAppleUrl();
            } elseif ($aps_params['action'] == 'send_apple_payment_aps') {
                $this->sendApplePaymentToAps();
            } elseif ($aps_params['action']  == 'create_cart_order') {
                $this->createCartOrder();
            } elseif ($aps_params['action'] == 'get_apple_pay_cart_data') {
                $this->getApplePayCartValues();
            } elseif ($aps_params['action'] == 'validate_apple_pay_shipping_address') {
                $this->validateApplePayShippingAddress();
            } elseif ($aps_params['action'] == 'displayError') {
                $this->displayError();
            }
        }
    }

    public function postApsPayment()
    {
        $result = [];
        $payment_method   = Tools::getValue('aps_payment_method');
        $integration_type = Tools::getValue('aps_integration_type');

        if (ApsConstant::APS_PAYMENT_METHOD_VALU == $payment_method) {
            // save payment method and integration type
            $id_order       = Context::getContext()->cookie->__get('aps_valu_id_order');
            ApsOrder::savePaymentMethodIntegration($id_order, $payment_method, $integration_type);
            $result = $this->valuPurchase();
            echo json_encode($result);
            exit;
        }

        //check required field installment hosted checkout
        if (ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS == $payment_method &&
        ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT == $integration_type) {
            $installment_plan_code   = Tools::getValue('aps_installment_plan_code');
            $installment_issuer_code = Tools::getValue('aps_installment_issuer_code');
            if (empty($installment_plan_code) || empty($installment_issuer_code)) {
                $result = array(
                    'success' => false,
                    'error_message' => $this->module->l('Please select installment plan.')
                );
                echo json_encode($result);
                exit;
            }
        }

        //validate & create order
        $this->validateRequestAndCreateOrder($payment_method);

        //get order id
        $id_order = $this->module->currentOrder;
        if (ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY == $payment_method) {
            Context::getContext()->cookie->__set('aps_apple_order_id', $id_order);
        }

        // save payment method and integration type
        ApsOrder::savePaymentMethodIntegration($id_order, $payment_method, $integration_type);

        if (ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT == $payment_method && ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT == $integration_type) {
            $visa_checkout_call_id = Tools::getValue('aps_visa_checkout_callid');
            $this->aps_helper->log('Visa call id' . $visa_checkout_call_id);
            if (isset($visa_checkout_call_id) && !(empty($visa_checkout_call_id))) {
                $url = $this->aps_payment->visaCheckoutHosted($visa_checkout_call_id, ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT, $id_order);
                $this->aps_helper->log('Visa call url' . $url);
                if (true === $url || 1 == $url) {
                    $objOrder = new Order($id_order);
                    $customer = new Customer($objOrder->id_customer);
                    $url = Context::getContext()->link->getPageLink(
                        'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                    );
                }
                $success = false;
                if ($url != false) {
                    $success = true;
                }
                $result = array(
                    'success' => $success,
                    'url' => '',
                    'data' => ['redirect_url' => $url],
                    'params' => ''
                );
            }
        } elseif (ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY == $payment_method) {
            $apple_order = $this->aps_payment->getApplePayOrderData();
            $result = array(
                    'success' => true,
                    'apple_order' => $apple_order
                );
        } else {
            $extras = [];
            $embedded_hosted_request = 0;
            // tokenization payment request
            $aps_payment_token_cc    = Tools::getValue('aps_payment_token_cc');
            $aps_card_security_code  = Tools::getValue('aps_saved_card_security_code');

            if (isset($aps_payment_token_cc) && ! empty($aps_payment_token_cc)) {
                $extras['aps_payment_token'] = trim($aps_payment_token_cc, ' ');
                $extras['aps_card_bin'] = ApsToken::getCardBinByToken($extras['aps_payment_token']);
            }
            if (isset($aps_card_security_code) && ! empty($aps_card_security_code)) {
                $extras['aps_payment_cvv'] = trim($aps_card_security_code, ' ');
            }

            // check for embeded hosted checkout request
            if (ApsConstant::APS_PAYMENT_METHOD_CC == $payment_method &&
            ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT == $integration_type) {
                $embedded_hosted_checkout = $this->aps_config->isEmbeddedHostedCheckout();
                if (null != Tools::getValue('aps_installment_plan_code') && $embedded_hosted_checkout) {
                    ApsOrder::saveApsPaymentMetaData($id_order, 'embedded_hosted_order', 1);
                    $embedded_hosted_request = 1;
                }
            }

            //installment hosted checkout
            if (ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS == $payment_method &&
            ApsConstant::APS_INTEGRATION_TYPE_HOSTED_CHECKOUT == $integration_type || $embedded_hosted_request == 1) {
                $installment_plan_code   = Tools::getValue('aps_installment_plan_code');
                $installment_issuer_code = Tools::getValue('aps_installment_issuer_code');
                $installment_confirmation_en = Tools::getValue('aps_installment_confirmation_en');
                $installment_confirmation_ar = Tools::getValue('aps_installment_confirmation_ar');
                $installment_interest    = Tools::getValue('aps_installment_interest');
                $installment_amount      = Tools::getValue('aps_installment_amount');

                if (empty($installment_plan_code) || empty($installment_issuer_code)) {
                    $error_message = $this->module->l('Please select installment plan.');
                    Context::getContext()->cookie->__set('aps_error_msg', $error_message);
                    Tools::redirect('index.php?controller=order&step=1');
                } else {
                    if (! empty($installment_plan_code)) {
                        ApsOrder::saveApsPaymentMetaData($id_order, 'installment_plan_code', $installment_plan_code);
                    }
                    if (! empty($installment_issuer_code)) {
                        ApsOrder::saveApsPaymentMetaData($id_order, 'installment_issuer_code', $installment_issuer_code);
                    }
                    if (! empty($installment_confirmation_en)) {
                        ApsOrder::saveApsPaymentMetaData($id_order, 'installment_confirmation_en', $installment_confirmation_en);
                    }
                    if (! empty($installment_confirmation_ar)) {
                        ApsOrder::saveApsPaymentMetaData($id_order, 'installment_confirmation_ar', $installment_confirmation_ar);
                    }
                    if (! empty($installment_interest)) {
                        ApsOrder::saveApsPaymentMetaData($id_order, 'installment_interest', $installment_interest);
                    }
                    if (! empty($installment_amount)) {
                        ApsOrder::saveApsPaymentMetaData($id_order, 'installment_amount', $installment_amount);
                    }
                }
            }

            //get aps payment request params
            $gateway_params = $this->aps_payment->getPaymentRequestParams($payment_method, $integration_type, $extras);
            $result = array(
                'success' => true,
                'url' => $gateway_params['url'],
                'data' => $gateway_params,
                'params' => $gateway_params['params']
            );
        }
        echo json_encode($result);
        exit;
    }

    public function responseOnline()
    {
        $this->aps_helper->log('responseOnline');
        $this->handleResponse('online');
    }

    public function responseOffline()
    {
        $this->aps_helper->log('responseOffline');
        $this->handleResponse('offline');
        $this->aps_helper->log('webhook processed');
    }

    private function merchantPageResponse()
    {
        $this->handleResponse('online', null);
    }

    private function handleResponse($response_mode = 'online', $integration_type = 'redirection')
    {
        $response_params = Tools::getAllValues(); //never use $_REQUEST, it might include PUT .. etc
        if (! isset($response_params['merchant_reference']) && empty($response_params['merchant_reference'])) {
            $params = Tools::file_get_contents('php://input');
            if ($params) {
                $response_params = json_decode(filter_var($params, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES), true);
                $this->aps_helper->log('Webhook params');
            } else {
                $this->aps_helper->log('webhook params empty');
            }
        }
        $this->aps_helper->log($response_mode . ': handleResponse '.print_r($response_params, 1));
        if (isset($response_params['merchant_reference'])) {
            $id_order = $response_params['merchant_reference'];
            if (isset($response_params['payment_option']) && 'VALU' === $response_params['payment_option'] && 'offline' === $response_mode) {
                $id_order = ApsOrder::getValuOrderIdByReference($response_params['merchant_reference']);
            }
            $objOrder = new Order($id_order);

            // get order id if webhook call for valu order and valu refund webhook
            if ($objOrder->id == null) {
                if ((isset($response_params['command']) && in_array($response_params['command'], array('REFUND', 'CAPTURE', 'VOID_AUTHORIZATION'))) && 'offline' === $response_mode) {
                    $id_order = ApsOrder::getValuOrderIdByReference($response_params['merchant_reference']);
                    $objOrder = new Order($id_order);
                    $this->aps_helper->log("Valu orderId" . $id_order);
                }
            }

            $customer = new Customer($objOrder->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                $redirectUrl = Context::getContext()->link->getPageLink('order&step=1');
            } else {
                $success = $this->aps_payment->handleApsResponse($response_params, $response_mode, $integration_type);
                if ($success) {
                    $redirectUrl = Context::getContext()->link->getPageLink(
                        'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                    );
                    $this->aps_helper->log('success redirectUrl');
                } else {
                    //$redirectUrl = Context::getContext()->link->getPageLink('order&step=1');
                    $cancelStatus = Configuration::get('PS_OS_CANCELED');
                    $state = $objOrder->getCurrentState();

                    if ($state == $cancelStatus) {
                        $error_message = $this->module->l('You have cancelled the payment, please try again.');
                        Context::getContext()->cookie->__set('aps_error_msg', $error_message);
                    }
                    $redirectUrl = Context::getContext()->link->getPageLink(
                        'error&fc=module&module=' .  $this->module->name . '&action=displayError'
                    );

                }
                if ('offline' === $response_mode) {
                    $this->aps_helper->log('Webhook processed complete');
                    header('HTTP/1.1 200 OK');
                    exit;
                } else {
                    if (in_array(
                        $objOrder->getCurrentOrderState(),
                        [
                            $this->aps_helper->processingOrderStatusId(),
                            $this->aps_helper->shippedOrderStatusId(),
                            $this->aps_helper->completeOrderStatusId(),
                            $this->aps_helper->processingProgressOrderStatusId()
                        ]
                    )) {
                        $this->aps_helper->log('redirect_url');
                        $redirectUrl = Context::getContext()->link->getPageLink(
                            'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                        );
                    }
                }
            }
            $this->aps_helper->log('handleResponse redirectUrl ' . $redirectUrl);
            echo '<script>window.top.location.href = "' . $redirectUrl . '"</script>';
            exit;
        }
    }


    public function validateRequestAndCreateOrder($payment_method, $custom_apple_order = 0)
    {
        /*
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die;
        }

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            $this->aps_helper->log("checked validated customer".$cart->id_customer. '===' . $cart->id_address_delivery);
            Tools::redirect('index.php?controller=order&step=1');
            return;
        }
        // Check that this payment option is still available
        // in case the customer changed his address
        // Just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'amazonpaymentservices') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized && !$custom_apple_order) {
            die($this->l('This payment method is not available.'));
        }
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
            return;
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        if ($this->isValidOrder() === true) {
            $payment_status = $this->aps_helper->pendingOrderStatusId();
            $message = null;
        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');
            /**
             * Add a message to explain why the order has not been validated
             */
            $message = $this->module->l('An error occurred while processing payment');
        }
        $payment_method_title = $this->aps_helper->getPaymentMethodTitle($payment_method);
        $this->module->validateOrder(
            $cart->id,
            $payment_status,
            $total,
            $payment_method_title,
            $message,
            array(),
            (int)$currency->id,
            false,
            $customer->secure_key
        );
    }


    protected function isValidOrder()
    {
        /*
         * Add your checks right there
         */
        return true;
    }

    private function merchantPageCancel()
    {
        //set as pending order
        $cart = $this->context->cart;

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer) || !isset($this->module->currentOrder)) {
            $error_message = $this->module->l('You have cancelled the payment, please try again.');

            $id_order = $this->module->currentOrder;
            if (! $id_order || null == $id_order || empty($id_order)) {
                $id_order = Context::getContext()->cookie->__get('aps_apple_order_id');
                $this->aps_payment->refillCart($id_order);
                $this->aps_helper->log('refill cart and merchantPageCancel called');
            }
            Context::getContext()->cookie->apsErrors = $error_message;
            Tools::redirect('index.php?controller=order&step=1');
        }
        $this->aps_payment->merchantPageCancel();
        $objOrder = new Order($this->module->currentOrder);
        if ($objOrder) {
            $this->aps_payment->refillCart($objOrder->id);
            $error_message = $this->module->l('You have cancelled the payment, please try again.');
            Context::getContext()->cookie->apsErrors = $error_message;
            $this->aps_helper->log('merchantPageCancel called');

            Tools::redirect('index.php?controller=order&step=1');
        } else {
            $error_message = $this->module->l('You have cancelled the payment, please try again.');
            Context::getContext()->cookie->__set('aps_error_msg', $error_message);
            Tools::redirect(
                'index.php?fc=module&module=amazonpaymentservices&controller=error&action=displayError'
            );
        }
    }
    
    private function getInstallmentPlans()
    {
        $card_bin   = Tools::getValue('card_bin');
        $card_bin = str_replace(array( ' ', '*' ), array( '', '' ), $card_bin);
        $embedded_hosted_checkout   = Tools::getValue('embedded_hosted_checkout');
        $response = $this->aps_payment->getInstallmentPlanHandler($card_bin, $embedded_hosted_checkout);

        echo json_encode($response);
        exit;
    }

    private function valuCustomerVerify()
    {
        $response = [];
        $mobile_number = Tools::getValue('mobile_number');

        if (empty($mobile_number)) {
            $response['error'] = true;
            $response['error_message'] = $this->module->l('Mobile number missing');
        } else {
            $response = $this->aps_payment->valuVerifyCustomer($mobile_number);
            if ('success'  == $response['status']) {
                $response = $this->valuGenerateOtp($mobile_number, ApsConstant::APS_PAYMENT_METHOD_VALU);
            }
        }
        //todo remove after testing
        // $response['status'] = 'success';
        echo json_encode($response);
        exit;
    }

    private function valuGenerateOtp($mobile_number, $payment_method)
    {
        //validate & create order
        $this->validateRequestAndCreateOrder($payment_method);

        //get order id
        $id_order = $this->module->currentOrder;

        Context::getContext()->cookie->__set('aps_valu_id_order', $id_order);
        $reference_id = Context::getContext()->cookie->__get('aps_valu_reference_id');
        $response     = $this->aps_payment->valuOtpGenerate($mobile_number, $reference_id);

        if ('error'  == $response['status'] || 'genotp_error' == $response['status']) {
            $this->aps_payment->refillCart($id_order);
        }
        return $response;
    }

    private function valuOtpVerify()
    {
        $response = [];
        $otp = Tools::getValue('otp');

        if (empty($otp)) {
            $response['error'] = true;
            $response['error_message'] = $this->module->l('OTP is missing');
        } else {
            $id_order      = Context::getContext()->cookie->__get('aps_valu_id_order');
            $reference_id  = Context::getContext()->cookie->__get('aps_valu_reference_id');
            $mobile_number = Context::getContext()->cookie->__get('aps_valu_mobile_number');
            $response = $this->aps_payment->valuVerfiyOtp($mobile_number, $reference_id, $otp, $id_order);
        }
        echo json_encode($response);
        exit;
    }

    private function valuPurchase()
    {
        $result          = [];
        $success         = true;
        $redirect_url    = '';
        $error_msg       = '';
        $active_tenure   = Tools::getValue('active_tenure');
        $tenure_amount   = Tools::getValue('tenure_amount');
        $tenure_interest = Tools::getValue('tenure_interest');
        if (empty($active_tenure)) {
            $success   = false;
            $error_msg = $this->module->l('Please select installment plan.');
        } else {
            $reference_id   = Context::getContext()->cookie->__get('aps_valu_reference_id');
            $id_order       = Context::getContext()->cookie->__get('aps_valu_id_order');
            $mobile_number  = Context::getContext()->cookie->__get('aps_valu_mobile_number');
            $otp            = Context::getContext()->cookie->__get('aps_valu_otp');
            $transaction_id = Context::getContext()->cookie->__get('aps_valu_transaction_id');
            if (!empty($reference_id)) {
                ApsOrder::saveApsPaymentMetaData($id_order, 'valu_reference_id', $reference_id);
            }
            $response = $this->aps_payment->valuExecutePurchase($mobile_number, $reference_id, $otp, $transaction_id, $active_tenure, $id_order);
            if ('success'  == $response['status']) {
                $objOrder = new Order($id_order);
                $customer = new Customer($objOrder->id_customer);
                $redirect_url = Context::getContext()->link->getPageLink(
                    'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                );
                if (!empty($active_tenure)) {
                    ApsOrder::saveApsPaymentMetaData($id_order, 'active_tenure', $active_tenure);
                }
                if (!empty($tenure_amount)) {
                    ApsOrder::saveApsPaymentMetaData($id_order, 'tenure_amount', $tenure_amount);
                }
                if (!empty($tenure_interest)) {
                    ApsOrder::saveApsPaymentMetaData($id_order, 'tenure_interest', $tenure_interest);
                }
            } else {
                Context::getContext()->cookie->__set('aps_error_msg', $response['message']);
                $redirect_url = Context::getContext()->link->getPageLink(
                    'error&fc=module&module=' .  $this->module->name . '&action=displayError'
                );
            }
        }
        $result = array(
            'success' => $success,
            'url' => '',
            'data' => ['redirect_url' => $redirect_url],
            'params' => '',
            'error_msg' => $error_msg
        );
        return $result;
    }

    public function validateAppleUrl()
    {
        try {
            $aps_params = Tools::getAllValues();
            $apple_url = $aps_params['apple_url'];
            if (empty($apple_url)) {
                throw new \Exception('Apple pay url is missing');
            }
            if (! filter_var($apple_url, FILTER_VALIDATE_URL)) {
                throw new \Exception('Apple pay url is invalid');
            }
            $parse_apple = parse_url($apple_url);
            $matched_apple = preg_match('/^(?:[^.]+\.)*apple\.com[^.]+$/', $apple_url);
            if (! isset($parse_apple['scheme']) || ! in_array($parse_apple['scheme'], array( 'https' ), true)  || ! $matched_apple) {
                throw new \Exception('Apple pay url is invalid');
            }
            echo json_encode($this->aps_payment->validateApplePayUrl($apple_url));
        } catch (\Exception $e) {
            echo json_encode(array( 'error' => $e->getMessage() ));
        }
        exit;
    }

    public function sendApplePaymentToAps()
    {
        $this->aps_helper->log('sendApplePaymentToAps called');
        $redirect_url   = '';
        $apple_pay_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($apple_pay_data['data']) && ! empty($apple_pay_data['data'])) {
            $params          = html_entity_decode($apple_pay_data['data']);
            $response_params = json_decode(filter_var($params, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));

            $id_order = $this->module->currentOrder;
            if (! $id_order || null == $id_order || empty($id_order)) {
                $id_order = Context::getContext()->cookie->__get('aps_apple_order_id');
            }
            ApsOrder::saveApsPaymentMetaData($id_order, 'payment_method', ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY);
            $this->aps_helper->log('sendApplePaymentToAps before initApplePayPayment');
            $apple_payment   = $this->aps_payment->initApplePayPayment($response_params);
            $this->aps_helper->log('sendApplePaymentToAps after initApplePayPayment' .  json_encode($apple_payment, true));
            $objOrder = new Order($id_order);
            if ('success'  == $apple_payment['status'] ||
                in_array(
                    $objOrder->getCurrentOrderState(),
                    [
                        $this->aps_helper->processingOrderStatusId(),
                        $this->aps_helper->shippedOrderStatusId(),
                        $this->aps_helper->completeOrderStatusId(),
                        $this->aps_helper->processingProgressOrderStatusId()
                    ]
                )) {
                $customer = new Customer($objOrder->id_customer);
                $redirect_url = Context::getContext()->link->getPageLink(
                    'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                );
                $this->aps_helper->log('sendApplePaymentToAps redirect_url0 '. $redirect_url);
            } else {
                Context::getContext()->cookie->__set('aps_error_msg', $apple_payment['message']);
                $redirect_url = Context::getContext()->link->getPageLink(
                    'error&fc=module&module=' .  $this->module->name . '&action=displayError'
                );
                $this->aps_helper->log('sendApplePaymentToAps redirect_url1 '. $redirect_url);
            }
        } else {
            $id_order = $this->module->currentOrder;
            if (! $id_order || null == $id_order || empty($id_order)) {
                $id_order = Context::getContext()->cookie->__get('aps_apple_order_id');
            }
            if ($id_order) {
                $objOrder = new Order($id_order);
                if (in_array(
                    $objOrder->getCurrentOrderState(),
                    [
                        $this->aps_helper->processingOrderStatusId(),
                        $this->aps_helper->shippedOrderStatusId(),
                        $this->aps_helper->completeOrderStatusId(),
                        $this->aps_helper->processingProgressOrderStatusId()
                    ]
                )) {
                    $customer = new Customer($objOrder->id_customer);
                    $redirect_url = Context::getContext()->link->getPageLink(
                        'order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key
                    );
                    $this->aps_helper->log('sendApplePaymentToAps redirect_url2 '. $redirect_url);
                }
            } else {
                $this->aps_helper->log('sendApplePaymentToAps else called');
                Context::getContext()->cookie->__set('aps_error_msg', 'You have canceled the payment, please try again.');
                $redirect_url = Context::getContext()->link->getPageLink('order&step=1');
            }
        }
        $this->aps_helper->log('sendApplePaymentToAps called redirect_url '. $redirect_url);
        echo '<script>window.top.location.href = "' . $redirect_url . '"</script>';
        exit;
    }

    public function getApplePayCartValues()
    {
        $result = $this->aps_payment->getApplePayCartData();
        echo json_encode($result);
        exit;
    }

    public function validateApplePayShippingAddress()
    {
        $checkoutSession = null;
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $deliveryOptionsFinder = new DeliveryOptionsFinder(
                $this->context,
                $this->getTranslator(),
                $this->objectPresenter,
                new \PrestaShop\PrestaShop\Adapter\Product\PriceFormatter()
            );

            $checkoutSession = new CheckoutSession(
                $this->context,
                $deliveryOptionsFinder
            );
        }

        $result = array(
            'status'    => 'error',
            'error_msg' => 'No Shipping options are available.',
        );
        //$address_obj  =  $_POST;
        $address_obj  =  Tools::getAllValues();
        if (isset($address_obj['address_obj'])) {
            $address_data = $address_obj['address_obj'];
            $result =  $this->aps_payment->validateShippingAddress($address_data, $checkoutSession);
        }
        echo json_encode($result);
        exit;
    }

    public function createCartOrder()
    {
        $status      = 'success';
        $error_msg   = '';
        //$address_obj =  $_POST;
        $address_obj =  Tools::getAllValues();
        try {
            if (isset($address_obj['address_obj'])) {
                $address_data = $address_obj['address_obj'];
                $response =  $this->aps_payment->updateCustomerAndAddress($address_data);
                if ('success' == $response['status']) {
                    $custom_apple_order = 1;
                    $this->aps_helper->log("called validateRequestAndCreateOrder");
                    $this->validateRequestAndCreateOrder(ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY, $custom_apple_order);
                    $this->aps_helper->log("called validateRequestAndCreateOrder " . $this->module->currentOrder);
                    $id_order = $this->module->currentOrder;
                    Context::getContext()->cookie->__set('aps_apple_order_id', $id_order);
                } else {
                    $status    = 'error';
                    $error_msg = $response['error_msg'];
                }
            }
        } catch (\Exception $e) {
            $status    = 'error';
            $error_msg = $e->getMessage();
        }
        $result = array(
            'status'    => $status,
            'error_msg' => $error_msg,
        );
        echo json_encode($result);
        exit;
    }

    protected function displayError($message = '')
    {
        $message = Context::getContext()->cookie->__get('aps_error_msg');
        $cart = $this->context->cart;

        if ($cart) {
            $this->errors[] = $message;
            Context::getContext()->cookie->apsErrors = $message;
            Tools::redirect('index.php?controller=order&step=1');
        }
         
        $support_link_message = $this->module->l('If you think this is an error, you can contact our');

        $this->context->smarty->assign(array(
            'error' => $message,
            'support_link_message' => $support_link_message
        ));
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->setTemplate('module:amazonpaymentservices/views/templates/front/error.tpl');
        } else {
            return $this->setTemplate('error16.tpl');
        }
    }
}
