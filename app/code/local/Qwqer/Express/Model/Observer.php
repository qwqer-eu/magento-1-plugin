<?php
class Qwqer_Express_Model_Observer extends Mage_Core_Model_Abstract {
	public function syncAndUpdateShippingInformation(Varien_Event_Observer $observer){
		$order = $observer->getEvent()->getOrder();
		$quote = $observer->getEvent()->getQuote();
        $helper = Mage::helper('qwqer_express');
        if (
            $order->getData('shipping_method') == 'qwqer_express_express'
            && $quote->getQwqerAddress()
            && $helper->getIsQwqerEnabled($quote->getStoreId())
        ){
			//$order->setData('shipping_description', $order->getShippingDescription() . ' - ' . $quote->getQwqerAddress());
            $pacedOrder =  Mage::getSingleton('qwqer_express/api_client')->getConnection()->orderPlace($order, $quote);
            if ($pacedOrder) {
                $order->setQwqerData(json_encode($pacedOrder));
                if (!empty($pacedOrder['data']['id'])) {
                    $order->addStatusHistoryComment('QWQER Express Order Id: ' . $pacedOrder['data']['id']);
                }
                if(!empty($pacedOrder['errors'])) {
                    if(isset($pacedOrder['errors']['origin.phone'])) {
                        Mage::throwException($helper->__("The phone field contains an invalid number"));
                    } else {
                        Mage::throwException($helper->__("Something went wrong. Contact an administrator"));
                    }
                }
            }
		}
	}

    public function setShippingMethodDescription(Varien_Event_Observer $observer){
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();
        if(Mage::helper('qwqer_express')->getIsQwqerEnabled($quote->getStoreId())) {
            foreach ($quote->getAllAddresses() as $address) {
                if ($address->getData('address_type') == 'shipping'
                    && $address->getShippingMethod() == 'qwqer_express_express'
                    && $quote->getQwqerAddress()
                ) {
                    foreach ($address->getAllShippingRates() as $rate) {
                        if ($rate->getCode() == 'qwqer_express_express') {
                            //$amountPrice = $address->getQuote()->getStore()->convertPrice($rate->getPrice(), false);
                            //$this->_setAmount($amountPrice);
                            //$this->_setBaseAmount($rate->getPrice());
                            /** @var $address Mage_Sales_Model_Quote_Address_Total_Shipping */
                            $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle() . ' - ' . $quote->getQwqerAddress();
                            $address->setShippingDescription(trim($shippingDescription, ' -'));
                            break;
                        }
                    }
                }
            }
        }
    }
}