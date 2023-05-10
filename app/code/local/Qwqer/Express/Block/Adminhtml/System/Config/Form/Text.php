<?php

class Qwqer_Express_Block_Adminhtml_System_Config_Form_Text extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    public $configValue = '';

    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('qwqer/system/config/text.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->configValue = $element->getEscapedValue();
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_qwqer/autocomplete');
    }

    /**
     * Return store address
     *
     * @return string
     */
    public function getConfigValue(): string
    {
        return $this->configValue;
    }

}