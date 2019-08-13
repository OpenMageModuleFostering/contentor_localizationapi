<?php
class Contentor_LocalizationApi_Model_ScheduleExport
{
	public function exportFlaggedProducts() 
	{
		if(Mage::getStoreConfig('contentor_options/contentor_automation/contentor_autoexport'))
		{
			require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
			$attribute = 'contentor_localize_me';
			$sourceLocale = str_replace('_', '-', Mage::getStoreConfig('contentor_options/contentor_source/contentor_source_input'));
			$targetIDs = explode(',',Mage::getStoreConfig('contentor_options/contentor_targets/contentor_targets_input'));
			foreach($targetIDs as $targetID) {
				$targets[$targetID] = str_replace('_', '-', Mage::getStoreConfig('general/locale/code', $targetID));
			}
			$totalToSend = 100;
			$sent = 0;
			foreach($targets as $targetID => $targetLocale) {
				Mage::app()->setCurrentStore($targetID);
				// Get products for this target to send
				$products = Mage::getModel('catalog/product')->getCollection();
				$products->addFieldToFilter(array(array('attribute'=>$attribute, 'eq'=>1)));
				
				foreach($products as $product) {
					// Sent with defaultsource, and this as target
					$sku = $product->getSku();
					if($fields = ContentorAPI::getFieldData($product)) {
	    				$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields);
	    				if($contentorID = ContentorAPI::send($request)) {
	    					$sent++;
	    					if(!ContentorAPI::logSent($contentorID, $sku, $sourceLocale, $targetLocale, $targetID, $product)) {
	    						return false;
	    					} elseif($sent >= $totalToSend) {
								break 2;
	    					}
	    				} else {
	    					return false;
	    				}
		    		} else {
		    			return false;
		    		}
				}
			}
		} else {
			// Do not auto export!
			return true;
		}
	}
}