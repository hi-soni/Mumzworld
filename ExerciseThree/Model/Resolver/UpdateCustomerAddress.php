<?php
namespace Mumzworld\ExerciseThree\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\CustomerGraphQl\Model\Customer\Address\UpdateCustomerAddress as UpdateAddress;
use Magento\Customer\Model\Session;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class UpdateCustomerAddress implements ResolverInterface
{
    private $updateCustomerAddress;
    private $customerSession;

    public function __construct(
        UpdateAddress $updateCustomerAddress,
        Session $customerSession
    ) {
        $this->updateCustomerAddress = $updateCustomerAddress;
        $this->customerSession = $customerSession;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$this->customerSession->isLoggedIn()) {
            throw new GraphQlInputException(__('The customer is not logged in.'));
        }

        try {
            $addressData = $args['input'];
            $addressData['customer_id'] = $this->customerSession->getCustomerId();
            $addressId = $args['id'];

            return $this->updateCustomerAddress->execute($addressId, $addressData);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
