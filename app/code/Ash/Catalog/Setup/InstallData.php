<?php

/**

 * Copyright © 2015 PlazaThemes.com. All rights reserved.



 * @author PlazaThemes Team <contact@plazathemes.com>

 */



namespace Ash\Catalog\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
            'show_in_buying_assistance',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Show in Buying Assistance',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '1',
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
            ]
        );
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'enable_buying_assistance', [
            'type'     => 'int',
            'label'    => 'Enable Buying Assistance',
            'input'    => 'boolean',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'visible'  => true,
            'default'  => '0',
            'required' => false,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'group'    => 'Display Settings',
        ]);
		
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY, 'buying_assistance_layout', [
            'type' => 'int',
            'label' => 'Buying Assistance Layout',
            'sort_order' => 100,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'is_html_allowed_on_front' => true,
            'group' => 'General Information',
			'input' => 'select',
			'source' => '\Ash\Catalog\Model\Config\Source\Layout',
			'required' => false,
			'used_in_product_listing' => true,
			'visible_on_front' => true
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY, 'sets_for_buying_guide', [
            'type' => 'text',
            'label' => 'Attribute Sets for Buying Guide',
            'input' => 'multiselect',
			'source' => 'Ash\Catalog\Model\Config\Source\Sets',
			'backend' => 'Ash\Catalog\Model\Category\Attribute\Source\Sets',
			'input_renderer' => 'Ash\Catalog\Block\Adminhtml\Category\Helper\Sets\Options',
            'required' => false,
            'sort_order' => 101,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'is_html_allowed_on_front' => true,
            'group' => 'General Information',
            ]
        );
    }
}