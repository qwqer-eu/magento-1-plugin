<?php

class Qwqer_Express_Model_Api_Service_ExecuteRequest
{
    /**
     * Key for API Qwqer Express
     *
     * @var string $key
     */
    protected $key;

    /**
     * @var bool $throwErrors Throw exceptions when in response is error
     */
    protected $throwErrors = FALSE;

    /**
     * @var string $format Format of returned data - array, json, xml
     */
    protected $format = 'array';

    /**
     * @var string $connectionType Connection type (curl | file_get_contents)
     */
    protected $connectionType = 'curl';

    /**
     * @var array $params Set params of current method of current model
     */
    protected $params;

    /**
     * @param $key
     * @param $throwErrors
     * @param $connectionType
     */
    function __construct($key, $throwErrors = FALSE, $connectionType = 'curl') {
        $this->throwErrors = $throwErrors;
        return $this
            ->setKey($key)
            ->setConnectionType($connectionType)
            ->setFormat('array');
    }

    /**
     * Setter for $connectionType property
     *
     * @param string $connectionType Connection type (curl | file_get_contents)
     * @return this
     */
    function setConnectionType($connectionType) {
        $this->connectionType = $connectionType;
        return $this;
    }

    /**
     * Getter for $connectionType property
     *
     * @return string
     */
    function getConnectionType() {
        return $this->connectionType;
    }

    /**
     * Setter for format property
     *
     * @param string $format Format of returned data by methods (json, xml, array)
     */
    function setFormat($format) {
        $this->format = $format;
        return $this;
    }

    /**
     * Getter for format property
     *
     * @return string
     */
    function getFormat() {
        return $this->format;
    }

    /**
     * Setter for key property
     *
     * @param string $key Qwqer Express API key
     * @return Qwqer Express_Api2
     */
    function setKey($key) {
        $this->key = $key;
        return $this;
    }

    /**
     * Getter for key property
     *
     * @return string
     */
    function getKey() {
        return $this->key;
    }

    /**
     * Prepare data before return it
     *
     * @param json $data
     * @return mixed
     */
    private function prepare($data) {
        //Returns array

        if ($this->format == 'array') {
            $result = is_array($data)
                ? $data
                : json_decode($data, 1);
            // If error exists, throw Exception
            if ($this->throwErrors AND $result['errors'])
                throw new \Exception(is_array($result['errors']) ? implode("\n", $result['errors']) : $result['errors']);
            return $result;
        }
        // Returns json or xml document
        return $data;
    }

    /**
     * Make request to Qwqer Express API
     *
     * @param $endpoint
     * @param $params
     * @return json|mixed
     * @throws Exception
     */
    private function request($endpoint, $params = NULL) {
        try {
            $post = '';
            if(!empty($params)) {
                $post = json_encode($params);
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => Mage::helper('qwqer_express')->getAPIBaseUrl() . $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Authorization: Bearer '.$this->key,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $result = $this->prepare($response);

            if(is_array($result)) {
                Mage::log(json_encode($result), null, 'qwqer.log');
            }
            Mage::log(json_encode($params), null, 'qwqer.log');

            return $result;

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return [];
    }

    /**
     * Set params of current method/property property
     *
     * @param array $params
     * @return mixed
     */
    function params($params) {
        $this->params = $params;
        return $this;
    }

    /**
     * Execute request to Qwqer Express API
     *
     * @return mixed
     */
    function execute() {
        return $this->request($this->model, $this->method, $this->params);
    }

    /**
     * Get Qwqer Express Addresses
     *
     * @param $params
     * @return json|mixed
     * @throws Exception
     */
    function getAddresses($params = []) {
        return $this->request(Mage::helper('qwqer_express')->getAutocompleteUrl(), $params);
    }

    /**
     * Get Qwqer Express Address coordinates
     *
     * @param $params
     * @return json|mixed
     * @throws Exception
     */
    function geoCode($params = []) {
        return $this->request(Mage::helper('qwqer_express')->getGeoCodeUrl(), $params);
    }

    /**
     * Get Qwqer Express Price
     *
     * @param $params
     * @return json|mixed
     * @throws Exception
     */
    function getShippingCost($params = []) {

        $storeOwnerAddress = $params;
        $storeOwnerAddress["address"] = Mage::helper('qwqer_express')->getStoreAddress();
        $storeOwnerAddress["coordinates"] = Mage::helper('qwqer_express')->getStoreAddressLocation();

        $bodyArray =  [
            'type' => Qwqer_Express_Helper_Data::DELIVERY_ORDER_TYPES,
            'real_type' => Qwqer_Express_Helper_Data::DELIVERY_ORDER_REAL_TYPE,
            'category' => Mage::helper('qwqer_express')->getCategory(),
            'origin' => $storeOwnerAddress,
            'destinations' => [$params],
        ];

        return $this->request(Mage::helper('qwqer_express')->getShippingCost(), $bodyArray);
    }

    /**
     * Public Qwqer Express Order
     *
     * @param $order
     * @param $quote
     * @return json|mixed
     * @throws Exception
     */
    function orderPlace($order, $quote) {

        $phone = $order->getShippingAddress()->getTelephone()
            ? $order->getShippingAddress()->getTelephone() : $order->getBillingAddress()->getTelephone();
        $phone = str_replace(['(', ')', '-', ' ', '+'], ['', '', '', '', ''], $phone);
        $orderId = $order->getIncrementId();

        $originData = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
            'phone' => "+".$phone,
            'address' => $quote->getQwqerAddress(),
            'incrementId' => $orderId
        ];

        $location = Mage::getSingleton('qwqer_express/api_client')->getConnection()->geoCode(
            ['address' => $quote->getQwqerAddress()]
        );

        if (!empty($location['data']['coordinates'])) {
            $originData["coordinates"] = $location['data']['coordinates'];
        }

        $storeOwnerAddress = $originData;
        unset($storeOwnerAddress['incrementId']);

        $storeOwnerAddress["address"] = Mage::helper('qwqer_express')->getStoreAddress();
        $storeOwnerAddress["coordinates"] = Mage::helper('qwqer_express')->getStoreAddressLocation();

        $bodyArray =  [
            'type' => Qwqer_Express_Helper_Data::DELIVERY_ORDER_TYPES,
            'real_type' => Qwqer_Express_Helper_Data::DELIVERY_ORDER_REAL_TYPE,
            'category' => Mage::helper('qwqer_express')->getCategory(),
            'origin' => $storeOwnerAddress,
            'delivery_order_id' => $orderId,
            'destinations' => [$originData],
        ];

        return $this->request(Mage::helper('qwqer_express')->getOrderPlaceUrl(), $bodyArray);
    }
}
