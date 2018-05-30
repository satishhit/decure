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
namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Framework\Event\ObserverInterface;

class RedirectConfigurableProduct implements ObserverInterface
{
    private $urlIdentifier;

    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier
    ) {
        $this->urlIdentifier = $urlIdentifier;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getData('request');
        $redirectUrl = $this->urlIdentifier->readUrl($request->getOriginalPathInfo());
        if ($redirectUrl['product'] != '0') {
            $controllerRequest = $observer->getData('controller_action')->getRequest();
            $controllerRequest->initForward();
            $params = ['id' => $redirectUrl['product'], 'category' => $redirectUrl['category']];
            $controllerRequest->setParams($params);
            $controllerRequest->setControllerName('product');
            $controllerRequest->setModuleName('catalog');
            $controllerRequest->setActionName('view');
            $controllerRequest->setDispatched(false);
        }
    }
}
