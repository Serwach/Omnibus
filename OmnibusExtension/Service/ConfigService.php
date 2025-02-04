<?php

declare(strict_types=1);

namespace Serwach\OmnibusExtension\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigService
{
    private const IS_ENABLED = 'serwach_omnibus_extension/configuration/is_enabled';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private StoreManagerInterface $storeManager
    ) {}

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        try {
            $website = $this->storeManager->getStore()->getWebsite();
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            self::IS_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $website->getCode()
        );
    }
}
