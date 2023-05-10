<?php
class Qwqer_Express_AjaxController extends Mage_Core_Controller_Front_Action {
	public function addressAction(){
        $html = '';
        if (Mage::app()->getRequest()->getParam("value")) {
            $params = ['input' => Mage::app()->getRequest()->getParam("value")];
            $addresses = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getAddresses($params);
            if(!empty($addresses) && !empty($addresses['data'])) {
                if(empty($addresses['message'])) {
                    $html .= "<ul>";
                    foreach ($addresses['data'] as $key => $value) {
                        $html .= "<li class='qwqer_item' id=" . $key . ">" . $value . "</li>";
                    }
                    $html .= "</ul>";
                }
            } elseif (!empty($addresses['message'])) {
                Mage::getSingleton('core/session')->addError($addresses['message']);
            }
            $this->collectQuoteWithQwqer('');
        }
        Mage::app()->getResponse()->setBody($html);
	}

    public function costAction()
    {
        $price = $this->getDefaultPrice();
        $resultArray = [];
        $resultArray['status'] = false;
        $resultArray['message'] = $this->__('QWQER Delivery option not available');

        if (Mage::app()->getRequest()->getParam("location")) {
            $params = ['address' => Mage::app()->getRequest()->getParam('location')];
            $location = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode($params);
            if (!empty($location['data']['coordinates'])) {
                $params["coordinates"] = $location['data']['coordinates'];
            }
            $result =  Mage::getSingleton('qwqer_express/api_client')->getConnection()->getShippingCost($params);

            if (!empty($result['data']) && isset($result['data']['client_price'])) {
                $price = $result['data']['client_price'] / 100;
                $resultArray['status'] = true;
                $resultArray['message'] = '';
            } else {
                $params['address'] = '';
            }
            $this->collectQuoteWithQwqer($params['address']);
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            if ($address->getData('address_type') == 'shipping') {
                $phone = str_replace(['(', ')', '-', ' ', '+'], ['', '', '', '', ''], $address->getTelephone());
                if(strlen($phone) < 10) {
                    $resultArray['status'] = false;
                    $resultArray['message'] = $this->__('Phone number in shipping address is not valid');
                }
            }
        }

        $priceHtml = Mage::helper('core')->currency($price, true, false);
        $resultArray['price'] = $priceHtml;

        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($resultArray));
    }

    public function savedAddressAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $resultArray['address'] = $quote->getQwqerAddress();

        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($resultArray));
    }

    private function getDefaultPrice()
    {
        return Mage::app()->getWebsite()->getConfig('carriers/qwqer_express/shipping_cost');
    }
    private function collectQuoteWithQwqer($address)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->setQwqerAddress($address);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
    }

}