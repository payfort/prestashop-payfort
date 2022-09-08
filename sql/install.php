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

    $sql = array();

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpaymentservices_order_meta` (
        `id_amazonpaymentservices_order_meta` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_order` int(10) unsigned NOT NULL,
        `meta_key` varchar(255) NULL,
        `meta_value` longtext,
        `date_add` datetime NOT NULL,
        PRIMARY KEY  (`id_amazonpaymentservices_order_meta`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpaymentservices_token` (
        `id_amazonpaymentservices_token` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_customer` int(10) unsigned NOT NULL,
        `token` varchar(255) NULL,
        `masking_card` varchar(30) NULL,
        `last4` varchar(4) NULL,
        `card_holder_name` varchar(255) NULL,
        `card_type` varchar(20) NULL,
        `expiry_month` varchar(02) NULL,
        `expiry_year` varchar(04) NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY  (`id_amazonpaymentservices_token`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
