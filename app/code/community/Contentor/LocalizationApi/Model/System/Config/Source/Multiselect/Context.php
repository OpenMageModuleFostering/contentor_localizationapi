<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Context
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'ProductUrl', 'label' => 'Product Url')
        );
    }
    public function toArray()
    {
        return array(
            'ProductUrl' => 'Product Url'
        );
    }
}