<?php
/**
 * 2007-2019 PrestaShop
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
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) Stripe
 * @license   Commercial license
 */

class AmazonpaymentservicesremoveapscardModuleFrontController extends ModuleFrontController
{
    private $aps_payment;

    public function __construct()
    {
        parent::__construct();
        $this->aps_payment = AmazonpaymentservicesPayment::getInstance();
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $status = 'error';
        $message = '';
        if ($this->context->customer->id != null) {
            $card_token = Tools::getValue('aps_card_token');
            if ($card_token) {
                $aps_token  = ApsToken::getApsToken($this->context->customer->id, $card_token);
                if (! (empty($aps_token))) {
                    $response = $this->aps_payment->delete_aps_token($card_token);
                    if ($response['status'] == 'success') {
                        $apsToken = new ApsToken($aps_token['id_amazonpaymentservices_token']);
                        $apsToken->delete();
                        $status = 'success';
                        $message = $this->module->l('Card successfully deleted');
                    } else {
                        $message = $response['message'];
                    }
                }
            } else {
                $message = $this->module->l('Card token is missing');
            }
        } else {
            $message = $this->module->l('You are not authorized to delete card');
        }

        $result = array(
            'status'    => $status,
            'message'   => $message,
        );
        echo json_encode($result);
        exit;
    }
}
