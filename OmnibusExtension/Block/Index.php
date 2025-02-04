<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as Helper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    public function __construct(
        Template\Context $context,
        private Helper $helper,
        private ProductRepositoryInterface $productRepository,
        private Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->registry->registry('current_product') ?? null;
    }

    /**
     * @param string $price
     * @return string
     */
    public function getPrice(string $price): string
    {
        return $this->helper->currency($price, true, false) ?? "";
    }

    /**
     * @param $item
     * @return ProductInterface|null
     * @throws NoSuchEntityException
     */
    public function getChildProduct($item): ProductInterface|null
    {
        try {
            if ($option = $item->getOptionByCode('simple_product')) {
                $productId = $option->getProduct()->getId();

                return $this->productRepository->getById($productId);
            }
        } catch (NoSuchEntityException $exception) {
            return null;
        }

        return null;
    }
}
