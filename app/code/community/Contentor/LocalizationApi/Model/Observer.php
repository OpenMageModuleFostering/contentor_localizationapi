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
	    		$contextValue = $this->_getRequest()->getPost('context');
	    		if(!empty($contextValue)) {
	    			// Now add some exra fields if needed
	    			$extraField['id'] = 'extraField';
	    			$extraField['name'] = $this->_getRequest()->getPost('contextname');
	    			$extraField['type'] = 'context';
	    			$extraField['data'] = 'string';
	    			$extraField['value'] = $contextValue;
	    		} else {
	    			$extraField = false;
	    		}
	    		// Get fields
	    		if($fields = ContentorAPI::getFieldData($product, 'product', $extraField)) {
	    			// Then Loop targets and send
	    			foreach($targets as $targetID => $targetLocale) {
	    				// Find out type and pass it along
	    				$type = 'standard';
	    				$prevID = false;
						// TODO: check settings for versioning
	    				if(Mage::getStoreConfig('contentor_versioning/contentor_versioning_activation/contentor_versioning_active', Mage::app()->getStore()) && Mage::getStoreConfig('contentor_versioning/contentor_versioning_sections/contentor_versioning_products', Mage::app()->getStore())) {
		    				$resource = Mage::getSingleton('core/resource');
		    				$readConnection = $resource->getConnection('core_read');
		    				
		    				$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		    				$query = "SELECT `contentor_id` FROM `" . $table . "` WHERE `sku` = :sku AND `target_locale` = :target_locale AND `source_locale` = :source_locale ORDER BY `sent_time` DESC";
		    				$binds = array('sku' 			=> $sku,
		    							   'target_locale'	=> $targetLocale,
		    							   'source_locale'  => $sourceLocale
		    				);
		    				$return = $readConnection->fetchOne($query, $binds);
		    				
		    				if($return) {
		    					$type = 'update';
		    					$prevID = $return;
		    				}
	    				}
	    				$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields, $type, $prevID);
	    				if($contentorID = ContentorAPI::send($request)) {
	    					if(!ContentorAPI::logSent($contentorID, $sku, $sourceLocale, $targetLocale, $targetID, $product, $type)) {
	    						return false;
	    					}
	    				} else {
	    					Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>Error!'));
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
	    		$contextValue = $this->_getRequest()->getPost('context');
	    		if(!empty($contextValue)) {
	    			// Now add some exra fields if needed
	    			$extraField['id'] = 'extraField';
	    			$extraField['name'] = $this->_getRequest()->getPost('contextname');
	    			$extraField['type'] = 'context';
	    			$extraField['data'] = 'string';
	    			$extraField['value'] = $contextValue;
	    		} else {
	    			$extraField = false;
	    		}
	    		// Get fields
	    		if($fields = ContentorAPI::getFieldData($category, 'category', $extraField)) {
	    			// Then Loop targets and send
	    			foreach($targets as $targetID => $targetLocale) {
	    				// Find out type and pass it along
	    				$type = 'standard';
	    				$prevID = false;
	    				// TODO: check settings for versioning
	    				if(Mage::getStoreConfig('contentor_versioning/contentor_versioning_activation/contentor_versioning_active', Mage::app()->getStore()) && Mage::getStoreConfig('contentor_versioning/contentor_versioning_sections/contentor_versioning_categories', Mage::app()->getStore())) {
	    					$resource = Mage::getSingleton('core/resource');
	    					$readConnection = $resource->getConnection('core_read');
	    				
	    					$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
	    					$query = "SELECT `contentor_id` FROM `" . $table . "` WHERE `cat_id` = :cat_id AND `target_locale` = :target_locale AND `source_locale` = :source_locale ORDER BY `sent_time` DESC";
	    					$binds = array('cat_id'	=> $categoryID,
	    							'target_locale'	=> $targetLocale,
	    							'source_locale' => $sourceLocale
	    					);
	    					$return = $readConnection->fetchOne($query, $binds);
	    				
	    					if($return) {
	    						$type = 'update';
	    						$prevID = $return;
	    					}
	    				}
	    				$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields, $type, $prevID);
	    				if($contentorID = ContentorAPI::send($request)) {
	    					if(!ContentorAPI::logSentCategory($contentorID, $categoryID, $sourceLocale, $targetLocale, $targetID, $type)) {
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
    
    public function cmspageSendToLocalization(Varien_Event_Observer $observer)
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
    		
    		$pageId = $id = $this->_getRequest()->getParam('page_id');
    		$cmsPage = Mage::getModel('cms/page')->load($pageId,'page_id');
    		//$pageId = $cmsPage->getId();
    		require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
    		// OK, lets go!
    	
    		// Check if source in targets?
    		if(!count($targets)) {
    			Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>No target selected'));
    		} elseif(in_array($sourceLocale, $targets)) {
    			Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>Source language in targets'));
    		} else {
    			require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
    			$contextValue = $this->_getRequest()->getPost('context');
    			if(!empty($contextValue)) {
    				// Now add some exra fields if needed
    				$extraField['id'] = 'extraField';
    				$extraField['name'] = $this->_getRequest()->getPost('contextname');
    				$extraField['type'] = 'context';
    				$extraField['data'] = 'string';
    				$extraField['value'] = $contextValue;
    			} else {
    				$extraField = false;
    			}
    			// Get fields
    			if($fields = ContentorAPI::getFieldData($cmsPage, 'cmspage', $extraField)) {
    				// Then Loop targets and send
    				foreach($targets as $targetID => $targetLocale) {
    					// Find out type and pass it along
    					$type = 'standard';
    					$prevID = false;
    					// TODO: check settings for versioning
    					if(Mage::getStoreConfig('contentor_versioning/contentor_versioning_activation/contentor_versioning_active', Mage::app()->getStore()) && Mage::getStoreConfig('contentor_versioning/contentor_versioning_sections/contentor_versioning_cmspages', Mage::app()->getStore())) {
    						$resource = Mage::getSingleton('core/resource');
    						$readConnection = $resource->getConnection('core_read');
    						 
    						$table = Mage::getConfig()->getTablePrefix()."contentor_cmspages";
    						$query = "SELECT `contentor_id` FROM `" . $table . "` WHERE `page_id` = :page_id AND `target_locale` = :target_locale AND `source_locale` = :source_locale ORDER BY `sent_time` DESC";
    						$binds = array('page_id'	=> $pageId,
    								'target_locale'	=> $targetLocale,
    								'source_locale' => $sourceLocale
    						);
    						$return = $readConnection->fetchOne($query, $binds);
    						 
    						if($return) {
    							$type = 'update';
    							$prevID = $return;
    						}
    					}
    					$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields, $type, $prevID);
    					if($contentorID = ContentorAPI::send($request)) {
    						if(!ContentorAPI::logSentCmspage($contentorID, $pageId, $sourceLocale, $targetLocale, $targetID, $type)) {
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
    }
}