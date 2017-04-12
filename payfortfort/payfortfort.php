<?php

/*
 * 2007-2013 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
    exit;

include_once dirname(__FILE__) . '/lib/payfortFort/init.php';

class PayfortFORT extends PaymentModule
{

    public function __construct()
    {

        $this->name                      = 'payfortfort';
        $this->version                   = '1.6.1';
        $this->author                    = 'Payfort';
        $this->tab                       = 'payments_gateways';
        $this->author_uri                = 'https://github.com/payfort/prestashop-payfort';
        $this->fort_available_currencies = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'NZD', 'SAR', 'JOD', 'QAR', 'AED');
        //$this->bootstrap = true;
        //$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        parent::__construct();

        $this->displayName = 'Payfort FORT Gateway';
        $this->description = $this->l('Receive payment with Credit or Debit Card');


        /* For 1.4.3 and less compatibility */
        $updateConfig = array(
            'PS_OS_CHEQUE'      => 1,
            'PS_OS_PAYMENT'     => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING'    => 4,
            'PS_OS_DELIVERED'   => 5,
            'PS_OS_CANCELED'    => 6,
            'PS_OS_REFUND'      => 7,
            'PS_OS_ERROR'       => 8,
            'PS_OS_OUTOFSTOCK'  => 9,
            'PS_OS_BANKWIRE'    => 10,
            'PS_OS_PAYPAL'      => 11,
            'PS_OS_WS_PAYMENT'  => 12);

        foreach ($updateConfig as $u => $v)
            if (!Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0)
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                else
                    Configuration::updateValue($u, $v);
            }

        /* Check if cURL is enabled */
        if (!is_callable('curl_exec'))
            $this->warning = $this->l('cURL extension must be enabled on your server to use this module.');

    }

    public function install()
    {

        // check if the order status is defined

        if (!defined('PS_OS_PAYFORT_PENDING')) {

            // order status is not defined - check if, it exists in the table

            $rq = Db::getInstance()->getRow('

            SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_state_lang`

            WHERE id_lang = \'' . pSQL('1') . '\' AND  name = \'' . pSQL('Pending Payfort Payment') . '\'');

            if ($rq && isset($rq['id_order_state']) && intval($rq['id_order_state']) > 0) {

                // order status exists in the table - define it.

                define('PS_OS_PAYFORT_PENDING', $rq['id_order_state']);
            }
            else {

                // order status doesn't exist in the table
                // insert it into the table and then define it.

                Db::getInstance()->Execute('

                INSERT INTO `' . _DB_PREFIX_ . 'order_state` (`unremovable`, `color`) VALUES(1, \'orange\')');

                $stateid = Db::getInstance()->Insert_ID();

                Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`)

                VALUES(' . intval($stateid) . ', 1, \'Pending Payfort Payment\', \'\')');

                define('PS_OS_PAYFORT_PENDING', $stateid);
            }
        }

        return parent::install() &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('payment') &&
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') &&
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 1) &&
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'en') &&
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'AUTHORIZATION') &&
                Configuration::updateValue('PAYFORT_HASH_ALGORITHM', 'SHA1') &&
                Configuration::updateValue('PAYFORT_FORT_HOLD_REVIEW_OS', _PS_OS_PAYMENT_) &&
                Configuration::updateValue('PS_OS_PAYFORT_PENDING', PS_OS_PAYFORT_PENDING) &&
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', 'redirection') &&
                Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 0) &&
//                Configuration::updateValue('PAYFORT_FORT_ORDER_PLACEMENT', 'all') &&
                Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', 'base');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PAYFORT_FORT_SANDBOX_MODE');
        Configuration::deleteByName('PAYFORT_FORT_LANGUAGE');
        Configuration::deleteByName('PAYFORT_FORT_MERCHANT_IDENTIFIER');
        Configuration::deleteByName('PAYFORT_FORT_ACCESS_CODE');
        Configuration::deleteByName('PAYFORT_FORT_COMMAND');
        Configuration::deleteByName('PAYFORT_SHA_ALGORITHM');
        Configuration::deleteByName('PAYFORT_REQUEST_SHA_PHRASE');
        Configuration::deleteByName('PAYFORT_RESPONSE_SHA_PHRASE');
        Configuration::deleteByName('PAYFORT_FORT_HOLD_REVIEW_OS');
        Configuration::deleteByName('PAYFORT_FORT_INTEGRATION_TYPE');
