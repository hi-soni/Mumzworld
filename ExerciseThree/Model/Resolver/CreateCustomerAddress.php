<?php
namespace Mumzworld\ExerciseThree\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\CustomerGraphQl\Model\Customer\Address\CreateCustomerAddress as CreateAddress;
use Magento\Customer\Model\Session;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CreateCustomerAddress implements ResolverInterface
{
    private $createCustomerAddress;
    private $customerSession;

    public function __construct(
        CreateAddress $createCustomerAddress,
        Session $customerSession
    ) {
        $this->createCustomerAddress = $createCustomerAddress;
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

            return $this->createCustomerAddress->execute($addressData);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
