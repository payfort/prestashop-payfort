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

class AdminAmazonpaymentservicesController extends ModuleAdminController
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

    public function ajaxProcessRefund()
    {
        $id_order = Tools::getValue('id_order');
        $amount   = Tools::getValue('amount');

        $result = array();

        if (isset($id_order) && $id_order != '' && isset($amount) && $amount > 0) {
            $result = $this->aps_payment->refundRequest($id_order, $amount);
        } else {
            $result['error'] = true;
            $result['msg'] = $this->module->l($amount.'Required data is missing'. $id_order);
        }
        echo json_encode($result);
        exit;
    }

    public function ajaxProcessCapture()
    {
        $id_order = Tools::getValue('id_order');
        $amount   = Tools::getValue('amount');

        $result = array();

        if (isset($id_order) && $id_order != '' && isset($amount) && $amount > 0) {
            $result = $this->aps_payment->captureVoidRequest($id_order, $amount, 'CAPTURE');
        } else {
            $result['error'] = true;
            $result['msg'] = $this->module->l($amount.'Required data is missing'. $id_order);
        }
        echo json_encode($result);
        exit;
    }

    public function ajaxProcessVoid()
    {
        $id_order = Tools::getValue('id_order');
        $amount   = Tools::getValue('amount');

        $result = array();

        if (isset($id_order) && $id_order != '' && isset($amount) && $amount > 0) {
            $result = $this->aps_payment->captureVoidRequest($id_order, $amount, 'VOID_AUTHORIZATION');
        } else {
            $result['error'] = true;
            $result['msg'] = $this->module->l($amount.'Required data is missing'. $id_order);
        }
        echo json_encode($result);
        exit;
    }
}
