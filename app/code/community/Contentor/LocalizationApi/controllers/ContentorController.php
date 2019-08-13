<?php
class Contentor_LocalizationApi_ContentorController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

    }

    public function reportsAction()
    {
    	$this->loadLayout()
    	->_setActiveMenu('report/contentor_reports');
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('Contentor/Reports/reports.phtml'));
    	$this->renderLayout();
    }
    
    public function productsreportAction()
    {
    	$this->loadLayout()
    	->_setActiveMenu('report/contentor_reports');
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('Contentor/Reports/productreport.phtml'));
        $this->renderLayout();
    }
    
    public function categoriesreportAction()
    {
    	$this->loadLayout()
    	->_setActiveMenu('report/contentor_reports');
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('Contentor/Reports/categoryreport.phtml'));
    	$this->renderLayout();
    }
    
    public function checkAction()
    {
    	require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
    	$auth = ContentorAPI::testAuth();
    	Mage::app()->getResponse()->setBody($auth);
    }
    
    public function sendbulkAction()
    {
    	$this->loadLayout()
    	->_setActiveMenu('catalog');
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('Contentor/bulkconfirm.phtml'));
    	$this->renderLayout();
    }
    
    public function postbulkAction()
    {
		// Get product list and send 10/50/100? first, then print form with the rest
		$max = 10;
		$data = Mage::app()->getRequest()->getPost();

		$productlist = explode(',', $data['products']);
		$numberProducts = count($productlist);
		$sourceLocale =  str_replace('_', '-', $data['source']);
		$targetIDs = explode(",", $data["targets"]);
		$numberTargets = count($targetIDs);

		foreach($targetIDs as $targetID) {
			$targets[$targetID] = str_replace('_', '-', Mage::getStoreConfig('general/locale/code', $targetID));
		}

		// Check if source in targets?
		if(!count($targets)) {
			Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>No target selected'));
			print 'No data sent!<br>No target selected';
		} elseif(in_array($sourceLocale, $targets)) {
			Mage::throwException(Mage::helper('adminhtml')->__('No data sent!<br>Source language in targets'));
			print 'No data sent!<br>Source language in targets';
		} else {
			// Loop products
			if($data["total"] == 0) {
				$total = $numberProducts*$numberTargets;
			} else {
				$total = $data["total"];
			}
			// Loop products and send the n first
			if($numberProducts < $max) {
				$max = $numberProducts;
			}
			for($i=0;$i<$max;$i++) {
				// Send some products
				$prodID = $productlist[$i];
				require_once(Mage::getModuleDir('', 'Contentor_LocalizationApi') . '/lib/api/ContentorAPI.php');
				$product = Mage::getModel('catalog/product')->load($prodID);
				$sku = $product->getSku();
				// Get fields
				if($fields = ContentorAPI::getFieldData($product)) {
					// Then Loop targets and send
					foreach($targets as $targetID => $targetLocale) {
						$request = ContentorAPI::createRequest($sourceLocale, $targetLocale, $fields);
						if($contentorID = ContentorAPI::send($request)) {
							if(!ContentorAPI::logSent($contentorID, $sku, $sourceLocale, $targetLocale, $targetID, $product)) {
								print 'No logs written';
							}
						} else {
							print 'Error sending to Contentor API';
						}
					}
				} else {
					print 'Error can\'t get fields from settings';
				}
				unset($productlist[$i]);
			}
		
			$productsLeft = $numberTargets*count($productlist);
			$procent = (1-($productsLeft/$total))*100;
			
			print "<h3>Progress</h3>";
			print "<svg height=20 width=\"100%\">";
			print "<g transform=\"translate(0,0)\">";
			print "<rect height=20 width=\"100%\" style=\"fill:#ccc;\"></rect>";
			print "</g>";
			print "<g transform=\"translate(0,0)\">";
			print "<rect height=20 width=\"" . $procent . "%\" style=\"fill:#d75f07;\"></rect>";
			print "</g>";
			print "</svg>";
			print '<b>' . $productsLeft . '</b> items remaining from a total of <b>' . $total . '</b> items';
			
			print "<form id=\"progressform\">";
			if($productsLeft > 0) {
				print "<input type=\"hidden\" name=\"form_key\" value=\"" . Mage::getSingleton('core/session')->getFormKey() . "\">";
				print "<input type=\"hidden\" name=\"total\" value=\"" . $total . "\">";
				print "<input type=\"hidden\" name=\"products\" value=\"" . join(',',$productlist) . "\">";
				print "<input type=\"hidden\" name=\"source\" value=\"" . $sourceLocale . "\">";
				print "<input type=\"hidden\" name=\"targets\" value=\"" . join(',', $targetIDs) . "\">";
			} else {
				print "<input type=\"hidden\" name=\"theend\" value=\"true\">";
				print "<h3>Done!</h3>";
				print "Go to <a href=\"" . $this->getUrl('adminhtml/contentor/reports') . "\">report page</a>";
			}
			print "</form>";
		}
    }
}