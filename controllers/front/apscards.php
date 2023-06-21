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

class AmazonpaymentservicesApscardsModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function initContent()
    {
        parent::initContent();

        $allCards = array();

        if ($this->context->customer->id != null) {
            $allCards = ApsToken::getApsTokens($this->context->customer->id);
            foreach ($allCards as &$card) {
                $card['card_type'] =  (Tools::strtolower($card['card_type']) == 'mada') ? Tools::strtolower($card['card_type']) : Tools::strtoupper($card['card_type']);
                $card['masking_card'] = Tools::substr($card['masking_card'], -4);
            }
        }

        $aps_config  = AmazonpaymentservicesConfig::getInstance();
        $this->context->smarty->assign(array(
            'cards' => $allCards,
            'hide_delete_token' => $aps_config->isHideDeleteToken()
        ));

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->setTemplate('module:amazonpaymentservices/views/templates/hook/aps-cards.tpl');
        } else {
            $this->setTemplate('aps-cards16.tpl');
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = [
            'title' => $this->l('Amazon Payment Services Cards', 'amazonpaymentservices'),
            'url' => $this->context->link->getModuleLink(
                'amazonpaymentservices',
                'apscards',
                array(),
                true
            ),
        ];
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        Media::addJsDef(array(
            'aps_remove_card_url' => $this->context->link->getModuleLink(
                'amazonpaymentservices',
                'removeapscard',
                array(),
                true
            )
        ));
        $this->addJS(_MODULE_DIR_ . 'amazonpaymentservices/views/js/aps-card.js');
    }
}
