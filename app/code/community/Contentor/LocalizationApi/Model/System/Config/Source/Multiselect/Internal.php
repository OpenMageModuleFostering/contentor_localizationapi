<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Internal
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'Id', 'label' => 'ID'),
            array('value' => 'Sku', 'label' => 'SKU')
        );
    }
    public function toArray()
    {
        return array(
            'Id' => 'ID',
            'Sku' => 'SKU'
        );
    }
}