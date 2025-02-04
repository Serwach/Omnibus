<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Plugin;

use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Serwach\OmnibusExtension\Service\ConfigService;

class FinalPricePlugin
{
    public function __construct(
        private ConfigService $configService
    ) {}

    /**
     * @param FinalPriceBox $subject
     * @param $template
     * @return array|string[]
     */
    public function beforeSetTemplate(FinalPriceBox $subject, $template)
    {
        if ($this->configService->isEnabled()) {
            if ($template == 'Magento_Catalog::product/price/final_price.phtml') {
                return ['Serwach_OmnibusExtension::product/price/final_price.phtml'];
            } else {
                return [$template];
            }
        } else {
            return [$template];
        }
    }
}
