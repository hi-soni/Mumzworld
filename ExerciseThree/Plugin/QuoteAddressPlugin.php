<?php

namespace Mumzworld\ExerciseThree\Plugin;

use Magento\Quote\Api\Data\AddressInterface;

class QuoteAddressPlugin
{
    public function afterGet(AddressInterface $subject, $result)
    {
        $extensionAttributes = $result->getExtensionAttributes();
        if ($extensionAttributes && !$extensionAttributes->getArea()) {
            $extensionAttributes->setArea($result->getCustomAttribute('area') ? $result->getCustomAttribute('area')->getValue() : null);
        }
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }

    public function beforeSave(AddressInterface $subject, $address)
    {
        $extensionAttributes = $address->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getArea()) {
            $address->setCustomAttribute('area', $extensionAttributes->getArea());
        }
        return $address;
    }
}
