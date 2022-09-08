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

<div class="form-group token-box">
	{if (isset($aps_tokens) && ! empty ($aps_tokens) )}
		{foreach from=$aps_tokens.tokens item=aps_token}
			<div class="radio aps_token_group">
				<label class="aps_token_row">
					<span class="aps-card-num">
						<input type="radio" name="aps_payment_token_cc" class="aps-radio" value="{$aps_token.token|escape:'htmlall':'UTF-8'}" data-cardbin="{$aps_token.masking_card|truncate:6:''}" data-cardtype="{$aps_token.card_type|lower:escape:'htmlall':'UTF-8'}"/>
						
						<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/{$aps_token.card_type|lower:escape:'htmlall':'UTF-8'}-logo.png" alt="{l s='mada' mod='amazonpaymentservices'}" class="card-icon" />
						<strong>
							{if ($aps_token.card_type == 'mada')} 
								{$aps_token.card_type|lower|escape:'htmlall':'UTF-8'}
							{else}  
								{$aps_token.card_type|upper|escape:'htmlall':'UTF-8'}
							{/if}
							{$aps_token.last4|escape:'htmlall':'UTF-8'}
						</strong> 
					</span>
					<span class="aps-card-expiry">
						{l s='exp' mod='amazonpaymentservices'} {$aps_token.expiry_month|escape:'htmlall':'UTF-8'}/{$aps_token.expiry_year|escape:'htmlall':'UTF-8'}
					</span>
				</label>
				<div class="aps_error aps_install_token_error"></div>
			</div>
		{/foreach}
		<div class="radio" {$aps_tokens.display_add_new_card|escape:'htmlall':'UTF-8'}>
			<label>
				<input type="radio" name="aps_payment_token_cc" class="aps_token_radio" value="" data-cardbin="" required checked /> 
				{l s='Add a new card' mod='amazonpaymentservices'}
			</label>
		</div>
	{/if}
</div>