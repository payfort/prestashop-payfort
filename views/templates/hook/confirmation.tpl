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

{if $prestashop_version != '1.7'}
	<p class="alert alert-success">{l s='Your order on %s is complete.' sprintf=$shop_name mod='amazonpaymentservices'}</p>
	<p>
	    <b>{l s='Congratulations, your order has been placed and will be processed soon.' mod='amazonpaymentservices'}</b><br/><br/>

	    {l s='You should receive a confirmation e-mail shortly.' mod="amazonpaymentservices"} <br/><br/>
	</p>
{/if}
{if (isset($payment_method) == true)}
	<h3>{$title|escape:'htmlall':'UTF-8'}</h3>
	{foreach from=$display_data item=data}
		<h4>{$data.label|escape:'htmlall':'UTF-8'} : {$data.value|escape:'htmlall':'UTF-8'} </h4>
	{/foreach}
{/if}
<hr />