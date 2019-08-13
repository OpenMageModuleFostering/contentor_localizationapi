<?php
class ContentorAPI
{
	public static function getFieldData($entity, $entityType, $extraField=false) {
		$messages = array();
		if($entityType == 'product') {
			$sku = $entity->getSku();
			$productID = $entity->getId();
			$fieldArray = unserialize(Mage::getStoreConfig('contentor_options/contentor_fields/contentor_fields_input', Mage::app()->getStore()));
			if(is_array($fieldArray)) {
				$fields[] = array('id' => 'auto_sku',
						'type' => 'internal',
						'data' => 'string',
						'value' => $sku);
	
				// Add extra content if sent
				if($extraField) {
					$fields[] = array('id' => 'extraField',
							'name' => $extraField['name'],
							'type' => $extraField['type'],
							'data' => $extraField['data'],
							'value' => $extraField['value']);
				}
	
				foreach($fieldArray as $field) {
					$textLocale = 	Mage::getStoreConfig('general/locale/code', $field['store']);
					$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $field['attribute']);
					$attributeType = $attribute->getFrontendInput();
					
					if($attributeType == 'gallery') {
						// Get value
						$imageProduct = Mage::getModel('catalog/product')->setStoreId($field['store'])->load($productID);
						$images = $imageProduct->getData('media_gallery');
						foreach($images['images'] as $image) {
							$imageFile = $image['file'];
							$value = $image['label'];
							$name = 'Image label [' . $textLocale . ']';
							if(!isset($n[$field['attribute']])) {
								$n[$field['attribute']] = 1;
							} else {
								$n[$field['attribute']]++;
							}
							$id = 'image_' . $imageFile . '_' . sprintf("%03d", $n[$field['attribute']]);
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
					
						if($field['attribute'] == 'productURL') {
							$value = $entity->getProductUrl();
							$name = 'Product URL';
						} else {
							$value 		=	Mage::getResourceModel('catalog/product')->getAttributeRawValue($productID, $field['attribute'], $field['store']);
							$name 		=	$entity->getResource()->getAttribute($field['attribute'])->getFrontendLabel();
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
			} else {
				$messages[] = 'No settings found';
			}
		} else if ($entityType == 'category') {
			$categoryID = $entity->getId();
			$fieldArray = unserialize(Mage::getStoreConfig('contentor_options/contentor_fields/contentor_category_fields_input', Mage::app()->getStore()));
			if(is_array($fieldArray)) {
				$fields[] = array('id' => 'auto_id',
						'type' => 'internal',
						'data' => 'string',
						'value' => $categoryID);
				// Add extra content if sent
				if($extraField) {
					$fields[] = array('id' => 'extraField',
							'name' => $extraField['name'],
							'type' => $extraField['type'],
							'data' => $extraField['data'],
							'value' => $extraField['value']);
				}
				
				// Loop and add fields as above
				foreach($fieldArray as $field) {
					$textLocale = 	Mage::getStoreConfig('general/locale/code', $field['store']);
					if($field['attribute'] == 'categoryURL') {
						$value = $entity->getCategoryUrl();
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
			} else {
				$messages[] = 'No settings found';
			}
			
		} else if($entityType == 'cmspage') {
			$cmsPage = $entity;
			$fieldArray = unserialize(Mage::getStoreConfig('contentor_options/contentor_fields/contentor_cmspage_fields_input', Mage::app()->getStore()));
			if(is_array($fieldArray)) {
				$pageData = $cmsPage->getData();
				$fieldNames = array(
		        	'title' 			=>	'Page title',
		        	'identifier'		=>	'URL key',
		        	'content_heading'	=>	'Content Heading',
		        	'content'			=>	'Content',
		        	'meta_keywords'		=>	'Meta Keywords',
		        	'meta_description'	=>	'Meta Description'
	        	);
				$fields[] = array('id' => 'auto_id',
						'type' => 'internal',
						'data' => 'string',
						'value' => $cmsPage->getId());
				// Add extra content if sent
				if($extraField) {
					$fields[] = array('id' => 'extraField',
							'name' => $extraField['name'],
							'type' => $extraField['type'],
							'data' => $extraField['data'],
							'value' => $extraField['value']);
				}
				foreach($fieldArray as $field) {
					$name = $fieldNames[$field['attribute']];
					$value = $pageData[$field['attribute']];
					
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
				$messages[] = 'No settings found';
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
	
	public static function createRequest($sourceLocale, $targetLocale, $fields, $type, $previd=false) {
		$data['language']['source'] = $sourceLocale;
		$data['language']['target'] = $targetLocale;
		$data['type'] = $type;
		if($previd && $type == 'update') {
			$data['previous'] = $previd;
		}
		$data['fields'] = $fields;
		return $data;
	}
	
	public static function logSent($contentorID, $sku, $sourceLocale, $targetLocale, $targetID, $product, $type) {
		$resource = Mage::getSingleton('core/resource') or die('No recource');
		
		$writeConnection = $resource->getConnection('core_write');
		$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		$query = "INSERT INTO " . $table . "
						  (contentor_id, sku, source_locale, target_locale, target_store, sent_time, state, type)
						  VALUES
						  (:contentor_id, :sku, :source_locale, :target_locale, :target_store, NOW(), 'pending', :type)";
		$binds = array(
				'contentor_id'	=> $contentorID,
				'sku'			=> $sku,
				'source_locale' => $sourceLocale,
				'target_locale' => $targetLocale,
				'target_store'  => $targetID,
				'type'			=> $type,
		);
		if(!$writeConnection->query($query, $binds)) {
			Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing product to DB'));
			return false;
		} else {
			$typeTable = Mage::getConfig()->getTablePrefix()."contentor_type";
			$typeQuery = "INSERT INTO `" . $typeTable . "` (`contentor_id`, `type`) VALUES (:contentor_id, 'product')";
			$typeBinds = array('contentor_id' => $contentorID);
			$writeConnection->query($typeQuery, $typeBinds);
			
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
	
	public static function logSentCategory($contentorID, $cat_id, $sourceLocale, $targetLocale, $targetID, $type) {
		$resource = Mage::getSingleton('core/resource') or die('No recource');
	
		$writeConnection = $resource->getConnection('core_write');
		$table = Mage::getConfig()->getTablePrefix()."contentor_categories";
		$query = "INSERT INTO " . $table . "
						  (contentor_id, cat_id, source_locale, target_locale, target_store, sent_time, state, type)
						  VALUES
						  (:contentor_id, :sku, :source_locale, :target_locale, :target_store, NOW(), 'pending', :type)";
		$binds = array(
				'contentor_id'	=> $contentorID,
				'sku'			=> $cat_id,
				'source_locale' => $sourceLocale,
				'target_locale' => $targetLocale,
				'target_store'  => $targetID,
				'type'			=> $type,
		);
	
		if(!$writeConnection->query($query, $binds)) {
			Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing product to DB'));
			return false;
		} else {
			$typeTable = Mage::getConfig()->getTablePrefix()."contentor_type";
			$typeQuery = "INSERT INTO `" . $typeTable . "` (`contentor_id`, `type`) VALUES (:contentor_id, 'category')";
			$typeBinds = array('contentor_id' => $contentorID);
			$writeConnection->query($typeQuery, $typeBinds);
			
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
	
	public static function logSentCmspage($contentorID, $page_id, $sourceLocale, $targetLocale, $targetID, $type) {
		$resource = Mage::getSingleton('core/resource') or die('No recource');
	
		$writeConnection = $resource->getConnection('core_write');
		$table = Mage::getConfig()->getTablePrefix()."contentor_cmspages";
		$query = "INSERT INTO " . $table . "
						  (contentor_id, page_id, source_locale, target_locale, target_store, sent_time, state, type)
						  VALUES
						  (:contentor_id, :page_id, :source_locale, :target_locale, :target_store, NOW(), 'pending', :type)";
		$binds = array(
				'contentor_id'	=> $contentorID,
				'page_id'		=> $page_id,
				'source_locale' => $sourceLocale,
				'target_locale' => $targetLocale,
				'target_store'  => $targetID,
				'type'			=> $type,
		);

		if(!$writeConnection->query($query, $binds)) {
			Mage::throwException(Mage::helper('adminhtml')->__('Error: Writing product to DB'));
			return false;
		} else {
			$typeTable = Mage::getConfig()->getTablePrefix()."contentor_type";
			$typeQuery = "INSERT INTO `" . $typeTable . "` (`contentor_id`, `type`) VALUES (:contentor_id, 'cmspage')";
			$typeBinds = array('contentor_id' => $contentorID);
			$writeConnection->query($typeQuery, $typeBinds);
				
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
		$token = self::getToken();
		$url = self::getURL();
		
		$request = Mage::getModel('Contentor_LocalizationApi/Hooks')->beforeSend($request);
		// Curl the object and get result
		$entry = json_encode($request);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $entry);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($response = curl_exec($ch)) {
			$response = json_decode($response, true);
			if(!isset($response['id'])) {
				Mage::log($response, null, 'contentor.log');
			}
			$contentorID = $response['id'];
			curl_close($ch);
			return $contentorID;
		} else {
			Mage::log(curl_getinfo($ch), null, 'contentor.log');
			curl_close($ch);
			return false;
		}

	}

	public static function receive($date) {
		$token = self::getToken();
		$url = self::getURL();
		// New url with new uery for modified!
		$args = array('criteria'=>array(array('type'=>'modified','criteria'=>array('from'=>$date,'to'=>'tomorrow'))),'sortBy'=>array('created:desc'));
		$query = array('query' => json_encode($args));
		$queryParams = http_build_query($query);
		$url .= '?' . $queryParams;
		// Result
		$result = self::getCurl($token, $url);
		if($result->total > 0) {
			// Process results from page 1
			foreach($result->requests as $request) {
				// This is a request, so fint out what happend with it, pending, confirmed, canceled or completed
				$requestId = $request->id;
				$newState = $request->state;
				$currentStatus = self::getRequestStatus($request->id);
				if($currentStatus['state'] != $newState && $currentStatus['state']) {
					// OK, I have to do stuff!
					if($newState == 'completed') {
						// Put product
						if($currentStatus['type'] == 'product') {
							self::putProduct($request);
						} else if($currentStatus['type'] == 'category') {
							self::putCategory($request);
						} else if($currentStatus['type'] == 'cmspage') {
							self::putCmspage($request);
						} else {
							Mage::log($id . ' had no type to be found.', null, 'contentor.log');
						}
					} else {
						// The state changed, so I have to log it!
						self::logStateChange($request, $currentStatus['type']);						
					}
				}
			}
			if($result->pages > 1) {
				for($i = 2;$i <= $result->pages;$i++) {
					// Curl page number $i
					$pageUrl = $url . '&page=' . $i;
					$pageResult = self::getCurl($token, $pageUrl);
					foreach($pageResult->requests as $request) {
						// This is a request, so fint out what happend with it, pending, confirmed, canceled or completed
						$requestId = $request->id;
						$newState = $request->state;
						$currentStatus = self::getRequestStatus($request->id);
						if($currentStatus['state'] != $newState && $currentStatus['state']) {
							// OK, I have to do stuff!
							if($newState == 'completed') {
								// Put product
								if($currentStatus['type'] == 'product') {
									self::putProduct($request);
								} else if($currentStatus['type'] == 'category') {
									self::putCategory($request);
								} else if($currentStatus['type'] == 'cmspage') {
									self::putCmspage($request);
								} else {
									Mage::log($id . ' had no type to be found.', null, 'contentor.log');
								}
							} else {
								// The state changed, so I have to log it!
								self::logStateChange($request, $currentStatus['type']);
							}
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

	public static function fetchSingle($id, $type) {
		// Get info from db wcontentorid = $id
		
		$token = ContentorAPI::getToken();
		$url = ContentorAPI::getURL();
		$url .= '/request/' . $id;

		switch ($type) {
			case 'product':
				$tableName = "contentor_products";
				$putFunction = 'putProduct';
				break;
			case 'category':
				$tableName = "contentor_categories";
				$putFunction = 'putCategory';
				break;
			case 'cmspage':
					$tableName = "contentor_cmspages";
					$putFunction = 'putCmspage';
					break;
		}

		$response = self::getCurl($token, $url);
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix() . $tableName;
		$query = "SELECT `contentor_id`, `completed_time`, `state` FROM `" . $table . "` WHERE `contentor_id` = :contentor_id";
		$binds = array('contentor_id'=>$id);
		$requestInfo = $readConnection->fetchAll($query, $binds);
		if($requestInfo[0]['state'] == NULL) {
			if($requestInfo[0]['completed_time'] == NULL) {
				$currentState = 'pending';
			} else {
				$currentState = 'completed';
			}
			// Write crrent state to db mabey?
		} else {
			$currentState = $requestInfo[0]['state'];
		}
		if($currentState != $response->state) {
			// State changed!!
			// If now completed, but not before, put product with putproduct
			if($response->state == 'completed') {
				// State changed to completed"
				// Put product and all that!
				self::$putFunction($response);				
			} else {
				// Log state change and send true for reload!
				self::logStateChange($response, $type);
			}
			return true;
		} else {
			return false;
		}
	}
	
	protected static function logStateChange($response, $type) {
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		
		switch ($type) {
			case 'product':
				$tableName = "contentor_products";
				break;
			case 'category':
				$tableName = "contentor_categories";
				break;
			case 'cmspage':
				$tableName = "contentor_cmspages";
				break;
		}
		
		$table = Mage::getConfig()->getTablePrefix() . $tableName;
		$query = "UPDATE `" . $table . "` SET `state` = :state WHERE `contentor_id` = :contentor_id";
		$binds = array(
				'state'			=> $response->state,
				'contentor_id'	=> $response->id,
		);
		$writeConnection->query($query, $binds);
		
		if($response->state == 'confirmed') {
			$statusMessage = 'Order with this entry was confirmed';
		} else if($response->state == 'canceled') {
			$statusMessage = 'Order with this entry was canceled';
		} else {
			// Generic message
			$statusMessage = 'Status was change to ' . $response->state;
		}
		
		$table = Mage::getConfig()->getTablePrefix()."contentor_status";
		$query = "INSERT INTO " . $table . " (`contentor_id`, `status`, `status_time`)
							VALUES (:contentor_id, :status, NOW())";
		$binds = array(
				'contentor_id'	=> $response->id,
				'status'		=> $statusMessage
		);
		$writeConnection->query($query, $binds);
	}

	protected static function putProduct($object) {
		// Get sku and target store from Magento DB
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix()."contentor_products";
		$query = "SELECT sku, target_store FROM `" . $table . "` WHERE `contentor_id` = :contentor_id AND completed_time IS NULL";
		$binds = array('contentor_id' => $object->id);
		if($productInfo = $readConnection->fetchRow($query, $binds)) {

			$product = Mage::getModel('catalog/product')->setStoreId($productInfo['target_store'])->loadByAttribute('sku',$productInfo['sku']);
			if($product) {
				// setData on product depending on licalizationsfields received on the right store
				Mage::app()->setCurrentStore($productInfo['target_store']);
				if(!Mage::getModel('Contentor_LocalizationApi/Hooks')->beforeSave($object, 'product', $product)) {
					return false;
					exit;
				}
				$images = array();
				foreach($object->fields as $field) {
					if($field->type == 'localizable') {
						// Update
						$attribute = substr($field->id, 0, -4);
						if(substr($attribute,0,6) == 'image_') {
							// This is image lable
							$newValue = str_replace(array('\n','\r'), '', $field->value);
							$newValue = htmlspecialchars($newValue, ENT_QUOTES, 'UTF-8');
							$file = substr($attribute, 6);
							$images[$file] = $newValue;
						} else {
							if($attribute == 'name' && Mage::getStoreConfig('contentor_options/contentor_automation/contentor_autourlgenerate')) {
								$urlKey = Mage::getModel('catalog/product_url')->formatUrlKey($field->value);
								$product->setUrlKey($urlKey);
								$product->getResource()->saveAttribute($product, 'url_key');
							} else if($attribute == 'url_key') {
								$urlKey = true;
							}
							$product->setData($attribute, $field->value);
							$product->getResource()->saveAttribute($product, $attribute);
						}
					}
				}
				if(Mage::getStoreConfig('contentor_options/contentor_automation/contentor_import_status')) {
					// Set product enabled
					$product->setStatus(1);
				}
				if(count($images)) {
					// I have images to update!
					$product = Mage::getModel('catalog/product')->load($product->getId());
					$gallery = $product->getTypeInstance(true)->getSetAttributes($product);
					$mediaGalleryBackendModel = $gallery['media_gallery']->getBackend();
					foreach($images as $imageFile => $imageLable) {
						$mediaGalleryBackendModel->updateImage($product, $imageFile, array('label' => $imageLable));
					}
				}
				if(!$urlKey) {
					$product->setUrlKey(false);
				}
				$product->save();
				
				// Write notice in Magento DB, set completed date in DB
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				$table = Mage::getConfig()->getTablePrefix()."contentor_products";
				$query = "UPDATE " . $table . " SET `completed_time` = :completed_time, `state` = 'completed' WHERE `contentor_id` = :contentor_id";
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

	protected static function putCategory($object) {
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
				if(!Mage::getModel('Contentor_LocalizationApi/Hooks')->beforeSave($object, 'category', $catagory)) {
					return false;
					exit;
				}
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
				$query = "UPDATE " . $table . " SET `completed_time` = :completed_time, `state` = 'completed' WHERE `contentor_id` = :contentor_id";
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
	
	protected static function putCmspage($object) {
		// Get id and target store from Magento DB
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix()."contentor_cmspages";
		$query = "SELECT page_id, target_store FROM `" . $table . "` WHERE `contentor_id` = :contentor_id AND completed_time IS NULL";
		$binds = array('contentor_id' => $object->id);
		$cmspageInfo = $readConnection->fetchRow($query, $binds);
		
		// Get current page from page_id
		$currentPage = Mage::getModel('cms/page')->load($cmspageInfo['page_id']);
		
		$currentPageData = $currentPage->getData();
		
		$newPageData = Array (
			'title' => $currentPageData['title'],
			'root_template' => $currentPageData['root_template'],
			'identifier' => $currentPageData['identifier'],
			'content' => $currentPageData['content'],
			'is_active' => 0,
			'stores' => array($cmspageInfo['target_store']),
			'sort_order' => $currentPageData['sort_order']
		);
		if(!Mage::getModel('Contentor_LocalizationApi/Hooks')->beforeSave($object, 'cmspage', $currentPage)) {
			return false;
			exit;
		}
		foreach($object->fields as $field) {
			if($field->type == 'localizable') {
				// Update
				$attribute = substr($field->id, 0, -4);
				if($attribute == 'identifier') {
					$value = Mage::getModel('catalog/product_url')->formatUrlKey($field->value);
				} else {
					$value = $field->value;
				}
				$newPageData[$attribute] = $value;
			}
		}

		$newPage = Mage::getModel('cms/page')->setData($newPageData)->save();

		// Write notice in Magento DB, set completed date in DB
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		$table = Mage::getConfig()->getTablePrefix()."contentor_cmspages";
		$query = "UPDATE " . $table . " SET `completed_time` = :completed_time, `state` = 'completed' WHERE `contentor_id` = :contentor_id";
		$binds = array(
				'completed_time'	=> $object->completed,
				'contentor_id'		=> $object->id,
		);
		$writeConnection->query($query, $binds);
		
		$table = Mage::getConfig()->getTablePrefix()."contentor_status";
		$query = "INSERT INTO " . $table . " (`contentor_id`, `status`, `status_time`)
				VALUES (:contentor_id, :status, NOW())";
		$binds = array(
				'contentor_id'	=> $object->id,
				'status'		=> 'Received as completed for ' . $object->language->target . ', completion time: ' . date("Y-m-d H:i:s", strtotime($object->completed)),
		);
		$writeConnection->query($query, $binds);
	}
	
	public static function testAuth($token=false) {
		$url = ContentorAPI::getURL(true);
		if(!$token) {
			$token = ContentorAPI::getToken();
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token, 'Accept: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$info = json_decode($response);
		
		if(isset($info->companyName)) {
			return $info->companyName;
		} else {
			Mage::log($info, null, 'contentor.log');
			return false;
		}
	}

	protected static function getCurl($token, $url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token, 'Accept: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		// If error log curl_getinfo();
		if(!$response) {
			Mage::log(curl_getinfo($ch), null, 'contentor.log');
		}
		curl_close($ch);
		$response =  json_decode($response);
		$return = Mage::getModel('Contentor_LocalizationApi/Hooks')->afterReceive($response);
		return $return;
	}

	protected static function getURL($auth=false) {
		$dev = self::getDEV();
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

	protected static function getRequestStatus($id) {
		$return['type'] = self::getType($id);
		if($return['type'] == 'product') {
			$tableName = 'contentor_products';
		} else if($return['type'] == 'category') {
			$tableName = 'contentor_categories';
		} else if($return['type'] == 'cmspage') {
			$tableName = 'contentor_cmspages';
		} else {
			return false;
		}
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = Mage::getConfig()->getTablePrefix() . $tableName;
		$query = "SELECT `state`, `completed_time` FROM `" . $table . "` WHERE `contentor_id` = :contentor_id";
		$binds = array('contentor_id' => $id);
		$requestInfo = $readConnection->fetchAll($query, $binds);
		if($requestInfo[0]['state'] != null) {
			$return['state'] = $requestInfo[0]['state'];
			return $return;
		} else {
			if($requestInfo[0]['completed_time'] != null) {
				$return['state'] = 'completed'; 
				return $return;
			} else {
				$return['state'] = 'pending'; 
				return $return;
			}
		}
	}

	protected static function getType($id) {
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$typeTable = Mage::getConfig()->getTablePrefix()."contentor_type";
		$query = "SELECT `type` FROM `" . $typeTable . "` WHERE `contentor_id` = :contentor_id";
		$binds = array('contentor_id' => $id);
		if($return = $readConnection->fetchOne($query, $binds)) {
			return $return;
		} else {
			// Check the old way!
			$producttable = Mage::getConfig()->getTablePrefix()."contentor_products";
			$categorytable = Mage::getConfig()->getTablePrefix()."contentor_categories";
			$query = "SELECT CASE WHEN (EXISTS (SELECT `contentor_id` FROM `" . $producttable . "` WHERE `contentor_id` = :contentor_id)) THEN 'product' WHEN (EXISTS (SELECT `contentor_id` FROM `" . $categorytable . "` WHERE `contentor_id` = :contentor_id)) THEN 'category' ELSE 'nope' END AS 'type'";
			$binds = array('contentor_id' => $id);
			$return = $readConnection->fetchOne($query, $binds);
			return $return;
		}
	}
	
	private static function getDEV() {
		return false;
	}
}