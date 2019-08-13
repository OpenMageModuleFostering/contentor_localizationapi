<?php 
class Contentor_LocalizationApi_Model_Validation_Cmspagefields extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    protected function _beforeSave()
    {
   		$value = $this->getValue();
   		$validfields = array('text', 'textarea');
   		foreach($value as $field) {
   			print_r($field);
   			if(is_array($field)) {
   				if(array_key_exists('attribute', $field) && array_key_exists('data', $field) && array_key_exists('type', $field)) {
							   		
	   			} else {
	   				Mage::throwException(Mage::helper('adminhtml')->__('No empty values in included field are allowed'));
	    		}
   			}
    	}
    	parent::_beforeSave();
    }
}