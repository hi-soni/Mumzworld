<?php

namespace Mumzworld\ExerciseTwo\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\Template;

/**
 * @deprecated not in use
 */
class ProductView extends Template
{
    protected $productRepository;

    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    // public function getEligibleForReturn($productId)
    // {
    //     $product = $this->productRepository->getById($productId);
    //     return $product->getData('eligible_for_return');
    // }
}
