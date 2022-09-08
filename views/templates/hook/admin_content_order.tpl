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
*  @author	PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="tab-pane" id="ApsPayment">
	{if (isset($order_data) && ! empty($order_data['formatted_order_total']))}
		<div class="alert alert-success" id="aps_transactions-msg" style="display:none;"></div>
		<div class="text-center"><strong>{l s='Capture/Void Authorization & Refund' mod='amazonpaymentservices'}</strong></div>
		<table class="table table-striped table-bordered">
			<tbody style=" width: 100%; display: inline-table; ">
				<tr>
					<td> {l s='Order Total' mod='amazonpaymentservices'} </td>
					<td> {$order_data['formatted_order_total']|escape:'htmlall':'UTF-8'} </td>
				</tr>
				{if (isset($order_data['is_authorization']) && $order_data['is_authorization'] == 1)}
					<tr>
						<td>{l s='Total Capture' mod='amazonpaymentservices'}</td>
						<td id="amazon_ps_total_captured"> {$order_data['formatted_total_captured']|escape:'htmlall':'UTF-8'} </td>
					</tr>
					<tr>
						<td>{l s='Remaining Capture' mod='amazonpaymentservices'}</td>
						<td id="amazon_ps_remaining_captured"> {$order_data['formatted_remain_capture']|escape:'htmlall':'UTF-8'}</td>
					</tr>
					<tr>
						<td>{l s='Void' mod='amazonpaymentservices'}</td>
						<td id="amazon_ps_total_void">{$order_data['formatted_total_void']|escape:'htmlall':'UTF-8'}</td>
					</tr>
					{if (isset($order_data['total_void']) && isset($order_data['remain_capture']) && $order_data['total_void'] == 0  && $order_data['remain_capture'] > 0 && $enable_extension == 1)}
						<tr>
							<td>{l s='Capture' mod='amazonpaymentservices'}</td>
							<td id="capture_status">
								<input type="text" width="10" id="capture-amount" value="{$order_data['remain_capture']|escape:'htmlall':'UTF-8'}"/>
								<a class="button btn btn-primary" id="button-capture">{l s='Capture' mod='amazonpaymentservices'}</a>
							</td>
						</tr>
					{/if}
					{if (isset($order_data['total_void']) && isset($order_data['total_captured']) && $order_data['total_void'] == 0  && $order_data['total_captured'] == 0 && $enable_extension == 1)}
						<tr>
							<td>{l s='Void' mod='amazonpaymentservices'}</td>
							<td id="void_status">
								<input type="text" width="10" id="void-amount" value="{$order_data['order_total']|escape:'htmlall':'UTF-8'}" style="display:none;"/>
								<a class="button btn btn-primary" id="button-void">{l s='Void' mod='amazonpaymentservices'}</a>
							</td>
						</tr>
					{/if}
				{/if}
				{if (isset($order_data['payment_method']) && $order_data['payment_method'] != 'amazonpaymentservices_knet')}
					<tr>
						<td> {l s='Refunable' mod='amazonpaymentservices'}</td>
						<td> {$order_data['formatted_total_refundable']|escape:'htmlall':'UTF-8'}</td>
					</tr>
					<tr>
						<td> {l s='Refunded' mod='amazonpaymentservices'}</td>
						<td> {$order_data['formatted_total_refunded']|escape:'htmlall':'UTF-8'}</td>
					</tr>
					<tr>
						<td>{l s='Refund' mod='amazonpaymentservices'}</td>
						<td id="refund_status">
							{if (isset($order_data['total_refundable']) && $order_data['total_refundable'] > 0  && $enable_extension == 1)}
								<input type="text" width="10" id="refund-amount" value="{$order_data['total_refundable']|escape:'htmlall':'UTF-8'}"/>
								<a class="button btn btn-primary" id="button-refund">{l s='Refund' mod='amazonpaymentservices'}</a>
							{/if}
						</td>
					</tr>
				{/if}
				<tr>
					<td>{l s='Transactions' mod='amazonpaymentservices'}:</td>
					<td>
						<table class="table table-striped table-bordered" id="amazon_ps_transactions" style="display: inline-table;">
							<thead>
							<tr>
								<td class="text-left"><strong>{l s='Date' mod='amazonpaymentservices'} </strong></td>
								<td class="text-left"><strong>{l s='Type' mod='amazonpaymentservices'} </strong></td>
								<td class="text-left"><strong>{l s='Amount' mod='amazonpaymentservices'} </strong></td>
							</tr>
							</thead>
							<tbody>
								{foreach from=$order_data['transaction_history'] item=transaction}
								<tr>
									<td class="text-left">{$transaction['date_add']|escape:'htmlall':'UTF-8'}</td>
									<td class="text-left">{$transaction['meta_key']|escape:'htmlall':'UTF-8'}</td>
									<td class="text-left">{$transaction['meta_value']|escape:'htmlall':'UTF-8'}</td>
								</tr>
								{/foreach}
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="aps-loader" id="aps-loader" style="display:none">
			<div class="loader">
				<button class="aps-spinner"></button>
			</div>
		</div>
	{/if}

	<div class="text-center"><strong> {l s='APS Payment Information' mod='amazonpaymentservices'} </strong></div>
	{if (isset($order_data) && isset($order_data['display_data']) && ! empty($order_data['display_data']))}
		<table class="table table-bordered">
			<thead style="width: 100%; display: inline-table;">
				<tr>
					<th style="width: 25%;"> {l s='Title' mod='amazonpaymentservices'} </th>
					<th style="width: 75%;"> {l s='Value' mod='amazonpaymentservices'} </th>
				</tr>
			</thead>
			<tbody style="width: 100%; display: inline-table;">
				{foreach from=$order_data['display_data'] item=data}
					<tr>
						<td style="width: 25%;"> {$data.label|escape:'htmlall':'UTF-8'} </td>
						<td style="width: 75%;"> {$data.value|escape:'htmlall':'UTF-8'} </td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{else}
		<div><strong>{l s='APS payment information not available' mod='amazonpaymentservices'}</strong></div>
	{/if}
