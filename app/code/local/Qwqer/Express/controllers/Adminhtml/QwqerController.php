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

    /**
     * @return void
     * @throws Mage_Core_Exception
     */
    public function syncAction()
    {
        $orderId = Mage::app()->getRequest()->getParam("id");
        $order = Mage::getModel('sales/order')->load($orderId);
        $quote = Mage::getModel('sales/quote')->setStoreId($order->getStoreId());
        $quote->load($order->getQuoteId());
        $helper = Mage::helper('qwqer_express');
        if (
            in_array($order->getData('shipping_method'), Qwqer_Express_Helper_Data::QWQER_METHODS)
            && $quote->getQwqerAddress()
        ) {
            $pacedOrder =  Mage::getSingleton('qwqer_express/api_client')->getConnection()->orderPlace($order, $quote);
            if ($pacedOrder) {
                $order->setQwqerData(json_encode($pacedOrder));
                if (!empty($pacedOrder['data']['id'])) {
                    $order->addStatusHistoryComment('QWQER Order Id: ' . $pacedOrder['data']['id']);
                    $this->_getSession()->addSuccess("Synced order to QWQER.");
                } else {
                    $this->_getSession()->addError("Can not sync order to QWQER.");
                }
                $order->save();
                if(!empty($pacedOrder['errors'])) {
                    if(isset($pacedOrder['errors']['origin.phone'])) {
                        Mage::throwException($helper->__("The phone field contains an invalid number"));
                    } else {
                        Mage::throwException($helper->__("Something went wrong. Contact an administrator"));
                    }
                }
            } else {
                $this->_getSession()->addError("Must be QWQER shipping method.");
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }
}