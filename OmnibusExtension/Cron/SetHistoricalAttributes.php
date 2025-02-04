<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Cron;

use Serwach\OmnibusExtension\Service\ConfigService;
use Serwach\OmnibusExtension\Service\OmnibusService;

class SetHistoricalAttributes
{
    public function __construct(
        private ConfigService  $configService,
        private OmnibusService $omnibusService
    ) {}

    public function execute(): void
    {
        if ($this->configService->isEnabled()) {
            $products = $this->omnibusService->getProductCollection();

            foreach ($products as $product) {
                $this->omnibusService->setHistoricalAttributes($product);
            }
        }
    }
}
