<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Serwach\OmnibusExtension\Service\ConfigService;
use Serwach\OmnibusExtension\Service\OmnibusService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetHistoricalAttributes extends Command
{
    public function __construct(
        private ConfigService  $configService,
        private OmnibusService $omnibusService,
        private State          $state,
        string                 $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('serwach:omnibus:set')
            ->setDescription('SUPREMIS setting command for Omnibus directive');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->configService->isEnabled()) {
            try {
                $this->state->setAreaCode(Area::AREA_GLOBAL);
            } catch (LocalizedException) {
                return 0;
            }

            $products = $this->omnibusService->getProductCollection();
            $output->writeln('Start');

            foreach ($products as $product) {
                $this->omnibusService->setHistoricalAttributes($product);
            }
        }

        $output->writeln('End');

        return 0;
    }
}
