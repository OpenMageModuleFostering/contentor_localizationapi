<?php
class ContentorAPI
{
	public static function getFieldData($product, $category=false) {
		$messages = array();
		if(!$category) {
			$sku = $product->getSku();
			$productID = $product->getId();
			$fieldArray = unserialize(Mage::getStoreConfig('contentor_options/contentor_fields/contentor_fields_input', Mage::app()->getStore()));
			$fields[] = array('id' => 'auto_sku',
					'type' => 'internal',
					'data' => 'string',
					'value' => $sku);
			
			foreach($fieldArray as $field) {
				$textLocale = 	Mage::getStoreConfig('general/locale/code', $field['store']);
				if($field['attribute'] == 'productURL') {
					$value = $product->getProductUrl();
					$name = 'Product URL';
				} else {
					$value 		=	Mage::getResourceModel('catalog/product')->getAttributeRawValue($productID, $field['attribute'], $field['store']);
					$name 		=	$product->getResource()->getAttribute($field['attribute'])->getFrontendLabel();
					$name		.=	' [' . $textLocale . ']';
				}
			
				if(!isset($n[$field['attribute']])) {
					$n[$field['attribute']] = 1;
				} else {
					$n[$field['attribute']]++;
				}
				$id = $field['attribute'] . '_' . sprintf("%03d", $n[$field['attribute']]);
			
				if($value != '') {
					$fields[] = array('id'=>$id,
							'name'=>$name,
							'type'=>$field['type'],
							'data'=>$field['data'],
							'value'=>$value);
				} else {
					if(isset($fields['required'])) {
						$messages[] = 'Required field: [' . $field['attribute'] . '] empty';
					}
				}
			}
			
		} else {
			$categoryID = $product->getId();
			$fieldArray = unserialize(Mage::getStoreConfig('contentor_options/contentor_fields/contentor_category_fields_input', Mage::app()->getStore()));
			
			$fields[] = array('id' => 'auto_id',
					'type' => 'internal',
					'data' => 'string',
					'value' => $categoryID);
			
			// Loop and add fields as above
			foreach($fieldArray as $field) {
				$textLocale = 	Mage::getStoreConfig('general/locale/code', $field['store']);
				if($field['attribute'] == 'categoryURL') {
					$value = $product->getCategoryUrl();
					$name = 'Category URL';
				} else {
					// Mage::getModel('catalog/category')->load($categoryID)->getData();
					$value 		= 	Mage::getModel('catalog/category')->setStoreId($field['store'])->load($categoryID)->getData($field['attribute']);
					$name 		=	Mage::getResourceModel('catalog/category')->getAttribute($field['attribute'])->getFrontendLabel();
					$name		.=	' [' . $textLocale . ']';
				}
					
				if(!isset($n[$field['attribute']])) {
					$n[$field['attribute']] = 1;
				} else {
					$n[$field['attribute']]++;
				}
				$id = $field['attribute'] . '_' . sprintf("%03d", $n[$field['attribute']]);
					
				if($value != '') {
					$fields[] = array('id'=>$id,
							'name'=>$name,
							'type'=>$field['type'],
							'data'=>$field['data'],
							'value'=>$value);
				} else {
					if(isset($fields['required'])) {
						$messages[] = 'Required field: [' . $field['attribute'] . '] empty';
					}
				}
			}
		}
		if(!count($messages)) {
			return $fields;
		} else {
			$message = implode('<br>', $messages);
			Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>' . $message));
			return false;
		}
	}
	
	public static function createRequest($sourceLocale, $targetLocale, $fields) {
		$data['language']['source'] = $sourceLocale;
		$data['language']['target'] = $targetLocale;
		$data['fields'] = $fields;
		return $data;
	}
	
