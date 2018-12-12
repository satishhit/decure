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
namespace Bss\FBT\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Helper\Cart as HelperCart;;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddMultiple extends \Magento\Checkout\Controller\Cart
{

    protected $productRepository;
    protected $resultPageFactory;
    protected $_layout;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_layout = $layout;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
    }
    
    public function execute()
    {   
    
        // if (!$this->_formKeyValidator->validate($this->getRequest())) {
        //     return $this->resultRedirectFactory->create()->setPath('*/*/');
        // }

        $addedProducts = [];
        if ($this->getRequest()->getPost('fbt-product-select')) {
            $productIds = $this->getRequest()->getPost('fbt-product-select');
        }else{
            $result['status'] = 'error';
            $result['mess'] = 'Please select product';
            $this->getResponse()->setBody(json_encode($result));
            return;
        }
        $params = $this->getRequest()->getParams();
        $product_success = [];
        $product_fail = [];
        foreach( $productIds as $productId) {
            try {

                $qty = $this->getRequest()->getPost($productId.'_qty', 0);
                if ($qty <= 0) continue; // nothing to add
                $storeId = \Magento\Framework\App\ObjectManager::getInstance()->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore()->getId();
                $product = $this->productRepository->getById($productId, false, $storeId);
                $related = $this->getRequest()->getParam('related_product');
                $_request = $this->getRequest();
                $params['product']= $productId;
                $params['qty']= $qty;
                if ($bundle_option = $this->getRequest()->getPost($productId . '_bundle_option')) {
                    $params['bundle_option']= $bundle_option;    
                }
                if ($bundle_option_qty = $this->getRequest()->getPost($productId . '_bundle_option_qty')) {
                    $params['bundle_option_qty']= $bundle_option_qty;
                }
                if ($super_attribute = $this->getRequest()->getPost($productId . '_super_attribute')) {
                    $params['super_attribute']= $super_attribute;
                }
                if ($options = $this->getRequest()->getPost($productId . '_options')) {
                    $params['options']= $options;
                }
                if ($links = $this->getRequest()->getPost($productId . '_links')) {
                    $params['bundle_option'] = $bundle_option;
                }

                if (!$product) {
                    return $this->goBack();
                }

                $this->cart->addProduct($product, $params);
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                /**
                 * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                 */
                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $product_success[] = $product->getId();
                        $addedProducts[] = $product;
                    }
                }
                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $product_fail[$product->getId()] = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    $product_fail[$product->getId()] = $e->getMessage();
                }
                $cartItem = $this->cart->getQuote()->getItemByProduct($product);
                if ($cartItem) {
                    $this->cart->getQuote()->deleteItem($cartItem);
                }

            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->critical($e);
               
            }
        }
        if ($addedProducts) {
            $products = [];
            foreach ($addedProducts as $product) {
                /** @var $product \Magento\Catalog\Model\Product */
                $products[] = '"' . $product->getName() . '"';
            }

            $this->messageManager->addSuccess(
                __('%1 product(s) have been added to shopping cart: %2.', count($addedProducts), join(', ', $products))
            );

            // save cart and collect totals
            $this->cart->save()->getQuote()->collectTotals();
        }
        $product_poup['success'] = $product_success;
        $product_poup['errors'] = $product_fail;
        // var_dump($product_poup);die('ss');
        $template = 'Bss_FBT::popup.phtml';
        $html = $this->_layout
                    ->createBlock('Bss\FBT\Block\OptionProduct')
                    ->setTemplate($template)
                    ->setProduct($product_poup)
                    ->setTypeadd('muntiple')
                    ->toHtml();
        $result['popup'] = $html;
        $result['status'] = 'success';
        $this->getResponse()->setBody(json_encode($result));
        return ;
    }

}
