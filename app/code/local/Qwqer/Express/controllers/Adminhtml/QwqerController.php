<?php

class Qwqer_Express_Adminhtml_QwqerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return some checking result
     *
     * @return void
     */
    public function autocompleteAction()
    {
        $html = '';
        if (Mage::app()->getRequest()->getParam("value")) {
            $params = ['input' => Mage::app()->getRequest()->getParam("value")];
            $addresses = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getAddresses($params);
            if(!empty($addresses) && !empty($addresses['data'])) {
                $html .= "<ul>";
                foreach ($addresses['data'] as $key => $value) {
                    $html .= "<li id=".$key.">".$value."</li>";
                }
                $html .= "</ul>";
            }
        }
        if (Mage::app()->getRequest()->getParam("location")) {
            $params = ['address' => Mage::app()->getRequest()->getParam('location')];
            $location = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode($params);
            if (!empty($location['data']['coordinates'])) {
                $html = implode(",", $location['data']['coordinates']);
            }
        }

        Mage::app()->getResponse()->setBody($html);
    }
}