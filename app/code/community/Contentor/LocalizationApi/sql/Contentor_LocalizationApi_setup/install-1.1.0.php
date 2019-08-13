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
			  `sent_time` datetime DEFAULT CURRENT_TIMESTAMP,
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


// Create Status Table
$table = Mage::getConfig()->getTablePrefix()."contentor_status";
if(!$installer->tableExists($table)) {
	$statusTable = "CREATE TABLE `" . $table . "` (
			  `contentor_id` char(10) NOT NULL,
			  `status_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `status` varchar(128) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

	$installer->run($statusTable);

	$statusIndex = "ALTER TABLE `" . $table . "`
			 ADD UNIQUE KEY `contentor_status` (`contentor_id`,`status_time`)";

	$installer->run($statusIndex);

}
$installer->endSetup();