	public static function logSent($contentorID, $sku, $sourceLocale, $targetLocale, $targetID, $product) {
		$resource = Mage::getSingleton('core/resource') or die('No recource');
		
		$writeConnection = $resource->getConnection('core_write');
		$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		$query = "INSERT INTO " . $table . "
						  (contentor_id, sku, source_locale, target_locale, target_store, sent_time)
						  VALUES
						  (:contentor_id, :sku, :source_locale, :target_locale, :target_store, NOW())";
		$binds = array(
				'contentor_id'	=> $contentorID,
				'sku'			=> $sku,
				'source_locale' => $sourceLocale,
				'target_locale' => $targetLocale,
				'target_store'  => $targetID,
		);
		
		if(!$writeConnection->query($query, $binds)) {
			Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing product to DB'));
			return false;
		} else {
			$table = Mage::getConfig()->getTablePrefix()."contentor_status";
			$query = "INSERT INTO " . $table . "
								(contentor_id, status, status_time)
								VALUES
								(:contentor_id, :status, NOW())";
			$binds = array(
				'contentor_id'	=> $contentorID,
				'status'		=> 'Sent for localisation to: ' . $targetLocale,
			);
			if(!$writeConnection->query($query, $binds)) {
				Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing status to DB'));
			} else {
				// Unflag the product
				$productID = $product->getId();
				$attribute = 'contentor_localize_me';
				if(Mage::getResourceModel('catalog/product')->getAttributeRawValue($productID, $attribute, $targetID)) {
					Mage::app()->setCurrentStore($targetID);
					$product->setData($attribute, 0);
					$product->getResource()->saveAttribute($product, $attribute);
				}
				return true;
			}
		}
	}
	
	public static function logSentCategory($contentorID, $cat_id, $sourceLocale, $targetLocale, $targetID) {
		$resource = Mage::getSingleton('core/resource') or die('No recource');
	
		$writeConnection = $resource->getConnection('core_write');
		$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
		$query = "INSERT INTO " . $table . "
						  (contentor_id, cat_id, source_locale, target_locale, target_store, sent_time)
						  VALUES
						  (:contentor_id, :sku, :source_locale, :target_locale, :target_store, NOW())";
		$binds = array(
				'contentor_id'	=> $contentorID,
				'sku'			=> $cat_id,
				'source_locale' => $sourceLocale,
				'target_locale' => $targetLocale,
				'target_store'  => $targetID,
		);
	
		if(!$writeConnection->query($query, $binds)) {
			Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing product to DB'));
			return false;
		} else {
			$table = Mage::getConfig()->getTablePrefix()."contentor_status";
			$query = "INSERT INTO " . $table . "
								(contentor_id, status, status_time)
								VALUES
								(:contentor_id, :status, NOW())";
			$binds = array(
					'contentor_id'	=> $contentorID,
					'status'		=> 'Sent for localisation to: ' . $targetLocale,
			);
			if(!$writeConnection->query($query, $binds)) {
				Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing status to DB'));
			} else {
				return true;
			}
		}
	}
	
