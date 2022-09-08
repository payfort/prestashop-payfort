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

class AmazonpaymentservicesErrorModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function initContent()
    {
        parent::initContent();
        $message = Context::getContext()->cookie->__get('aps_error_msg');
        $cart = $this->context->cart;

        if ($cart->nbProducts() > 0) {
            $this->errors[] = $message;
            Context::getContext()->cookie->apsErrors = $message;
            Tools::redirect('index.php?controller=order&step=1');
        }
         
        $support_link_message = $this->module->l('If you think this is an error, you can contact our');

        $this->context->smarty->assign(array(
            'error' => $message,
            'support_link_message' => $support_link_message
        ));
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->setTemplate('module:amazonpaymentservices/views/templates/front/error.tpl');
        } else {
            $this->setTemplate('error16.tpl');
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = [
            'title' => $this->l('Amazon Payment Services Error', 'amazonpaymentservices'),
            'url' => $this->context->link->getModuleLink(
                'amazonpaymentservices',
                'error',
                array(),
                true
            ),
        ];
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
