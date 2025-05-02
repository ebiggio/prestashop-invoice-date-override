<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Form;

use Ebiggio\InvoiceDateOverride\Config\ModuleSettings;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Context;
use OrderState;

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

        foreach (ModuleSettings::SETTINGS as $settings_name => $settings_value) {
            if ('INVOICE_DATE_OVERRIDE_ORDER_STATUS' === $settings_name) {
                $configuration[strtolower($settings_name)] = json_decode($this->configuration->get($settings_name), true);
            } else {
                $configuration[strtolower($settings_name)] = (bool)$this->configuration->get($settings_name);
            }
        }

        // Default values for the manual setting of ignoring previous invoice date
        $configuration['invoice_date_override_manual_ignore_previous_date'] = false;

        return $configuration;
    }

    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            foreach ($configuration as $settings_name => $settings_value) {
                // We're not interested in saving the manual settings, since they are intended to be defined on the fly
                if (str_contains($settings_name, 'manual')) {
                    continue;
                }

                $this->configuration->set(strtoupper($settings_name), is_array($settings_value) ? json_encode($settings_value) : $settings_value);
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
        $is_valid_configuration = true;

        foreach ($configuration as $settings_name => $settings_value) {
            // We don't validate the manual settings
            if (str_contains($settings_name, 'manual')) {
                continue;
            }

            // We check if the form fields exist in the module settings
            if ( ! isset(ModuleSettings::SETTINGS[strtoupper($settings_name)])) {
                return false;
            }

            if ('invoice_date_override_order_status' === $settings_name) {
                // At least one order status must be selected. We also check if the setting is an array
                if (empty($settings_value) || ! is_array($settings_value)) {
                    $this->errors['invoice_date_override_order_status'] = $context->getTranslator()->trans(
                        'At least one order status must be selected for the invoice date override. If you would like to avoid overriding the invoice date, please disable the module.',
                        [],
                        'Modules.InvoiceDateOverride.Admin'
                    );
                    $is_valid_configuration = false;
                }

                $order_states = OrderState::getOrderStates(Context::getContext()->language->id);
                $order_states_ids = array_column($order_states, 'id_order_state');
                // Check if the selected order statuses are valid
                foreach ($settings_value as $potential_order_status_id) {
                    if ( ! in_array($potential_order_status_id, $order_states_ids)) {
                        $this->errors['invoice_date_override_order_status'] = $context->getTranslator()->trans(
                            'The selected order status is not valid. Please select a valid order status.',
                            [],
                            'Modules.InvoiceDateOverride.Admin'
                        );
                        $is_valid_configuration = false;
                    }
                }
            } else {
                // The rest of the settings, behave like a "boolean switch", so their values can be either `true` or `false`
                if ( ! in_array($settings_value, [true, false], true)) {
                    $this->errors[$settings_name] = $context->getTranslator()->trans(
                        'The value is not valid.',
                        [],
                        'Modules.InvoiceDateOverride.Admin'
                    );
                }
            }
        }

        return $is_valid_configuration;
    }
}