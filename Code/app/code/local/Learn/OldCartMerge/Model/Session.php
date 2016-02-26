<?php
class Learn_OldCartMerge_Model_Session extends Mage_Checkout_Model_Session
{
    public function loadCustomerQuote()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            return $this;
        }

        Mage::dispatchEvent('load_customer_quote_before', array('checkout_session' => $this));
        $customerQuote = Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

        /*-  Merging the old cart items. -*/
        /*        
        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                $customerQuote->merge($this->getQuote())->collectTotals()->save();
            }
            $this->setQuoteId($customerQuote->getId());
            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;
        }
        */
        
        /*-  Removing old cart items of the customer. -*/
        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            foreach ($customerQuote->getAllItems() as $item) {
                $item->isDeleted(true);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $child->isDeleted(true);
                    }
                }
            }
            $customerQuote->collectTotals()->save();
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer())
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();
        }
        return $this;
    }


}