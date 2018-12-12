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
namespace Bss\FBT\Model;

class Product extends \Magento\Catalog\Model\Product
{
    /**
     * Retrieve array of custom type products
     *
     * @return array
     */
    public function getFbtProducts() 
    {
        if (!$this->hasFbtProducts()) {
            $products = [];
            foreach ($this->getFbtProductCollection() as $product) {
                $products[] = $product;
            }
            $this->setFbtProducts($products);
        }
        return $this->getData('fbt_products');
    }
    /**
     * Retrieve custom type products identifiers
     *
     * @return array
     */
    public function getFbtIds() 
    {
        if (!$this->hasFbtbProductIds()) {
            $ids = [];
            foreach ($this->getFbtProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setFbtProductIds($ids);
        }
        return $this->getData('fbt_product_ids');
    }
    /**
     * Retrieve collection custom type product
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getFbtProductCollection() 
    {
        $collection = $this->getLinkInstance()->useFbtLinks()->getProductCollection()->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
    }
    /**
     * Retrieve collection custom type link
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Collection
     */
    public function getFbtLinkCollection() 
    {
        $collection = $this->getLinkInstance()->useFbtLinks()->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }
    
}