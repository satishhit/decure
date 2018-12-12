<?php

namespace Ash\Catalog\Controller\Index;



use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class Index extends Action 

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
		
		
		
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem'); 
		$mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();


       $path = realpath(dirname(__FILE__));
		if (is_array($_FILES)) {
           if (is_uploaded_file($_FILES['option_image']['tmp_name'])) {

               $sourcePath = $_FILES['option_image']['tmp_name'];
			   $dynamicFolder = rand().'/';
			   $mediaPath = $mediaPath .'attribute/options/'.$dynamicFolder;
               $targetPath = $mediaPath . $_FILES['option_image']['name'];
				
				if (!file_exists($mediaPath)) {
					mkdir($mediaPath, 0777, true);
				}

               move_uploaded_file ($sourcePath, $targetPath) ;
			   $data['path'] = $dynamicFolder.$_FILES['option_image']['name'];
				$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
				$resultJson->setData($data);
		   }
	    }
		return $resultJson;
    }
}