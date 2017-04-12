function submitMerchantPage() {
    payfortFortMerchantPage.loadMerchantPage(baseDir + 'index.php');
}
function showMerchantPage2Form() {
    $('#payfortfort_form').toggle();
    $.uniform.update("select.form-control");
    
}