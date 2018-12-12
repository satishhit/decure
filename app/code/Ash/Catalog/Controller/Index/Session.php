<?php

namespace Ash\Catalog\Controller\Index;



use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class Session extends Action 

{
    public function __construct(
		Context $context,
		ResultFactory $resultJsonFactory

	) {
		$this->resultFactory = $resultJsonFactory;
        parent::__construct($context);

    }



    /**

     * Intialization of request

     */

    public function execute() {
		
		$_SESSION['show_all_product'] = 1;
	    $data['show_all_prod'] = true;
	    $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($data);
		return $resultJson;
    }
}