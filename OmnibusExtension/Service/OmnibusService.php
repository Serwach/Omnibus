<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OmnibusService
{
    public function __construct(
        private CollectionFactory          $collection,
        private ProductRepositoryInterface $productRepository,
        private TimezoneInterface          $timezone
    ) {}

    public function getProductCollection(): array
    {
        $collection = $this->collection->create()->addFinalPrice();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('price', ['gt' => 0]);

        return $collection->getItems();
    }

    public function setHistoricalAttributes(Product $product): void
    {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $basePrice = $product->getPrice();
            $minimalPrice = $product->getMinimalPrice();

            if ($basePrice > $minimalPrice) {
                if (!empty($product->getData('historical_price')) && !empty($minimalPrice)
                    && $minimalPrice >= $product->getData('historical_price')
                ) {
                    return;
                } elseif (!empty($product->getData('historical_price')) && !empty($minimalPrice)
                    && $minimalPrice < $product->getData('historical_price')) {
                    $logger->info($product->getSku() . " setting case 1 = " . $minimalPrice);
                    $product->setData('historical_price', $minimalPrice);
                    $product->setData('historical_price_updated_at', $this->timezone->date()->format('Y-m-d'));
                    $this->productRepository->save($product);
                } else {
                    $logger->info($product->getSku() . " setting case 2 = " . $basePrice);
                    $product->setData('historical_price', $basePrice);
                    $product->setData('historical_price_updated_at', $this->timezone->date()->format('Y-m-d'));
                    $this->productRepository->save($product);
                }
            }
        } catch (CouldNotSaveException|InputException|StateException) {
            return;
        }
    }
}
