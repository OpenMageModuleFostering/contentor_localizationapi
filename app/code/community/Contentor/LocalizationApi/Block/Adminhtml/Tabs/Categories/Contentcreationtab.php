<?php 
class Contentor_LocalizationApi_Block_Adminhtml_Tabs_Categories_Contentcreationtab extends Mage_Core_Block_Template 
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function __construct(){
		$this->setTemplate('Contentor/Categories/contentcreationtab.phtml');
		parent::__construct();
	}

 //Label to be shown in the tab
	public function getTabLabel(){
		return Mage::helper('core')->__('Contentor Content Creation');
	}

	public function getTabTitle(){
		return Mage::helper('core')->__('Contentor Content Creation');
	}

	public function canShowTab(){
		return true;
	}

	public function isHidden(){
		return false;
	}
}