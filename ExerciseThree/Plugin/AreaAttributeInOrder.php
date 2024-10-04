<?php
namespace Mumzworld\ExerciseThree\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;

class AreaAttributeInOrder
{
    /**
     * @var OrderAddressExtensionInterfaceFactory
     */
    private $addressExtensionInterfaceFactory;

    public function __construct(
        OrderAddressExtensionInterfaceFactory $addressExtensionInterfaceFactory
    ) {
        $this->addressExtensionInterfaceFactory = $addressExtensionInterfaceFactory;
    }

    /**
     * Plugin to add area extension attribute to Order Repository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->addAreaInOrderAddress($order);
        return $order;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(\Magento\Sales\Api\OrderRepositoryInterface $subject,
         \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult)
    {
        foreach ($searchResult->getItems() as $order) {
            $this->addAreaInOrderAddress($order);
        }
        
        return $searchResult;
    }

    public function addAreaInOrderAddress($order) {
        if (!empty($order)) {
            /**
             * @var OrderAddressInterface  $billingAddress
             */
            $billingAddress = $order->getBillingAddress();

            $billingAddressExtensionAttributes = (null !== $billingAddress->getExtensionAttributes())?
                $billingAddress->getExtensionAttributes():
                $this->addressExtensionInterfaceFactory->create();
            $billingAddressExtensionAttributes->setArea($billingAddress->getArea());

            $billingAddress->setExtensionAttributes($billingAddressExtensionAttributes);

            if (!$order->getIsVirtual()) {
                $shippingAddress = $order->getShippingAddress();
                $shippingAddressExtensionAttributes = (null!== $shippingAddress->getExtensionAttributes())?
                    $shippingAddress->getExtensionAttributes():
                    $this->addressExtensionInterfaceFactory->create();

                $shippingAddressExtensionAttributes->setArea($shippingAddress->getArea());
                $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);
            }
        }
    }
}