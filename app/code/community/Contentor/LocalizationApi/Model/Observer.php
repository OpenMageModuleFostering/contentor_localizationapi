<?php
class Contentor_LocalizationApi_Model_Observer
{
	protected function _getRequest()
	{
		return Mage::app()->getRequest();
	}
	
    public function sendToLocalization(Varien_Event_Observer $observer)
    {
    	$translate = $this->_getRequest()->getPost('translate');
    	// Check attribute flag if send instead of checkbox for seamless send when scripting in products
    	if($translate) {
    		// Do send to translation
    		
    		// Get source and target
	    	$sourceLocale =  str_replace('_', '-', $this->_getRequest()->getPost('source'));
	    	$targetIDs = $this->_getRequest()->getPost('targets');
	    	foreach($targetIDs as $targetID) {
	    		$targets[$targetID] = str_replace('_', '-', Mage::getStoreConfig('general/locale/code', $targetID));
	    	}
	    	$product = $observer->getEvent()->getProduct();
	    	$sku = $product->getSku();
	    	
	    	// Check if source in targets?
	    	if(!count($targets)) {
	    		Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>No target selected'));
    		} elseif(in_array($sourceLocale, $targets)) {
	    		Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>Source language in targets'));
	    	} else {
	    		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
	    		// Get fields
	    		if($fields = ContentorAPI::getFieldData($product)) {
	    			// Then Loop targets and send
	    			foreach($targets as $targetID => $targetLocale) {
	    				$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields);
	    				if($contentorID = ContentorAPI::send($request)) {
	    					if(!ContentorAPI::logSent($contentorID, $sku, $sourceLocale, $targetLocale, $targetID, $product)) {
	    						return false;
	    					}
	    				} else {
	    					return false;
	    				}
	    			}
	    		} else {
	    			return false;
	    		}
	    	}
    	} else {
    		// No translation to be sent, just move along.
    		return true;
    	}
    }
    
    public function categorySendToLocalization(Varien_Event_Observer $observer)
    {
    	$translate = $this->_getRequest()->getPost('translate');
    	// Check attribute flag if send instead of checkbox for seamless send when scripting in products
    	if($translate) {
    		// Get source and target
    		$sourceLocale =  str_replace('_', '-', $this->_getRequest()->getPost('source'));
    		$targetIDs = $this->_getRequest()->getPost('targets');
    		foreach($targetIDs as $targetID) {
    			$targets[$targetID] = str_replace('_', '-', Mage::getStoreConfig('general/locale/code', $targetID));
    		}
    		
    		$category = $observer->getEvent()->getCategory();
    		$categoryID = $category->getId();
    		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
    		// OK, lets go!
    		
    		// Check if source in targets?
	    	if(!count($targets)) {
	    		Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>No target selected'));
    		} elseif(in_array($sourceLocale, $targets)) {
	    		Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>Source language in targets'));
	    	} else {
	    		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
	    		// Get fields
	    		if($fields = ContentorAPI::getFieldData($category, true)) {
	    			// Then Loop targets and send
	    			foreach($targets as $targetID => $targetLocale) {
	    				$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields);
	    				if($contentorID = ContentorAPI::send($request)) {
	    					if(!ContentorAPI::logSentCategory($contentorID, $categoryID, $sourceLocale, $targetLocale, $targetID)) {
	    						return false;
	    					}
	    				} else {
	    					return false;
	    				}
	    			}
	    		} else {
	    			return false;
	    		}
	    	}
    	} else {
    		// No translation to be sent, just move along.
    		return true;
    	}
    }
    
    public function multiSendToLocalization(Varien_Event_Observer $observer)
    {
    	$block = $observer->getEvent()->getBlock();
    	$block->getMassactionBlock()->addItem('sendbulk', array(
    			'label'=> Mage::helper('catalog')->__('Send for Localization'),
    			'url'  => $block->getUrl('adminhtml/contentor/sendbulk')
    	));
    }
    
    public function addCustomCategoryTab(Varien_Event_Observer $observer)
    {
    	/** @var Mage_Adminhtml_Block_Catalog_Category_Tabs $tabsBlock */
    	$tabsBlock = $observer->getTabs();
    
    	if (!$tabsBlock) {
    		return;
    	}
    
    	/** @var Mage_Catalog_Model_Category $category */
    	$category = Mage::registry('current_category');

    	/**
    	 * Conditional code if you do not want to display custom tab
    	 * for root level categories.
    	 */
    	if (!$category || $category->getLevel() < 2) {
    		return;
    	}
    
    	/** @var ArchApps_CustomTabs_Helper_Data $helper */
    	$helper = Mage::helper('Contentor_LocalizationApi');
    
    	$tabsBlock->addTab('contentor_category_localization', array(
    			'label' => $helper->__('Contentor Localization'),
    			'content' => $tabsBlock->getLayout()->createBlock(
    					'Contentor_LocalizationApi/adminhtml_tabs_categories_localizationtab',
    					'contentor_category_localization.category.tab'
    					)->toHtml(),
    	));
    	
		/*
    	$tabsBlock->addTab('contentor_category_content_creation', array(
    			'label' => $helper->__('Contentor Content Creation'),
    			'content' => $tabsBlock->getLayout()->createBlock(
    					'Contentor_LocalizationApi/adminhtml_tabs_categories_contentcreationtab',
    					'contentor_category_content_creation.category.tab'
    					)->toHtml(),
    	));
		*/
    }
}