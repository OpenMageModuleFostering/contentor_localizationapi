<?php
class ContentorAPI
{
	public function sendToLocalization($sku,$objects)
	{
		foreach($objects as $store_id => $object) {
			$result = ContentorAPI::send($object);
			// Put data in database
			if($result) {
				$resource = Mage::getSingleton('core/resource') or die('No recource');
				
				$writeConnection = $resource->getConnection('core_write') or die('No connection');
				$table = Mage::getConfig()->getTablePrefix()."contentor_products";
				$query = "INSERT INTO " . $table . "
						  (contentor_id, sku, source_locale, target_locale, target_store)
						  VALUES
						  ('" . $result . "',
						  		'" . $sku . "',
						  		'" . $object['language']['source'] . "',
						  		'" . $object['language']['target'] . "',
						  		'" . $store_id . "')";
	
				if(!$writeConnection->query($query)) {
					Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing product to DB'));
				} else {
					$table = Mage::getConfig()->getTablePrefix()."contentor_status";
					$query = "INSERT INTO " . $table . "
								(contentor_id, status)
								VALUES
								('" . $result . "',
								'Sent for localisation to: " . $object['language']['target'] . "')";
					if(!$writeConnection->query($query)) {
						Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing status to DB'));
					}
				}
			} else {
				Mage::throwException(Mage::helper('adminhtml')->__('Error: Contentor API!'));
			}
		}
		return true;
	}
	
	protected function send($object) {
		$token = ContentorAPI::getToken();
		$url = ContentorAPI::getURL();
		
		// Curl the object and get result
		$entry = json_encode($object);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $entry);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($response = curl_exec($ch)) {
			$response = json_decode($response,true);
			$contentor_id = $response['id'];
			curl_close($ch);
			return $contentor_id;
		} else {
			curl_close($ch);
			return false;
		}
		
	}

	public function receive($date) {
		$token = ContentorAPI::getToken();
		$url = ContentorAPI::getURL();
		$url .= '?state=completed&from=' . $date;
		// Result
		$response = ContentorAPI::getCurl($token, $url);
		$result = json_decode($response);
		
		if($result->total > 0) {
			// Process results from page 1
			foreach($result->requests as $request) {
				ContentorAPI::putProduct($request);
			}
			if($result->pages > 1) {
				for($i = 2;$i <= $result->pages;$i++) {
					// Curl page number $i 
					$pageUrl = $url . '&page=' . $i;
					$pageResponse = ContentorAPI::getCurl($token, $pageUrl);
					$pageResult = json_decode($pageResponse);
					foreach($pageResult->requests as $request) {
						ContentorAPI::putProduct($request);
					}
				}
				return true;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	
	protected function putProduct($object) {
		// Get sku and target store from Magento DB
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		$query = "SELECT sku, target_store FROM `" . $table . "` WHERE `contentor_id` = '" . $object->id . "' AND completed_time IS NULL";
		if($productInfo = $readConnection->fetchRow($query)) {
			
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$productInfo['sku']);
			if($product) {
				// setData on product depending on licalizationsfields received on the right store
				Mage::app()->setCurrentStore($productInfo['target_store']);
				foreach($object->fields as $field) {
					if($field->type == 'localizable') {
						// Update
						$attribute = $field->id;
						$func = 'set' . $attribute;
						$product->$func($field->value);					
					}
				}
				if(Mage::getStoreConfig('contentor_options/contentor_import/contentor_import_status')) {
					// Set product enabled
					$product->setStatus(1);
				}
				$product->save();
				// Write notice in Magento DB, set completed date in DB
				$resource = Mage::getSingleton('core/resource') or die('No recource');
				$writeConnection = $resource->getConnection('core_write') or die('No connection');
				$table = Mage::getConfig()->getTablePrefix()."contentor_products";
				$query = "UPDATE " . $table . " SET `completed_time` = '" . $object->completed ."' WHERE `contentor_id` = '" . $object->id . "'";
				if(!$writeConnection->query($query)) {
					// Set notice that something didn't work!
					return false;
				}
				$resource = Mage::getSingleton('core/resource') or die('No recource');
				$writeConnection = $resource->getConnection('core_write') or die('No connection');;
				$table = Mage::getConfig()->getTablePrefix()."contentor_status";
				$query = "INSERT INTO " . $table . " (`contentor_id`, `status`) VALUES ('" . $object->id . "', 'Received as completed, completion time: " . $object->completed . "')";
				$writeConnection->query($query);
			}
		} else {
			return true;
		}
	}
	
	public function testAuth() {
		$url = ContentorAPI::getURL(true);
		$token = ContentorAPI::getToken();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token, 'Accept: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$info = json_decode($response);
		if(isset($info->companyName)) {
			print $info->companyName;
		} else {
			print 'Unauthorized'; 
		}
		
	}
	
	protected function getCurl($token, $url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token, 'Accept: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	protected function getURL($auth=false) {
		$dev = ContentorAPI::getDEV();
		if($dev) {
			if($auth) {
				return 'http://api.dev.contentor.com:8080/v1/auth';
			} else {
				return 'http://api.dev.contentor.com:8080/v1/content';
			}
		} else {
			if($auth) {
				return 'https://api.contentor.com/v1/auth';
			} else {
				return 'https://api.contentor.com/v1/content';
			}
		}
	}
	
	protected function getToken() {
		return Mage::getStoreConfig('contentor_options/contentor_token/contentor_token_input');
	}
	
	private function getDEV() {
		return false;
	}
}