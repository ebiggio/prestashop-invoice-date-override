<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Install;

use Ebiggio\InvoiceDateOverride\Config\ModuleSettings;

use Module;
use Configuration;

class Installer
{
    public function install(Module $module): bool
    {
        if ( ! $this->registerPrestaShopHooks($module)) {
            return false;
        }

        if ( ! $this->saveDefaultSettings()) {
            return false;
        }

        return true;
    }

    /**
     * Registers the PrestaShop hooks that this module uses.
     *
     * @param Module $module The module instance.
     *
     * @return bool Whether the hooks were registered successfully.
     */
    private function registerPrestaShopHooks(Module $module): bool
    {
        $hooks = [
            'actionValidateOrderAfter',
            'actionOrderHistoryAddAfter'
        ];

        return $module->registerHook($hooks);
    }

    /**
     * Saves the default settings for the module.
     *
     * @return bool Whether the default settings were saved successfully.
     */
    private function saveDefaultSettings(): bool
    {
        foreach (ModuleSettings::SETTINGS as $setting_name => $setting_value) {
            if ( ! Configuration::updateValue($setting_name, $setting_value)) {
                return false;
            }
        }

        return true;
    }
}