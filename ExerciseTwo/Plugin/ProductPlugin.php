<?php

namespace Mumzworld\ExerciseTwo\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ProductPlugin
{
    const XML_PATH_ENABLE_RETURN_ELIGIBILITY = 'exercise_two/general/enable';

    protected $scopeConfig;

    /**
     * ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterGetById(
        ProductRepositoryInterface $subject,
        $result,
        $productId
    ) {
        // Check if the system config to display return eligibility is enabled
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLE_RETURN_ELIGIBILITY, ScopeInterface::SCOPE_STORE);

        if (!$isEnabled) {
            // If it's not enabled, set the return eligibility days to 0
            //$result->setCustomAttribute('eligible_for_return', 0);
            $result->setData('eligible_for_return', 0);
        } else {
            $result->setData('eligible_for_return', $result->getCustomAttribute('eligible_for_return')->getValue());
        }

        return $result;
    }
}
