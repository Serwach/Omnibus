<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Serwach\OmnibusExtension\Service\ConfigService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitHistoricalPrice extends Command
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FilterBuilder $filterBuilder,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private State $state,
        private ConfigService $configService,
        private TimezoneInterface $timezone,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return array
     */
    private function getProducts(): array
    {
        $filter = $this->filterBuilder
            ->setField('type_id')
            ->setConditionType('eq')
            ->setValue('simple')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }

    protected function configure(): void
    {
        $this->setName('serwach:omnibus:init')
            ->setDescription('SUPREMIS initialization for Omnibus directive');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->configService->isEnabled()) {
            try {
                $this->state->setAreaCode(Area::AREA_GLOBAL);
                $products = $this->getProducts();
                $output->writeln('Start');

                foreach ($products as $product) {
                    if ($product->getTypeId() === "configurable" || $product->getTypeId() === "grouped") {
                        continue;
                    }

                    $output->writeln('inside foreach: sku = ' . $product->getSku());
                    $product->setData('historical_price', $product->getPrice());
                    $product->setData('historical_price_updated_at', $this->timezone->date()->format('Y-m-d'));
                    $this->productRepository->save($product);
                }

            } catch (CouldNotSaveException|InputException|StateException $e) {
                $output->writeln($e->getMessage());
            }
        }

        $output->writeln('End');

        return 0;
    }
}
