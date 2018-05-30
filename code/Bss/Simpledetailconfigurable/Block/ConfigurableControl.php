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
namespace Bss\Simpledetailconfigurable\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableControl extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const BSS_SWATCH_RENDERER_TEMPLATE = 'Bss_Simpledetailconfigurable::SimpledetailControl.phtml';

    const BSS_CONFIGURABLE_RENDERER_TEMPLATE =
    'Bss_Simpledetailconfigurable::SimpledetailControlNoswatch.phtml';

    private $linkData;

    private $moduleConfig;

    private $customerSession;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        \Bss\Simpledetailconfigurable\Helper\ProductData $linkData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        array $data = []
    ) {
        $this->linkData = $linkData;
        $this->moduleConfig = $moduleConfig;
        $this->customerSession = $currentCustomer;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
    }

    /**
     * Bss_commerce
     * Get child product data
     */
    public function getJsonChildProductData()
    {
        return $this->jsonEncoder->encode(
            $this->linkData->getAllData(
                $this->getProduct()->getEntityId(),
                $this->customerSession->getCustomerId()
            )
        );
    }

    /**
     * Bss_commerce
     * Get module config
     */
    public function getJsonModuleConfig()
    {
        
        if ($this->linkData->getEnabledModuleOnProduct($this->getProduct()->getEntityId()) == '0') {
            return $this->jsonEncoder->encode($this->moduleConfig->getNullConfig());
        } else {
            return $this->jsonEncoder->encode($this->moduleConfig->getAllConfig());
        }
    }
    
    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getRendererTemplate()
    {
        return self::BSS_SWATCH_RENDERER_TEMPLATE;
    }
}
