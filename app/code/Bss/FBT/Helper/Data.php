<?php
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
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FBT\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_configSectionId = 'fbt';

    public function getConfigFlag($path, $store = null, $scope = null) {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->isSetFlag($path, $scope, $store);
    }

    public function getConfigValue($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }
    // General
    public function isActive() {
        return $this->getConfigFlag($this->_configSectionId.'/general/enable');
    }
    public function getSortItemType($store = null) {
        return $this->getConfigValue($this->_configSectionId.'/general/sort_item_type',$store);
    }
    public function getStartDate() {
        return $this->getConfigValue($this->_configSectionId.'/general/start_date');
    }
    // Frequently Bought Together List Setting
    public function getDisplaylist() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/display_list');
    }
    public function getShowCurentProduct() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/show_curent_product');
    }
    public function getShowAddtocartsgnbtn() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/sng_cart');
    }
    public function getShowAddalltocartbtn() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/btn_cart');
    }
    public function getShowAddalltowishlistbtn() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/btn_wishlist');
    }
    public function getLLimitProduct() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/limit_products');
    }
    public function getLTitle() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/title');
    }
    public function getLItemslide() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/item_on_slide');
    }
    public function getLSpeedslide() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/slide_speed');
    }
    public function getLAutoslide() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/slide_auto');
    }
    public function getShowPrice() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/show_price');
    }
     public function getShowReview() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/show_review');
    }
    public function getShowPreview() {
        return $this->getConfigFlag($this->_configSectionId.'/list_product_fbt/preview');
    }
    public function getLTextBtnCart() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/btn_text_cart');
    }
    public function getLBackgroundBtnCart() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/btn_cart_bg');
    }
    public function getLColorBtnCart() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/btn_cart_cl');
    }
    public function getLTextBtnWl() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/btn_text_wishlist');
    }
    public function getLBackgroundBtnWl() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/btn_wishlist_bg');
    }
    public function getLColorBtnWl() {
        return $this->getConfigValue($this->_configSectionId.'/list_product_fbt/btn_wishlist_cl');
    }

    // Popup setting
    public function getPItemslide() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/item_on_slide');
    }
    public function getPSpeedslide() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/slide_speed');
    }
    public function getPAutoslide() {
        return $this->getConfigFlag($this->_configSectionId.'/success_popup/slide_auto');
    }
    public function getPShowprice() {
        return $this->getConfigFlag($this->_configSectionId.'/success_popup/product_price');
    }
    public function getPShowContinueBtn() {
        return $this->getConfigFlag($this->_configSectionId.'/success_popup/continue_button');
    }
    public function getPActivecountdown() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/active_countdown');
    }
    public function getPCountdowntime() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/countdown_time');
    }
    public function getPShowminicart() {
        return $this->getConfigFlag($this->_configSectionId.'/success_popup/mini_cart');
    }
    public function getPShowminicheckout() {
        return $this->getConfigFlag($this->_configSectionId.'/success_popup/mini_checkout');
    }
    public function getPTextBtn() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_text_cart');
    }
    public function getPBackgroundBtn() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_cart_bg');
    }
    public function getPColorTextBtn() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_cart_cl');
    }
    public function getPTextBtnV() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_text_viewcart');
    }
    public function getPBackgroundBtnV() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_viewcart_bg');
    }
    public function getPColorTextBtnV() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_viewcart_cl');
    }
    public function getPTextBtnC() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_text_continue');
    }
    public function getPBackgroundBtnC() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_continue_bg');
    }
    public function getPColorTextBtnC() {
        return $this->getConfigValue($this->_configSectionId.'/success_popup/btn_continue_cl');
    }

}
