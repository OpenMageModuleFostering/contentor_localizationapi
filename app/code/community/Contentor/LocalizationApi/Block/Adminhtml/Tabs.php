<?php
class Contentor_LocalizationApi_Block_Adminhtml_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
	private $parent;

	protected function _prepareLayout()
	{
		//get all existing tabs
		$this->parent = parent::_prepareLayout();
		//add new tab
		$this->addTab('tabid', array(
						'label'     => Mage::helper('catalog')->__('Contentor API'),
						'content'   => $this->getLayout()
				->createBlock('LocalizationApi/adminhtml_tabs_localizationtab')->toHtml(),
		));
		return $this->parent;
	}
}