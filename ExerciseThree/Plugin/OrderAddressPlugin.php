<?php

namespace Mumzworld\ExerciseThree\Plugin;

use Magento\Sales\Api\Data\OrderAddressInterface;

class OrderAddressPlugin
{
    public function afterGet(OrderAddressInterface $subject, $result)
    {
        $extensionAttributes = $result->getExtensionAttributes();
        if ($extensionAttributes && !$extensionAttributes->getArea()) {
            $extensionAttributes->setArea($result->getCustomAttribute('area') ? $result->getCustomAttribute('area')->getValue() : null);
        }
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }

    public function beforeSave(OrderAddressInterface $subject, $address)
    {
        $extensionAttributes = $address->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getArea()) {
            $address->setCustomAttribute('area', $extensionAttributes->getArea());
        }
        return $address;
    }
}
