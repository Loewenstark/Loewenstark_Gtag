<?php

class Loewenstark_Gtag_Helper_Data
extends Mage_GoogleAnalytics_Helper_Data
{

    const XML_PATH_ACCOUNT       = 'google/analytics/account';

    /**
     * get Account Id
     * 
     * @param mixed $store
     * @return string
     */
    public function getAccountId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNT, $store);
    }
}
