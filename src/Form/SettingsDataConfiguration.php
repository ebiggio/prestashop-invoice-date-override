<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Form;

use Ebiggio\InvoiceDateOverride\Config\ModuleSettings;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Context;

class SettingsDataConfiguration implements DataConfigurationInterface
{
    /**
     * @var ConfigurationInterface
     */
    private ConfigurationInterface $configuration;

    /**
     * Holds the errors that occurred while updating the configuration.
     *
     * @var array
     */
    private array $errors = [];

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $configuration = [];

        foreach (ModuleSettings::SETTINGS as $settingName => $settingValue) {
            if ($settingName === 'INVOICE_DATE_OVERRIDE_ORDER_STATUS') {
                $configuration[strtolower($settingName)] = json_decode($this->configuration->get($settingName), true);
            } else {
                $configuration[strtolower($settingName)] = (bool)$this->configuration->get($settingName);
            }
        }

        return $configuration;
    }

    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            foreach ($configuration as $key => $value) {
                $this->configuration->set(strtoupper($key), is_array($value) ? json_encode($value) : $value);
            }
        }

        return $this->errors;
    }

    /**
     * Validates the configuration values, setting the errors in the `$errors` property if any validation fails.
     *
     * @param array $configuration Configuration values to validate.
     *
     * @return bool True if the configuration is valid, false otherwise.
     */
    public function validateConfiguration(array $configuration): bool
    {
        $context = Context::getContext();
        $isValidConfiguration = true;

        return $isValidConfiguration;
    }
}