<?php 
$installer = $this;

$installer->startSetup();

// Add columns to product table
$table = Mage::getConfig()->getTablePrefix()."contentor_products";
if($installer->tableExists($table)) {
	// Add my new column here
	$connection = $installer->getConnection();
	$connection->addColumn($table, 'deadline_time', 'datetime DEFAULT NULL');
	$connection->addColumn($table, 'canceled_time', 'datetime DEFAULT NULL');
	$connection->addColumn($table, 'state', 'VARCHAR(16)');
	$connection->addColumn($table, 'type', 'VARCHAR(16)');
}

// Add columns to category table
$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
if($installer->tableExists($table)) {
	// Add my new column here
	$connection = $installer->getConnection();
	$connection->addColumn($table, 'deadline_time', 'datetime DEFAULT NULL');
	$connection->addColumn($table, 'canceled_time', 'datetime DEFAULT NULL');
	$connection->addColumn($table, 'state', 'VARCHAR(16)');
	$connection->addColumn($table, 'type', 'VARCHAR(16)');
}

// Add CMS Page table
$table = Mage::getConfig()->getTablePrefix()."contentor_cmspages";
if(!$installer->tableExists($table)) {

	$cmspageTable = "CREATE TABLE `" . $table . "` (
			`contentor_id` char(10) NOT NULL,
			`page_id` varchar(64) NOT NULL,
			`source_locale` varchar(5) NOT NULL,
			`target_locale` varchar(5) NOT NULL,
			`target_store` smallint(5) unsigned NOT NULL,
			`sent_time` datetime NOT NULL,
			`completed_time` datetime DEFAULT NULL,
			`deadline_time` datetime DEFAULT NULL,
			`canceled_time` datetime DEFAULT NULL,
			`state` varchar(16) DEFAULT NULL,
			`type` varchar(16) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	
	$installer->run($cmspageTable);

	$cmspageIndex = "ALTER TABLE `" . $table . "`
			 ADD PRIMARY KEY (`contentor_id`),
			 ADD UNIQUE KEY `contentor_entity` (`contentor_id`,`page_id`),
			 ADD KEY `CATID` (`page_id`),
			 ADD KEY `sent_time` (`sent_time`)";

	$installer->run($cmspageIndex);
}

// Add Type table
$table = Mage::getConfig()->getTablePrefix()."contentor_type";
if(!$installer->tableExists($table)) {
	$typeTable = "CREATE TABLE IF NOT EXISTS `" . $table . "` (
			`contentor_id` varchar(16) NOT NULL,
			`type` varchar(16) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
	$installer->run($typeTable);
	
	$typeIndex = "ALTER TABLE `" . $table . "`
 				  ADD PRIMARY KEY (`contentor_id`)";
	
	$installer->run($typeIndex);
}


// Add Config table
$table = Mage::getConfig()->getTablePrefix()."contentor_config";
if(!$installer->tableExists($table)) {
	$configTable = "CREATE TABLE IF NOT EXISTS `" . $table . "` (
  				 `key` varchar(16) NOT NULL,
  				 `value` varchar(256) NOT NULL
				 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
	$installer->run($configTable);
	
	$configIndex = "ALTER TABLE `" . $table . "`
 				  ADD PRIMARY KEY (`key`)";
	
	$installer->run($configIndex);
}

$installer->endSetup();