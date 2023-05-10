<?php
class Qwqer_Express_Model_Api_Client
{
    protected $_client;
    protected function _getApiKey()
    {
        $key = Mage::helper('qwqer_express')->getApiBearerToken();
        if (!trim($key)) {
            throw new Exception('No API key configured');
        }
        return $key;
    }
    protected function _getClient()
    {
        if (!$this->_client) {
            $this->_client = new Qwqer_Express_Model_Api_Service_ExecuteRequest(
                $this->_getApiKey(),
                FALSE,
                'curl'
            );
        }
        return $this->_client;
    }

    public function getConnection(){
        return $this->_getClient();
    }
}
