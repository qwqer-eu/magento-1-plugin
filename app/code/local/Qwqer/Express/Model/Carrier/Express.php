<?php

class Qwqer_Express_Model_Carrier_Express extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'qwqer_express';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(
    Mage_Shipping_Model_Rate_Request $request
    ) {
        $result = Mage::getModel('shipping/rate_result');

        $available = $this->checkAvailableProduct();
        if(!$available) {
            return $result;
        }

        $available = $this->checkWorkingHours();
        if(!$available) {
            return $result;
        }

        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result->append($this->_getStandardShippingRate());

        return $result;
    }

    /**
     * @return mixed
     */
    protected function checkWorkingHours()
    {
        $cart = Mage::getSingleton('checkout/session')->getQuote();
        $helper = Mage::helper('qwqer_express/data');
        return $helper->checkWorkingHours($cart->getStoreId());
    }

    /**
     * @return bool
     */
    protected function checkAvailableProduct()
    {
        $cart = Mage::getSingleton('checkout/session')->getQuote();
        $items = $cart->getAllItems();
        foreach ($items as $item) {
            $isAvailable = intval($item->getProduct()->getData(Qwqer_Express_Helper_Data::ATTRIBUTE_CODE_AVAILABILITY));
            if($isAvailable) {
                continue;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @return false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardShippingRate() {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);

        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('express');

        $rate->setMethodTitle($this->getConfigData('name'));

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $address = $quote->getQwqerAddress();
        $rate->setMethodDescription($address);
        $qwqerMethod = $quote->getQwqerAddressMethod();
        $rate->setPrice($this->calculatePrice($address, $qwqerMethod));
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Calculate Price
     *
     * @param $address
     * @param $qwqerMethod
     * @return float
     */
    public function calculatePrice($address, $qwqerMethod = null)
    {
        $price = $this->getConfigData('shipping_cost');
        $calculatePrice = $this->getConfigData('calculate_shipping_price');
        if ($address && $calculatePrice) {
            if ($qwqerMethod && $qwqerMethod !== "qwqer_express_express")
            {
                return (float) $price;
            }
            $params = ['address' => $address];
            $coordinates = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode($params);

            if (!empty($coordinates['data']['coordinates'])) {
                $params["coordinates"] = $coordinates['data']['coordinates'];
                $params = array_merge($params, $coordinates);
                $params['real_type'] = Qwqer_Express_Helper_Data::DELIVERY_ORDER_REAL_TYPE;
                $result = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getShippingCost($params);
                if (!empty($result['data']) && isset($result['data']['client_price'])) {
                    $price = $result['data']['client_price'] / 100;
                }
            }
        } elseif (!$calculatePrice) {
            $price = $this->getConfigData('base_shipping_cost');
        }
        return (float) $price;
    }

    /**
     * @return array
     */
    public function getAllowedMethods() {
        return [
            'express' => $this->getConfigData('name'),
        ];
    }

}
