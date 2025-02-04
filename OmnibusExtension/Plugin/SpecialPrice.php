<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Plugin;

use Magento\Catalog\Api\SpecialPriceStorageInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Serwach\OmnibusExtension\Service\ConfigService;

class SpecialPrice
{
    public function __construct(
        private CollectionFactory $collection,
        private ConfigService $configService,
        private ProductRepositoryInterface $productRepository,
        private TimezoneInterface $timezone
    ) {}

    /**
     * @param string $sku
     * @param float $price
     * @return void
     */
    private function updateHistoricalPrice(string $sku, float $price): void
    {
        try {
            $product = $this->productRepository->get($sku);
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();

            if ($product->getData('historical_price') && $price > $product->getData('historical_price')) {
                return;
            }

            if (!$product->getData('historical_price')) {
                $product->setData('historical_price', $regularPrice);
            } elseif ($product->getSpecialPrice()) {
                $product->setData('historical_price', $product->getSpecialPrice());
            }

            $product->setData('historical_price_updated_at', $this->timezone->date()->format('Y-m-d'));
            $this->productRepository->save($product);

            return;
        } catch (NoSuchEntityException|CouldNotSaveException|StateException|InputException $e) {
            return;
        }
    }

    /**
     * @param SpecialPriceStorageInterface $subject
     * @param array $prices
     * @return void
     */
    public function beforeUpdate(SpecialPriceStorageInterface $subject, array $prices): void
    {
        if ($this->configService->isEnabled()) {
            foreach ($prices as $price) {
                $this->updateHistoricalPrice($price->getSku(), $price->getPrice());
            }
        }
    }

    /**
     * @param SpecialPriceStorageInterface $subject
     * @param array $prices
     * @return void
     */
    public function beforeDelete(SpecialPriceStorageInterface $subject, array $prices): void
    {
        if ($this->configService->isEnabled()) {
            foreach ($prices as $price) {
                $this->updateHistoricalPrice($price->getSku(), $price->getPrice());
            }
        }
    }
}
