<?php
class Contentor_LocalizationApi_Model_ScheduleImport
{
	public function importCompleted() 
	{
		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
		
		// Get newest completion in system
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT MAX(`completed_time`) FROM `contentor_products`";
		$newest = $readConnection->fetchOne($query);
		
		// Fix the date the ugly way!
		if($newest <= '2016-07-06') {
			$newest = date('Y-m-d\TH:i:s.u\Z', strtotime('2016-07-13'));
		} else {
			$newest = date('Y-m-d\TH:i:s.u\Z', strtotime($newest));
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