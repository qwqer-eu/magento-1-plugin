<?php

class Qwqer_Express_Helper_Data extends Mage_Core_Helper_Abstract
{
    /** GENERAL */
    public const API_IS_ENABLED = 'carriers/qwqer_express/active';
    public const API_IS_ENABLED_DOOR = 'carriers/qwqer_door/active';
    public const API_IS_ENABLED_PARCEL = 'carriers/qwqer_parcel/active';
    public const API_BEARER_TOKEN = 'carriers/qwqer_express/api_bearer_token';
    public const API_BASE_URL_PATH = 'carriers/qwqer_express/auth_endpoint';
    public const API_TRADING_POINT_ID = 'carriers/qwqer_express/trading_point_id';
    public const API_STORE_ADDRESS = 'carriers/qwqer_express/store_address';
    public const API_STORE_ADDRESS_LOCATION = 'carriers/qwqer_express/geo_store';
    public const API_CATEGORY = 'carriers/qwqer_express/category';
    public const API_PARCEL_SIZE = 'carriers/qwqer_parcel/parcel_size';
    public const API_AUTOCOMPLETE_URL = '/v1/plugins/magento/places/autocomplete';
    public const API_PARCEL_MACHINES_URL = '/v1/plugins/magento/parcel-machines';
    public const API_GEOCODE_URL = '/v1/plugins/magento/places/geocode';
    public const API_ORDER_PRICE_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders/get-price';
    public const API_ORDER_CREATE_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders';
    public const API_ORDER_LIST_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders';
    public const API_ORDER_DETAILS_URL = '/v1/plugins/magento/delivery-orders/{order-id}';

    public const DELIVERY_ORDER_TYPES = "Regular";

    public const DELIVERY_ORDER_REAL_TYPE = "ExpressDelivery";
    public const DELIVERY_ORDER_REAL_TYPE_DOOR = "ScheduledDelivery";
    public const DELIVERY_ORDER_REAL_TYPE_PARCEL = "OmnivaParcelTerminal";
    public const DEFAULT_PRICE_IF_ERROR = 3;
    public const ATTRIBUTE_CODE_AVAILABILITY = 'is_qwqer_available';
    public const API_TRADING_POINT_INFO
        = '/v1/plugins/magento/trading-points/{trading-point-id}?include=working_hours,merchant';
    public const API_WORKING_HOURS = 'carriers/qwqer_express/working_hours';
    public const QWQER_METHODS = [
        'qwqer_express_express',
        'qwqer_parcel_express',
        'qwqer_door_express'
    ];

    public const QWQER_REAL_TYPES = [
        'qwqer_express_express' => self::DELIVERY_ORDER_REAL_TYPE,
        'qwqer_door_express' => self::DELIVERY_ORDER_REAL_TYPE_DOOR,
        'qwqer_parcel_express' => self::DELIVERY_ORDER_REAL_TYPE_PARCEL,
    ];

    /**
     * Get is API integration enabled
     *
     * @param $storeId
     * @return bool
     */
    public function getIsQwqerEnabled($storeId = null): bool
    {
        return Mage::getStoreConfigFlag(
            self::API_IS_ENABLED,
            $storeId
        );
    }

    /**
     * Get is API integration enabled
     *
     * @return bool
     */
    public function getIsQwqerDoorEnabled($storeId = null): bool
    {
        return Mage::getStoreConfigFlag(
            self::API_IS_ENABLED_DOOR,
            $storeId
        );
    }

    /**
     * Get is API integration enabled
     *
     * @return bool
     */
    public function getIsQwqerParcelEnabled($storeId = null): bool
    {
        return Mage::getStoreConfigFlag(
            self::API_IS_ENABLED_PARCEL,
            $storeId
        );
    }

    /**
     * Get is API base url
     *
     * @param $storeId
     * @return mixed
     */
    public function getAPIBaseUrl($storeId = null)
    {
        return Mage::getStoreConfig(
            self::API_BASE_URL_PATH,
            $storeId
        );
    }

    /**
     * Get API bearer token
     *
     * @param $storeId
     * @return mixed
     */
    public function getApiBearerToken($storeId = null)
    {
        return Mage::getStoreConfig(
            self::API_BEARER_TOKEN,
            $storeId
        );
    }

