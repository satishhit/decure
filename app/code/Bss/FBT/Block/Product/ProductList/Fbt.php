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
namespace Bss\FBT\Block\Product\ProductList;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

class Fbt extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Framework\DataObject\IdentityInterface
{

    protected $_itemCollection;

    protected $fbt_itemCollection;

    protected $_checkoutSession;

    protected $_catalogProductVisibility;

    protected $_checkoutCart;

    protected $moduleManager;

    protected $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bss\FBT\Helper\Data $helper,
        array $data = []
    ) {
        $this->_checkoutCart = $checkoutCart;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */
        $this->_itemCollection = $product->getFbtProductCollection()->addAttributeToSelect(
            'required_options'
        )->setPositionOrder()->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    public function getItems()
    {
        return $this->_itemCollection;
    }

    public function getItemsFbt()
    {
        $at_itemCollection = [];
        $order_ids = [];
        $product = $this->_coreRegistry->registry('product');
        $sortableString = $this->helper->getSortItemType();
        $sortable = null;
        parse_str($sortableString, $sortable);
        foreach ($sortable['sort'] as $value) {

           if ($value == 4) {
               # Auto
                $startdate = $this->helper->getStartDate();
                
                $magentoDateObject = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Stdlib\DateTime\DateTime');
                $magentoDate = $magentoDateObject->gmtDate();
                $dateStart = date('Y-m-d' . ' 00:00:00', strtotime(str_replace('/','-',$startdate)));
                $dateEnd = date('Y-m-d' . ' 23:59:59', strtotime($magentoDate));
                
                $item_collection  = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\ResourceModel\Order\Item\Collection');
                $item_collection->addFieldToSelect('created_at')
                                ->addFieldToSelect('product_id')
                                ->addFieldToSelect('order_id')
                                ->addFieldToSelect('parent_item_id');
                if ($startdate) {
                    $item_collection->addFieldToFilter('created_at', array('from' => $dateStart, 'to' => $dateEnd));
                }
                $item_collection->addFieldToFilter('parent_item_id', array('null' => true));
                // $item_collection ->getSelect()->group('sku');
                if ($item_collection->getSize() > 0) {
                    foreach ($item_collection as  $item) {
                        if ($item->getProductId() == $product->getId()) {
                             $order_ids[] = $item->getOrderId();
                        }
                       
                    }
                }
                if (count($order_ids) > 0) {
                    $collection  = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\ResourceModel\Order\Item\Collection');
                    $collection->addFieldToFilter('order_id', ['in' => $order_ids]);
                    $collection->addFieldToFilter('parent_item_id', array('null' => true));
                    $collection->getSelect()->columns('SUM(qty_ordered) as qty_ordered')->group('fbtproduct_id');
                    $collection->setOrder('qty_ordered', 'DESC');
                    if ($collection->getSize() > 0) {
                    
                        foreach ($collection as  $key => $cproduct) {
                            if ($cproduct->getProductId() != $product->getId()) {    
                                $at_itemCollection[$cproduct->getProductId()] = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\Product')->load($cproduct->getProductId());
                            }
                        }
                    }
                }

                $this->fbt_itemCollection = $at_itemCollection;
                if (count($this->fbt_itemCollection) < 1) {
                    continue;
                }else{
                    break;
                }
            }else{
                if ($value == 0) {
                   # Related Products
                    $this->fbt_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect('required_options')->setPositionOrder()->addStoreFilter();
                }elseif ($value == 1) {
                   # Up-Sell Products
                   $this->fbt_itemCollection = $product->getUpSellProductCollection()->setPositionOrder()->addStoreFilter();
                }
                elseif ($value == 2) {
                   # Cross-Sell Products
                    $this->fbt_itemCollection = $product->getCrossSellProductCollection()->addAttributeToSelect($this->_catalogConfig->getProductAttributes())->setPositionOrder()->addStoreFilter();
                }
                elseif ($value == 3){
                   # Frequently Bought Together Products
                    $this->fbt_itemCollection = $product->getFbtProductCollection()->addAttributeToSelect('required_options')->setPositionOrder()->addStoreFilter();
                }

                if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                    $this->_addProductAttributesAndPrices($this->fbt_itemCollection);
                }
                $this->fbt_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
                
                $this->fbt_itemCollection->load();
                if ($this->fbt_itemCollection->getSize() < 1) {
                    continue;
                }else{
                    break;
                }
            }
        }
        return $this->fbt_itemCollection;

    }

    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }

    public function canItemsAddToCart()
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }
        return false;
    }
    public function getCurrentProduct(){
        return $this->_coreRegistry->registry('product');
    }
}
