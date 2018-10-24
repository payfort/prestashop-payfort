        <style>
        p.payment_module a:hover {
            background-color: #f6f6f6 !important;
        }
        </style>
        {if $credit_card == 1}
            {if $integration_type == 'merchantPage'}
                <link rel="stylesheet" href="{$module_dir}css/checkout.css" type="text/css"/>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <p class="payment_module">
                            <a class="bankwire payfortfort-payment-tab" onclick="submitMerchantPage('{$url}')" title="{l s='Pay With Debit / Cradit Card' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/cc.png) 15px 15px no-repeat #fbfbfb;clear:both">
                                {l s='Pay With Debit / Cradit Card' mod='payfortfort'}
                            </a>
                        </p>
                    </div>
                </div>
                <form style="display:none" name="payfort_payment_form" id="payfort_payment_form" method="post"></form>
                <div class="pf-iframe-background" id="div-pf-iframe" style="display:none">
                    <div class="pf-iframe-container">
                        <span class="pf-close-container">
                            <i class="fa fa-times-circle pf-iframe-close" onclick="payfortFortMerchantPage.closePopup()"></i>
                        </span>
                        <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
                        <div class="pf-iframe" id="pf_iframe_content"></div>
                    </div>
                </div>
            {elseif $integration_type == 'merchantPage2'}
                <link rel="stylesheet" href="{$module_dir}css/checkout.css" type="text/css"/>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="payfortfort">
                            <p class="payment_module">
                                <a class="bankwire payfortfort-payment-tab" onclick="showMerchantPage2Form()" title="{l s='Pay With Debit / Cradit Card' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/cc.png) 15px 15px no-repeat #fbfbfb;clear:both">
                                    {l s='Pay With Debit / Cradit Card' mod='payfortfort'}
                                </a>
                            </p>
                            <div id="payfortfort_form" style="display: none;">
                                <form id="frm_payfortfort_merchantpage2" class="std box mini-payment" name="frm_payfortfort_merchantpage2" method="post" action="#" style="">
                                    <div class="payment_form">
                                        <div class="required form-group">
                                            <label class="" for="payfort_fort_card_holder_name"> {l s='text_card_holder_name' mod='payfortfort'} </label>
                                            <input id="payfort_fort_card_holder_name" class="form-control" type="text" autocomplete="off" value="" maxlength="50">
                                        </div>
                                        <div class="required form-group">
                                            <label class="required" for="payfort_fort_card_number"> {l s='text_card_number' mod='payfortfort'} </label>
                                            <input id="payfort_fort_card_number" class="form-control" type="text" autocomplete="off" value="" maxlength="19">
                                        </div>
                                        <div class="required select form-group">
                                            <label class="required" for="payfort_fort_expiry_month"> {l s='text_expiry_date' mod='payfortfort'} </label>
                                            <div class="row">
                                                <div class="col-xs-2">
                                                    <select class="form-control" id="payfort_fort_expiry_month">
                                                        {section name=date_m start=01 loop=13}					
                                                            <option value="{"%02d"|sprintf:$smarty.section.date_m.index}">{"%02d"|sprintf:$smarty.section.date_m.index}</option>
                                                        {/section}
                                                    </select>
                                                </div>
                                                <div class="col-xs-3">
                                                    <select class="form-control" id="payfort_fort_expiry_year">
                                                        {section name=date_y start=14 loop=26}
                                                            <option value="{$smarty.section.date_y.index}">20{$smarty.section.date_y.index}</option>
                                                        {/section}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="required form-group">
                                            <label class="required" for="payfort_fort_card_security_code"> {l s='text_cvc_code' mod='payfortfort'} </label>
                                            <input id="payfort_fort_card_security_code" class="form-control" type="text" autocomplete="off" value="" size="4" maxlength="4" style="width: 60px">
                                            <p>{l s='help_cvc_code' mod='payfortfort'}</p>
                                        </div>
                                        <p class="clearfix"> 
                                            <button onclick="payfortFortMerchantPage2.submitMerchantPage()" class="btn btn-default button button-medium hideOnSubmit" type="button"><span>{l s='Confirm my order' mod='payfortfort'}</span></button>
                                        </p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript"><!--
                    var arr_messages = [];
                    {$arr_js_messages}
                 //--></script>   
            {else}
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <p class="payment_module">
                            <a class="bankwire payfortfort-payment-tab" onclick="$('#payfortpaymentform input[type=submit]').click();" title="{l s='Pay With Debit / Cradit Card' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/cc.png) 15px 15px no-repeat #fbfbfb;clear:both">
                                {l s='Pay With Debit / Cradit Card' mod='payfortfort'}                        
                            </a>
                        </p>
                    </div>
                    <form id="payfortpaymentform" style="display:none" name="payfortpaymentform" method="post" action="{$url}">
                        <input type="submit"/>
                    </form>
                </div>
            {/if}
        {/if}
        
        {if $installments == 1}
            {if $integration_type_installments == 'merchantPage'}
                <link rel="stylesheet" href="{$module_dir}css/checkout.css" type="text/css"/>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <p class="payment_module">
                            <a class="bankwire payfortfort-payment-tab" onclick="submitMerchantPage('{$url}','Payfort Installments')" title="{l s='Pay with Installments' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/cc.png) 15px 15px no-repeat #fbfbfb;clear:both">
                                {l s='Pay with Installments' mod='payfortfort'}
                            </a>
                        </p>
                    </div>
                </div>
                <form style="display:none" name="payfort_payment_form" id="payfort_payment_form" method="post"></form>
                <div class="pf-iframe-background" id="div-pf-iframe" style="display:none">
                    <div class="pf-iframe-container">
                        <span class="pf-close-container">
                            <i class="fa fa-times-circle pf-iframe-close" onclick="payfortFortMerchantPage.closePopup()"></i>
                        </span>
                        <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
                        <div class="pf-iframe" id="pf_iframe_content"></div>
                    </div>
                </div>
                
        {else}
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <p class="payment_module">
                    <a class="bankwire payfortfort-payment-tab" onclick="$('#payfortpaymentforminstallments input[type=submit]').click();" title="{l s='Pay with Installments' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/cc.png) 15px 15px no-repeat #fbfbfb;clear:both">
                        {l s='Pay With Installments' mod='payfortfort'}
                    </a>
                </p>
            </div>
            <form id="payfortpaymentforminstallments" style="display:none" name="payfortpaymentforminstallments" method="post" action="{$url}">
                <input name="INSTALLMENTS" value="1">
                <input type="submit"/>
            </form>
        </div>
            {/if}
        {/if}
         
        {if $SADAD == 1}
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <p class="payment_module">
                    <a class="bankwire payfortfort-payment-tab" onclick="$('#payfortpaymentformsadad input[type=submit]').click();" title="{l s='Pay with SADAD' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/SADAD-logo.png) 15px 15px no-repeat #fbfbfb;clear:both">
                        {l s='Pay With SADAD' mod='payfortfort'}
                    </a>
                </p>
            </div>
            <form id="payfortpaymentformsadad" style="display:none" name="payfortpaymentformsadad" method="post" action="{$url}">
                <input name="SADAD" value="1">
                <input type="submit"/>
            </form>
        </div>
        {/if}
        {if $NAPS == 1}
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <p class="payment_module">
                    <a class="bankwire payfortfort-payment-tab" onclick="$('#payfortpaymentformnaps input[type=submit]').click();" title="{l s='Pay with NAPS' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url({$payfort_path}/img/qpay-logo.png) 15px 15px no-repeat #fbfbfb;clear:both">
                        {l s='Pay With NAPS' mod='payfortfort'}
                    </a>
                </p>
            </div>
            <form id="payfortpaymentformnaps" style="display:none" name="payfortpaymentformnaps" method="post" action="{$url}">
                <input name="NAPS" value="1">
                <input type="submit"/>
            </form>
        </div>
        {/if}
