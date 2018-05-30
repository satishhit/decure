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
namespace Bss\FBT\Controller\Wishlist;

use Magento\Wishlist\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Controller\ResultFactory;

class Add extends \Magento\Framework\App\Action\Action
{
    protected $wishlistData;

    protected $wishlistProvider;

    protected $customerSession;

    protected $productRepository;
   
    protected $formKeyValidator;

    public function __construct(
        Action\Context $context,
        Data $wishlistData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator
    ) {
        $this->wishlistData = $wishlistData;
        $this->customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // if (!$this->formKeyValidator->validate($this->getRequest())) {
        //      return $this->resultRedirectFactory->create()->setPath('*/*/');
        // }

        $params = $this->getRequest()->getParams();

        $wishlist = $this->wishlistProvider->getWishlist();
        $session = $this->customerSession;
        
        if(!$session->isLoggedIn()) {
            $url = $this->_redirect->getRefererUrl();
            $session->setAfterAuthUrl($url);
            $session->authenticate();
            return;
        }
        $addedProducts = [];
        $error = [];
        if ($this->getRequest()->getPost('fbt-product-select')) {
            $productIds = $this->getRequest()->getPost('fbt-product-select');
        }else{
            $this->messageManager->addErrorMessage(__('Please select products.'));
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }
        $params = $this->getRequest()->getParams();
        $product_success = [];
        $product_fail = [];
        foreach( $productIds as $productId) {
            try {
                    $requestParams['product'] = $productId;
                    $qty = $this->getRequest()->getPost($productId.'_qty', 0);
                    $requestParams['qty'] = $qty;
                    $buyRequest = new \Magento\Framework\DataObject($requestParams);
                    if ($qty < 0) continue;

                    $wishlist->addNewItem($productId, $buyRequest);

                    $referer = $session->getBeforeWishlistUrl();
                    if ($referer) {
                        $session->setBeforeWishlistUrl(null);
                    } else {
                        $referer = $this->_redirect->getRefererUrl();
                    }

                    $product = $this->productRepository->getById($productId);

                    $addedProducts[] = $product;

                    
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage(
                        __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('We can\'t add the item to Wish List right now.')
                    );
                }
            }

        if ($addedProducts) {
            $products = [];
            foreach ($addedProducts as $product) {
                
                $products[] = '"' . $product->getName() . '"';
            }

            $this->messageManager->addSuccess(
                __('%1 product(s) have been added to your Wish List: %2.', count($addedProducts), join(', ', $products))
            );

            $wishlist->save();
            $this->wishlistData->calculate();
        }
        $resultRedirect->setPath('wishlist');
        return $resultRedirect;
    }
}
