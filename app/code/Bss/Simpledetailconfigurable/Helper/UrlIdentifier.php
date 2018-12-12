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

class UrlIdentifier extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $moduleConfig;

    private $productUrl;

    private $responseFactory;

    private $eavAttribute;

    private $categoryPathFactory;

    public function __construct(
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Bss\Simpledetailconfigurable\Model\ProductUrlFactory $productUrl,
        \Bss\Simpledetailconfigurable\Model\CategoryPathFactory $categoryPathFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->moduleConfig = $moduleConfig;
        $this->productUrl = $productUrl;
        $this->eavAttribute = $eavAttribute;
        $this->categoryPathFactory = $categoryPathFactory;
    }

    public function readUrl($url)
    {
        $result = ['product' => '0'];
        $productInfo = explode('+', $url);
        $productPath = explode('/', $productInfo[0]);
        $productKey = array_pop($productPath);
        array_shift($productPath);
        $path = implode('/', $productPath);
        $urlKeyId = $this->eavAttribute->getIdByCode('catalog_product', 'url_key');
        $urlPathId = $this->eavAttribute->getIdByCode('catalog_category', 'url_path');
        $nameId = $this->eavAttribute->getIdByCode('catalog_product', 'name');
        $result['category'] = $this->getPathId($path, $urlPathId);
        if (count($productInfo) > 1
            && $this->moduleConfig->isModuleEnable()
            && $this->moduleConfig->customUrl()) {
            $productId = $this->getProductId($productKey, $urlKeyId);
            if ($productId != null) {
                $result['product'] = $productId;
            } else {
                $productId = $this->getProductId(str_replace('-', ' ', $productKey), $nameId);
                if ($productId != null) {
                    $result['product'] = $productId;
                }
            }
        }
        return $result;
    }
    
    public function getProductId($url, $attrId)
    {
        $result = 0;
        $collection =  $this->productUrl->create()->getCollection()
        ->addFieldToFilter('attribute_id', $attrId)
        ->addFieldToFilter('value', $url);
        foreach ($collection as $value) {
            $result = $value['entity_id'];
        }
        return $result;
    }
    public function getPathId($pathUrl, $pathId)
    {
        $collection =  $this->categoryPathFactory->create()->getCollection()
        ->addFieldToFilter('attribute_id', $pathId)
        ->getItemByColumnValue('value', $pathUrl);
        if ($collection === null) {
            return null;
        } else {
            return $collection->getEntityId();
        }
    }
}
