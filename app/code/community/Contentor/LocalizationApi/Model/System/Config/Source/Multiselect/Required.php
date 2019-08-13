<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Required
{
    public function toOptionArray()
    {
    	$fieldlist['localizable'] = explode(',',Mage::getStoreConfig('contentor_options/contentor_fields/contentor_localizable_input'));
    	$fieldlist['context'] = explode(',',Mage::getStoreConfig('contentor_options/contentor_fields/contentor_context_input'));
    	foreach($fieldlist as $fieldtype => $fields) {
    		foreach($fields as $field) {
    			$field_list[] = array('value' => $field, 'label' => $field);
    			// Do some check if empty, localizable fields not empty?
    		}
    	}
    	
    	return $field_list;
 
    }
    public function toArray()
    {
        return array(
            'ProductUrl' => 'Product Url'
        );
    }
}