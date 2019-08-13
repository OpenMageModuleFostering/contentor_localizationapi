<?php
class Contentor_LocalizationApi_Model_Hooks
{
	/*	
	 *  Override these functions to make custimations
	 */
	public function beforeSend($request)
	{
		/*
		 * $request are the request about to be sent
		 * See our documentation for details: 
		 * http://developer.contentor.com/api/
		 * 
		 * $request is a array
		 */
		
		return $request;
	}
	
	public function afterReceive($request)
	{
		/*
		 * $request are the request just received
		 * See our documentation for details:
		 * http://developer.contentor.com/api/
		 *
		 * $request is a standard object
		 */
		
		return $request;
	}
	
	public function beforeSave($request, $type, $object)
	{
		/*
		 * This is run just before save.
		 *
		 * $request is the received request as standard object
		 * See our documentation for details:
		 * http://developer.contentor.com/api/
		 *
		 * $type is one of 'product', 'category' or 'cmspage'
		 * 
		 * $object is the Magento object, Product, Category or CMS Page
		 * 
		 * If returned true, everything runns as normal after this
		 * If returned false, nothing is processed after this
		 * 
		 */
		
		return true;
	}
}