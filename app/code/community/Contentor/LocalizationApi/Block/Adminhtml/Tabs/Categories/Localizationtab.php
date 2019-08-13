<?php
class Contentor_LocalizationApi_Block_Adminhtml_Tabs_Categories_Localizationtab
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Set the template file for the custom category tab block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Contentor/Categories/localizationtab.phtml');
    }
}