<?php

class Loewenstark_Gtag_Model_Observer
{
    /**
     * from Mage_GoogleAnalytics_Model_Observer::setGoogleAnalyticsOnOrderSuccessPageView
     * 
     * @param Varien_Event_Observer $observer
     */
    public function setGoogleAnalyticsOnOrderSuccessPageView(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('gtag');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}