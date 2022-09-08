{*
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
*}

{extends file='page.tpl'}

{block name='content'}
    <section id="content-hook_order_confirmation" class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <h3>{l s='An error occurred' mod='amazonpaymentservices'}:</h3>
						<ul>
							<li class="alert alert-danger">{$error|escape:'htmlall':'UTF-8'}</li>
							<li>{$support_link_message|escape:'htmlall':'UTF-8'} <a href="{$urls.pages.contact|escape:'htmlall':'UTF-8'}">{l s='customer support' mod='amazonpaymentservices'}</a> </li>
						</ul>
                    </p>
                </div>
            </div>
        </div>
    </section>
{/block}