    /**
     * Get API API_TRADING_POINT_ID
     *
     * @param $storeId
     * @return mixed
     */
    public function getTradingPointId($storeId = null)
    {
        return Mage::getStoreConfig(
            self::API_TRADING_POINT_ID,
            $storeId
        );
    }

    /**
     * Get API category
     *
     * @param $storeId
     * @return mixed
     */
    public function getCategory($storeId = null)
    {
        return Mage::getStoreConfig(
            self::API_CATEGORY,
            $storeId
        );
    }

    /**
     * Get parcel size
     *
     * @param $storeId
     * @return string
     */
    public function getParcelSize($storeId = null): string
    {
        return Mage::getStoreConfig(
            self::API_PARCEL_SIZE,
            $storeId
        );
    }

    /**
     * Get Store Address
     *
     * @param $storeId
     * @return mixed
     */
    public function getStoreAddress($storeId = null)
    {
        return Mage::getStoreConfig(
            self::API_STORE_ADDRESS,
            $storeId
        );
    }

    /**
     * Get Store Address Location
     *
     * @param $storeId
     * @return array
     */
    public function getStoreAddressLocation($storeId = null): array
    {
        $configData = Mage::getStoreConfig(
            self::API_STORE_ADDRESS_LOCATION,
            $storeId
        );
        if (!empty($configData)) {
            return explode(",", $configData);
        }
        return [];
    }

    /**
     * GetAutocompleteUrl
     *
     * @return string
     */
    public function getAutocompleteUrl()
    {
        return self::API_AUTOCOMPLETE_URL;
    }

    /**
     * @return string
     */
    public function getGeoCodeUrl()
    {
        return self::API_GEOCODE_URL;
    }

    /**
     * @return array|string|string[]
     */
    public function getShippingCost()
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_ORDER_PRICE_URL);
    }

    /**
     * @param $params
     * @return array|string|string[]
     */
    public function getOrderPlaceUrl()
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_ORDER_CREATE_URL);
    }

    /**
     * GetOrderInfoUrl
     *
     * @param $orderId
     * @return array|string|string[]
     */
    public function getOrderInfoUrl($orderId)
    {
        return str_replace('{order-id}', $orderId, self::API_ORDER_DETAILS_URL);
    }

    /**
     * @return array|string|string[]
     */
    public function getOrdersList()
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_ORDER_LIST_URL);
    }

    /**
     * @return string
     */
    public function getTradingPointUrl(): string
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_TRADING_POINT_INFO);
    }

    /**
     * Get Parcel Machines Url
     *
     * @return string
     */
    public function getParcelMachinesUrl(): string
    {
        return self::API_PARCEL_MACHINES_URL;
    }

    /**
     * @param $path
     * @return void
     */
    public function getStoreConfig($path)
    {
        Mage::getStoreConfig($path);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function checkWorkingHours($storeId)
    {
        $workingHoursConfig = Mage::getStoreConfig(
            self::API_WORKING_HOURS,
            $storeId
        );
        if(!$workingHoursConfig) {
            return true;
        }
        $workingHoursArray = json_decode($workingHoursConfig);
        if(!is_array($workingHoursArray) || empty($workingHoursArray)) {
            return true;
        }
        $days = ['Sunday','Monday', 'Tuesday', 'Wednesday','Thursday','Friday','Saturday'];
        $today = date("d") / 1;
        $dayOfWeek = $days[$today];
        $isOpen = false;
        foreach ($workingHoursArray as $workingHour) {
            if($workingHour->day_of_week == $dayOfWeek) {
                $startTimeObj = DateTime::createFromFormat('H:i', $workingHour->time_from);
                $endTimeObj = DateTime::createFromFormat('H:i', $workingHour->time_to);
                $currentTime = Mage::getModel('core/date')->date('H:i');
                $currentTimeObj = DateTime::createFromFormat('H:i', $currentTime);

                if ($currentTimeObj >= $startTimeObj && $currentTimeObj <= $endTimeObj) {
                    $isOpen = true;
                    break;
                }
            }
        }
        $currentDateTime = Mage::getModel('core/date')->date('H:i');

        return $isOpen;
    }
}