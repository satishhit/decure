<?php
 
namespace Ash\Catalog\Model\Config\Source;
 
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource; 
/**
 * Custom Attribute Renderer
 *
 * @author      Webkul Core Team <support@webkul.com>
 */
class Sets extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(\Magento\Catalog\Model\Config $catalogConfig)
    {
        $this->_catalogConfig = $catalogConfig;
    }

    /**
     * Retrieve Catalog Config Singleton
     *
     * @return \Magento\Catalog\Model\Config
     */
    protected function _getCatalogConfig()
    {
        return $this->_catalogConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			$coll = $objectManager->create(\Magento\Catalog\Model\Product\AttributeSet\Options::class);

			$this->_options=[ ['label'=>'Select Attribute Set', 'value'=>'']];

			foreach($coll->toOptionArray() as $d){
				if($d['label'] !== 'Default') {
					$this->_options[] = ['label' => $d['label'], 'value' => $d['value']];
				}
			}
		}	
		return $this->_options;
		
		/*$this->_options = [
                                ['label' => __('Label1'), 'value' => 'value1'],
                                ['label' => __('Label2'), 'value' => 'value2'],
                                ['label' => __('Label3'), 'value' => 'value3'],
                                ['label' => __('Label4'), 'value' => 'value4']
							];

        }
        return $this->_options;*/
    }
}