//        Configuration::deleteByName('PAYFORT_FORT_ORDER_PLACEMENT');
        Configuration::deleteByName('PAYFORT_FORT_GATEWAY_CURRENCY');
        Configuration::deleteByName('PAYFORT_FORT_DEBUG_MODE');

        /* Removing credentials configuration variables */
        $currencies = Currency::getCurrencies(false, true);
        foreach ($currencies as $currency)
            if (in_array($currency['iso_code'], $this->fort_available_currencies)) {
                Configuration::deleteByName('PAYFORT_FORT_LOGIN_ID_' . $currency['iso_code']);
                Configuration::deleteByName('PAYFORT_FORT_KEY_' . $currency['iso_code']);
            }

        return parent::uninstall();
    }

    public function hookOrderConfirmation($params)
    {
        if ($params['objOrder']->module != $this->name)
            return;
        
        $successStatus = Configuration::get('PAYFORT_FORT_HOLD_REVIEW_OS');
        $declineStatus = Configuration::get('PS_OS_ERROR') ? Configuration::get('PS_OS_ERROR') : _PS_OS_ERROR_;
        $cancelStatus = Configuration::get('PS_OS_CANCELED') ? Configuration::get('PS_OS_CANCELED') : _PS_OS_CANCELED_;
        if ($params['objOrder']->getCurrentState() == $successStatus) {
            Configuration::updateValue('PAYFORTFORT_CONFIGURATION_OK', true);
            $this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['objOrder']->id)));
        }
        elseif($params['objOrder']->getCurrentState() == $cancelStatus) {
            $this->context->smarty->assign('status', 'cancelled');
        }
        else {
            $this->context->smarty->assign('status', 'failed');
        }

        $this->context->smarty->assign('order_status', $params['objOrder']->getCurrentState());
        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJQuery();
        if (version_compare(_PS_VERSION_, '1.5', '>='))
            $this->context->controller->addJqueryPlugin('fancybox');

        //$this->context->controller->addJS($this->_path . 'js/payfortfort.js');
        //$this->context->controller->addCSS($this->_path . 'css/payfortfort.css');
    }

    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('submitModule')) {
            $payfort_sandbox_mode = (int) Tools::getvalue('payfort_sandbox_mode');
            if ($payfort_sandbox_mode == 1) {
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 0);
            }
            $payfort_sadad = (int) Tools::getvalue('payfort_sadad');
            if ($payfort_sadad == 1) {
                Configuration::updateValue('PAYFORT_FORT_SADAD', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_SADAD', 0);
            }
            $payfort_naps = (int) Tools::getvalue('payfort_naps');
            if ($payfort_naps == 1) {
                Configuration::updateValue('PAYFORT_FORT_NAPS', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_NAPS', 0);
            }
            $payfort_credit_card = (int) Tools::getvalue('payfort_credit_card');
            if ($payfort_credit_card == 1) {
                Configuration::updateValue('PAYFORT_FORT_CREDIT_CARD', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_CREDIT_CARD', 0);
            }
            $payfort_integration_type = Tools::getvalue('payfort_integration_type');
            if (empty($payfort_integration_type)) {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', 'redirection');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', $payfort_integration_type);
            }


            $payfort_language = Tools::getvalue('payfort_language');
            if ($payfort_language == 'ar') {
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'ar');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'en');
            }
            $payfort_fort_command = Tools::getvalue('payfort_fort_command');
            if ($payfort_fort_command == 'AUTHORIZATION') {
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'AUTHORIZATION');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'PURCHASE');
            }
            $payfort_fort_sha_algorithm = Tools::getvalue('payfort_fort_sha_algorithm');
            if (empty($payfort_fort_sha_algorithm)) {
                Configuration::updateValue('PAYFORT_FORT_SHA_ALGORITHM', 'SHA1');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', $payfort_fort_sha_algorithm);
            }

            $payfort_fort_gateway_currency = Tools::getvalue('payfort_fort_gateway_currency');
            if (empty($payfort_fort_gateway_currency)) {
                Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', 'base');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', $payfort_fort_gateway_currency);
            }

