<?php
namespace Mumzworld\ExerciseThree\Model\Resolver;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class OrderResolver implements ResolverInterface
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $order = $this->orderRepository->get($args['order_id']);
        return [
            'shipping_address' => $order->getShippingAddress(),
            'billing_address' => $order->getBillingAddress()
        ];
    }
}
