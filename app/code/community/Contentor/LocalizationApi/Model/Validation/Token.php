<?php 
class Contentor_LocalizationApi_Model_Validation_Token extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
   		$value = $this->getValue();
   		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
   		$auth = ContentorAPI::testAuth($value);
		if(!$auth) {
			Mage::throwException(Mage::helper('adminhtml')->__('Token not valid'));
		}
    	parent::_beforeSave();
    }
}