{*
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
*}
{if $order_status == '6'}
     <h2>{l s='Payment Cancelled!' mod='payfortfort'}</h2>
        <p class="warning">
            {l s='You have canceled the payment, please try agian.' mod='payfortfort'} 
        </p>
{else}
    {if $status == 'ok'}
        <h2>{l s='Payment Accepted!' mod='payfortfort'}</h2>
            <p>{l s='Your order on' mod='payfortfort'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='payfortfort'}
                    <br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='payfortfort'}</span>
                    <br /><br />{l s='For any questions or for further information, please contact our' mod='payfortfort'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payfortfort'}</a>.
            </p>
    {else}
        <h2>{l s='Payment Failed!' mod='payfortfort'}</h2>
            <p class="warning">
                    {l s='Sorry, Could not complete payment for your order, please check your payment details and try again. If you think this is an error, you can contact our' mod='payfortfort'} 
                    <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payfortfort'}</a>.
            </p>
    {/if}
{/if}