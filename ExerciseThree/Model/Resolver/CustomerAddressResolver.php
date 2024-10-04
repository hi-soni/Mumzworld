<?php
namespace Mumzworld\ExerciseThree\Model\Resolver;

use Magento\Customer\Model\Session;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CustomerAddressResolver implements ResolverInterface
{
    private $customerSession;

    public function __construct(Session $customerSession)
    {
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

        $customer = $this->customerSession->getCustomer();
        return $customer->getAddresses();
    }
}
