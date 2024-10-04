<?php

namespace Mumzworld\ExerciseThree\Plugin;

class ShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Quote\Model\QuoteRepository
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Add area attribute in quote address
     * 
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
        $area = $extAttributes->getArea();
        $quote = $this->quoteRepository->getActive($cartId);
        
        if ($area) {
            $quote->getShippingAddress()->setArea($area);
            $quote->getBillingAddress()->setArea($area);
        }
    }
}