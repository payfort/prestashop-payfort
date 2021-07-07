<div class="payfortfort-wrapper">
    <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
        <fieldset>
            <legend>{l s='Configure your Payfort FORT Payment Gateway' mod='payfortfort'}</legend>
            {assign var='configuration_merchant_identifier' value="PAYFORT_FORT_MERCHANT_IDENTIFIER"}
            {assign var='configuration_access_code' value="PAYFORT_FORT_ACCESS_CODE"}
            {assign var='configuration_request_sha_phrase' value="PAYFORT_FORT_REQUEST_SHA_PHRASE"}
            {assign var='configuration_response_sha_phrase' value="PAYFORT_FORT_RESPONSE_SHA_PHRASE"}
            <table>
                <tr>
                    <td>
                        <p>{l s='Credentials for' mod='payfortfort'}</p>
                        <label for="PAYFORT_FORT_MERCHANT_IDENTIFIER">{l s='Merchant Identifier' mod='payfortfort'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_MERCHANT_IDENTIFIER" name="PAYFORT_FORT_MERCHANT_IDENTIFIER" value="{$PAYFORT_FORT_MERCHANT_IDENTIFIER}" /></div>

                        <label for="PAYFORT_FORT_ACCESS_CODE">{l s='Access Code' mod='payfortfort'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_ACCESS_CODE" name="PAYFORT_FORT_ACCESS_CODE" value="{$PAYFORT_FORT_ACCESS_CODE}" /></div>

                        <label for="PAYFORT_FORT_REQUEST_SHA_PHRASE">{l s='Request SHA Phrase' mod='payfortfort'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_REQUEST_SHA_PHRASE" name="PAYFORT_FORT_REQUEST_SHA_PHRASE" value="{$PAYFORT_FORT_REQUEST_SHA_PHRASE}" /></div>

                        <label for="PAYFORT_FORT_RESPONSE_SHA_PHRASE">{l s='Response SHA Phrase' mod='payfortfort'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_RESPONSE_SHA_PHRASE" name="PAYFORT_FORT_RESPONSE_SHA_PHRASE" value="{$PAYFORT_FORT_RESPONSE_SHA_PHRASE}" /></div>
                    </td>
                </tr>
            </table><br />
            <hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />
            <label for="payfort_sandbox_mode"> {l s='Sandbox Mode:' mod='payfortfort'}</label>
            <div class="margin-form" id="payfortfort_sandbox_mode">
                <input type="radio" name="payfort_sandbox_mode" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_SANDBOX_MODE}checked="checked"{/if} />
                <span>{l s='Yes' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_sandbox_mode" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_SANDBOX_MODE}checked="checked"{/if} />
                <span>{l s='No' mod='payfortfort'}</span><br/>
            </div>
            <label for="payfort_fort_command">{l s='Command:' mod='payfortfort'}</label>
            <div class="margin-form" id="payfortfort_command">
                <input type="radio" name="payfort_fort_command" value="AUTHORIZATION" style="vertical-align: middle;" {if 'AUTHORIZATION' eq $PAYFORT_FORT_COMMAND}checked="checked"{/if} />
                <span>{l s='AUTHORIZATION' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_fort_command" value="PURCHASE" style="vertical-align: middle;" {if 'PURCHASE' eq $PAYFORT_FORT_COMMAND}checked="checked"{/if} />
                <span>{l s='PURCHASE' mod='payfortfort'}</span><br/>
            </div>
            <label for="payfort_fort_sha_algorithm">{l s='SHA Algorithm' mod='payfortfort'}</label>
            <div class="margin-form">
                <select id="payfort_fort_sha_algorithm" name="payfort_fort_sha_algorithm">';
                    <option value="SHA1" {if 'SHA1' eq $PAYFORT_FORT_SHA_ALGORITHM} selected {/if}>
                        SHA-1
                    </option>
                    <option value="SHA256" {if 'SHA256' eq $PAYFORT_FORT_SHA_ALGORITHM} selected {/if}>
                        SHA-256
                    </option>
                    <option value="SHA512" {if 'SHA512' eq $PAYFORT_FORT_SHA_ALGORITHM} selected {/if}>
                        SHA-512
                    </option>
                </select>
            </div>
            <label for="payfort_fort_language">{l s='Language' mod='payfortfort'}</label>
            <div class="margin-form">
                <select id="payfort_fort_language" name="PAYFORT_FORT_LANGUAGE">';
                    <option value="en" {if 'en' eq $PAYFORT_FORT_LANGUAGE} selected {/if}>
                        English (en)
                    </option>
                    <option value="ar" {if 'ar' eq $PAYFORT_FORT_LANGUAGE} selected {/if}>
                        Arabic (ar)
                    </option>
                </select>
            </div>
            <label for="payfort_fort_hold_review_os">{l s='Order status:  "Hold for Review" ' mod='payfortfort'}</label>
            <div class="margin-form">
                <select id="payfort_fort_hold_review_os" name="PAYFORT_FORT_HOLD_REVIEW_OS">';
                    // Hold for Review order state selection
                    {foreach from=$order_states item='os'}
                        <option value="{$os.id_order_state|intval}" {if $os.id_order_state|intval eq $PAYFORT_FORT_HOLD_REVIEW_OS} selected {/if}>
                            {$os.name|stripslashes}
                        </option>
                    {/foreach}
                </select>
            </div>
            <label for="payfort_fort_gateway_currency">{l s='Gateway Currency' mod='payfortfort'}</label>
            <div class="margin-form">
                <select id="payfort_fort_gateway_currency" name="payfort_fort_gateway_currency">';
                    <option value="base" {if 'base' eq $PAYFORT_FORT_GATEWAY_CURRENCY} selected {/if}>
                        {l s='Base' mod='payfortfort'}
                    </option>
                    <option value="front" {if 'front' eq $PAYFORT_FORT_GATEWAY_CURRENCY} selected {/if}>
                        {l s='Front' mod='payfortfort'}
                    </option>
                </select>
            </div>
            <label for="payfort_fort_debug_mode"> {l s='Debug Mode' mod='payfortfort'}</label>
            <div class="margin-form" id="payfort_fort_debug_mode">
                <input type="radio" name="payfort_fort_debug_mode" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_DEBUG_MODE}checked="checked"{/if} />
                <span>{l s='No' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_fort_debug_mode" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_DEBUG_MODE}checked="checked"{/if} />
                <span>{l s='Yes' mod='payfortfort'}</span><br/>
            </div>
            <label for="host_to_host_url">{l s='Host to Host URL: ' mod='payfortfort'}</label>
            <div class="margin-form">
                <input type="text" size="50" value="{$host_to_host_url}" readonly/>
            </div>
            <br />
            
        </fieldset>
        <fieldset>
            <legend>{l s='Credit \ Debit Card' mod='payfortfort'}</legend>
            <label for="payfort_credit_card"> {l s='Enabled' mod='payfortfort'}</label>
            <div class="margin-form" id="payfort_credit_card">
                <input type="radio" name="payfort_credit_card" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_CREDIT_CARD} checked="checked"{/if} />
                <span>{l s='Yes' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_credit_card" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_CREDIT_CARD} checked="checked"{/if} />
                <span>{l s='No' mod='payfortfort'}</span><br/>
            </div>
            <label for="payfort_fort_integration_type">{l s='Integration Type' mod='payfortfort'}</label>
            <div class="margin-form">
                <select id="payfort_fort_integration_type" name="payfort_fort_integration_type">';
                    <option value="redirection" {if 'redirection' eq $PAYFORT_FORT_INTEGRATION_TYPE} selected {/if}>
                        {l s='Redirection' mod='payfortfort'}
                    </option>
                    <option value="merchantPage" {if 'merchantPage' eq $PAYFORT_FORT_INTEGRATION_TYPE} selected {/if}>
                        {l s='Merchant Page' mod='payfortfort'}
                    </option>
                    <option value="merchantPage2" {if 'merchantPage2' eq $PAYFORT_FORT_INTEGRATION_TYPE} selected {/if}>
                        {l s='Merchant Page 2.0' mod='payfortfort'}
                    </option>
                </select>
            </div>
            <label for="payfort_mada_branding"> {l s='mada Option:' mod='payfortfort'}</label>
            <div class="margin-form" id="payfortfort_mada_branding">
                <input type="radio" name="payfort_mada_branding" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_MADA_BRANDING}checked="checked"{/if} />
                <span>{l s='Enabled' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_mada_branding" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_MADA_BRANDING}checked="checked"{/if} />
                <span>{l s='Disabled' mod='payfortfort'}</span><br/>
            </div>         
        </fieldset>
                    
                    
                    
                    
                    
                    
                    
            <fieldset>
            <legend>{l s='Installments' mod='payfortfort'}</legend>
            <label for="payfort_installments"> {l s='Enabled' mod='payfortfort'}</label>
            <div class="margin-form" id="payfort_installments">
                <input type="radio" name="payfort_installments" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_INSTALLMENTS} checked="checked"{/if} />
                <span>{l s='Yes' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_installments" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_INSTALLMENTS} checked="checked"{/if} />
                <span>{l s='No' mod='payfortfort'}</span><br/>
            </div>
            <label for="payfort_fort_integration_type_installments">{l s='Integration Type' mod='payfortfort'}</label>
            <div class="margin-form">
                <select id="payfort_fort_integration_type_installments" name="payfort_fort_integration_type_installments">';
                    <option value="redirection" {if 'redirection' eq $PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS} selected {/if}>
                        {l s='Redirection' mod='payfortfort'}
                    </option>
                    <option value="merchantPage" {if 'merchantPage' eq $PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS} selected {/if}>
                        {l s='Merchant Page' mod='payfortfort'}
                    </option>
                </select>
            </div>
        </fieldset>        
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
        <fieldset>
            <legend>{l s='Sadad' mod='payfortfort'}</legend>
            <label for="payfort_sadad"> {l s='Enabled' mod='payfortfort'}</label>
            <div class="margin-form" id="payfort_sadad">
                <input type="radio" name="payfort_sadad" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_SADAD}checked="checked"{/if} />
                <span>{l s='Yes' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_sadad" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_SADAD}checked="checked"{/if} />
                <span>{l s='No' mod='payfortfort'}</span><br/>
            </div>
        </fieldset>
        <fieldset>
            <legend>{l s='Naps' mod='payfortfort'}</legend>
            <label for="payfort_naps"> {l s='Enabled' mod='payfortfort'}</label>
            <div class="margin-form" id="payfort_naps">
                <input type="radio" name="payfort_naps" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_NAPS}checked="checked"{/if} />
                <span>{l s='Yes' mod='payfortfort'}</span><br/>
                <input type="radio" name="payfort_naps" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_NAPS}checked="checked"{/if} />
                <span>{l s='No' mod='payfortfort'}</span><br/>
            </div>
        </fieldset>
        <center>
            <input type="submit" name="submitModule" value="{l s='Update settings' mod='payfortfort'}" class="button" />
        </center>
        <sub></sub>
    </form>
</div>
<script>
    jQuery(document).ready(function () {
        jQuery('[name=submitModule]').click(function () {
            if (jQuery('[name=payfort_naps]:checked').val() == '0' && jQuery('[name=payfort_sadad]:checked').val() == '0' && jQuery('[name=payfort_credit_card]:checked').val() == '0' && jQuery('[name=payfort_installments]:checked').val() == '0') {
                alert('Please enable at least 1 payment method!');
                return false;
            }
        })
    });
</script>