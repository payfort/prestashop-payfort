<?php

class Payfort_Fort_Order extends Payfort_Fort_Super
{

    private $order = array();
    private $orderId;
    private $pfConfig;

    public function __construct()
    {
        parent::__construct();
        $this->pfConfig = Payfort_Fort_Config::getInstance();
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
        if(empty($orderId)) {
            $orderId = $_SESSION['id_order'];
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

    public function getLoadedOrder()
    {
        return $this->order;
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

    public function getTotal()
    {
        return $this->order->total_paid_tax_incl;
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
        $history           = new OrderHistory();
        $history->id_order = (int) $this->getOrderId();
        $history->changeIdOrderState($status, $this->getLoadedOrder()); //order status=3                
        $history->addWithemail(false, array());
        return true;
    }

    public function declineOrder($reason = '')
    {
        $status = Configuration::get('PS_OS_ERROR') ? Configuration::get('PS_OS_ERROR') : _PS_OS_ERROR_;
        if (!$this->getOrderId()) {
            return true;
        }
        if ($this->getStatusId() == $status) {
            return true;
        }
        $this->changeOrderStatus($status);
        $message = $this->module->l('Payfort Fort update: payment failed.', 'order') . ' (' . $reason . ')';
        $this->addMessage($message);
        if ($this->pfConfig->orderPlacementIsOnSuccess()) {
            //$this->order->update_status( '', 'Hide Order' );
        }
        return true;
    }

    public function cancelOrder()
    {
        $status = Configuration::get('PS_OS_CANCELED') ? Configuration::get('PS_OS_CANCELED') : _PS_OS_CANCELED_;
        if (!$this->getOrderId()) {
            return true;
        }
        if ($this->getStatusId() == $status) {
            return true;
        }
        $this->changeOrderStatus($status);
        $message = $this->module->l('Payfort Fort update: payment canceled.', 'order');
        $this->addMessage($message);
        if ($this->pfConfig->orderPlacementIsOnSuccess()) {
            //$this->order->update_status( '', 'Hide Order' );
        }
        return true;
    }

    public function successOrder($response_params, $response_mode)
    {
        $status = $this->pfConfig->getSuccessOrderStatusId();
        if ($this->getStatusId() == $status) {
            return true;
        }
        if ($this->getOrderId()) {
            $history           = new OrderHistory();
            $history->id_order = (int) $this->getOrderId();
            $history->changeIdOrderState($status, $this->getLoadedOrder());
            $history->addWithemail(true, array());
            $message           = $this->module->l('Payfort Fort update: payment complete.', 'order');
            $this->addMessage($message);
        }
        return true;
    }

    /**
     * Add order private message.
     *
     * @param $text
     * @return bool
     */
    public function addMessage($text)
    {
        $message = new Message();
        $text    = strip_tags($text, '<br>');

        if (!Validate::isCleanHtml($text)) {
            $text = 'Invalid payment message.';
        }

        $message->message  = $text;
        $message->id_order = (int) $this->getOrderId();
        $message->private  = 1;

        return $message->add();
    }

}

?>