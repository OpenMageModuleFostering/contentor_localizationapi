<?php
class Contentor_LocalizationApi_Model_ScheduleImport
{
	public function importCompleted() 
	{
		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
		// Don't do anything unless the token are valid 
		if(ContentorAPI::testAuth()) {
			// Create now, as in run now to save later on, so we don't miss time between start and stop.
			$now = gmdate('Y-m-d H:i');
			// Get last run from db and ues in receive()
			// Get newest completion in system
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$table = Mage::getConfig()->getTablePrefix()."contentor_config";
			$query = "SELECT `value` FROM `" . $table . "` WHERE `key` = 'lastRun'";
			$lastRun = $readConnection->fetchOne($query);

		    if(!$lastRun) {
	    		$noTime = true;
	    		$lastRun = gmdate('Y-m-d H:i', 0);
	    	}

			// Go curl new completions since last time
			// Everyting OK? Do your magic here!
			$result = ContentorAPI::receive($lastRun);

			if($result !== true) {
				return false;
			} else {
				// If successful, write new time to db!
				$writeConnection = $resource->getConnection('core_write');
				$table = Mage::getConfig()->getTablePrefix()."contentor_config";
				if($noTime) {
					$query = "INSERT INTO `" . $table . "` (`key`, `value`) VALUES ('lastRun', '" . $now . "')";
				} else {
					$query = "UPDATE `" . $table . "` SET `value` = '" . $now . "' WHERE `key` = 'lastRun'";
				}
				$writeConnection->query($query);
				
				return true;
			}
		}
	}
}