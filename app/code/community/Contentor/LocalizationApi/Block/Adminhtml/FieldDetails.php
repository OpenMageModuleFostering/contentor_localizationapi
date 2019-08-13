<?php
class Contentor_LocalizationApi_Block_Adminhtml_FieldDetails
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	protected $attributeList;
	protected $storeList;
	protected $typeList;
	protected $dataList;
	
    public function __construct()
    {
    	$this->addColumn('attribute', array(
    			'label' => Mage::helper('Contentor_LocalizationApi')->__('Attribute'),
    			'style' => 'width:200px',
    	));
    	$this->addColumn('store', array(
    			'label' => Mage::helper('Contentor_LocalizationApi')->__('Source Store View'),
    			'style' => 'width:150px',
    	));
    	$this->addColumn('type', array(
            'label' => Mage::helper('Contentor_LocalizationApi')->__('Field Type'),
            'style' => 'width:100px',
        ));
        $this->addColumn('data', array(
            'label' => Mage::helper('Contentor_LocalizationApi')->__('Field Data Type'),
            'style' => 'width:100px',
        ));
        $this->addColumn('required', array(
        		'label' => Mage::helper('Contentor_LocalizationApi')->__('Required'),
        		
        ));
 
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add field');
        
        parent::__construct();
        $this->setTemplate('Contentor/array_dropdown.phtml');
        
        // Make custom options for attributes
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->addVisibleFilter();
        foreach($attributes as $attribute) {
        	$this->attributeList[$attribute->getdata('attribute_code')] = $attribute->getData('frontend_label') . ' (' . $attribute->getFrontendInput() . ')';
        }
        $this->attributeList['productURL'] = 'Product URL (url)';
        asort($this->attributeList);
        // Make custom options for stores        
        $stores = Mage::getModel('core/store')->getCollection();
        $this->storeList[0] = 'Default config';
        foreach($stores as $store) {
        	$this->storeList[$store->getId()] = $store->getName();
        }
        // Make custom options for types        
        $this->typeList = array('internal' => 'Internal', 'context' => 'Context', 'localizable' => 'Localizable');
        // Make custom options for data        
        $this->dataList = array('string' => 'Text', 'html:relaxed' => 'HTML');
        
    }
    
    protected function _renderCellTemplate($columnName)
    {
    	if (empty($this->_columns[$columnName])) {
    		throw new Exception('Wrong column name specified.');
    	}
    	$column = $this->_columns[$columnName];
    	$inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
    	$id = '#{_id}_' . $columnName;
		
    	if (in_array($columnName, array('attribute','store','type','data'))) {
    		//$row = $this->calcOptionHash($this->getData($columnName));
    		$configArray = unserialize(Mage::getStoreConfig('contentor_options/contentor_fields_new/contentor_fields_input', Mage::app()->getStore()));
    		$listName = $columnName."List";
    		$rendered = '<select id=' . $id . ' name="' . $inputName . '" ' . ($column['style'] ? 'style="' . $column['style'] . '"' : '') . '>';
    		foreach($this->$listName as $att => $name) {
    			$rendered .= '<option value="' . $att . '">' . $name . '</option>';
    		}
    		$rendered .= '</select>';
    	} else if(in_array($columnName, array('required'))) {
    		return '<input type="checkbox" id="' . $id . '" name="' . $inputName . '" value="yes" ' . ($column['style'] ? 'style="' . $column['style'] . '"' : '') . '/>';
    	}
    
    	return $rendered;
    }

}