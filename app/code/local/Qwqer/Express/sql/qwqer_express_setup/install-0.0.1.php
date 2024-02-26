<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');

$setup->addAttribute('order', 'qwqer_address', array(
        'group'             => 'General',
        'label'             => 'Qwqer address',
        'note'              => '',
        'type'              => 'varchar',
        'input'             => 'text',
        'frontend_class'    => '',
        'source'            => '',
        'backend'           => '',
        'frontend'          => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required'          => false,
        'visible_on_front'  => false,
        'is_configurable'   => false,
        'used_in_product_listing'   => false,
    )
);

$setup->addAttribute('order', 'qwqer_data', array(
        'group'             => 'General',
        'label'             => 'Qwqer sync data',
        'note'              => '',
        'type'              => 'text',
        'input'             => 'text',
        'frontend_class'    => '',
        'source'            => '',
        'backend'           => '',
        'frontend'          => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required'          => false,
        'visible_on_front'  => false,
        'is_configurable'   => false,
        'used_in_product_listing'   => false,
    )
);

$setup->addAttribute('quote', 'qwqer_address', array(
        'group'             => 'General',
        'label'             => 'Qwqer address',
        'note'              => '',
        'type'              => 'varchar',
        'input'             => 'text',
        'frontend_class'    => '',
        'source'            => '',
        'backend'           => '',
        'frontend'          => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required'          => false,
        'visible_on_front'  => false,
        'is_configurable'   => false,
        'used_in_product_listing'   => false,
    )
);

$installer->endSetup();