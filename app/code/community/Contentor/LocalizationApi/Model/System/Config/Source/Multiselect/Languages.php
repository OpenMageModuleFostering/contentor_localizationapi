<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Languages
{
    public function toOptionArray()
    {
        return Mage::app()->getLocale()->getOptionLocales();
    }
}