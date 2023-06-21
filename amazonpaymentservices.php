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

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = dirname(__FILE__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

require_once dirname(__FILE__).'/lib/init.php';
class Amazonpaymentservices extends PaymentModule
{
    protected $config_form = false;
    protected $aps_admin_config = '';
    protected $aps_config = '';

    public function __construct()
    {
        $this->name = 'amazonpaymentservices';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.0';
        $this->author = 'Amazon Payment Services';
        $this->need_instance = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        $this->display = 'view';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7.9.99');

        parent::__construct();

        $this->displayName = $this->l('Amazon Payment Services');
        $this->description = $this->l('Amazon Payment Services payment module offers seamless payments for PrestaShop platform merchants. If you don’t have an APS account sign up here https://paymentservices.amazon.com/ for Amazon Payment Services account. We know that payment processing is critical to your business. With this module we aim to increase your payment processing capabilities. Do you have a business-critical questions? View our quick reference documentation https://paymentservices.amazon.com/docs/EN/index.html for key insights covering payment acceptance, integration, and reporting.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Amazon Payment Services module?');
        $this->aps_admin_config = new ApsAdminConfig();
    }

    public function install()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            // Incompatible version of PrestaShop.
            return false;
        }

        if (!parent::install()) {
            return false;
        }

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        include(dirname(__FILE__).'/sql/install.php');

        if (!$this->installOrderState()) {
            return false;
        }

        return $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerPaymentHooks() &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayShoppingCart')&&
            $this->registerHook('displayProductButtons')&&
            $this->registerHook('displayCustomerAccount')&&
            $this->registerHook('displayAdminOrderTabOrder')&&
            $this->registerHook('displayAdminOrderContentOrder')&&
            $this->registerHook('displayAdminOrderTabLink')&&
            $this->registerHook('displayAdminOrderTabContent')&&
            $this->installDefaultValue()&&
            $this->installTab();
    }

    public function registerPaymentHooks()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (! $this->registerHook('payment') || ! $this->registerHook('displayPaymentEU')) {
                $this->aps_helper->log('Hook « displayPaymentEU » could not be saved.');
                return false;
            }
        } else {
            if (! $this->registerHook('paymentOptions')) {
                $this->aps_helper->log('Hook « paymentOptions » could not be saved.');
                return false;
            }
        }
        return true;
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = 'AdminAmazonpaymentservices';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Amazonpaymentservices';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;

        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminAmazonpaymentservices');
        if ($id_tab) {
            $tab = new Tab($id_tab);

            return $tab->delete();
        }
        return false;
    }

    public function uninstall()
    {
        //delete module configuration
        ApsAdminConfig::deleteConfig();

        include(dirname(__FILE__).'/sql/uninstall.php');
        if (!$this->uninstallTab()) {
            return false;
        }
        return parent::uninstall();
    }

    public function installDefaultValue()
    {
        Configuration::updateValue(
            'AMAZONPAYMENTSERVICES_HOST_TO_HOST_URL',
            $this->context->link->getModuleLink(
                'amazonpaymentservices',
                'validation',
                array('action' => 'offline_response'),
                Configuration::get('PS_SSL_ENABLED')
            )
        );
        Configuration::updateValue('AMAZONPAYMENTSERVICES_INSTALLMENTS_SAR_ORDER_MIN_VALUE', 1000);
        Configuration::updateValue('AMAZONPAYMENTSERVICES_INSTALLMENTS_AED_ORDER_MIN_VALUE', 1000);
        Configuration::updateValue('AMAZONPAYMENTSERVICES_INSTALLMENTS_EGP_ORDER_MIN_VALUE', 1000);
        Configuration::updateValue('AMAZONPAYMENTSERVICES_VALU_ORDER_MIN_VALUE', 500);
        return true;
    }

    public function installOrderState()
    {
        /* Create Order State for APS Payment pending */
        if (!Configuration::get('APS_OS_PENDING', null)
            || !Validate::isLoadedObject(new OrderState(Configuration::get('APS_OS_PENDING', null)))) {
            $order_state = new OrderState();
            $order_state->name = array();
            $order_state->module_name = $this->name;
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = pSQL('Pending APS Payment');
            }
            $order_state->unremovable = true;
            $order_state->invoice = false;
            $order_state->send_email = false;
            $order_state->logable = false;
            $order_state->color = '#8A6D3D';
            $order_state->add();
            Configuration::updateValue('APS_OS_PENDING', $order_state->id);
        }

        if (!Configuration::get('APS_OS_ONHOLD', null)
            || !Validate::isLoadedObject(new OrderState(Configuration::get('APS_OS_ONHOLD', null)))) {
            $order_state = new OrderState();
            $order_state->name = array();
            $order_state->module_name = $this->name;
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = pSQL('On Hold APS Payment');
            }
            $order_state->unremovable = true;
            $order_state->invoice = false;
            $order_state->send_email = false;
            $order_state->logable = false;
            $order_state->color = '#9B4D3D';
            $order_state->add();
            Configuration::updateValue('APS_OS_ONHOLD', $order_state->id);
        }

        /* Create Order State for APS Payment VOIDED */
        if (!Configuration::get('APS_OS_VOIDED', null)
            || !Validate::isLoadedObject(new OrderState(Configuration::get('APS_OS_VOIDED', null)))) {
            $order_state = new OrderState();
            $order_state->name = array();
            $order_state->module_name = $this->name;
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = pSQL('Voided');
            }
            $order_state->unremovable = true;
            $order_state->invoice = false;
            $order_state->send_email = false;
            $order_state->logable = false;
            $order_state->color = '#8A6D3D';
            $order_state->add();
            Configuration::updateValue('APS_OS_VOIDED', $order_state->id);
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAmazonpaymentservicesModule')) == true) {
            $output = $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAmazonpaymentservicesModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => ApsAdminConfig::getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm($this->aps_admin_config->getAdminConfigForm());
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        return $this->aps_admin_config->updateConfigValues();
    }

    /**
    * Todo check this is required
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/back.css');
        /* $this->context->controller->addJS([
             $this->getPathUri() . 'views/js/back.js',
         ]);*/
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $controller = $this->context->controller;
        if ($controller instanceof OrderController || $controller instanceof OrderOpcController) {
            if (isset($this->context->cookie->apsErrors)) {
                // Process errors from other pages.
                $controller->errors = array_merge(
                    $controller->errors,
                    explode("\n", $this->context->cookie->apsErrors)
                );
                unset($this->context->cookie->apsErrors);

                // Unset HTTP_REFERER from global server variable to avoid back link display in error message.
                $_SERVER['HTTP_REFERER'] = null;
                $this->context->smarty->assign('server', $_SERVER);
            }
        }

        $this->addJsDefList();
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if ($this->context->controller instanceof OrderController) {
                $this->context->controller->registerJavascript(
                    $this->name.'-payments-slick',
                    'modules/'.$this->name.'/views/js/slick.js'
                );
                $this->context->controller->registerJavascript(
                    $this->name.'-payments-checkout-visa',
                    'modules/'.$this->name.'/views/js/aps-visa-checkout.js'
                );

                $this->context->controller->registerJavascript(
                    $this->name.'-payments-checkout',
                    'modules/'.$this->name.'/views/js/aps_checkout.js'
                );

                $this->context->controller->registerJavascript(
                    $this->name.'-payments-checkout-apple',
                    'modules/'.$this->name.'/views/js/aps-apple-pay.js'
                );
            }
            if ($this->context->controller instanceof CartController) {
                $this->context->controller->registerJavascript(
                    $this->name.'-payments-cart-apple',
                    'modules/'.$this->name.'/views/js/aps-apple-pay-cart.js'
                );
            }
            if ($this->context->controller instanceof ProductControllerCore) {
                $this->context->controller->registerJavascript(
                    $this->name.'-payments-product-apple',
                    'modules/'.$this->name.'/views/js/aps-apple-pay-product.js'
                );
            }

            $this->context->controller->registerStylesheet(
                $this->name.'-payments',
                'modules/'.$this->name.'/views/css/aps_checkout.css'
            );
        } else {
            if ($controller instanceof OrderController || $controller instanceof OrderOpcController) {
                $this->context->controller->addJS($this->_path.'/views/js/aps_checkout.js');
                $this->context->controller->addJS($this->_path.'/views/js/aps-visa-checkout.js');
                $this->context->controller->addJS($this->_path.'/views/js/aps-apple-pay.js');
                $this->context->controller->addJS($this->_path.'/views/js/slick.js');
            }
            if ($controller instanceof CartController || $controller instanceof OrderController) {
                $this->context->controller->addJS($this->_path.'/views/js/aps-apple-pay-cart.js');
            }
            if ($controller instanceof ProductController) {
                $this->context->controller->addJS($this->_path.'/views/js/aps-apple-pay-product.js');
            }
            $this->context->controller->addCSS($this->_path.'/views/css/aps_checkout.css');
        }
    }

    protected function addJsDefList()
    {
        $integration_type = Configuration::get('AMAZONPAYMENTSERVICES_INSTALLMENTS_INTEGRATION_TYPE');
        $is_embedded_hosted_checkout = 0;
        if ($integration_type == 'embedded_hosted_checkout') {
            $is_embedded_hosted_checkout = 1;
        }

        $integration_type = Configuration::get('AMAZONPAYMENTSERVICES_VISA_CHECKOUT_INTEGRATION_TYPE');
        $data = [];
        if ('hosted_checkout' == $integration_type) {
            $data['vc_integration_type'] = 'hosted_checkout';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $prestashop_version = '1.7';
        } else {
            $prestashop_version = '1.6';
        }
        Media::addJsDef([
            'aps_front_controller' => $this->context->link->getModuleLink(
                'amazonpaymentservices',
                'validation',
                array(),
                Configuration::get('PS_SSL_ENABLED')
            ),
            'prestashop_version'      => $prestashop_version,
            'aps_visa_checkout_params' => $data,
            'aps_js_messages' => $this->apsJsErrorMessages(),
            'mada_bins'  => Configuration::get('AMAZONPAYMENTSERVICES_CC_MADA_BINS'),
            'meeza_bins' => Configuration::get('AMAZONPAYMENTSERVICES_CC_MEEZA_BINS'),
            'is_embedded_hosted_checkout' => $is_embedded_hosted_checkout
        ]);
    }

    public function hookDisplayShoppingCart($params)
    {
        // In case the extension is disabled, do nothing
        if ($this->active == false) {
            return;
        }
        $aps_config = AmazonpaymentservicesConfig::getInstance();
        // In case the apple pay is disabled, do nothing
        if (!$aps_config->isApplePayActive()) {
            return;
        }
        // In case the button not enable on cart page, do nothing
        if (!$aps_config->isEnabledApplePayCartPage()) {
            return;
        }

        // In case the guest checkout is disable and customer not logged in, do noting
        if ( (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) && (! Context::getContext()->customer->isLogged()) ) {
            return;
        }

        $apple_var = $this->setAppleVariables(ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY, 'redirection');
        $apple_var['btn_page'] = 'cart';
        $this->smarty->assign($apple_var);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_apple_pay_btn.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_apple_pay_btn.tpl');
        }
    }

    /**
     * Add a tab to controle intents on an order details admin page (tab header)
     * @return html
     */
    public function hookDisplayAdminOrderTabOrder($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7.4', '>=')) {
            $order = new Order($params['id_order']);
        } else {
            $order = $params['order'];
        }

        if ($order->module != 'amazonpaymentservices') {
            return;
        }

        return $this->display(__FILE__, 'views/templates/hook/admin_tab_order.tpl');
    }

    public function hookDisplayAdminOrderTabLink($params)
    {
        return $this->hookDisplayAdminOrderTabOrder($params);
    }

    /**
     * Add a tab to controle intents on an order details admin page (tab content)
     * @return html
     */
    public function hookDisplayAdminOrderContentOrder($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7.4', '>=')) {
            $order = new Order($params['id_order']);
        } else {
            $order = $params['order'];
        }

        $order_data = ApsOrder::getOrderData($order, $order->id);
        if (empty($order_data) && empty($order_data['display_data'])) {
            return;
        }
        $admin_amazonpaymentservices_ajax_url =  $this->context->link->getAdminLink('AdminAmazonpaymentservices', true, [], ['ajax' => 1]);

        $enable_extension = 1;
        // In case the extension is disabled, do not refund capture void
        if ($this->active == false || Configuration::get('AMAZONPAYMENTSERVICES_STATUS', null) == 0) {
            $enable_extension = 0;
        }

        $this->context->smarty->assign(array(
            'id_order' => $order->id,
            'order_data'   =>  $order_data,
            'enable_extension' => $enable_extension,
            'admin_amazonpaymentservices_ajax_url' => $admin_amazonpaymentservices_ajax_url
        ));

        return $this->display(__FILE__, 'views/templates/hook/admin_content_order.tpl');
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        return $this->hookDisplayAdminOrderContentOrder($params);
    }

    public function hookDisplayProductButtons($params)
    {
        // In case the extension is disabled, do nothing
        if ($this->active == false) {
            return;
        }

        // In case the apple pay is disabled, do nothing
        if (!Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_STATUS')) {
            return;
        }

        // In case the guest checkout is disable and customer not logged in, do noting
        if ( (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) && (! Context::getContext()->customer->isLogged()) ) {
            return;
        }

        // In case the button not enable on product page, do nothing
        if (!Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_PRODUCT_PAGE')) {
            return;
        }

        $apple_var = $this->setAppleVariables(ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY, 'redirection');
        $apple_var['btn_page'] = 'product';
        $this->smarty->assign($apple_var);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_apple_pay_btn.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_apple_pay_btn.tpl');
        }
    }

    public function hookDisplayCustomerAccount()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $prestashop_version = '1.7';
        } else {
            $prestashop_version = '1.6';
        }

        $this->context->smarty->assign(array(
            'prestashop_version' => $prestashop_version
        ));

        return $this->display(__FILE__, 'my-account-aps-cards.tpl');
    }

    /**
     * This method is used to render the payment button for PS1.5,1.6,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        return $this->hookPaymentOptions($params);
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        //$order = $params['order'];
        $order = isset($params['order']) ? $params['order'] : $params['objOrder'];

        $payment_method = ApsOrder::getApsMetaValue($order->id, 'payment_method');

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $prestashop_version = '1.7';
        } else {
            $prestashop_version = '1.6';
        }

        $payment_methods = [
            ApsConstant::APS_PAYMENT_METHOD_KNET,
            ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS,
            ApsConstant::APS_PAYMENT_METHOD_VALU,
            ApsConstant::APS_PAYMENT_METHOD_CC
        ];
        //No extra info to display
        if ($prestashop_version == '1.7' && ! in_array($payment_method, $payment_methods)) {
            return;
        }

        if (ApsConstant::APS_PAYMENT_METHOD_CC == $payment_method) {
            // No extra info if cc order not with installment
            $embedded_order = ApsOrder::getApsMetaValue($order->id, 'embedded_hosted_order');
            if ($prestashop_version == '1.7' && $embedded_order != 1) {
                return;
            }
        }
        
        $payment_data = ApsOrder::getPaymentDisplayData($payment_method, $order->id);

        if ($prestashop_version == '1.7' && empty($payment_data)) {
            return;
        }

        $this->smarty->assign(array(
            'payment_method'    => $payment_method,
            'title' => isset($payment_data['title']) ? $payment_data['title'] : null,
            'display_data' => isset($payment_data['display_data']) ? $payment_data['display_data'] : null,
            'prestashop_version' => $prestashop_version
        ));
        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array|null
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        $aps_config = AmazonpaymentservicesConfig::getInstance();

        $credit_card    = $aps_config->getCcStatus();
        $installments   = $aps_config->getInstallmentsStatus();
        $visa_checkout  = $aps_config->getVisaCheckoutStatus();
        $NAPS           = $aps_config->getNapsStatus();
        $KNET           = $aps_config->getKnetStatus();
        $valu           = $aps_config->getValuStatus();
        $apple_pay      = $aps_config->getApplePayStatus();

        $aps_helper     = AmazonpaymentservicesHelper::getInstance();
        $cartTotal      = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        $options = array();

        if ($credit_card) {
            $options[] = array( 'method' => $this->getCreditCardPaymentOption(), 'sort_order' => $aps_config->getCcSortOrder());
        }
        if ($installments) {
            if ($aps_helper->checkOrderEligibleForInstallments($cartTotal)) {
                $options[] = array( 'method' => $this->getInstallmentsPaymentOption(), 'sort_order' => $aps_config->getInstallmentsSortOrder());
            }
        }
        if ($visa_checkout) {
            $options[] = array( 'method' => $this->getVisaCheckoutPaymentOption(), 'sort_order' => $aps_config->getVisaCheckoutSortOrder());
        }
        if ($NAPS) {
            if ($aps_helper->checkOrderEligibleForNaps()) {
                $options[] = array( 'method' => $this->getNapsPaymentOption(), 'sort_order' => $aps_config->getNapsSortOrder());
            }
        }
        if ($KNET) {
            if ($aps_helper->checkOrderEligibleForKnet()) {
                $options[] = array( 'method' => $this->getKnetPaymentOption(), 'sort_order' => $aps_config->getKnetSortOrder());
            }
        }
        if ($valu) {
            if ($aps_helper->checkOrderEligibleForValu($cartTotal)) {
                $options[] = array( 'method' => $this->getValuPaymentOption(), 'sort_order' => $aps_config->getValuSortOrder());
            }
        }
        if ($apple_pay) {
            $options[] = array( 'method' => $this->getApplePayPaymentOption(), 'sort_order' => $aps_config->getApplePaySortOrder());
        }

        $sort_order = array_column($options, 'sort_order');
        array_multisort($sort_order, SORT_ASC, $options);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return array_column($options, 'method');
        } else {
            $aps_methods = array_column($options, 'method');
            if (isset($aps_methods)) {
                return implode(',', $aps_methods);
            } else {
                return '';
            }
        }
    }

    public function getCreditCardPaymentOption()
    {
        $credit_card_html = '';
        $title = $this->l('Credit / Debit card');
        $aps_config = AmazonpaymentservicesConfig::getInstance();

        if ($aps_config->isMadaBranding()) {
            $title = $this->l('mada debit card / Credit Cards');
        }
        $logo = 'cc_';
        if ($aps_config->isMadaBranding() && $aps_config->isMeezaBranding()) {
            $logo = 'cc_with_brand_';
        } elseif ($aps_config->isMadaBranding()) {
            $logo = 'cc_with_mada_';
        } elseif ($aps_config->isMeezaBranding()) {
            $logo = 'cc_with_meeza_';
        }
        $language = $aps_config->getLanguage();
        $logo .= $language.'.png';
        $logo_path = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/' .$logo);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $credit_card_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $credit_card_option->setCallToActionText($title);
            $credit_card_option->setLogo($logo_path);
        }
        switch ($aps_config->getCcIntegrationType()) {
            case 'redirection':
                $credit_card_html = $this->apsRedirectionForm(ApsConstant::APS_PAYMENT_METHOD_CC, 'redirection', $title, $logo_path);
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $credit_card_option->setAdditionalInformation(
                        $this->context->smarty->fetch(
                            'module:'.$this->name.'/views/templates/hook/payment_info.tpl'
                        )
                    );

                    $credit_card_option->setForm($credit_card_html);
                }
                break;
            case 'standard_checkout':
                $credit_card_html = $this->apsStandardIframeForm(ApsConstant::APS_PAYMENT_METHOD_CC, 'standard_checkout', $title, $logo_path);
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $credit_card_option->setForm($credit_card_html);
                }
                break;
            case 'hosted_checkout':
                $credit_card_html = $this->apsHostedForm(ApsConstant::APS_PAYMENT_METHOD_CC, 'hosted_checkout', $title, $logo_path);
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $credit_card_option->setForm($credit_card_html);
                }
                break;
        }
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $credit_card_option;
        } else {
            return $credit_card_html;
        }
    }

    public function getInstallmentsPaymentOption()
    {
        $installment_html = '';
        $title = $this->l('Installments');
        $aps_config = AmazonpaymentservicesConfig::getInstance();
        $language = $aps_config->getLanguage();
        $logo = 'installment_'.$language.'.png';
        $logo_path = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $installment_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $installment_option->setCallToActionText($title);
            $installment_option->setLogo($logo_path);
        }
        switch ($aps_config->getInstallmentsIntegrationType()) {
            case 'redirection':
                $installment_html = $this->apsRedirectionForm(
                    ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS,
                    'redirection',
                    $title,
                    $logo_path
                );
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $installment_option->setAdditionalInformation(
                        $this->context->smarty->fetch(
                            'module:'.$this->name.'/views/templates/hook/payment_info.tpl'
                        )
                    );
                    $installment_option->setForm($installment_html);
                }
                break;
            case 'standard_checkout':
                $installment_html = $this->apsStandardIframeForm(
                    ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS,
                    'standard_checkout',
                    $title,
                    $logo_path
                );
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $installment_option->setForm($installment_html);
                }
                break;
            case 'hosted_checkout':
                $installment_html = $this->apsHostedForm(
                    ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS,
                    'hosted_checkout',
                    $title,
                    $logo_path
                );
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $installment_option->setForm($installment_html);
                }
                break;
        }
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $installment_option;
        } else {
            return $installment_html;
        }
    }

    public function getVisaCheckoutPaymentOption()
    {
        $title = $this->l('Visa Checkout');
        $visa_checkout_html = '';
        $aps_config = AmazonpaymentservicesConfig::getInstance();

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $visa_checkout_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $visa_checkout_option->setCallToActionText($title);
        }
        switch ($aps_config->getVisaCheckoutIntegrationType()) {
            case 'redirection':
                $visa_checkout_html = $this->apsRedirectionForm(ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT, 'redirection', $title);
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $visa_checkout_option->setAdditionalInformation(
                        $this->context->smarty->fetch(
                            'module:'.$this->name.'/views/templates/hook/payment_info.tpl'
                        )
                    );
                    $visa_checkout_option->setForm($visa_checkout_html);
                }
                break;
            case 'hosted_checkout':
                $visa_checkout_html = $this->apsHostedVisaCheckoutForm(ApsConstant::APS_PAYMENT_METHOD_VISA_CHECKOUT, 'hosted_checkout', $title);
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $visa_checkout_option->setForm($visa_checkout_html);
                }
                break;
        }
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $visa_checkout_option;
        } else {
            return $visa_checkout_html;
        }
    }

    public function getValuPaymentOption()
    {
        
        $title     = $this->l('Buy Now, Pay Monthly');
        $logo_path = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/valu-logo.png');
        $aps_config = AmazonpaymentservicesConfig::getInstance();
        $allow_downpayment = $aps_config->isValuDownpaymentEnabled();
        $downpayment_value = $aps_config->getValuDownpaymentvalue();
        $valu_html = $this->apsValuForm(ApsConstant::APS_PAYMENT_METHOD_VALU, 'redirection', $title, $logo_path, $allow_downpayment, $downpayment_value);
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $valu_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $valu_option->setCallToActionText($title);
            $valu_option->setLogo($logo_path);
            $valu_option->setForm($valu_html);
            return $valu_option;
        }
        return $valu_html;
    }

    public function getKNETPaymentOption()
    {
        $title = $this->l('KNET');
        $knet_html = $this->apsRedirectionForm(ApsConstant::APS_PAYMENT_METHOD_KNET, 'redirection', $title);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $knet_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $knet_option->setCallToActionText($title);
            $knet_option->setAdditionalInformation(
                $this->context->smarty->fetch(
                    'module:'.$this->name.'/views/templates/hook/payment_info.tpl'
                )
            );
            $knet_option->setForm($knet_html);
            return $knet_option;
        } else {
            return $knet_html;
        }
    }

    public function getNAPSPaymentOption()
    {
        $title = $this->l('NAPS');
        $naps_html = $this->apsRedirectionForm(ApsConstant::APS_PAYMENT_METHOD_NAPS, 'redirection', $title);
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $naps_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            $naps_option->setCallToActionText($title);
            $naps_option->setAdditionalInformation(
                $this->context->smarty->fetch(
                    'module:'.$this->name.'/views/templates/hook/payment_info.tpl'
                )
            );
            $naps_option->setForm($naps_html);

            return $naps_option;
        } else {
            return $naps_html;
        }
    }

    public function getApplePayPaymentOption()
    {
        $title = $this->l('Apple Pay');
        $apple_pay_html = $this->apsApplePayForm(ApsConstant::APS_PAYMENT_METHOD_APPLE_PAY, 'redirection', $title);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $apple_pay_option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $apple_pay_option->setCallToActionText($title);
            $apple_pay_option->setForm($apple_pay_html);

            return $apple_pay_option;
        }
        return $apple_pay_html;
    }

    protected function apsRedirectionForm($payment_method, $integration_type = '', $payment_title = '', $logo_path = null)
    {
        $aps_tokens = [];
        $aps_helper = AmazonpaymentservicesHelper::getInstance();
        if (ApsConstant::APS_PAYMENT_METHOD_CC == $payment_method) {
            $aps_tokens = $aps_helper->getTokensData($payment_method);
        }
        $this->smarty->assign([
            'payment_method'   => $payment_method,
            'payment_title'    => $payment_title,
            'integration_type' => $integration_type,
            'module_dir' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name),
            'aps_tokens'       => $aps_tokens,
            'logo_path'        => $logo_path,
            'url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array('action'=>'postApsPayment'),
                true
            ),
        ]);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_redirect.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_redirect.tpl');
        }
    }

    protected function apsStandardIframeForm($payment_method, $integration_type, $payment_title = '', $logo_path = null)
    {
        $aps_tokens = [];
        $aps_helper = AmazonpaymentservicesHelper::getInstance();
        if (ApsConstant::APS_PAYMENT_METHOD_CC == $payment_method) {
            $aps_tokens = $aps_helper->getTokensData($payment_method);
        }
        $this->smarty->assign([
            'payment_title'    => $payment_title,
            'payment_method'   => $payment_method,
            'integration_type' => $integration_type,
            'module_dir' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name),
            'aps_tokens'       => $aps_tokens,
            'logo_path'        => $logo_path,
            'url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array('action'=>'postApsPayment'),
                true
            ),
        ]);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_standard.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_standard.tpl');
        }
    }

    protected function apsValuForm($payment_method, $integration_type, $payment_title = '', $logo_path = null, $allow_downpayment, $downpayment_value = 0)
    {
        $this->smarty->assign([
            'payment_title'    => $payment_title,
            'payment_method'   => $payment_method,
            'integration_type' => $integration_type,
            'country_code'     => ApsConstant::APS_VALU_EG_COUNTRY_CODE,
            'module_dir'       => Media::getMediaPath(_PS_MODULE_DIR_.$this->name),
            'logo_path'        => $logo_path,
            'url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array('action'=>'postApsPayment'),
                true
            ),
            'term_url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array(),
                true
            ),
            'allow_downpayment' => $allow_downpayment,
            'downpayment_value' => $downpayment_value
        ]);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_valu.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_valu.tpl');
        }
    }

    protected function apsHostedVisaCheckoutForm($payment_method, $integration_type)
    {
        $aps_config = AmazonpaymentservicesConfig::getInstance();
        $aps_helper = AmazonpaymentservicesHelper::getInstance();

        $total    = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $currency = $aps_helper->getGatewayCurrencyCode();
        $con_rate = $aps_helper->getConversionRate($currency);
        $amount   = $aps_helper->convertGatewayAmount($total, $con_rate, $currency);

        $id_country  = Context::getContext()->shop->getAddress()->id_country;
        $country_iso = Country::getIsoById($id_country);
        $country_iso = (isset($country_iso) ? $country_iso : 'US');

        $this->smarty->assign([
            'payment_method'   => $payment_method,
            'integration_type' => $integration_type,
            'module_dir' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name),
            'visa_checkout_button_url' => $aps_config->getVisaCheckoutButton(),
            'hosted_visa_checkout_sdk_url' => $aps_config->getVisaCheckoutJS(),
            'language' => $aps_config->getLanguage(),
            'amount' => $amount,
            'currency' => $currency,
            'merchant_message' => Context::getContext()->shop->name,
            'api_key' => $aps_config->getVisaCheckoutApiKey(),
            'profile_name' => $aps_config->getVisaCheckoutProfileName(),
            'country_code' => $country_iso,
            'url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array('action'=>'postApsPayment'),
                true
            ),
        ]);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_hosted_visa_checkout.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_hosted_visa_checkout.tpl');
        }
    }

    protected function apsHostedForm($payment_method, $integration_type, $payment_title = '', $logo_path = null)
    {
        $aps_config = AmazonpaymentservicesConfig::getInstance();
        $aps_helper = AmazonpaymentservicesHelper::getInstance();

        $this->smarty->assign([
            'payment_title'    => $payment_title,
            'payment_method'   => $payment_method,
            'integration_type' => $integration_type,
            'module_dir' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name),
            'is_mada_branding' => $aps_config->isMadaBranding(),
            'is_meeza_branding' => $aps_config->isMeezaBranding(),
            'aps_tokens' => $aps_helper->getTokensData($payment_method),
            'logo_path'        => $logo_path,
            'url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array('action'=>'postApsPayment'),
                true
            ),
        ]);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_hosted.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_hosted.tpl');
        }
    }

    public function getApsGatewayCurrencyCode($base_currency_code = null, $current_currency_code = null)
    {
        $gateway_currency = Configuration::get('AMAZONPAYMENTSERVICES_GATEWAY_CURRENCY');

        if ($base_currency_code == null) {
            $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $base_currency_code = $currency->iso_code;
        }

        if ($current_currency_code == null) {
            $currency = $this->context->currency;
            $current_currency_code =  $currency->iso_code;
        }

        $currency_code    = $base_currency_code;
        if ($gateway_currency == 'front') {
            $currency_code = $current_currency_code;
        }
        return $currency_code;
    }

    public function setAppleVariables($payment_method, $integration_type, $payment_title = '')
    {
        $certificate_path              = _PS_UPLOAD_DIR_ . 'aps_certificate/' . Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_CRT_FILE', null);
        $apple_pay_merchant_identifier = openssl_x509_parse(Tools::file_get_contents($certificate_path))['subject']['UID'];


        $currency = $this->getApsGatewayCurrencyCode();

        $id_country  = Context::getContext()->shop->getAddress()->id_country;
        $country_iso = Country::getIsoById($id_country);
        $country_iso = (isset($country_iso) ? $country_iso : 'US');

        $button_type = Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_BTN_TYPE');
        $data = [
            'payment_title'    => $payment_title,
            'payment_method'   => $payment_method,
            'integration_type' => $integration_type,
            'button_type'      => $button_type,
            'module_dir' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name),
            'merchant_identifier' => $apple_pay_merchant_identifier,
            'country_code'        => $country_iso,
            'currency_code'       => Tools::strtoupper($currency),
            'display_name'        => Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_DISPLAY_NAME'),
            'supported_networks'  => Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_SUPPORTED_NETWORK'),
            'url' => $this->context->link->getModuleLink(
                $this->name,
                'validation',
                array('action'=>'postApsPayment'),
                true
            ),
        ];
        return $data;
    }

    protected function apsApplePayForm($payment_method, $integration_type, $payment_title)
    {
        $apple_var = $this->setAppleVariables($payment_method, $integration_type, $payment_title);
        $this->smarty->assign($apple_var);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->fetch('module:amazonpaymentservices/views/templates/hook/payment_form_apple_pay.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/bc/payment_form_apple_pay.tpl');
        }
    }

    public function apsJsErrorMessages()
    {
        $arr_js_messages = array(
            'invalid_card_length'      => $this->l('Invalid card length'),
            'card_empty'               => $this->l('Card number cannot be empty'),
            'invalid_card'             => $this->l('Card number is invalid'),
            'invalid_card_holder_name' => $this->l('Card holder name is invalid'),
            'invalid_card_cvv'         => $this->l('Card CVV is invalid'),
            'invalid_expiry_month'     => $this->l('Expiry month is invalid'),
            'invalid_expiry_year'      => $this->l('Expiry year is invalid'),
            'invalid_expiry_date'      => $this->l('Expiry date is invalid'),
            'required_field'           => $this->l('This is a required field'),
            'valu_pending_msg'         => $this->l('Please complete the evaluation process.'),
            'valu_select_plan'         => $this->l('Please select valU plan'),
            'valu_terms_msg'           => $this->l('Please accept the terms and conditions'),
            'valu_invalid_mobile'      => $this->l('Mobile number is invalid'),
        );
        return $arr_js_messages;
    }
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
}
