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

class ProductData extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $productInfo;

    private $stockRegistry;

    private $configurableData;

    private $imageBuilder;

    private $imageHelper;

    private $productHelper;

    private $preselectKey;

    private $productEnabledModule;

    private $taxCalculation;

    private $moduleConfig;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModule,
        \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory $preselectKey,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->productInfo = $productInfo;
        $this->stockRegistry = $stockRegistry;
        $this->configurableData = $configurableData;
        $this->imageBuilder = $imageBuilder;
        $this->imageHelper = $imageHelper;
        $this->productHelper = $productHelper;
        $this->preselectKey = $preselectKey;
        $this->productEnabledModule = $productEnabledModule;
        $this->taxCalculation = $taxCalculation;
        $this->moduleConfig = $moduleConfig;
    }

    public function getAllData($productEntityId, $customerId)
    {
        $result = [];
        $map_r = [];
        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        $product = $this->productInfo->getById($productEntityId);

        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        $result['entity'] = $productEntityId;
        foreach ($parentAttribute as $attrKey => $attrValue) {
            $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
            $result['map2'][$attrCode]['code'] = $attrCode;
            $result['map2'][$attrCode]['id'] = $attrValue->getAttributeId();
            foreach ($product->getAttributes()[$attrValue->getProductAttribute()->getAttributeCode()]
                ->getOptions() as $tvalue) {
                $result['map'][$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $result['map'][$attrValue->getAttributeId()][$tvalue->getValue()] = $tvalue->getLabel();
                $map_r[$attrValue->getAttributeId()][$tvalue->getLabel()] = $tvalue->getValue();
            }
        }
        $productStock = $this->stockRegistry->getStockItem($productEntityId);
        $result['sku'] = $product->getSku();
        $result['name'] = $product->getName();
        $result['desc'] = $product->getDescription();
        $result['sdesc'] = $product->getShortDescription();
        $result['stock_number'] = $productStock->getQty();
        $result['stock_status'] = $productStock->getIsInStock();
        $result['image'] = $this->getGalleryImages($product);
        $result['preselect'] = $this->getSelectingDataWithConfig($productEntityId);
        $result['url'] = $this->productHelper->getProductUrl($product);
        $taxRequest = $this->taxCalculation->getDefaultRateRequest();
        $result['tax'] = $this->taxCalculation->getRate($taxRequest->setProductClassId($product->getTaxClassId()));
        $storeRate = $this->taxCalculation->getStoreRate(
            $taxRequest->setProductClassId($product->getTaxClassId()),
            $this->moduleConfig->getStoreId()
        );

        $result['same_rate_as_store'] =
            (bool)$this->moduleConfig->isCrossBorder() || abs($result['tax'] - $storeRate) < 0.00001;
        if ($result['url'] == null) {
            $result['url'] = str_replace(' ', '-', $result['name']);
        }
        
        $parentPrice = 0;
        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = [];
            $childProductPrice = [];
            $childProduct['entity'] = $simpleProduct;
            $child = $this->productInfo->getById($childProduct['entity']);
            $childStock = $this->stockRegistry->getStockItem($childProduct['entity']);
            $childProduct['image'] = $this->getGalleryImages($child);
            $childProduct['sku'] = $child->getSku();
            $childProduct['name'] = $child->getName();
            $childProduct['desc'] = $child->getDescription();
            $childProduct['sdesc'] = $child->getShortDescription();
            $childProduct['stock_number'] = $childStock->getQty();
            $childProduct['stock_status'] = $childStock->getIsInStock();
            $childProduct['minqty'] = ($childStock->getUseConfigMinSaleQty()) ? 0 : $childStock->getMinSaleQty();
            $childProduct['maxqty'] = ($childStock->getUseConfigMaxSaleQty()) ? 0 : $childStock->getMaxSaleQty();
            $childProduct['tax'] = $this->taxCalculation->getRate(
                $taxRequest->setProductClassId($child->getTaxClassId())
            );

            $storeRate = $this->taxCalculation->getStoreRate(
                $taxRequest->setProductClassId($child->getTaxClassId()),
                $this->moduleConfig->getStoreId()
            );

            $childProduct['same_rate_as_store'] =
                (bool)$this->moduleConfig->isCrossBorder() || abs($childProduct['tax'] - $storeRate) < 0.00001;
            $childProduct['increment'] = ($childStock->getUseConfigQtyIncrements()) ? 0 :
            $childStock->getQtyIncrements();
            foreach ($child->getTierPrices() as $keyprice => $price) {
                $childProductPrice[$keyprice]['qty'] = $price->getQty();
                $childProductPrice[$keyprice]['value'] = $price->getValue();
                $childProductPrice[$keyprice]['id'] = $price->getCustomerGroupId();
            }
            $childProduct['price']['basePrice'] = $child->getPriceModel()->getPrice($child);
            $childProduct['price']['finalPrice'] = $child->getFinalPrice();
            $childProduct['price']['tier_price']
            = $this->getPrice(
                $childProductPrice,
                $childProduct['price']['finalPrice'],
                $customerId
            );
            $key = '';
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
                $childRow = $child->getAttributes()[$attrCode]->getFrontend()->getValue($child);
                $attrKey = $child->getData($attrCode);
                $key .= $attrKey . '_';
                $result['map2'][$attrCode]['child'][$attrKey] = $childRow;
            }
            $result['child'][$key] = $childProduct;
            $parentPrice = $childProduct['price']['basePrice'];
        }
        foreach ($result['child'] as $rk => $ri) {
            $parentPrice = ($ri['price']['basePrice'] < $parentPrice) ? $ri['price']['basePrice'] : $parentPrice;
        }
        $result['price']['basePrice'] = $parentPrice;
        return $result;
    }

    public function getSelectingKey($productId)
    {
        $result = [];
        $parentProduct = $this->configurableData->getChildrenIds($productId);
        $product = $this->productInfo->getById($productId);
        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        foreach ($parentProduct[0] as $simpleProduct) {
            $child = $this->productInfo->getById($simpleProduct);
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $result[$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                $result[$attrValue->getAttributeId()]['child'][$child->getData($attrLabel)] = $childRow;
            }
        }
        return $result;
    }

    public function getSelectingData($productId)
    {
        $result = [];
        $collection = $this->preselectKey->create()
        ->getCollection()
        ->addFieldToFilter('product_id', $productId);
        foreach ($collection as $value) {
            $result[$value['attribute_key']] = $value['value_key'];
        }
        return $result;
    }

    public function getSelectingDataWithConfig($product)
    {
        $result = [];
        $result['data'] = $this->getSelectingData($product);
        if ($result['data'] != null) {
            $result['enabled'] = true;
        } else {
            $result['enabled'] = false;
        }
        return $result;
    }

    public function getEnabledModuleOnProduct($productId)
    {
        $result = $this->productEnabledModule->create()->load($productId)['enabled'];
        if ($result == null) {
            return '1';
        }
        return $result;
    }

    public function getPrice($productPrices, $basePrice, $customerId)
    {
        $customerPrice = [];
        $result = [];
        foreach ($productPrices as $key => $price) {
            if (((int)$price['id'] > 4 || (int)$price['id'] == (int)$customerId)
                && (float)$price['value'] < (float)$basePrice) {
                $customerPrice[$key]['qty'] = $price['qty'];
                $customerPrice[$key]['value'] = $price['value'];
            }
        }
        return $customerPrice;
    }

    public function getGalleryImages($product)
    {
        $images = $product->getMediaGalleryImages();
        $imagesItems = [];
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $imagesItems[] = [
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => $image->getLabel(),
                    'position' => $image->getPosition(),
                    'isMain' => $product->getImage() == $image->getFile(),
                ];
            }
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
            ];
        }
        return $imagesItems;
    }
}
