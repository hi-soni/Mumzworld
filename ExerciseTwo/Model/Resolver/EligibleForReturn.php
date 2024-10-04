<?php

declare(strict_types=1);

namespace Mumzworld\ExerciseTwo\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class EligibleForReturn implements ResolverInterface
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $values = null,
        array $args = null
    ) {
        /** @var Product $product */
        $product = $values['model'];

        // Check if the config value is enabled
        if ($this->scopeConfig->isSetFlag('exercise_two/general/enable', ScopeInterface::SCOPE_STORE)) {
            return (string)$product->getData('eligible_for_return');
        }

        return '0'; // Return '0' if the config value is disabled
    }
}