//            $payfort_fort_order_placement = Tools::getvalue('payfort_fort_order_placement');
//            if(empty($payfort_fort_order_placement)) {
//                Configuration::updateValue('PAYFORT_FORT_ORDER_PLACEMENT', 'base');
//            }
//            else{
//                Configuration::updateValue('PAYFORT_FORT_ORDER_PLACEMENT', $payfort_fort_order_placement);
//            }

            $payfort_fort_debug_mode = (int) Tools::getvalue('payfort_fort_debug_mode');
            if ($payfort_fort_debug_mode == 1) {
                Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 0);
            }

            foreach ($_POST as $key => $value) {
                if ($key != "tab" && $key != "submitModule") {
                    Configuration::updateValue(strtoupper($key), $value);
                }
            }
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }
        // For "Hold for Review" order status
        $order_states = OrderState::getOrderStates((int) $this->context->cookie->id_lang);
        $this->context->smarty->assign(array(
            'available_currencies'          => $this->fort_available_currencies,
            'module_dir'                    => $this->_path,
            'order_states'                  => $order_states,
            'PAYFORT_FORT_SANDBOX_MODE'     => Configuration::get('PAYFORT_FORT_SANDBOX_MODE'),
            'PAYFORT_FORT_SADAD'            => Configuration::get('PAYFORT_FORT_SADAD'),
            'PAYFORT_FORT_NAPS'             => Configuration::get('PAYFORT_FORT_NAPS'),
            'PAYFORT_FORT_CREDIT_CARD'      => Configuration::get('PAYFORT_FORT_CREDIT_CARD'),
            'PAYFORT_FORT_INTEGRATION_TYPE' => Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE'),
            'PAYFORT_FORT_HOLD_REVIEW_OS'   => (int) Configuration::get('PAYFORT_FORT_HOLD_REVIEW_OS'),
            'PAYFORT_FORT_COMMAND'          => Configuration::get('PAYFORT_FORT_COMMAND'),
            'PAYFORT_FORT_LANGUAGE'         => Configuration::get('PAYFORT_FORT_LANGUAGE'),
            'PAYFORT_FORT_SHA_ALGORITHM'    => Configuration::get('PAYFORT_FORT_SHA_ALGORITHM'),
//            'PAYFORT_FORT_ORDER_PLACEMENT' => Configuration::get('PAYFORT_FORT_ORDER_PLACEMENT'),
            'PAYFORT_FORT_GATEWAY_CURRENCY' => Configuration::get('PAYFORT_FORT_GATEWAY_CURRENCY'),
            'PAYFORT_FORT_DEBUG_MODE'       => Configuration::get('PAYFORT_FORT_DEBUG_MODE'),
        ));

        $configuration_merchant_identifier = 'PAYFORT_FORT_MERCHANT_IDENTIFIER';
        $configuration_access_code         = 'PAYFORT_FORT_ACCESS_CODE';
        $configuration_request_sha_phrase  = 'PAYFORT_FORT_REQUEST_SHA_PHRASE';
        $configuration_response_sha_phrase = 'PAYFORT_FORT_RESPONSE_SHA_PHRASE';
        $configuration_sha_algorithm       = 'PAYFORT_FORT_SHA_ALGORITHM';

        $this->context->smarty->assign($configuration_merchant_identifier, Configuration::get($configuration_merchant_identifier));
        $this->context->smarty->assign($configuration_access_code, Configuration::get($configuration_access_code));
        $this->context->smarty->assign($configuration_request_sha_phrase, Configuration::get($configuration_request_sha_phrase));
        $this->context->smarty->assign($configuration_response_sha_phrase, Configuration::get($configuration_response_sha_phrase));
        $this->context->smarty->assign($configuration_sha_algorithm, Configuration::get($configuration_sha_algorithm));
        $this->context->smarty->assign('host_to_host_url', $this->_getUrl('fc=module&module=payfortfort&controller=payment&action=processPaymentResponse'));
        return $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/admin/configuration.tpl');
    }

    public function hookPayment($params)
    {
        $currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);
        $isFailed = Tools::getValue('payfortforterror');

        $url              = $this->_getUrl('fc=module&module=payfortfort&controller=payment&action=postPaymentForm');
        $SADAD            = Configuration::get('PAYFORT_FORT_SADAD');
        $NAPS             = Configuration::get('PAYFORT_FORT_NAPS');
        $credit_card      = Configuration::get('PAYFORT_FORT_CREDIT_CARD');
        $integration_type = Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE');

        $pfHelper = Payfort_Fort_Helper::getInstance();

        $frontCurrency = $pfHelper->getFrontCurrency();
        $baseCurrency  = $pfHelper->getBaseCurrency();
        $fortCurrency  = $pfHelper->getFortCurrency($baseCurrency, $frontCurrency);
        if ($fortCurrency != 'SAR') {
            $SADAD = 0;
        }
        if ($fortCurrency != 'QAR') {
            $NAPS = 0;
        }

        $this->context->smarty->assign('url', $url);
        $this->context->smarty->assign('SADAD', $SADAD);
        $this->context->smarty->assign('NAPS', $NAPS);
        $this->context->smarty->assign('credit_card', $credit_card);
        $this->context->smarty->assign('integration_type', $integration_type);
        $this->context->smarty->assign('payfort_path', $this->getPathUri());
        
        $arr_js_messages = 
                    array(
                        'error_invalid_card_number' => $this->l('error_invalid_card_number'),
                        'error_invalid_card_holder_name' => $this->l('error_invalid_card_holder_name'),
                        'error_invalid_expiry_date' => $this->l('error_invalid_expiry_date'),
                        'error_invalid_cvc_code' => $this->l('error_invalid_cvc_code'),
                        'error_invalid_cc_details' => $this->l('error_invalid_cc_details'),
                    );
        $js_msgs = $pfHelper->loadJsMessages($arr_js_messages);
        $this->context->smarty->assign('arr_js_messages', $js_msgs);
        return $this->display(__FILE__, 'views/templates/hook/payfortfort.tpl');
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . 'js/jquery.creditCardValidator.js');
        $this->context->controller->addJS($this->_path . 'js/payfort_fort.js');
        $this->context->controller->addJS($this->_path . 'js/checkout.js');
        $this->context->controller->addCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
        $this->context->controller->addCSS($this->_path . 'css/checkout.css');
    }

    private function _getUrl($path)
    {
        $url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?' . $path;

        $ssl = Configuration::get('PS_SSL_ENABLED');
        if ($ssl) {
            $url = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . 'index.php?' . $path;
        }

        return $url;
    }

}
