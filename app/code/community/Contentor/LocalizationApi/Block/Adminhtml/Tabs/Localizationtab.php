<?php 
class Contentor_LocalizationApi_Block_Adminhtml_Tabs_Localizationtab extends Mage_Adminhtml_Block_Widget
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('Contentor/localizationtab.phtml');
	}
}