	public static function send($request) {
		$token = ContentorAPI::getToken();
		$url = ContentorAPI::getURL();
		
		// Curl the object and get result
		$entry = json_encode($request);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $entry);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($response = curl_exec($ch)) {
			$response = json_decode($response,true);
			$contentorID = $response['id'];
			curl_close($ch);
			return $contentorID;
		} else {
			curl_close($ch);
			return false;
		}

	}

	public static function receive($date) {
		$token = ContentorAPI::getToken();
		$url = ContentorAPI::getURL();
		$url .= '?state=completed&from=' . $date;
		// Result
		$response = ContentorAPI::getCurl($token, $url);
		$result = json_decode($response);
		
		if($result->total > 0) {
			// Process results from page 1
			foreach($result->requests as $request) {
				if(ContentorAPI::getType($request->id) == 'product') {
					ContentorAPI::putProduct($request);
				} else if(ContentorAPI::getType($request->id) == 'category') {
					ContentorAPI::putCategory($request);
				} else {
					
				}
			}
			if($result->pages > 1) {
				for($i = 2;$i <= $result->pages;$i++) {
					// Curl page number $i 
					$pageUrl = $url . '&page=' . $i;
					$pageResponse = ContentorAPI::getCurl($token, $pageUrl);
					$pageResult = json_decode($pageResponse);
					foreach($pageResult->requests as $request) {
						if(ContentorAPI::getType($request->id) == 'product') {
							ContentorAPI::putProduct($request);
						} else if(ContentorAPI::getType($request->id) == 'category') {
							ContentorAPI::putCategory($request);
						} else {
							
						}
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
	
	protected static function putProduct($object) {
		// Get sku and target store from Magento DB
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		$query = "SELECT sku, target_store FROM `" . $table . "` WHERE `contentor_id` = :contentor_id AND completed_time IS NULL";
		$binds = array('contentor_id' => $object->id);
		if($productInfo = $readConnection->fetchRow($query, $binds)) {
			
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$productInfo['sku']);
			if($product) {
				// setData on product depending on licalizationsfields received on the right store
				Mage::app()->setCurrentStore($productInfo['target_store']);
				foreach($object->fields as $field) {
					if($field->type == 'localizable') {
						// Update
						$attribute = substr($field->id, 0, -4);
						$product->setData($attribute, $field->value);
						$product->getResource()->saveAttribute($product, $attribute);
					}
				}
				if(Mage::getStoreConfig('contentor_options/contentor_automation/contentor_import_status')) {
					// Set product enabled
					$product->setStatus(1);
				}
				$product->save();
				// Write notice in Magento DB, set completed date in DB
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				$table = Mage::getConfig()->getTablePrefix()."contentor_products";
				$query = "UPDATE " . $table . " SET `completed_time` = :completed_time WHERE `contentor_id` = :contentor_id";
				$binds = array(
						'completed_time'	=> $object->completed,
						'contentor_id'		=> $object->id,
				);
				if(!$writeConnection->query($query, $binds)) {
					// Set notice that something didn't work!
					return false;
				}
				
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				$table = Mage::getConfig()->getTablePrefix()."contentor_status";
				$query = "INSERT INTO " . $table . " (`contentor_id`, `status`, `status_time`) 
						VALUES (:contentor_id, :status, NOW())";
				$binds = array(
						'contentor_id'	=> $object->id,
						'status'		=> 'Received as completed for ' . $object->language->target . ', completion time: ' . date("Y-m-d H:i:s", strtotime($object->completed)),
				);
				$writeConnection->query($query, $binds);
			}
		} else {
			// Not waiting for a product with this id
			return false;
		}
	}
	
	protected static function putCategory($object)
	{
		// Get id and target store from Magento DB
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
		$query = "SELECT cat_id, target_store FROM `" . $table . "` WHERE `contentor_id` = :contentor_id AND completed_time IS NULL";
		$binds = array('contentor_id' => $object->id);
		if($categoryInfo = $readConnection->fetchRow($query, $binds)) {
				
			$catagory = Mage::getSingleton('catalog/category')->setId($categoryInfo['cat_id']);
			if($catagory) {
				// setData on product depending on licalizationsfields received on the right store
				Mage::app()->setCurrentStore($categoryInfo['target_store']);
				foreach($object->fields as $field) {
					if($field->type == 'localizable') {
						// Update
						$attribute = substr($field->id, 0, -4);
						$catagory->setData($attribute, $field->value);
						$catagory->getResource()->saveAttribute($catagory, $attribute);
					}
				}

				// Write notice in Magento DB, set completed date in DB
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
				$query = "UPDATE " . $table . " SET `completed_time` = :completed_time WHERE `contentor_id` = :contentor_id";
				$binds = array(
						'completed_time'	=> $object->completed,
						'contentor_id'		=> $object->id,
				);
				if(!$writeConnection->query($query, $binds)) {
					// Set notice that something didn't work!
					return false;
				}
		
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				$table = Mage::getConfig()->getTablePrefix()."contentor_status";
				$query = "INSERT INTO " . $table . " (`contentor_id`, `status`, `status_time`)
						VALUES (:contentor_id, :status, NOW())";
				$binds = array(
						'contentor_id'	=> $object->id,
						'status'		=> 'Received as completed for ' . $object->language->target . ', completion time: ' . date("Y-m-d H:i:s", strtotime($object->completed)),
				);
				$writeConnection->query($query, $binds);
			}
		} else {
			// Not waiting for a product with this id
			return false;
		}
	}
	
	public static function testAuth() {
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
	
	protected static function getCurl($token, $url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token, 'Accept: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	protected static function getURL($auth=false) {
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
	
	protected static function getToken() {
		return Mage::getStoreConfig('contentor_options/contentor_token/contentor_token_input');
	}
	
	protected static function getType($id) {
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$producttable = Mage::getConfig()->getTablePrefix()."contentor_products";
		$categorytable = Mage::getConfig()->getTablePrefix()."contentor_categories";
		$query = "SELECT CASE WHEN (EXISTS (SELECT `contentor_id` FROM `" . $producttable . "` WHERE `contentor_id` = :contentor_id)) THEN 'product' WHEN (EXISTS (SELECT `contentor_id` FROM `" . $categorytable . "` WHERE `contentor_id` = :contentor_id)) THEN 'category' ELSE 'nope' END AS 'type'";
		$binds = array('contentor_id' => $id);
		$return = $readConnection->fetchOne($query, $binds);
		return $return;
	}
	
	private static function getDEV() {
		return false;
	}
}