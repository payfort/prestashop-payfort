<?php

class AmazonpaymentservicesSuper extends ModuleFrontController
{
    public function __construct()
    {
        $this->name     = 'amazonpaymentservices';
        $_GET['module'] = $this->name;

        parent::__construct();
    }
}
