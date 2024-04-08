<?php
class Qwqer_Express_Model_Cron extends Mage_Core_Model_Abstract {

    /**
     * @return void
     */
    public function execute()
    {
        $helper = Mage::helper('qwqer_express');
        if ($helper->getIsQwqerEnabled() || $helper->getIsQwqerDoorEnabled() || $helper->getIsQwqerParcelEnabled()){
            $data = Mage::getSingleton('qwqer_express/api_client')->getConnection()->getTradingPointInfo();
            if (!empty($data['data']['working_hours'])) {
                Mage::getConfig()->saveConfig(
                    Qwqer_Express_Helper_Data::API_WORKING_HOURS,
                    json_encode($data['data']['working_hours']),
                    'default',
                    0
                );
            }
        }
    }
}