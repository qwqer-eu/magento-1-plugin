<?php
class Qwqer_Express_Model_System_Config_Source_Category
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('qwqer_express');
        return [
            ['value' => 'Other', 'label' => $helper->__('Other')],
            ['value' => 'Flowers', 'label' => $helper->__('Flowers')],
            ['value' => 'Food', 'label' => $helper->__('Food')],
            ['value' => 'Electronics', 'label' => $helper->__('Electronics')],
            ['value' => 'Cake', 'label' => $helper->__('Cake')],
            ['value' => 'Present', 'label' => $helper->__('Present')],
            ['value' => 'Clothes', 'label' => $helper->__('Clothes')],
            ['value' => 'Document', 'label' => $helper->__('Document')],
            ['value' => 'Jewelry', 'label' => $helper->__('Jewelry')],
        ];
    }
}