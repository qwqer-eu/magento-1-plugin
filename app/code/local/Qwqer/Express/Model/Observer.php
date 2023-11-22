<?php
class Qwqer_Express_Model_Observer extends Mage_Core_Model_Abstract {

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     * @throws Mage_Core_Exception
     */
	public function syncAndUpdateShippingInformation(Varien_Event_Observer $observer){
		$order = $observer->getEvent()->getOrder();
		$quote = $observer->getEvent()->getQuote();
        $helper = Mage::helper('qwqer_express');
        if (
            in_array($order->getData('shipping_method'), Qwqer_Express_Helper_Data::QWQER_METHODS)
            && $quote->getQwqerAddress()
        ){
			//$order->setData('shipping_description', $order->getShippingDescription() . ' - ' . $quote->getQwqerAddress());
            $pacedOrder =  Mage::getSingleton('qwqer_express/api_client')->getConnection()->orderPlace($order, $quote);
            if ($pacedOrder) {
                $order->setQwqerData(json_encode($pacedOrder));
                if (!empty($pacedOrder['data']['id'])) {
                    $order->addStatusHistoryComment('QWQER Order Id: ' . $pacedOrder['data']['id']);
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

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setShippingMethodDescription(Varien_Event_Observer $observer){
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            if ($address->getData('address_type') == 'shipping'
                && in_array($address->getShippingMethod(), Qwqer_Express_Helper_Data::QWQER_METHODS)
                && $quote->getQwqerAddress()
            ) {
                foreach ($address->getAllShippingRates() as $rate) {
                    if (
                        in_array($rate->getCode(), Qwqer_Express_Helper_Data::QWQER_METHODS)
                        && $address->getShippingMethod() == $rate->getCode()
                    ) {
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