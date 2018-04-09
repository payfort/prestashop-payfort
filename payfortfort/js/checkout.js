function submitMerchantPage(url, paymentMethod) {
    payfortFortMerchantPage.loadMerchantPage(window.location.origin + '/index.php', paymentMethod);
}

function showMerchantPage2Form() {
    $('#payfortfort_form').toggle();
    $.uniform.update("select.form-control");
    
}