</div>
<script type="text/javascript"><!--
	$("#button-capture").click(function () {
		if (confirm('{l s='Are you sure you want to capture the payment?' mod='amazonpaymentservices'}')) {
			$.ajax({
				type: 'POST',
				url: '{$admin_amazonpaymentservices_ajax_url}',
				dataType: 'json',
				data:
				{
					action : 'capture',
					ajax : true,
					controller : 'AdminAmazonpaymentservices',
					id_order: {$id_order|escape:'htmlall':'UTF-8'},
					amount: $('#capture-amount').val()
				},
				beforeSend: function () {
					$('#button-capture').hide();
					$('#capture-amount').hide();
					$('#aps-loader').show();
					$('#amazon_ps_transactions-msg').hide();

					$('#button-void').hide();
					$('#void-amount').hide();
					$('#button-refund').hide();
				},
				success: function (data) {
					if (data.error == false) {
						if (data.msg != '') {
							alert(data.msg);
						}
					}
					if (data.error == true) {
						alert(data.msg);
					}
					setInterval('location.reload()', 3000);
				}
			});
		}
	});

	$("#button-void").click(function () {
		if (confirm('{l s='Are you sure you want to Void the payment?' mod='amazonpaymentservices'}')) {
			$.ajax({
				type: 'POST',
				url: '{$admin_amazonpaymentservices_ajax_url}',
				dataType: 'json',
				data:
				{
					action : 'void',
					ajax : true,
					controller : 'AdminAmazonpaymentservices',
					id_order: {$id_order|escape:'htmlall':'UTF-8'},
					amount: $('#void-amount').val()
				},
				beforeSend: function () {
					$('#button-void').hide();
					$('#void-amount').hide();
					$('#aps-loader').show();
					$('#amazon_ps_transactions-msg').hide();

					$('#button-capture').hide();
					$('#capture-amount').hide();
					$('#button-refund').hide();
				},
				success: function (data) {
					if (data.error == false) {
						if (data.msg != '') {
							alert(data.msg);
						}
					}
					if (data.error == true) {
						alert(data.msg);
					}
					setInterval('location.reload()', 3000);
				}
			});
		}
	});

	$("#button-refund").click(function () {
		if (confirm('{l s='Are you sure you want to refund the payment?' mod='amazonpaymentservices'}')) {
			$.ajax({
				type: 'POST',
				url: '{$admin_amazonpaymentservices_ajax_url}',
				dataType: 'json',
				data:
				{
					action : 'refund',
					ajax : true,
					controller : 'AdminAmazonpaymentservices',
					id_order: {$id_order|escape:'htmlall':'UTF-8'},
					amount: $('#refund-amount').val()
				},

				beforeSend: function () {
					$('#button-refund').hide();
					$('#refund-amount').hide();
					$('#aps-loader').show();
					$('#amazon_ps_transactions-msg').hide();

					$('#button-void').hide();
					$('#button-capture').hide();
				},
				success: function (data) {
					if (data.error == false) {
						if (data.msg != '') {
							alert(data.msg);
						}
					}
					if (data.error == true) {
						alert(data.msg);
					}
					setInterval('location.reload()', 3000);
				}
			});
		}
	});
//--></script>