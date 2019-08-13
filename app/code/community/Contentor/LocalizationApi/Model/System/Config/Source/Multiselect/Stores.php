<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Stores 
{
    public function toOptionArray()
    {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false); 
    }
}