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
namespace Bss\FBT\Model\Config\Source;

class Itemtype implements \Magento\Framework\Option\ArrayInterface {
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return [
                ['value' => '0', 'label' => __('Related Products')], 
                ['value' => '1', 'label' => __('Up-Sell Products')], 
                ['value' => '2', 'label' => __('Cross-Sell Products')],
                ['value' => '3', 'label' => __('Frequently Bought Together Products')],
                ['value' => '4', 'label' => __('Real Data')],
                ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return ['0' => __('Related Products'),
                '1' => __('Up-Sell Products'), 
                '2' => __('Cross-Sell Products'),
                '3' => __('Frequently Bought Together Products'), 
                '4' => __('Real Data')
                ];
    }
}