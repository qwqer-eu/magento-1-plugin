<?php
class Qwqer_Express_AjaxController extends Mage_Core_Controller_Front_Action {
	public function addressAction(){
        $html = '';
        $shippingMethod = Mage::app()->getRequest()->getParam("method");

        if ($this->getRequest()->getParam('parcels')) {
            $params = ['input' => Mage::app()->getRequest()->getParam("parcels")];
            $addresses = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getParcelMachinesWithoutKey($params);
            $this->collectQuoteWithQwqer('');
            $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
            return $this->getResponse()->setBody(json_encode($addresses));
        }

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
        $shippingMethod = Mage::app()->getRequest()->getParam("method");
        $price = $this->getDefaultPrice($shippingMethod);
        $resultArray = [];
        $resultArray['status'] = false;
        $resultArray['message'] = $this->__('QWQER Delivery option is not available');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $location = Mage::app()->getRequest()->getParam("location");
        if ($location) {
            $realType = Qwqer_Express_Helper_Data::DELIVERY_ORDER_REAL_TYPE;
            if ($shippingMethod) {
                $realTypes = Qwqer_Express_Helper_Data::QWQER_REAL_TYPES;
                if(!empty($realTypes[$shippingMethod])) {
                    $realType = $realTypes[$shippingMethod];
                }
            }
            $params = ['address' => $location];
            $location = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode($params);
            if (!empty($location['data']['coordinates'])) {
                $params["coordinates"] = $location['data']['coordinates'];
            }
            $params['real_type'] = $realType;
            $result =  Mage::getSingleton('qwqer_express/api_client')->getConnection()->getShippingCost($params);
            if (!empty($result['data']) && isset($result['data']['client_price'])) {
                $price = $result['data']['client_price'] / 100;
                $resultArray['status'] = true;
                $resultArray['message'] = '';
            } else {
                $params['address'] = '';
            }
            $this->collectQuoteWithQwqer($params['address'], $shippingMethod);
        }

        foreach ($quote->getAllAddresses() as $address) {
            if ($address->getData('address_type') == 'shipping') {
                $phone = str_replace(['(', ')', '-', ' ', '+'], ['', '', '', '', ''], $address->getTelephone());
                if(strlen($phone) < 10) {
                    $resultArray['status'] = false;
                    $resultArray['message'] = $this->__('Phone number in shipping address is not valid');
                }
            }
        }

        $calculateShipping = $this->getUseShippingCostFromQwqer($shippingMethod);
        if (!$calculateShipping) {
            $price = $this->getBaseShippingCost($shippingMethod);
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

    private function getDefaultPrice($method)
    {
        if ($method == 'qwqer_express_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_express/shipping_cost');
        } elseif ($method == 'qwqer_door_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_door/shipping_cost');
        } elseif ($method == 'qwqer_parcel_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_parcel/shipping_cost');
        } else {
            return Qwqer_Express_Helper_Data::DEFAULT_PRICE_IF_ERROR;
        }
    }

    private function getBaseShippingCost($method)
    {
        if ($method == 'qwqer_express_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_express/base_shipping_cost');
        } elseif ($method == 'qwqer_door_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_door/base_shipping_cost');
        } elseif ($method == 'qwqer_parcel_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_parcel/base_shipping_cost');
        } else {
            return $this->getDefaultPrice($method);
        }
    }

    private function getUseShippingCostFromQwqer($method)
    {
        if ($method == 'qwqer_express_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_express/calculate_shipping_price');
        } elseif ($method == 'qwqer_door_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_door/calculate_shipping_price');
        } elseif ($method == 'qwqer_parcel_express') {
            return Mage::app()->getWebsite()->getConfig('carriers/qwqer_parcel/calculate_shipping_price');
        } else {
            return $this->getDefaultPrice($method);
        }
    }

    private function collectQuoteWithQwqer($address, $shippingMethod = false)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->setQwqerAddress($address);
        $quote->setQwqerAddressMethod($shippingMethod);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->setQwqerAddressMethod(false);
        $quote->save();
    }

}