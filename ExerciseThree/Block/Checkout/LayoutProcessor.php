<?php

namespace Mumzworld\ExerciseThree\Block\Checkout;

class Layoutprocessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    public function process($jsLayout)
    {
        $customAttributeCode = 'area';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $this->getAreaAttributeForAddress($customAttributeCode, 'shippingAddress');
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']))
        {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $payment)
            {
                $paymentCode = 'billingAddress'.str_replace('-form','',$key);
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$customAttributeCode] = $this->getAreaAttributeForAddress($customAttributeCode, $paymentCode);
            }

        }

        return $jsLayout;
    }

    public function getAreaAttributeForAddress($customAttributeCode, $addressType) {
        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => $addressType . '.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
            ],
            'dataScope' => $addressType . '.custom_attributes.' . $customAttributeCode,
            'label' => 'Area',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 150,
            'options' => [],
            'validation' => [
                'required-entry' => true
            ]
        ];

        return $customField;
    }
}
