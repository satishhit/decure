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
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Helper;

class AdditionalInfoSaving
{
    private $preselectKey;

    private $productEnabledModule;

    public function __construct(
        \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey $preselectKey,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\ProductEnabledModule $productEnabledModule
    ) {
        $this->preselectKey = $preselectKey;
        $this->productEnabledModule = $productEnabledModule;
    }

    public function savePreselectKey($postData, $productId)
    {
        $this->preselectKey->deleteOldKey($productId);
        foreach ($postData['sdcp_preselect'] as $key => $value) {
            $this->preselectKey->savePreselectKey($productId, $key, $value);
        }
    }

    public function saveEnabledModuleOnProduct($productId, $enabled)
    {
        $this->productEnabledModule->deleteOldKey($productId);
        $this->productEnabledModule->saveEnabled($productId, $enabled);
    }
}
