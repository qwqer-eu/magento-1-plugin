<?php

class Qwqer_Express_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'qwqer_express';

    public function collectRates(
    Mage_Shipping_Model_Rate_Request $request
    ) {
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */

        $result->append($this->_getStandardShippingRate());

        return $result;
    }

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

        $rate->setPrice($this->calculatePrice($address));
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Calculate Price
     *
     * @param $address
     * @return float
     */
    public function calculatePrice($address)
    {

        $price = $this->getConfigData('shipping_cost');
        if ($address) {
            $params = ['address' => $address];
            $coordinates = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode($params);

            if (!empty($coordinates['data']['coordinates'])) {
                $params["coordinates"] = $coordinates['data']['coordinates'];
                $params = array_merge($params, $coordinates);
                $result = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getShippingCost($params);
                if (!empty($result['data']) && isset($result['data']['client_price'])) {
                    $price = $result['data']['client_price'] / 100;
                }
            }

        }
        return (float) $price;
    }
    public function getAllowedMethods() {
        return [
            'express' => $this->getConfigData('name'),
        ];
    }

}
