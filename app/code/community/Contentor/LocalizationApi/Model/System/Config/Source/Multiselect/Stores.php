<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Stores 
{
    public function toOptionArray()
    {
    	$storeinfo = array();
    	$storesdata=Mage::getModel('core/store')->getCollection();
    	foreach($storesdata as $storedata)
    	{
    		$storeid = $storedata->getId();
    		$storeinfo[] = array('value' => $storeid, 'label' => $storedata->getName() . ' (' . Mage::getStoreConfig('general/locale/code', $storeid) . ')');
    	}
    	
        return $storeinfo;
    }
    public function toArray()
    {
    	$storeinfo = array();
    	$storesdata=Mage::getModel('core/store')->getCollection();
    	foreach($storesdata as $storedata)
    	{
    		$storeinfo[$storeid]['label'] = $storedata->getName() . ' (' . Mage::getStoreConfig('general/locale/code', $storedata->getId()) . ')';
    	}
        return $storeinfo;
    }
}