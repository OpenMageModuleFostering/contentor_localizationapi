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
    	if($translate) {
    		// Do send to translation
    		
    		// Get source and target
	    	$source = $this->_getRequest()->getPost('source');
	    	$targets = $this->_getRequest()->getPost('targets');
	    	
	    	// Check if source in targets?
	    	if(!count($targets)) {
	    		Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>No target selected'));
    		} elseif(in_array($source, $targets)) {
	    		Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>Source language in targets'));
	    	} else {
				// Get all data, based on settings
	    		$product = $observer->getEvent()->getProduct();
	    		$sku = $product->getSku();
				
	    		$fieldlist['localizable'] = explode(',',Mage::getStoreConfig('contentor_options/contentor_fields/contentor_localizable_input'));
	    		$fieldlist['context'] = explode(',',Mage::getStoreConfig('contentor_options/contentor_fields/contentor_context_input'));
	    		$requiredFields = explode(',',Mage::getStoreConfig('contentor_options/contentor_fields/contentor_required_input'));

	    		$message = array();
	    		foreach($targets as $targetid) {
	    			$data[$targetid]['language']['source'] = $source;
	    			$target = str_replace('_', '-', Mage::getStoreConfig('general/locale/code', $targetid));
	    			$data[$targetid]['language']['target'] = $target;
	    			// Add SKU as internal field
	    			$data[$targetid]['fields'][] = array('id' => 'sku',
		    											 'type' => 'internal',
		    											 'data' => 'string',
		    											 'value' => $sku);
	    			
		    		foreach($fieldlist as $fieldtype => $fields) {
		    			foreach($fields as $field) {
		    				$func = 'get' . $field;
		    				$value = $product->$func();
		    				if(!empty($value)) {
			    				$data[$targetid]['fields'][] = array('id' => $field,
			    												 'type' => $fieldtype,
			    												 'data' => 'html:relaxed',
			    												 'value' => $value);
		    				}
		    				
		    				// Do some check if empty, localizable fields not empty?
		    				if(in_array($field, $requiredFields) && empty($value)) {
		    					$message[] = 'Required field [' . $field . '] empty.';
		    				}
		    			}
		    		}
	    		}
		        if(count($message)) {
		        	// If data missing, throw exception
		        	$message = implode('<br>', $message);
		        	Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>' . $message));

		        } else {
		        	// Everyting OK? Do your magic here!
		        	require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
		        	$result = ContentorAPI::sendToLocalization($sku,$data);
		        	if($result !== true) {
		        		Mage::throwException(Mage::helper('adminhtml')->__($result));
		        	} else {
		        		return true;
		        	}
		        }
	    	}
    	} else {
    		// No translation to be sent, just move along.
    		return true;
    	}
    }
}