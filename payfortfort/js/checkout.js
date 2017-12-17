function submitMerchantPage(url, paymentMethod) {
    payfortFortMerchantPage.loadMerchantPage(baseDir + 'index.php', paymentMethod);
}

function showMerchantPage2Form() {
    $('#payfortfort_form').toggle();
    $.uniform.update("select.form-control");
    
}