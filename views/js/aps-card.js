/**
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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(function(){
    $('.remove_card').click(function(event) {
        event.preventDefault();
        var element = $(this).closest('tr');
        $( '.aps-loader' ).show();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            async: true,
            url: aps_remove_card_url,
            data: {
                aps_card_token: $(this).data('aps_card_token')
            },
            beforeSend: function () {
                $( '.aps-loader' ).show();
            },
            success: function(response) {
                if (response.status == 'success') {
                    element.remove();
                    alert(response.message);
                } else if (response.status == 'error') {
                    alert(response.message);
                }
                $( '.aps-loader' ).hide();
            },
            error: function(err) {
                console.log(err);
                $( '.aps-loader' ).hide();
            }
        });
    });
})