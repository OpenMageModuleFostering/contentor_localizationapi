<?php
class Contentor_LocalizationApi_Model_ScheduleImport
{
	public function importCompleted() 
	{
		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
		
		// Get newest completion in system
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		$query = "SELECT MAX(`completed_time`) FROM `" . $table . "`";
		$newest = $readConnection->fetchOne($query);
		
		// Fix the date!
	    if($newest) {
    		$newest = date('Y-m-d\TH:i:s.u\Z', strtotime($newest));
    	} else {
    		$newest = date('Y-m-d\TH:i:s.u\Z', 0);
    	}

		// Go curl new completions since last time
		// Everyting OK? Do your magic here!
		$result = ContentorAPI::receive($newest);
		
		if($result !== true) {
			return false;
		} else {
			return true;
		}
	}
}