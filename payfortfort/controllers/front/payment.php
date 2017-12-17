<?php

include_once dirname(__FILE__) . '/../../lib/payfortFort/init.php';

/**
 * @since 1.5.0
 */
class PayfortfortPaymentModuleFrontController extends ModuleFrontController
{

    private $pfHelper;
    private $pfConfig;
    private $pfPayment;
    private $pfOrder;    

    public function __construct()
    {
        parent::__construct();
        $this->pfConfig  = Payfort_Fort_Config::getInstance();
        $this->pfHelper  = Payfort_Fort_Helper::getInstance();
        $this->pfPayment = Payfort_Fort_Payment::getInstance();
        $this->pfOrder   = new Payfort_Fort_Order();
        if(session_id() == '') {
            session_start();
        }
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $fortParams = array_merge($_GET, $_POST);
        if (isset($fortParams['action'])) {
            if ($fortParams['action'] == 'postPaymentForm') {
                $this->postPaymentForm();
            }
            elseif ($fortParams['action'] == 'getMerchantPageData') {
                $merchantPageData = $this->_getMerchantPageData();
                die(Tools::jsonEncode($merchantPageData));
            }
            elseif ($fortParams['action'] == 'processPaymentResponse') {
                $this->processPaymentResponse();
            }
            elseif ($fortParams['action'] == 'responseOnline') {
                $this->responseOnline();
            }
            elseif ($fortParams['action'] == 'merchantPageResponse') {
                $this->merchantPageResponse();
            }
            elseif ($fortParams['action'] == 'merchantPageCancel') {
                $this->merchantPageCancel();
            }
        }
    }

    private function _getPaymentFormData($paymentMethod, $integrationType)
    {
        //set as pending order
        $cart = $this->context->cart;

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $url = Context::getContext()->link->getPageLink('order&step=1');
            return array('success' => false, 'url' => $url);
        }

        $currency = $this->context->currency;

        $total    = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $mailVars = array();

        $invoiceAddress = new Address((int) $cart->id_address_invoice);

        $this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYFORT_PENDING'), $total, $paymentMethod, NULL, $mailVars, (int) $currency->id, false, $customer->secure_key);

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            $url = Context::getContext()->link->getPageLink('order&step=1');
            return array('success' => false, 'url' => $url);
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'payfortfort') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }
        $_SESSION['id_order'] = $this->module->currentOrder;
        if ($integrationType == PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION) {
            $form = $this->pfPayment->getPaymentRequestForm($paymentMethod, $integrationType);
            return array('success' => true, 'form' => $form);
        }
        else {
            $gatewayParams = $this->pfPayment->getPaymentRequestParams($paymentMethod, $integrationType);
            return array('success' => true, 'url' => $gatewayParams['url'],'params' => $gatewayParams['params']);
        }
    }

    public function postPaymentForm()
    {
        $paymentMethod   = PAYFORT_FORT_PAYMENT_METHOD_CC;
        $integrationType = PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION;
        $isInstallments  = isset($_POST['INSTALLMENTS']) && $_POST['INSTALLMENTS'] == '1' ? true : false;
        $isSADAD         = isset($_POST['SADAD']) && $_POST['SADAD'] == '1' ? true : false;
        $isNaps          = isset($_POST['NAPS']) && $_POST['NAPS'] == '1' ? true : false;
        if ($isInstallments) {
            $paymentMethod = PAYFORT_FORT_PAYMENT_METHOD_INSTALLMENTS;
        }
        if ($isSADAD) {
            $paymentMethod = PAYFORT_FORT_PAYMENT_METHOD_SADAD;
        }
        elseif ($isNaps) {
            $paymentMethod = PAYFORT_FORT_PAYMENT_METHOD_NAPS;
        }

        $result = $this->_getPaymentFormData($paymentMethod, $integrationType);
        $form   = '';
        if (!$result['success']) {
            Tools::redirect($result['url']);
        }
        else {
            $form = $result['form'];
        }

        echo '<html>';
        echo '<head><script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>';
        echo '</head>';
        echo '<body>';
        echo 'Redirecting to PayFort ....';
        echo $form;
        echo '</body>';
        echo '<script>$(document).ready(function(){$("#frm_payfort_fort_payment input[type=submit]").click();})</script>';
        echo '</html>';
        die();
    }

    private function _getMerchantPageData()
    {
        $fortParams = array_merge($_GET, $_POST);
        if(!empty($fortParams['paymentMethod']) && $fortParams['paymentMethod'] == PAYFORT_FORT_PAYMENT_METHOD_INSTALLMENTS){
            $integrationType = $this->pfConfig->getInstallmentsIntegrationType();
            $paymentMethod   = PAYFORT_FORT_PAYMENT_METHOD_INSTALLMENTS;
            $result          = $this->_getPaymentFormData($paymentMethod, $integrationType);
            return $result;
        }
        $integrationType = $this->pfConfig->getCcIntegrationType();
        $paymentMethod   = PAYFORT_FORT_PAYMENT_METHOD_CC;
        $result          = $this->_getPaymentFormData($paymentMethod, $integrationType);
        return $result;
    }

    public function processPaymentResponse()
    {
        $this->_handleResponse('offline');
    }

    public function responseOnline()
    {
        $this->_handleResponse('online');
    }

    public function merchantPageResponse()
    {
        $fortParams = array_merge($_GET, $_POST);
        if (!empty($fortParams['installments']) && $fortParams['installments'] == 'STANDALONE')
            $this->_handleResponse('online', $this->pfConfig->getInstallmentsIntegrationType ());
        else
            $this->_handleResponse('online', $this->pfConfig->getCcIntegrationType());
    }

    private function _handleResponse($response_mode = 'online', $integration_type = PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION)
    {
        $response_params = array_merge($_GET, $_POST); //never use $_REQUEST, it might include PUT .. etc
        if (isset($response_params['merchant_reference'])) {
            $objOrder = new Order($response_params['merchant_reference']);
            $customer = new Customer($objOrder->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                $redirectUrl = Context::getContext()->link->getPageLink('order&step=1');
            }
            else {
                $success = $this->pfPayment->handleFortResponse($response_params, $response_mode, $integration_type);
                if ($success) {
                    $redirectUrl = Context::getContext()->link->getPageLink('order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key);
                }
                else {
                    //$redirectUrl = Context::getContext()->link->getPageLink('order&step=1');
                    $redirectUrl = Context::getContext()->link->getPageLink('order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key);
                }
            }

            echo '<script>window.top.location.href = "' . $redirectUrl . '"</script>';
            exit;
        }
    }

    function merchantPageCancel()
    {
        //set as pending order
        $cart = $this->context->cart;

        $customer = new Customer($cart->id_customer);
        
        if (!Validate::isLoadedObject($customer) || !isset($_SESSION['id_order'])) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        $this->pfPayment->merchantPageCancel();
        $objOrder = new Order($_SESSION['id_order']);
        $this->pfPayment->refillCart($objOrder->id);
        unset($_SESSION['id_order']);
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $objOrder->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $objOrder->id . '&key=' . $customer->secure_key);
    }

}
