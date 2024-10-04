<?php

namespace Mumzworld\ExerciseThree\Plugin;

use Magento\Customer\Api\Data\AddressInterface;

class AddressRepositoryPlugin
{
    public function afterGetById(AddressInterface $subject, $result)
    {
        $extensionAttributes = $result->getExtensionAttributes();
        $extensionAttributes->setArea($result->getCustomAttribute('area') ? $result->getCustomAttribute('area')->getValue() : null);
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
