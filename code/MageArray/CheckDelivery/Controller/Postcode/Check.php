<?php

namespace MageArray\CheckDelivery\Controller\Postcode;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product as ProductModel;
use MageArray\CheckDelivery\Helper\Data as DataHelper;
use Magento\Framework\App\Action\Action;

/**
 * Class Check
 * @package MageArray\CheckDelivery\Controller\Postcode
 */
class Check extends Action
{
    /**
     * @var ProductModel
     */
    protected $_productModel;
    /**
     * @var DataHelper
     */
    protected $_helper;

    /**
     * Check constructor.
     * @param Context $context
     * @param ProductModel $productModel
     * @param DataHelper $helper
     */
    public function __construct(
        Context $context,
        ProductModel $productModel,
        DataHelper $helper
    ) {
        parent::__construct($context);
        $this->_productModel = $productModel;
        $this->_helper = $helper;
    }

    /**
     *
     */
    public function execute()
    {
        $response = [];
        try {
            if (!$this->getRequest()->isAjax()) {
                throw new \Exception('Invalid request.');
            }
            if (!$postcode = $this->getRequest()->getParam('postcode')) {
                throw new \Exception('Please enter postcode.');
            }

            $productId = $this->getRequest()->getParam('id', 0);
            $product = $this->_productModel->load($productId);

            if (!$product->getId()) {
                throw new \Exception('Product not found.');
            }
            $postcodes = trim($product->getCheckDeliveryPostcodes());
            if (!$postcodes) {
                $postcodes = $this->_helper->getPostcodes();
            }
            $postcodes = array_map('trim', explode(',', $postcodes));
            if (in_array($postcode, $postcodes)) {
                $response['type'] = 'success';
                $response['message'] = $this->_helper->getSuccessMessage();
            } else {
                $response['type'] = 'error';
                $response['message'] = $this->_helper->getErrorMessage();
            }
        } catch (\Exception $e) {
            $response['type'] = 'error';
            $response['message'] = $e->getMessage();
        }
        $this->getResponse()->setContent(json_encode($response));
    }

}