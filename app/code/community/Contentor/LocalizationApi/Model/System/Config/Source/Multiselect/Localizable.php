<?php
class Contentor_LocalizationApi_Model_System_Config_Source_Multiselect_Localizable
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'Name', 'label' => 'Name'),
            array('value' => 'Description', 'label' => 'Description'),
            array('value' => 'ShortDescription', 'label' => 'Short Description'),
        	array('value' => 'UrlKey', 'label' => 'URL Key'),
       		array('value' => 'MetaTitle', 'label' => 'Meta Title'),
       		array('value' => 'MetaKeywords', 'label' => 'Meta Keywords'),
       		array('value' => 'MetaDescription', 'label' => 'Meta Description')
        );
    }
    public function toArray()
    {
        return array(
            'Name' => 'Name',
            'Description' => 'Description',
            'ShortDescription' => 'Short Descriptio',
        	'UrlKey' => 'URL Key',
       		'MetaTitle' => 'Meta Title',
       		'MetaKeywords' => 'Meta Keywords',
       		'MetaDescription' => 'Meta Description'
        );
    }
}