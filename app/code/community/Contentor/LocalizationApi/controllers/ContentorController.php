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
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('Contentor/reports.phtml'));
        $this->renderLayout();
    }
}