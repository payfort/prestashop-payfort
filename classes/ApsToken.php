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

class ApsToken extends ObjectModel
{
    public $id;
    public $id_amazonpaymentservices_token;
    public $id_customer;
    public $token;
    public $masking_card;
    public $last4;
    public $card_holder_name;
    public $card_type;
    public $expiry_month;
    public $expiry_year;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'        => 'amazonpaymentservices_token',
        'primary'      => 'id_amazonpaymentservices_token',
        'fields'       => array(
            'id_customer' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'token'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 255,
            ),
            'masking_card'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 30,
            ),
            'last4'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 4,
            ),
            'card_holder_name'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 255,
            ),
            'card_type'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 20,
            ),
            'expiry_month'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 2,
            ),
            'expiry_year'  => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isString',
                'size'     => 4,
            ),
            'date_add'  => array(
                'type'     => ObjectModel::TYPE_DATE,
                'validate' => 'isDate',
            ),
            'date_upd'  => array(
                'type'     => ObjectModel::TYPE_DATE,
                'validate' => 'isDate',
            ),
        ),
    );

    public function setIdCustomer($id_customer)
    {
        $this->id_customer = $id_customer;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setMaskingCard($masking_card)
    {
        $this->masking_card = $masking_card;
    }

    public function setLastFour($last4)
    {
        $this->last4 = $last4;
    }

    public function setCardHolderName($card_holder_name)
    {
        $this->card_holder_name = $card_holder_name;
    }

    public function setCardType($card_type)
    {
        $this->card_type = $card_type;
    }

    public function setExpiryMonth($expiry_month)
    {
        $this->expiry_month = $expiry_month;
    }

    public function setExpiryYear($expiry_year)
    {
        $this->expiry_year = $expiry_year;
    }

    public function setDateAdd($date_add)
    {
        $this->date_add = $date_add;
    }

    public function setDateUpdate($date_upd)
    {
        $this->date_upd = $date_upd;
    }

    public static function saveApsToken($id_customer, $params)
    {
        $aps_helper = AmazonpaymentservicesHelper::getInstance();
        // if tokenization is not enabled then return

        if (Configuration::get('AMAZONPAYMENTSERVICES_TOKENIZATION') == false) {
            $aps_helper->log('Tokenization not enabled:');
            return;
        }

        //check response with get Method and card detail contain * only
        if (isset($params['expiry_date'])) {
            if (!preg_match('#[^*]#', $params['expiry_date'])) {
                // return all character are *
                $aps_helper->log('Token expiry_date not valid');
                return;
            }
        }

        $aps_helper->log('Token save update for customer #: ' . $id_customer);
        if (isset($id_customer) && $id_customer > 0) {
            $aps_token = self::getApsToken($id_customer, $params['token_name']);
            if (! (empty($aps_token))) {
                $apsToken = new ApsToken($aps_token['id_amazonpaymentservices_token']);
            } else {
                $apsToken = new ApsToken();
                $apsToken->setDateAdd(date("Y-m-d H:i:s"));
            }

            $apsToken->setIdCustomer($id_customer);
            if (isset($params['token_name'])) {
                $apsToken->setToken($params['token_name']);
            }

            if (isset($params['payment_option'])) {
                $apsToken->setCardType(Tools::strtolower($params['payment_option']));
            }
            if (isset($params['card_holder_name'])) {
                $apsToken->setCardHolderName($params['card_holder_name']);
            }
            if (isset($params['card_number'])) {
                $apsToken->setMaskingCard($params['card_number']);

                $last4 = Tools::substr($params['card_number'], -4);
                $apsToken->setLastFour($last4);
            }
            if (isset($params['expiry_date'])) {
                $short_year  = Tools::substr($params['expiry_date'], 0, 2);
                $short_month = Tools::substr($params['expiry_date'], 2, 2);
                $date_format = \DateTime::createFromFormat('y', $short_year);
                $full_year   = $date_format->format('Y');
                $apsToken->setExpiryMonth($short_month);
                $apsToken->setExpiryYear($full_year);
            }
            $apsToken->setDateUpdate(date("Y-m-d H:i:s"));
            $apsToken->save();
            return $apsToken->id;
        }
    }

    public static function getApsToken($id_customer, $token)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('id_customer = '.pSQL($id_customer));
        $query->where('token = "'.pSQL($token).'"');
        $aps_token = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query->build());
        return $aps_token;
    }

    public static function getApsTokens($id_customer)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('id_customer = '.pSQL($id_customer));
        $aps_token = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());
        return $aps_token;
    }

    public static function getCardBinByToken($token)
    {
        $query = new DbQuery();
        $query->select('masking_card');
        $query->from(static::$definition['table']);
        $query->where('token = \''.pSQL($token) .'\'');
        $card_bin = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build());

        if ($card_bin) {
            $card_bin = Tools::substr($card_bin, 0, 6);
        }
        return $card_bin;
    }

    public static function getTokenCardType($token)
    {
        $query = new DbQuery();
        $query->select('card_type');
        $query->from(static::$definition['table']);
        $query->where('token = \''.pSQL($token) .'\'');
        $card_type = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build());
        return $card_type;
    }
}
