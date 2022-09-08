<?php

class AmazonpaymentservicesOrder extends AmazonpaymentservicesSuper
{
    public $id;
    private $order = array();
    private $orderId;
    private $aps_config;
    private $aps_helper;

    public function __construct()
    {
        parent::__construct();
        $this->aps_config = AmazonpaymentservicesConfig::getInstance();
        $this->aps_helper = AmazonpaymentservicesHelper::getInstance();
    }

    public function loadOrder($orderId)
    {
        $this->orderId = $orderId;
        $this->order   = $this->getOrderById($orderId);
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getSessionOrderId()
    {
        $orderId = $this->module->currentOrder;
        if (empty($orderId)) {
            $orderId = 0;
        }
        return $orderId;
    }

    public function getOrderId()
    {
        return $this->order->id;
    }

    public function getOrderById($orderId)
    {
        $order = new Order($orderId);
        return $order;
    }

    public function getShopId()
    {
        return (int) $this->order->id_shop;
    }

    public function getShopGroupId()
    {
        return (int) $this->order->id_shop_group;
    }

    public function getLoadedOrder()
    {
        return $this->order;
    }

    public function getCustomerId()
    {
        return $this->order->id_customer;
    }

    public function getEmail()
    {
        $customer = new Customer($this->order->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            return '';
        }
        return $customer->email;
    }

    public function getCustomerName()
    {
        $customer = new Customer($this->order->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            return '';
        }
        $fullName  = '';
        $firstName = $customer->firstname;
        $lastName  = $customer->lastname;

        $fullName = trim($firstName . ' ' . $lastName);
        return $fullName;
    }

    public function getCurrencyCode()
    {
        $currency = new Currency($this->order->id_currency);
        return $currency->iso_code;
    }

    public function getCurrencyValue()
    {
        return $this->order->conversion_rate;
    }

    public function getLanguageCode()
    {
        $iso_code = Language::getIsoById( (int)$this->order->id_lang );
        if ($iso_code != 'ar') {
            $iso_code = 'en';
        }
        return $iso_code;
    }

    public function getTotal()
    {
        return $this->order->total_paid_tax_incl;
    }

    public function getShippingTotal()
    {
        return $this->order->total_shipping_tax_incl;
    }

    public function getDiscountTotal()
    {
        return $this->order->total_discounts_tax_incl;
    }

    public function getSubtotal()
    {
        return $this->order->total_products_wt;
    }

    public function getPaymentMethod()
    {
        return $this->order->payment;
    }

    public function getStatusId()
    {
        $current_state = $this->order->getCurrentOrderState();
        return $current_state->id;
    }

    public function changeOrderStatus($status)
    {
        $this->aps_helper->log($this->getOrderId(). 'changeOrderStatus' . $status);
        $history           = new OrderHistory();
        $history->id_order = (int) $this->getOrderId();
        $history->id_employee = 0;
        $history->changeIdOrderState($status, $this->getLoadedOrder()); //order status=3
        $history->add(true);
        return true;
    }

    public function onHoldOrder($reason)
    {
        $this->aps_helper->log("onHoldOrder Called");
        $status = $this->aps_helper->onHoldOrderStatusId();
        if (!$this->getOrderId()) {
            return true;
        }
        if ($this->getStatusId() == $status) {
            return true;
        }
        // Don't onhold order if already payment success
        if ( in_array($this->getStatusId(),
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
        }
        $this->changeOrderStatus($status);
        $message = $this->module->l('Aps update : payment on hold.', 'order') . ' (' . $reason . ')';
        $this->addMessage($message);
        return true;
    }

    public function declineOrder($response_params = array(), $reason = '')
    {
        $this->aps_helper->log("decline Order Called");
        $status = Configuration::get('PS_OS_ERROR') ? Configuration::get('PS_OS_ERROR') : _PS_OS_ERROR_;
        if (!$this->getOrderId()) {
            return true;
        }
        if ($this->getStatusId() == $status) {
            return true;
        }
        // Don't cancelled order if already payment success
        if ( in_array($this->getStatusId(),
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
        }
        $this->changeOrderStatus($status);
        $message = $this->module->l('Aps update : payment failed.', 'order') . ' (' . $reason . ')';
        $this->addMessage($message);
        return true;
    }

    public function cancelOrder()
    {
        $this->aps_helper->log("cancel Order Called");
        $status = Configuration::get('PS_OS_CANCELED') ? Configuration::get('PS_OS_CANCELED') : _PS_OS_CANCELED_;
        if (!$this->getOrderId()) {
            return true;
        }
        if ($this->getStatusId() == $status) {
            return true;
        }

        // Don't cancelled order if already payment success
        if ( in_array($this->getStatusId(),
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
        }

        $this->changeOrderStatus($status);
        $message = $this->module->l('Aps update : payment canceled.', 'order');
        $this->addMessage($message);
        return true;
    }

    public function updateOrderHistory($message)
    {
        $this->addMessage($message);
    }

    public function successOrder($response_params, $response_mode)
    {
        $this->aps_helper->log("Success Order Called with mode " . $response_mode);
        $status = $this->aps_helper->processingOrderStatusId();
        if ($this->getStatusId() == $status) {
            if (isset($response_params['token_name'])) {
                $token_id = ApsToken::saveApsToken($this->getCustomerId(), $response_params);
                if ($token_id) {
                    $this->aps_helper->log("Aps Token Updated with token_id #" . $token_id);
                }
            }
            return true;
        }
        if ($this->getOrderId()) {
            $id_order = (int) $this->getOrderId();
            if (! empty($response_params)) {
                $meta_id =  ApsOrder::saveApsPaymentMetaData($id_order, 'aps_payment_response', Tools::jsonEncode($response_params));
                $this->aps_helper->log('success order #'.$id_order.' meta updated id'.$meta_id);
            }

            if (isset($response_params['token_name'])) {
                $token_id = ApsToken::saveApsToken($this->getCustomerId(), $response_params);
                if ($token_id) {
                    $this->aps_helper->log("Aps Token Updated with token_id #" . $token_id);
                }
            }

            $history           = new OrderHistory();
            $history->id_order = $id_order;
            $history->changeIdOrderState($status, $this->getLoadedOrder());
            $history->addWithemail(true, array());
            $message           = $this->module->l('APS update: payment complete with fort id #' . $response_params['fort_id'], 'order');
            $this->addMessage($message);
        }
        return true;
    }

    /**
     * Refund order webhook
     */
    public function refund_order($responseParams, $responseMode ) {
        $status = $this->aps_helper->refundedOrderStatusId();
        if ($this->getStatusId() == $status) {
            $this->aps_helper->log( 'APS refunded already');
            return true;
        }

        if ($this->getOrderId()) {
            $currency = $this->getCurrencyCode();
            $value = $this->getCurrencyValue();
            //amount convert back to original amount
            $amount = $responseParams['amount'];
            $amount = $this->aps_helper->convertGatewayToOrderAmount( $amount, $currency , $value);
            $meta_id = ApsOrder::saveApsPaymentMetaData( $this->getOrderId(), ApsConstant::APS_COMMAND_REFUND, $amount );
            $this->aps_helper->log( 'APS refund meta update with meta id'.$meta_id);
            if ($amount == $this->getTotal()){
                $this->changeOrderStatus($status);
                $message = $this->module->l('Aps update : payment refunded.', 'order');
                $this->addMessage($message);
                return true;
            }else{
                $refund_history = ApsOrder::getApsMetaValues( $this->getOrderId(), ApsConstant::APS_COMMAND_REFUND );
                $total_refunded = array_sum( array_column( $refund_history, 'meta_value' ) );
                $this->aps_helper->log( 'Refunded amt' . $total_refunded . 'order amt'. $this->getTotal());
                if ($total_refunded == $this->getTotal()){
                    $this->aps_helper->log( 'Refund change order status' . $status);
                    $this->changeOrderStatus($status);
                    $this->aps_helper->log( 'Refund change changed' . $status);
                    $message = $this->module->l('Aps update : payment refunded.', 'order');
                    $this->addMessage($message);
                    return true;
                }
            }
        }
    }

    /**
     * Void order webhook
     */
    public function void_order($responseParams, $responseMode ) {
        $this->aps_helper->log( 'APS void webhook called order' . $this->getOrderId());
        $status = $this->aps_helper->voidedOrderStatusId();
        if ($this->getStatusId() == $status) {
            $this->aps_helper->log( 'Order already void');
            return true;
        }

        $amount = $this->getTotal();
        $meta_id = ApsOrder::saveApsPaymentMetaData( $this->getOrderId(), ApsConstant::APS_COMMAND_VOID_AUTHORIZATION, $amount );
        $this->aps_helper->log( 'APS void meta update with meta id'. $meta_id);
        $this->changeOrderStatus($status);
        $this->aps_helper->log( 'Order #' . $this->getOrderId() . 'voided');

    }

        /**
     * Capture order webhook
     */
    public function capture_order($responseParams, $responseMode ) {
        $currency = $this->getCurrencyCode();
        $value = $this->getCurrencyValue();
        $amount = $responseParams['amount'];

        //amount convert back to original amount
        $amount = $this->aps_helper->convertGatewayToOrderAmount( $amount, $currency , $value);

        $this->aps_helper->log( 'APS capture meta update with amount' . $amount . $currency);

        $meta_id = ApsOrder::saveApsPaymentMetaData( $this->getOrderId(), ApsConstant::APS_COMMAND_CAPTURE, $amount );
        $this->aps_helper->log( 'APS capture meta update with meta id'.$meta_id);
    }

    /**
     * Add order private message.
     *
     * @param $text
     * @return bool
     */
    public function addMessage($text)
    {
        try {
            $text    = strip_tags($text, '<br>');
            if (!Validate::isCleanHtml($text)) {
                $text = 'Invalid payment message.';
            }

            $message = new Message();
            $message->message  = $text;
            $message->id_order = (int) $this->getOrderId();
            $message->private  = 1;
            $message->add();
            // Mark message as read to archive it.
            Message::markAsReaded($message->id, 0);
        } catch(Exception $e) {
            $this->aps_helper->log("Error in add msg " . $e->getMessage() );
        }
    }
}
