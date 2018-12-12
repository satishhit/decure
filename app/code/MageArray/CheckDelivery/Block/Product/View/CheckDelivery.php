<?php

namespace MageArray\CheckDelivery\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Class CheckDelivery
 * @package MageArray\CheckDelivery\Block\Product\View
 */
class CheckDelivery extends Template
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * CheckDelivery constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }
}