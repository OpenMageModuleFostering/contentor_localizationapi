<?php 
$installer = $this;

$installer->startSetup();

// Create Product table
$table = Mage::getConfig()->getTablePrefix()."contentor_products";
if(!$installer->tableExists($table)) {
	$productTable = "CREATE TABLE `" . $table . "` (
			  `contentor_id` char(10) NOT NULL,
			  `sku` varchar(64) NOT NULL,
			  `source_locale` varchar(5) NOT NULL,
			  `target_locale` varchar(5) NOT NULL,
			  `target_store` smallint(5) unsigned NOT NULL,
			  `sent_time` datetime NOT NULL,
			  `completed_time` datetime DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

	$installer->run($productTable);

	$productIndex = "ALTER TABLE `" . $table . "`
			 ADD PRIMARY KEY (`contentor_id`), 
			 ADD UNIQUE KEY `contentor_entity` (`contentor_id`,`sku`), 
			 ADD KEY `SKU` (`sku`), 
			 ADD KEY `sent_time` (`sent_time`)";

	$installer->run($productIndex);
}

// Create Category table
$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
if(!$installer->tableExists($table)) {
	$categoryTable = "CREATE TABLE `" . $table . "` (
			  `contentor_id` char(10) NOT NULL,
			  `cat_id` varchar(64) NOT NULL,
			  `source_locale` varchar(5) NOT NULL,
			  `target_locale` varchar(5) NOT NULL,
			  `target_store` smallint(5) unsigned NOT NULL,
			  `sent_time` datetime NOT NULL,
			  `completed_time` datetime DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

	$installer->run($categoryTable);

	$categoryIndex = "ALTER TABLE `" . $table . "`
			 ADD PRIMARY KEY (`contentor_id`),
			 ADD UNIQUE KEY `contentor_entity` (`contentor_id`,`cat_id`),
			 ADD KEY `CATID` (`cat_id`),
			 ADD KEY `sent_time` (`sent_time`)";

	$installer->run($categoryIndex);
}

// Create Status Table
$table = Mage::getConfig()->getTablePrefix()."contentor_status";
if(!$installer->tableExists($table)) {
	$statusTable = "CREATE TABLE `" . $table . "` (
			  `contentor_id` char(10) NOT NULL,
			  `status_time` datetime NOT NULL,
			  `status` varchar(128) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

	$installer->run($statusTable);

	$statusIndex = "ALTER TABLE `" . $table . "`
			 ADD UNIQUE KEY `contentor_status` (`contentor_id`,`status_time`)";

	$installer->run($statusIndex);

}

// Create product attribute for autoexport products to localization
$installer = Mage::getResourceModel('catalog/setup','catalog_setup');
$attributeCode = 'contentor_localize_me';
$attributeData = array(
				'type'=>'int',
				'input'=>'boolean', //for Yes/No dropdown
				'sort_order'=>50,
				'label'=>$attributeCode,
				'global'=>Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
				'required'=>'0',
				'comparable'=>'0',
				'searchable'=>'0',
				'is_configurable'=>'0',
				'user_defined'=>'1',
				'visible_on_front' => 0, //want to show on frontend?
				'visible_in_advanced_search' => 0,
				'is_html_allowed_on_front' => 0,
				'required'=> 0,
				'unique'=>false,
				'apply_to' => 'configurable', //simple,configurable,bundled,grouped,virtual,downloadable
				'is_configurable' => false
				);

$output = $installer->addAttribute('catalog_product', $attributeCode, $attributeData);
$installer->addAttributeToSet('catalog_product', 'Default', 'General', $attributeCode);
$installer->endSetup();