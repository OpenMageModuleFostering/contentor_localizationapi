<?php 
class Contentor_LocalizationApi_Model_Validation_Fields extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    protected function _beforeSave()
    {
   		$value = $this->getValue();
   		$validfields = array('text', 'textarea');
   		foreach($value as $field) {
   			print_r($field);
   			if(is_array($field)) {
   				if(array_key_exists('attribute', $field) && array_key_exists('store', $field) && array_key_exists('data', $field) && array_key_exists('type', $field)) {
	    			if($field['type'] == 'localizable') {
	    				$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $field['attribute']);
	    				$type = $attribute->getFrontendInput();
	    				if(!in_array($type, $validfields)) {
	    					Mage::throwException(Mage::helper('adminhtml')->__('Only text or textfield attributes are valid as localizable fields [' . $attribute->getData('frontend_label') . ']'));
		   				}
		   			}
		   		
	   			} else {
	   				Mage::throwException(Mage::helper('adminhtml')->__('No empty values in included field are allowed'));
	    		}
   			}
    	}
    	parent::_beforeSave();
    }
}