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

class ApsOrder extends ObjectModel
{
    public $id;
    public $id_amazonpaymentservices_order_meta;
    public $id_order;
    public $meta_key;
    public $meta_value;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'        => 'amazonpaymentservices_order_meta',
        'primary'      => 'id_amazonpaymentservices_order_meta',
        'fields'       => array(
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'meta_key'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 255,
            ),
            'meta_value'  => array(
                'type'     => ObjectModel::TYPE_HTML,
                'validate' => 'isCleanHtml',
                'size'     => 3999999999999,
            ),
            'date_add'  => array(
                'type'     => ObjectModel::TYPE_DATE,
                'validate' => 'isDate',
            ),
        ),
    );

    public function setIdOrder($id_order)
    {
        $this->id_order = $id_order;
    }

    public function setMetaKey($meta_key)
    {
        $this->meta_key = $meta_key;
    }

    public function setMetaValue($meta_value)
    {
        $this->meta_value = $meta_value;
    }

    public function setDateAdd($date_add)
    {
        $this->date_add = $date_add;
    }

    public static function savePaymentMethodIntegration($id_order, $payment_method, $integration_type)
    {
        $apsOrder = new ApsOrder();
        $apsOrder->setIdOrder($id_order);
        $apsOrder->setMetaKey('payment_method');
        $apsOrder->setMetaValue($payment_method);
        $apsOrder->setDateAdd(date("Y-m-d H:i:s"));
        $apsOrder->save();
       
        $apsOrder = new ApsOrder();
        $apsOrder->setIdOrder($id_order);
        $apsOrder->setMetaKey('integration_type');
        $apsOrder->setMetaValue($integration_type);
        $apsOrder->setDateAdd(date("Y-m-d H:i:s"));
        $apsOrder->save();
    }

    public static function saveApsPaymentMetaData($id_order, $meta_key, $meta_value)
    {
        $apsOrder = new ApsOrder();
        $apsOrder->setIdOrder($id_order);
        $apsOrder->setMetaKey($meta_key);
        $apsOrder->setMetaValue($meta_value);
        $apsOrder->setDateAdd(date("Y-m-d H:i:s"));
        $apsOrder->save();
        return $apsOrder->id;
    }

    public static function getApsMetaValue($id_order, $meta_key)
    {
        $query = new DbQuery();
        $query->select('meta_value');
        $query->from(static::$definition['table']);
        $query->where('id_order = '.pSQL($id_order));
        $query->where('meta_key = "'.pSQL($meta_key).'"');
        $meta_value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build());
        return $meta_value;
    }

    public static function getApsMetaValues($id_order, $meta_key)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('id_order = '.pSQL($id_order));
        $query->where('meta_key = "'.pSQL($meta_key).'"');
        $meta_values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());
        return $meta_values;
    }

    public static function getValuOrderIdByReference($valu_reference_id)
    {
        $query = new DbQuery();
        $query->select('id_order');
        $query->from(static::$definition['table']);
        $query->where('meta_key = "valu_reference_id"');
        $query->where('meta_value = "'.pSQL($valu_reference_id).'"');
        $id_order = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build());
        return $id_order;
    }

    public static function getPaymentDisplayData($payment_method, $id_order)
    {
        $display_data = [];
        $aps_payment_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_payment_response'), true);
        $embedded_order = ApsOrder::getApsMetaValue($id_order, 'embedded_hosted_order');
        $objAps = new Amazonpaymentservices();
        if (ApsConstant::APS_PAYMENT_METHOD_KNET == $payment_method) {
            $display_data['title'] = $objAps->l('KNET Details');

            if (isset($aps_payment_response['third_party_transaction_number'])) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('KNET third party transaction number'),
                    'value' => $aps_payment_response['third_party_transaction_number']
                 );
            }
            if (isset($aps_payment_response['knet_ref_number'])) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('KNET ref number'),
                    'value' => $aps_payment_response['knet_ref_number']
                 );
            }
        } elseif (ApsConstant::APS_PAYMENT_METHOD_INSTALLMENTS == $payment_method || $embedded_order == 1) {
            $display_data['title'] = $objAps->l('Installment Details');
            $aps_installment_amount = ApsOrder::getApsMetaValue($id_order, 'installment_amount');
            $installment_interest    = ApsOrder::getApsMetaValue($id_order, 'installment_interest');
            $lang_code = Context::getContext()->language->iso_code;
            if ($lang_code != 'ar') {
                $lang_code = 'en';
            }
            $confirmation_msg = ApsOrder::getApsMetaValue($id_order, 'installment_confirmation_' . $lang_code);

            if (! empty($aps_installment_amount)) {
                $amt = $aps_installment_amount . ' ' . $aps_payment_response['currency'];
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('EMI'),
                    'value' =>  $amt . '/' . $objAps->l('Month')
                );
            }

            if (! empty($installment_interest)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Interest'),
                    'value' => $installment_interest
                );
            }

            if (isset($aps_payment_response['number_of_installments'])) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Installments'),
                    'value' => $aps_payment_response['number_of_installments']
                );
            }

            if (! empty($confirmation_msg)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Confirmation'),
                    'value' => $confirmation_msg
                );
            }
        } elseif (ApsConstant::APS_PAYMENT_METHOD_VALU == $payment_method) {
            $display_data['title'] = $objAps->l('VALU Details');
            $active_tenure = ApsOrder::getApsMetaValue($id_order, 'active_tenure');
            $tenure_amount = ApsOrder::getApsMetaValue($id_order, 'tenure_amount');
            $tenure_interest = ApsOrder::getApsMetaValue($id_order, 'tenure_interest');
            $fees_amount = ApsOrder::getApsMetaValue($id_order, 'fees_amount');
            $transaction_id= ApsOrder::getApsMetaValue($id_order, 'valu_transaction_id');
            $loan_number= ApsOrder::getApsMetaValue($id_order, 'loan_number');
            $cashback_amount= ApsOrder::getApsMetaValue($id_order, 'valu_cashback_amount') ?? 0;
            $downpayment_amount= ApsOrder::getApsMetaValue($id_order, 'valu_downpayment') ?? 0;
            $wallet_amount= ApsOrder::getApsMetaValue($id_order, 'valu_wallet_amount') ?? 0;
            
            if (! empty($transaction_id)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Transaction Id'),
                    'value' => $transaction_id
                );
            }
            if (! empty($tenure_amount) && ! empty($active_tenure)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Installment Plans'),
                    'value' => $tenure_amount . " " . $aps_payment_response['currency'] . "/" . $objAps->l('Month') . " for " . $active_tenure . " " . $objAps->l('Months')
                );
            }
            // if (! empty($fees_amount)) {
            //     $display_data['display_data'][] = array(
            //         'label' => $objAps->l('Admin Fees'),
            //         'value' => number_format($fees_amount/100,2,'.','')
            //     );
            // }
            if (! empty($wallet_amount)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Wallet Amount'),
                    'value' => $wallet_amount . " " . $aps_payment_response['currency']
                );
            }
            if (! empty($cashback_amount)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Cashback Amount'),
                    'value' => $cashback_amount . " " . $aps_payment_response['currency']
                );
            }
            if (! empty($downpayment_amount)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Downpayment'),
                    'value' => $downpayment_amount . " " . $aps_payment_response['currency']
                );
            }
            if (! empty($loan_number)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Loan Number'),
                    'value' => $loan_number
                );
            }

        }
        return $display_data;
    }

    public static function getAdminOrderDisplayData($aps_payment_response, $payment_method, $id_order)
    {
        $display_data = [];
        $display_data['display_data'] = [];
        $objAps = new Amazonpaymentservices();

        if (isset($aps_payment_response['command']) && ! empty($aps_payment_response['command'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('Command'),
                'value' => $aps_payment_response['command'],
            );
        }
        if (isset($aps_payment_response['query_command']) && ! empty($aps_payment_response['query_command'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('Query Command'),
                'value' => $aps_payment_response['query_command'],
            );
        }
        if (isset($aps_payment_response['merchant_reference']) && ! empty($aps_payment_response['merchant_reference'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('Merchant Reference'),
                'value' => $aps_payment_response['merchant_reference'],
            );
        }
        if (isset($aps_payment_response['fort_id']) && ! empty($aps_payment_response['fort_id'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('Fort Id'),
                'value' => $aps_payment_response['fort_id'],
            );
        }
        if (isset($aps_payment_response['payment_option']) && ! empty($aps_payment_response['payment_option'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('Payment Option'),
                'value' => $aps_payment_response['payment_option'],
            );
        }

        $installment_amount   = ApsOrder::getApsMetaValue($id_order, 'installment_amount');
        $installment_interest = ApsOrder::getApsMetaValue($id_order, 'installment_interest');

        if (isset($aps_payment_response['installments']) && ! empty($aps_payment_response['installments'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Installments'),
                'value' => $aps_payment_response['installments'],
            );
        }

        if (isset($aps_payment_response['number_of_installments']) && ! empty($aps_payment_response['number_of_installments'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('No of Installments'),
                'value' => $aps_payment_response['number_of_installments'],
            );
        }
        if (! empty($installment_amount)) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Installment Amount'),
                'value' => $installment_amount,
            );
        }
        if (! empty($installment_interest)) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Installment Interest'),
                'value' => $installment_interest,
            );
        }

        if (ApsConstant::APS_PAYMENT_METHOD_VALU === $payment_method) {
            $tenure          = ApsOrder::getApsMetaValue($id_order, 'active_tenure');
            $tenure_amount   = ApsOrder::getApsMetaValue($id_order, 'tenure_amount');
            $tenure_interest = ApsOrder::getApsMetaValue($id_order, 'tenure_interest');
            if (! empty($tenure)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Tenure'),
                    'value' => $tenure,
                );
            }
            if (! empty($tenure_amount)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Tenure Amount'),
                    'value' => $tenure_amount . ' ' . $aps_payment_response['currency'],
                );
            }
            if (! empty($tenure_interest)) {
                $display_data['display_data'][] = array(
                    'label' => $objAps->l('Tenure Interest'),
                    'value' => $tenure_interest . '%',
                );
            }
        }

        if (isset($aps_payment_response['token_name']) && ! empty($aps_payment_response['token_name'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Card Token'),
                'value' => $aps_payment_response['token_name'],
            );
        }
        if (isset($aps_payment_response['expiry_date']) && ! empty($aps_payment_response['expiry_date'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Card Expiry'),
                'value' => $aps_payment_response['expiry_date'],
            );
        }
        if (isset($aps_payment_response['card_number']) && ! empty($aps_payment_response['card_number'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Card Number'),
                'value' => $aps_payment_response['card_number'],
            );
        }
        if (isset($aps_payment_response['authorization_code']) && ! empty($aps_payment_response['authorization_code'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Authorization Code'),
                'value' => $aps_payment_response['authorization_code'],
            );
        }
        if (isset($aps_payment_response['response_code']) && ! empty($aps_payment_response['response_code'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Response Code'),
                'value' => $aps_payment_response['response_code'],
            );
        }
        if (isset($aps_payment_response['acquirer_response_code']) && ! empty($aps_payment_response['acquirer_response_code'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Acquier Response Code'),
                'value' => $aps_payment_response['acquirer_response_code'],
            );
        }
        if (isset($aps_payment_response['reconciliation_reference']) && ! empty($aps_payment_response['reconciliation_reference'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Reconciliation Reference'),
                'value' => $aps_payment_response['reconciliation_reference'],
            );
        }
        if (isset($aps_payment_response['acquirer_response_message']) && ! empty($aps_payment_response['acquirer_response_message'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Acquirer Response Message'),
                'value' => $aps_payment_response['acquirer_response_message'],
            );
        }
        if (isset($aps_payment_response['customer_ip']) && ! empty($aps_payment_response['customer_ip'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Customer IP'),
                'value' => $aps_payment_response['customer_ip'],
            );
        }
        if (isset($aps_payment_response['customer_email']) && ! empty($aps_payment_response['customer_email'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Customer Email'),
                'value' => $aps_payment_response['customer_email'],
            );
        }
        if (isset($aps_payment_response['phone_number']) && ! empty($aps_payment_response['phone_number'])) {
            $display_data['display_data'][] = array(
                'label' => $objAps->l('Phone Number'),
                'value' => $aps_payment_response['phone_number'],
            );
        }
        if (isset($aps_payment_response['third_party_transaction_number']) && ! empty($aps_payment_response['third_party_transaction_number'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('Third Party Transaction Number'),
                'value' => $aps_payment_response['third_party_transaction_number'],
            );
        }
        if (isset($aps_payment_response['knet_ref_number']) && ! empty($aps_payment_response['knet_ref_number'])) {
            $display_data['display_data'][] =  array(
                'label' => $objAps->l('KNET Ref Number'),
                'value' => $aps_payment_response['knet_ref_number'],
            );
        }
        return $display_data['display_data'];
    }

    public static function getOrderData($order, $id_order)
    {
        $data = [];
        $aps_payment_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_payment_response'), true);
        $aps_check_status_response = json_decode(ApsOrder::getApsMetaValue($id_order, 'aps_check_status_response'), true);
        $amazon_ps_data = array_merge((array)$aps_payment_response, (array)$aps_check_status_response);

        $payment_method = ApsOrder::getApsMetaValue($id_order, 'payment_method');
        $data['payment_method']    = $payment_method;

        $data['display_data']      = [];
        if ($amazon_ps_data) {
            $display_data = ApsOrder::getAdminOrderDisplayData($amazon_ps_data, $payment_method, $id_order);
            $data['display_data']  = $display_data;
        } else {
            return $data;
        }

        $data['is_authorization']      = 0;
        $total_captured                = 0;
        $total_void                    = 0;
        $order_total                   = $order->total_paid_tax_incl;

        $data['order_total']           = ApsOrder::getConvertedAmt($order_total, $order->id_currency);
        $data['formatted_order_total'] = ApsOrder::formatPrice(
            $order_total,
            (int) $order->id_currency
        );
        $data['capture_history'] = array();

        if ((! empty($amazon_ps_data) && isset($amazon_ps_data['command']) && $amazon_ps_data['command'] == 'AUTHORIZATION')||(! empty($amazon_ps_data) && isset($amazon_ps_data['query_command']) && $amazon_ps_data['query_command'] == 'CHECK_STATUS' && $amazon_ps_data['transaction_code'] == ApsConstant::APS_PAYMENT_AUTHORIZATION_SUCCESS_RESPONSE_CODE)
        ) {
            $data['is_authorization'] = 1;

            $data['capture_history'] = ApsOrder::getApsMetaValues($id_order, 'CAPTURE');
            $total_captured          = array_sum(array_column($data['capture_history'], 'meta_value'));

            $data['void_history'] = ApsOrder::getApsMetaValues($id_order, 'VOID_AUTHORIZATION');
            $total_void           = array_sum(array_column($data['void_history'], 'meta_value'));

            $data['capture_history'] = array_merge((array)$data['capture_history'], (array)$data['void_history']);


            $remain_capture = $order_total - $total_captured;
            $data['total_captured'] = ApsOrder::getConvertedAmt($total_captured, $order->id_currency);
            $data['total_void'] = ApsOrder::getConvertedAmt($total_void, $order->id_currency);

            $data['remain_capture'] = ApsOrder::getConvertedAmt($remain_capture, $order->id_currency);

            $data['formatted_total_captured'] = ApsOrder::formatPrice(
                $total_captured,
                (int) $order->id_currency
            );
            $data['formatted_total_void'] = ApsOrder::formatPrice(
                $total_void,
                (int) $order->id_currency
            );

            $data['formatted_remain_capture'] = ApsOrder::formatPrice(
                $remain_capture,
                (int) $order->id_currency
            );
        }
        $data['refund_history'] = ApsOrder::getApsMetaValues($id_order, ApsConstant::APS_COMMAND_REFUND);
        $data['transaction_history'] = array_merge((array)$data['capture_history'], (array)$data['refund_history']);

        foreach ($data['transaction_history'] as $key => $value) {
            $data['transaction_history'][$key]['meta_value'] = ApsOrder::formatPrice(
                $value['meta_value'],
                (int) $order->id_currency
            );
        }

        $total_refunded = array_sum(array_column($data['refund_history'], 'meta_value'));
        $data['total_refunded'] = ApsOrder::getConvertedAmt($total_refunded, $order->id_currency);

        if ($data['is_authorization']) {
            $total_refundable = $total_captured-$total_refunded;
        } else {
            $total_refundable = $order_total-$total_refunded;
        }
        // KNET not support refund
        if ($payment_method == ApsConstant::APS_PAYMENT_METHOD_KNET) {
            $total_refundable = 0;
        }
        $data['total_refundable'] = ApsOrder::getConvertedAmt($total_refundable, $order->id_currency);
        $data['formatted_total_refundable'] = ApsOrder::formatPrice(
            $total_refundable,
            (int) $order->id_currency
        );

        $data['formatted_total_refunded'] = ApsOrder::formatPrice(
            $total_refunded,
            (int) $order->id_currency
        );
        return $data;
    }

    public static function getConvertedAmt($amount, $id_currency)
    {
        $currency       = new Currency($id_currency);
        $currency_code =  $currency->iso_code;

        $aps_helper = AmazonpaymentservicesHelper::getInstance();

        $decimal_points =  $aps_helper->getCurrencyDecimalPoints($currency_code);
        return round($amount, (int)$decimal_points);
    }

    public static function formatPrice($amount, $currency_id)
    {
        $currency = new Currency($currency_id);

        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            return Context::getContext()->currentLocale->formatPrice($amount, $currency->iso_code);
        }

        return Tools::displayPrice($amount, $currency);
    }
}