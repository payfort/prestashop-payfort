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

class ApsAdminConfig extends Module
{
    /**
     * Array of keys contain origin Db keys
     *
     * @var array config fields
     */
    public static $aps_config_fields = array(
        'aps_status' => 'AMAZONPAYMENTSERVICES_STATUS',
        'merchant_identifier' => 'AMAZONPAYMENTSERVICES_MERCHANT_IDENTIFIER',
        'access_code' => 'AMAZONPAYMENTSERVICES_ACCESS_CODE',
        'request_sha_phrase' => 'AMAZONPAYMENTSERVICES_REQUEST_SHA_PHRASE',
        'response_sha_phrase' => 'AMAZONPAYMENTSERVICES_RESPONSE_SHA_PHRASE',
        'sandbox_mode' => 'AMAZONPAYMENTSERVICES_SANDBOX_MODE',
        'command' => 'AMAZONPAYMENTSERVICES_COMMAND',
        'sha_type' => 'AMAZONPAYMENTSERVICES_SHA_TYPE',
        'gateway_currency' => 'AMAZONPAYMENTSERVICES_GATEWAY_CURRENCY',
        'debug' => 'AMAZONPAYMENTSERVICES_DEBUG',
        'order_status_id' => 'AMAZONPAYMENTSERVICES_ORDER_STATUS_ID',
        'host_to_host_url' => 'AMAZONPAYMENTSERVICES_HOST_TO_HOST_URL',
        'cc_status' => 'AMAZONPAYMENTSERVICES_CC_STATUS',
        'cc_integration_type' => 'AMAZONPAYMENTSERVICES_CC_INTEGRATION_TYPE',
        'cc_show_mada_branding' => 'AMAZONPAYMENTSERVICES_CC_SHOW_MADA_BRANDING',
        'cc_show_meeza_branding' => 'AMAZONPAYMENTSERVICES_CC_SHOW_MEEZA_BRANDING',
        'cc_mada_bins' => 'AMAZONPAYMENTSERVICES_CC_MADA_BINS',
        'cc_meeza_bins' => 'AMAZONPAYMENTSERVICES_CC_MEEZA_BINS',
        'tokenization' => 'AMAZONPAYMENTSERVICES_TOKENIZATION',
        'hide_delete_token' => 'AMAZONPAYMENTSERVICES_HIDE_DELETE_TOKEN',
        'cc_sort_order' => 'AMAZONPAYMENTSERVICES_CC_SORT_ORDER',
        'visa_checkout_status' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_STATUS',
        'visa_checkout_integration_type' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_INTEGRATION_TYPE',
        'visa_checkout_api_key' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_API_KEY',
        'visa_checkout_profile_name' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_PROFILE_NAME',
        'visa_checkout_sort_order' => 'AMAZONPAYMENTSERVICES_VISA_CHECKOUT_SORT_ORDER',
        'installments_status' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_STATUS',
        'installments_integration_type' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_INTEGRATION_TYPE',
        'installments_sar_order_min_value' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_SAR_ORDER_MIN_VALUE',
        'installments_aed_order_min_value' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_AED_ORDER_MIN_VALUE',
        'installments_egp_order_min_value' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_EGP_ORDER_MIN_VALUE',
        'installments_issuer_name' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_ISSUER_NAME',
        'installments_issuer_logo' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_ISSUER_LOGO',
        'installments_sort_order' => 'AMAZONPAYMENTSERVICES_INSTALLMENTS_SORT_ORDER',
        'naps_status' => 'AMAZONPAYMENTSERVICES_NAPS_STATUS',
        'naps_sort_order' => 'AMAZONPAYMENTSERVICES_NAPS_SORT_ORDER',
        'knet_status' => 'AMAZONPAYMENTSERVICES_KNET_STATUS',
        'knet_sort_order' => 'AMAZONPAYMENTSERVICES_KNET_SORT_ORDER',
        'valu_status' => 'AMAZONPAYMENTSERVICES_VALU_STATUS',
        'valu_allow_downpayment' => 'AMAZONPAYMENTSERVICES_VALU_ALLOW_DOWNPAYMENT',
        'valu_downpayment_value' => 'AMAZONPAYMENTSERVICES_VALU_DOWNPAYMENT_VALUE',
        'valu_order_min_value' => 'AMAZONPAYMENTSERVICES_VALU_ORDER_MIN_VALUE',
        'valu_sort_order' => 'AMAZONPAYMENTSERVICES_VALU_SORT_ORDER',
        'apple_pay_status' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_STATUS',
        'apple_pay_sha_type' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_SHA_TYPE',
        'apple_pay_btn_type' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_BTN_TYPE',
        'apple_pay_access_code' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_ACCESS_CODE',
        'apple_pay_request_sha_phrase' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_REQUEST_SHA_PHRASE',
        'apple_pay_response_sha_phrase' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_RESPONSE_SHA_PHRASE',
        'apple_pay_domain_name' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_DOMAIN_NAME',
        'apple_pay_display_name' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_DISPLAY_NAME',
        'apple_pay_supported_network' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_SUPPORTED_NETWORK',
        'apple_pay_production_key' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_PRODUCTION_KEY',
        'apple_pay_sort_order' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_SORT_ORDER',
        'apple_pay_product_page' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_PRODUCT_PAGE',
        'apple_pay_cart_page' => 'AMAZONPAYMENTSERVICES_APPLE_PAY_CART_PAGE',
        'check_status_cron_url' => 'AMAZONPAYMENTSERVICES_CHECK_STATUS_CRON_URL',
        'check_status_cron_duration' => 'AMAZONPAYMENTSERVICES_CHECK_STATUS_CRON_DURATION'
    );

