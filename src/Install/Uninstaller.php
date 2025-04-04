<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Install;

use Ebiggio\InvoiceDateOverride\Config\ModuleSettings;

use Configuration;

class Uninstaller
{
    /**
     * Performs the uninstallation process, deleting the module's configuration settings.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        foreach (ModuleSettings::SETTINGS as $setting_name) {
            if ( ! Configuration::deleteByName($setting_name)) {
                return false;
            }
        }

        return true;
    }
}