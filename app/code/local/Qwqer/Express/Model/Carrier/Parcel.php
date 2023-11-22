<?php

class Qwqer_Express_Model_Carrier_Parcel extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'qwqer_parcel';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(
        Mage_Shipping_Model_Rate_Request $request
    ) {
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */

        $result->append($this->_getStandardShippingRate());

        return $result;
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
        if ($address) {
            if ($qwqerMethod && $qwqerMethod !== "qwqer_parcel_express")
            {
                return (float) $price;
            }
            $params = ['address' => $address];
            $coordinates = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode($params);

            if (!empty($coordinates['data']['coordinates'])) {
                $params["coordinates"] = $coordinates['data']['coordinates'];
                $params = array_merge($params, $coordinates);
                $params['real_type'] = Qwqer_Express_Helper_Data::DELIVERY_ORDER_REAL_TYPE_PARCEL;
                $result = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getShippingCost($params);
                if (!empty($result['data']) && isset($result['data']['client_price'])) {
                    $price = $result['data']['client_price'] / 100;
                }
            }

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
