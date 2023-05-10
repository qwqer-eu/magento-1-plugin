<?php

class Qwqer_Express_Helper_Address extends Mage_Customer_Helper_Address
{
    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeValidationClass($attributeCode)
    {
        $class = parent::getAttributeValidationClass($attributeCode);

        $helper = Mage::helper('qwqer_express');

        if($helper->getIsQwqerEnabled() && $attributeCode == 'telephone')  {
            $class .= ' validate-phone-lv';
        }

        return $class;
    }
}