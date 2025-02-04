<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Cron;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Serwach\OmnibusExtension\Service\ConfigService;

class CleanupSpecialPrices
{
    public function __construct(
        private ConfigService              $configService,
        private FilterBuilder              $filterBuilder,
        private SearchCriteriaBuilder      $searchCriteriaBuilder,
        private ProductRepositoryInterface $productRepository,
        private TimezoneInterface          $timezone
    ) {}

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->configService->isEnabled()) {
            $date = $this->timezone
                ->date(strtotime($this->timezone->date()->format('Y-m-d')))
                ->format('Y-m-d');

            $filter = $this->filterBuilder
                ->setField('special_to_date')
                ->setConditionType('lt')
                ->setValue($date)
                ->create();

            $this->searchCriteriaBuilder->addFilters([$filter]);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $products = $this->productRepository->getList($searchCriteria)->getItems();

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/serwach_omnibus_cleanup.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            foreach ($products as $product) {
                $product->setData('special_from_date');
                $product->setData('special_price');
                $product->setData('special_to_date');
                $logger->info('removing special price and date for sku = ' . $product->getSku());

                try {
                    $this->productRepository->save($product);
                } catch (Exception $e) {
                    $logger->info('cannot remove for sku = ' . $product->getSku() . ' error = ' . $e->getMessage());
                }
            }
        }
    }
}
