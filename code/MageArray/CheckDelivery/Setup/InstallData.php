<?php
namespace MageArray\CheckDelivery\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'check_delivery_enable',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Enable check delivery',
                'group' => 'general',
                'input' => 'select',
                'class' => '',
                'source' =>
                    '\Magento\Catalog\Model\Product\Attribute\Source\Status',
                'global' => Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 2,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,configurable,bundle,grouped',
				'note'		=> 'Display check delivery on product detail page.'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'check_delivery_postcodes',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Delivery postcodes',
                'group' => 'general',
                'input' => 'textarea',
                'class' => '',
                'source' => '',
                'global' => Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,bundle,grouped',
				'note'		=> "Specified postcode's for this product."
            ]
        );
    }
}