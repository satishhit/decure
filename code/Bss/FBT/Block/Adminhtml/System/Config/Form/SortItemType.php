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
namespace Bss\FBT\Block\Adminhtml\System\Config\Form;

class SortItemType extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $helper;

    public function _construct()
    {
        parent::_construct();

        $this->helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Bss\FBT\Helper\Data');

        $this->setTemplate('system/config/sortitemtype.phtml');
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;
        
        return $this->toHtml();
    }

    public function getItemType()
    {
        return ['0' => __('Related Products'),
                '1' => __('Up-Sell Products'), 
                '2' => __('Cross-Sell Products'),
                '3' => __('Frequently Bought Together Products'), 
                '4' => __('Real Data')
                ];
    }

    public function sortItemType()
    {
        $store =  null;
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $store = $storeManager->getStore($storeId);
        return $this->helper->getSortItemType($store);
    }
}