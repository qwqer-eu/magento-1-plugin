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
                        /** @var $address Mage_Sales_Model_Quote_Address_Total_Shipping */
                        $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle() . ' - ' . $quote->getQwqerAddress();
                        $address->setShippingDescription(trim($shippingDescription, ' -'));
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param $event
     * @return void
     */
    public function adminhtmlWidgetContainerHtmlBeforeButton($event)
    {
        $block = $event->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            if($qwqerData = $block->getOrder()->getData('qwqer_data')) {
                try {
                    $qwqerDataArray = Mage::helper('core')->jsonDecode($qwqerData);
                    if(!empty($qwqerDataArray['data']['id'])) {
                        $url = "https://qwqer.hostcream.eu/storage/delivery-order-covers/" . $qwqerDataArray['data']['id'] . ".pdf";
                        $blank = "_blank";
                        $block->addButton('print_label_qwqer', array(
                            'label' => Mage::helper('qwqer_express')->__('Print Label'),
                            'onclick' => 'window.open(\'' . $url . '\', \'' . $blank . '\')',
                            'class' => 'go'
                        ));
                    }
                } catch (\Exception $exception) {
                    //skip button
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function saveWorkingHours(Varien_Event_Observer $observer)
    {
        $data =  Mage::getSingleton('qwqer_express/api_client')->getConnection()->getTradingPointInfo();
        if(!empty($data['data']['working_hours'])) {
            Mage::getConfig()->saveConfig(Qwqer_Express_Helper_Data::API_WORKING_HOURS, json_encode($data['data']['working_hours']), 'default', 0);
        }
    }
}