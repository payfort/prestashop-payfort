{*
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
*}

{capture name=path}
    <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
        {l s='My account' mod='amazonpaymentservices'}
    </a>
    <span class="navigation-pipe">{$navigationPipe}</span>
    <span class="navigation_page">{l s='Amazon Payment Services Cards' mod='amazonpaymentservices'}</span>
{/capture}
{include file="$tpl_dir./errors.tpl"}
<h1 class="page-heading bottom-indent">{l s='Amazon Payment Services Cards' mod='amazonpaymentservices'}</h1>

{if $cards|@count > 0}
    <table class="table table-striped table-bordered table-labeled table-responsive-lg">
        <thead class="thead-default">
            <tr>
                <th class="text-left">{l s='Cards details' mod='amazonpaymentservices'}</th>
                <th class="text-center">{l s='Expires' mod='amazonpaymentservices'}</th>
                {if ($hide_delete_token != true)}
                    <th class="text-center">{l s='Delete' mod='amazonpaymentservices'}</th>
                {/if}
            </tr>
        </thead>
        <tbody>
            {foreach from=$cards item=card}
                <tr>
                    <td class="text-left" data-label="{l s='Cards details' mod='amazonpaymentservices'}">
                        {$card['card_type']|escape:'htmlall':'UTF-8'} card ending in {$card['masking_card']|escape:'htmlall':'UTF-8'}
                    </td>
                    <td class="text-center" data-label="{l s='Expires' mod='amazonpaymentservices'}">
                       {$card['expiry_month']|escape:'htmlall':'UTF-8'}/{$card['expiry_year']|escape:'htmlall':'UTF-8'}
                    </td>
                    {if ($hide_delete_token != true)}
                        <td class="text-center" data-label="{l s='Delete' mod='amazonpaymentservices'}">
                            <span class="remove_card" data-aps_card_token="{$card['token']|escape:'htmlall':'UTF-8'}">
                                <i class="icon-trash-o"></i>
                            </span>
                        </td>
                    {/if}
                </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="aps-loader" id="div-aps-loader" style="display:none">
        <div class="loader">
             <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
        </div>
    </div>
{else}
    <p>{l s='There are no cards stored on our database.' mod='amazonpaymentservices'}</p>
{/if}
<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
            <span>
                <i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='amazonpaymentservices'}
            </span>
        </a>
    </li>
    <li>
        <a class="btn btn-default button button-small" href="{$base_dir}">
            <span><i class="icon-chevron-left"></i> {l s='Home' mod='amazonpaymentservices'}</span>
        </a>
    </li>
</ul>