    public $error_output;

    public function __construct()
    {
        $this->error_output = '';
    }

    /**
     * get config values for the inputs.
     */
    public static function getConfigFormValues()
    {
        $configValues = [];
        $aps_config_fields = AmazonpaymentservicesConfig::getConfigKeys();
        foreach ($aps_config_fields as $key => $field) {
            if ('apple_pay_supported_network' == $key) {
                $supported_network = explode(',', Configuration::get($field, null));
                $configValues['apple_pay_supported_network[]'] = $supported_network;
            } else {
                $configValues[$key] = Configuration::get($field, null);
            }
            if ('cc_mada_bins' == $key && empty($configValues[$key])) {
                $configValues[$key] = ApsConstant::MADA_BINS;
            }

            if ('cc_meeza_bins' == $key && empty($configValues[$key])) {
                $configValues[$key] = ApsConstant::MEEZA_BINS;
            }

            if ('host_to_host_url' == $key && empty($configValues[$key])) {
                $configValues[$key] = Context::getContext()->link->getModuleLink(
                    'amazonpaymentservices',
                    'validation',
                    array('action' => 'offline_response'),
                    Configuration::get('PS_SSL_ENABLED'),
                    null
                );
            }
            if ('check_status_cron_url' == $key && empty($configValues[$key])) {
                $id_shop = '';
                if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    $id_shop = '&id_shop='.(int) Context::getContext()->shop->id;
                }

                $configValues[$key] = Context::getContext()->shop->getBaseURL(true). 'module/amazonpaymentservices/cron?action=check_status&token=' . Tools::substr(Tools::encrypt('amazonpaymentservices/cron'), 0, 10) . $id_shop;
            }
        }
        return $configValues;
    }

    /**
     * delete config when uninstall.
     */
    public static function deleteConfig()
    {
        foreach (self::$aps_config_fields as $key => $field) {
            Configuration::deleteByName($field);
        }
    }

    /**
     * get admin configuration keys
     * @return array
     */
    public static function getConfigKeys()
    {
        return AmazonpaymentservicesConfig::getConfigKeys();
        return self::$aps_config_fields;
    }

    /**
     * Update admin configuration
     *
     * @return void
     */
    public function updateConfigValues()
    {
        $this->validateConfigValues();

        if ('' == $this->error_output) {
            $configkeys = self::getConfigKeys();
            foreach ($configkeys as $key => $dbKey) {
                if ($key == 'apple_pay_supported_network') {
                    $form_fields = Tools::getAllValues();
                    $supported_network = '';
                    if (isset($form_fields['apple_pay_supported_network'])) {
                        $supported_network = (string)implode(',', $form_fields['apple_pay_supported_network']);
                    }
                    Configuration::updateValue($dbKey, $supported_network);
                } else {
                    Configuration::updateValue($dbKey, Tools::getValue($key));
                }
            }

            /*
                upload certiticate
            */
            if (!file_exists(_PS_UPLOAD_DIR_ . 'aps_certificate')) {
                // Apparently sometimes mkdir cannot set the rights, and sometimes chmod can't. Trying both.
                $success = @mkdir(_PS_UPLOAD_DIR_ . 'aps_certificate', 0775, true);
                $chmod = @chmod(_PS_UPLOAD_DIR_ . 'aps_certificate', 0775);

                // Create an index.php file in the new folder
                if (($success || $chmod)
                    && !file_exists(_PS_UPLOAD_DIR_ . 'aps_certificate/' . 'index.php')
                    && file_exists(_PS_UPLOAD_DIR_ . 'index.php')) {
                    copy(_PS_UPLOAD_DIR_ . 'index.php', _PS_UPLOAD_DIR_ . 'aps_certificate/' . 'index.php');
                }
            }

            if (isset($_FILES['apple_pay_certificate_crt_file'])) {
                if (is_uploaded_file($_FILES['apple_pay_certificate_crt_file']['tmp_name'])) {
                    copy($_FILES['apple_pay_certificate_crt_file']['tmp_name'], _PS_UPLOAD_DIR_ . 'aps_certificate/identifier.crt.pem');
                     @unlink($_FILES['apple_pay_certificate_crt_file']['tmp_name']);
                     Configuration::updateValue('AMAZONPAYMENTSERVICES_APPLE_PAY_CRT_FILE', 'identifier.crt.pem');
                }
            }

            if (isset($_FILES['apple_pay_certificate_key_file'])) {
                if (is_uploaded_file($_FILES['apple_pay_certificate_key_file']['tmp_name'])) {
                    copy($_FILES['apple_pay_certificate_key_file']['tmp_name'], _PS_UPLOAD_DIR_ . 'aps_certificate/identifier.key.pem');
                     @unlink($_FILES['apple_pay_certificate_key_file']['tmp_name']);
                    Configuration::updateValue('AMAZONPAYMENTSERVICES_APPLE_PAY_KEY_FILE', 'identifier.key.pem');
                }
            }

            /**/
            $output = $this->displayConfirmation($this->l('Settings updated'));
            return $output;
        } else {
            return $this->error_output;
        }
    }

    /**
     * Validate admin configuration filed
     *
     * @return void
     */
    public function validateConfigValues()
    {
        $this->error_output = '';
        // retrieve the value set by the user
        $configValue = (string) Tools::getValue('merchant_identifier');
        // check that the value is valid
        if (empty($configValue)) {
            $this->error_output .= $this->displayError($this->l('Merchant identifier is missing.'));
        }

        $configValue = (string) Tools::getValue('access_code');
        // check that the value is valid
        if (empty($configValue)) {
            $this->error_output .= $this->displayError($this->l('Access code is missing.'));
        }

        $configValue = (string) Tools::getValue('request_sha_phrase');
        // check that the value is valid
        if (empty($configValue)) {
            $this->error_output .= $this->displayError($this->l('Request SHA phrase is missing.'));
        }

        $configValue = (string) Tools::getValue('response_sha_phrase');
        // check that the value is valid
        if (empty($configValue)) {
            $this->error_output .= $this->displayError($this->l('Response SHA phrase is missing.'));
        }
    }

    /**
     * Admin Config Form
     *
     * @return void
     */
    public function getAdminConfigForm()
    {
        $adminConfig[] =  $this->getAdminMerchantConfigForms();
        $adminConfig[] =  $this->getAdminGlobalConfigForms();
        $adminConfig[] =  $this->getAdminCreditDebitConfigForms();
        $adminConfig[] =  $this->getAdminInstallmentsConfigForms();
        $adminConfig[] =  $this->getAdminVisaCheckoutConfigForms();
        $adminConfig[] =  $this->getAdminNAPSConfigForms();
        $adminConfig[] =  $this->getAdminKNETConfigForms();
        $adminConfig[] =  $this->getAdminValuConfigForms();
        $adminConfig[] =  $this->getAdminApplePayConfigForms();
        return $adminConfig;
    }

    /**
     * Get payment method merchant configuration.
     *
     * @return mixed
     */
    protected function getAdminMerchantConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                //'title' => $this->l('Amazon Payment Services Merchant Configuration'),
                'title' => $this->l('Amazon Payment Services Merchant Configuration'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'aps_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'name' => 'merchant_identifier',
                        'required' => true,
                        'label' => $this->l('Merchant Identifier'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'name' => 'access_code',
                        'required' => true,
                        'label' => $this->l('Access Code'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'name' => 'request_sha_phrase',
                        'required' => true,
                        'label' => $this->l('Request SHA Phrase'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'name' => 'response_sha_phrase',
                        'required' => true,
                        'label' => $this->l('Response SHA Phrase'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method global configuration.
     *
     * @return mixed
     */
    protected function getAdminGlobalConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Global Configuration'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Sandbox Mode'),
                        'name' => 'sandbox_mode',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Command'),
                        'name' => 'command',
                        'options' => array(
                            'query' => array(
                                array('id' => 'PURCHASE', 'name' => $this->l('Purchase')),
                                array('id' => 'AUTHORIZATION', 'name' => $this->l('Authorization'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('SHA Type'),
                        'name' => 'sha_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'sha256', 'name' => $this->l('SHA-256')),
                                array('id' => 'sha512', 'name' => $this->l('SHA-512')),
                                array('id' => 'hmac256', 'name' => $this->l('HMAC-256')),
                                array('id' => 'hmac512', 'name' => $this->l('HMAC-512'))
                                
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Gateway Currency'),
                        'name' => 'gateway_currency',
                        'desc' => $this->l('Currency should be sent to the payment gateway.'),
                        'options' => array(
                            'query' => array(
                                array('id' => 'front', 'name' => $this->l('Front')),
                                array('id' => 'base', 'name' => $this->l('Base'))
                                
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Debug Mode'),
                        'name' => 'debug',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 8,
                        'type' => 'text',
                        'name' => 'host_to_host_url',
                        'label' => $this->l('Host to Host URL'),
                        'disabled' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable Tokenization'),
                        'name' => 'tokenization',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Hide delete token button'),
                        'name' => 'hide_delete_token',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 8,
                        'type' => 'text',
                        'name' => 'check_status_cron_url',
                        'label' => $this->l('CRON: Check Order Payment Status'),
                        'disabled' => true,
                        'desc' => $this->l('Run CRON to check payment status for order which status is pending.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('CRON: Check Status Duration'),
                        'name' => 'check_status_cron_duration',
                        'desc' => $this->l('Order place duration to check payment status for order which payment is pending. (Ex. Order Place before 15 Minutes).'),
                        'options' => array(
                            'query' => array(
                                array('id' => 15, 'name' => $this->l('15 Minutes')),
                                array('id' => 30, 'name' => $this->l('30 Minutes')),
                                array('id' => 45, 'name' => $this->l('45 Minutes')),
                                array('id' => 60, 'name' => $this->l('01 Hours')),
                                array('id' => 120, 'name' => $this->l('02 Hours'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method Credit Debit Card configuration.
     *
     * @return mixed
     */
    protected function getAdminCreditDebitConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : Credit Debit Card'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'cc_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Integration Type'),
                        'name' => 'cc_integration_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'redirection', 'name' => $this->l('Redirection')),
                                array('id' => 'standard_checkout', 'name' => $this->l('Standard Checkout')),
                                array('id' => 'hosted_checkout', 'name' => $this->l('Hosted Checkout'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Show mada Branding'),
                        'name' => 'cc_show_mada_branding',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Show Meeza Branding'),
                        'name' => 'cc_show_meeza_branding',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'textarea',
                        'name' => 'cc_mada_bins',
                        'label' => $this->l('mada Bins'),
                        'desc' => $this->l('Please do not change any of the below BINs configuration unless it is instructed by Amazon Payment Services Integration team. For further inquiries: integration-ps@amazon.com'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'textarea',
                        'name' => 'cc_meeza_bins',
                        'label' => $this->l('Meeza Bins'),
                        'desc' => $this->l('Please do not change any of the below BINs configuration unless it is instructed by Amazon Payment Services Integration team. For further inquiries: integration-ps@amazon.com'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'cc_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method installments configuration.
     *
     * @return mixed
     */
    protected function getAdminInstallmentsConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : Installments'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'installments_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Integration Type'),
                        'name' => 'installments_integration_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'redirection', 'name' => $this->l('Redirection')),
                                array('id' => 'standard_checkout', 'name' => $this->l('Standard Checkout')),
                                array('id' => 'hosted_checkout', 'name' => $this->l('Hosted Checkout')),
                                array('id' => 'embedded_hosted_checkout', 'name' => $this->l('Embedded Hosted Checkout'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'installments_sar_order_min_value',
                        'label' => $this->l('Installments Order Purchase minimum limit(SAR)'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'installments_aed_order_min_value',
                        'label' => $this->l('Installments Order Purchase minimum limit(AED)'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'installments_egp_order_min_value',
                        'label' => $this->l('Installments Order Purchase minimum limit(EGP)'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Show issuer name'),
                        'name' => 'installments_issuer_name',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Show issuer logo'),
                        'name' => 'installments_issuer_logo',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'installments_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method Visa Checkout configuration.
     *
     * @return mixed
     */
    protected function getAdminVisaCheckoutConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : Visa Checkout'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'visa_checkout_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Integration Type'),
                        'name' => 'visa_checkout_integration_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'redirection', 'name' => $this->l('Redirection')),
                                array('id' => 'hosted_checkout', 'name' => $this->l('Hosted Checkout'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'visa_checkout_api_key',
                        'label' => $this->l('API Key'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'visa_checkout_profile_name',
                        'label' => $this->l('Profile Name'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'visa_checkout_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method NAPS configuration.
     *
     * @return mixed
     */
    protected function getAdminNAPSConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : NAPS'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'naps_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'naps_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method KNET configuration.
     *
     * @return mixed
     */
    protected function getAdminKNETConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : KNET'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'knet_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'knet_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method Valu configuration.
     *
     * @return mixed
     */
    protected function getAdminValuConfigForms()
    {
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : Valu'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'valu_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Downpayment'),
                        'name' => 'valu_allow_downpayment',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'valu_downpayment_value',
                        'label' => $this->l('Downpayment value'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'valu_order_min_value',
                        'label' => $this->l('VALU Order Purchase minimum limit in EGP'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'valu_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get payment method Apple Pay configuration.
     *
     * @return mixed
     */
    protected function getAdminApplePayConfigForms()
    {
        $key_file_name = Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_KEY_FILE', null);
        $key_file_path = '';
        if ($key_file_name) {
            $key_file_path = _PS_UPLOAD_DIR_ . 'aps_certificate/' . $key_file_name;
        }
        $crt_file_name = Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_CRT_FILE', null);
        $crt_file_path = '';
        if ($crt_file_name) {
            $crt_file_path = _PS_UPLOAD_DIR_ . 'aps_certificate/' . $crt_file_name;
        }
        return array(
            'form' =>
                array(
                'legend' => array(
                'title' => $this->l('Amazon Payment Services Configuration : Apple Pay'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enable'),
                        'name' => 'apple_pay_status',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enabled Apple Pay in product page'),
                        'name' => 'apple_pay_product_page',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enabled Apple Pay in cart page'),
                        'name' => 'apple_pay_cart_page',
                        'options' => array(
                            'query' => array(
                                array('id' => 1, 'name' => $this->l('Yes')),
                                array('id' => 0, 'name' => $this->l('No'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('SHA Type'),
                        'name' => 'apple_pay_sha_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'sha256', 'name' => $this->l('SHA-256')),
                                array('id' => 'sha512', 'name' => $this->l('SHA-512')),
                                array('id' => 'hmac256', 'name' => $this->l('HMAC-256')),
                                array('id' => 'hmac512', 'name' => $this->l('HMAC-512'))
                                
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Apple Pay Button Types'),
                        'name' => 'apple_pay_btn_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'apple-pay-buy', 'name' => $this->l('BUY')),
                                array('id' => 'apple-pay-donate', 'name' => $this->l('DONATE')),
                                array('id' => 'apple-pay-plain', 'name' => $this->l('PLAIN')),
                                array('id' => 'apple-pay-set-up', 'name' => $this->l('SETUP')),
                                array('id' => 'apple-pay-book', 'name' => $this->l('BOOK')),
                                array('id' => 'apple-pay-check-out', 'name' => $this->l('CHECKOUT')),
                                array('id' => 'apple-pay-subscribe', 'name' => $this->l('SUBSCRIBE')),
                                array('id' => 'apple-pay-add-money', 'name' => $this->l('ADDMONEY')),
                                array('id' => 'apple-pay-contribute', 'name' => $this->l('CONTRIBUTE')),
                                array('id' => 'apple-pay-order', 'name' => $this->l('ORDER')),
                                array('id' => 'apple-pay-reload', 'name' => $this->l('RELOAD')),
                                array('id' => 'apple-pay-rent', 'name' => $this->l('RENT')),
                                array('id' => 'apple-pay-support', 'name' => $this->l('SUPPORT')),
                                array('id' => 'apple-pay-tip', 'name' => $this->l('TIP')),
                                array('id' => 'apple-pay-top-up', 'name' => $this->l('TOPUP'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_access_code',
                        'label' => $this->l('Access Code'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_request_sha_phrase',
                        'label' => $this->l('Request SHA Phrase'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_response_sha_phrase',
                        'label' => $this->l('Response SHA Phrase'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_domain_name',
                        'label' => $this->l('Domain Name'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_display_name',
                        'label' => $this->l('Display Name'),
                        'desc' => $this->l('A string of 64 or fewer UTF-8 characters containing the canonical name for your store, suitable for display. Do not localize the name.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Supported Network'),
                        'name' => 'apple_pay_supported_network[]',
                        'multiple' => true ,
                        'class' => 'chosen' ,
                        'options' => array(
                            'query' => array(
                                array('id' => 'amex', 'name' => $this->l('American Express')),
                                array('id' => 'visa', 'name' => $this->l('Visa')),
                                array('id' => 'masterCard', 'name' => $this->l('MasterCard')),
                                array('id' => 'mada', 'name' => $this->l('mada'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
 
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_production_key',
                        'label' => $this->l('Production Key'),
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'file',
                        'name' => 'apple_pay_certificate_crt_file',
                        'label' => $this->l('Certificate File'),
                        'desc' => $crt_file_path,
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'file',
                        'name' => 'apple_pay_certificate_key_file',
                        'label' => $this->l('Certificate Key File'),
                        'desc' => $key_file_path,
                    ),
                    array(
                        'col'  => 6,
                        'type' => 'text',
                        'name' => 'apple_pay_sort_order',
                        'label' => $this->l('Sort Order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
}