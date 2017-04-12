<?php

class Payfort_Fort_Super extends ModuleFrontController
{

    public function __construct()
    {
        $this->name     = 'payfortfort';
        $_GET['module'] = $this->name;

        parent::__construct();
    }

}

?>