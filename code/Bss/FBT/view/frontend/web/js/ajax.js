/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_FBT
* @author     Extension Team
* @copyright  Copyright (c) 2014-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
var BssAjaxCart = function() {
    var self = this;
    this.init = function(addUrls) {
        jQuery('.fbt-product-list .tocart,.fbt-product-list .fbtaddtocart,.fbt-product-list .fbtaddtowishlist').click(function (e){
            e.preventDefault();
            var addUrl = jQuery(this).parents('form').attr('action');
            if (jQuery(this).hasClass('tocart')) {
                addUrl = addUrls + 'add';
                var data = '';
                var dataPost = jQuery(this).parents('.item').find('.ip-fbt');
                    if(dataPost) {
                        var productid =  jQuery(dataPost).find('.fbt-product-select').val();
                        var qty = jQuery(dataPost).find('.fbt-qty').val();
                        data += 'id=' +productid + '&product=' + productid + '&qty=' + qty;
                        self.sendAjax(addUrl, data);
                        return false;
                    }
                }

            if (jQuery(this).hasClass('fbtaddtocart')) {
                // if(jQuery(this).parents('form').find('.product-select').length){
                    var form = jQuery('#fbt-products');
                    var data = jQuery(form).serialize();
                    addUrl = jQuery(form).attr('action');
                    self.sendAjax(addUrl, data);
                // }else{
                    // alert('Please select product !');
                // }
                return false;
            }
            if (jQuery(this).hasClass('fbtaddtowishlist')) {
                    var form = jQuery('#fbt-products');
                    var data = jQuery(form).serialize();
                    addUrl = addUrls.replace('cart','wishlist') + 'add';
                    jQuery(form).attr('action',addUrl);
                    jQuery(form).submit();
                return false;
            }
        });
    };

    this.sendAjax = function(addUrl, data) {
        self.showLoader();

        jQuery.ajax({
            type: 'post',
            url: addUrl,
            data: data, 
            dataType: 'json',
            success: function (data) {
                if(data.popup && data.status == 'success') {
                    jQuery('#bss_fbt_cart_popup').html(data.popup);
                    self.showPopup();
                }
                if (data.status == 'error') {
                    alert(data.mess);
                    jQuery.fancybox.hideLoading();
                    jQuery('.fancybox-overlay').hide();
                }

            },
            error: function(){
                // window.location.href = '';
            }
        });
    };


    this.showLoader = function() {
        jQuery.fancybox.showLoading();
        jQuery.fancybox.helpers.overlay.open({parent: 'body'});
    };

    this.showPopup = function() {
        jQuery.fancybox({
            href: '#bss_fbt_cart_popup', 
            modal: false,
            helpers: {
                overlay: {
                    locked: false
                }
            },
            afterClose: function() {
                clearInterval(count);
            }
        });
    }
}