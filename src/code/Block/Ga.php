<?php

class Loewenstark_Gtag_Block_Ga
extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Google Order Tracking
     * 
     * @return string
     */
    public function getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        // added Adwords Data
        if ($this->getAwAccount() && $this->getAwAccountOnce())
        {
            $result[] = '  gtag(\'config\', \''.$this->getAwAccount().'\');';
        }
        foreach ($collection as $order)
        {
            /* @var $order Mage_Sales_Model_Order */
            $data = array(
                'transaction_id' => $order->getIncrementId(),
                'affiliation'    => Mage::app()->getStore()->getFrontendName(),
                'value'          => round((float)$order->getBaseGrandTotal(), 2),
                'currency'       => $order->getBaseCurrencyCode(),
                'tax'            => round((float)$order->getBaseTaxAmount(), 2),
                'shipping'       => round((float)$order->getBaseShippingAmount(), 2),
                'items'          => array()
            );
            $i = 0;
            foreach ($order->getAllVisibleItems() as $item)
            {
                /* @var $item Mage_Sales_Model_Order_Item */
                $i++;
                $data['items'][] = array(
                    'id'            => $item->getSku(),
                    'name'          => $item->getName(),
                    // 'brand'         => 'BRAND',
                    'category'      => null,
                    'list_position' => $i,
                    'quantity'      => (float) $item->getQtyOrdered(),
                    'price'         => (float)round((float)$item->getBasePrice(), 2)
                );
            }
            $result[] = '  gtag(\'event\', \'purchase\', '.$this->jsonEncode($data, true).');';
            if ($this->getAwAccount() && $this->getAwLabel())
            {
                $awData = array(
                    'send_to'    => $this->getAwAccount().'/'.$this->getAwLabel(),
                    'value'      => round((float)$order->getBaseGrandTotal(), 2),
                    'currency'   => $order->getBaseCurrencyCode()
                );
                $result[] = '  gtag(\'event\', \'conversion\', '.$this->jsonEncode($awData, true).');'; 
            }
        }
        return implode("\n", $result);
    }

    /**
     * 
     * @return boolean
     */
    public function getGtagOptions()
    {
        $result = array(
            'send_page_view' => true
        );
        if ($this->isAnonymizationEnabled())
        {
            $result['anonymize_ip'] = true;
        }
        return $this->jsonEncode($result, false);
    }

    /**
     * 
     * @return boolean
     */
    public function isAnonymizationEnabled()
    {
        // check on default and MageSetup also!
        if (Mage::getStoreConfig('google/analytics/anonymization'))
        {
            return true;
        }
        if (Mage::getStoreConfig('google/analytics/ip_anonymization'))
        {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getAwAccountOnce()
    {
        if (!Mage::registry('aw_code_already_used'))
        {
            Mage::register('aw_code_already_used', true);
            return $this->getAwAccount();
        }
        return false;
    }

    /**
     * 
     * @return string|boolean
     */
    public function getAwAccount()
    {
        if(!Mage::getStoreConfigFlag('google/adwords/active'))
        {
            return false;
        }
        return Mage::getStoreConfig('google/adwords/account');
    }

    /**
     * 
     * @return string|boolean
     */
    public function getAwLabel()
    {
        if(!Mage::getStoreConfigFlag('google/adwords/active'))
        {
            return false;
        }
        return Mage::getStoreConfig('google/adwords/label');
    }

    /**
     * 
     * @return boolean
     */
    protected function _isAvailable() {
        return parent::_isAvailable();
    }

    /**
     * JSON ENCODE
     * 
     * @param mixed $data
     * @return string
     */
    public function jsonEncode($data, $format = false)
    {
        $jsonOptions = 0;
        if ($format && defined('JSON_PRETTY_PRINT'))
        {
            $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        }
        return json_encode($data, $jsonOptions);
    }
}
