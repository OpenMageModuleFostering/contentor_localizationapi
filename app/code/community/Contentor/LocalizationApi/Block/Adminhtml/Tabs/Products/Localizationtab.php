<?php 
class Contentor_LocalizationApi_Block_Adminhtml_Tabs_Products_Localizationtab extends Mage_Core_Block_Template 
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function __construct(){
		$this->setTemplate('Contentor/Products/localizationtab.phtml');
		parent::__construct();
	}

 //Label to be shown in the tab
	public function getTabLabel(){
		return Mage::helper('core')->__('Contentor Localization');
	}

	public function getTabTitle(){
		return Mage::helper('core')->__('Contentor Localization');
	}

	public function canShowTab(){
		return true;
	}

	public function isHidden(){
		return false;
	}
}