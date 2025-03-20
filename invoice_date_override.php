<?php
/**
 * Invoice date override module.
 *
 * Updates the invoice date of orders according to several rules.
 *
 * @author Enzo Biggio <ebiggio@gmail.com>
 * @version 0.0.1
 * @licence GNU General Public License 3.0
 */
declare(strict_types = 1);

use Ebiggio\InvoiceDateOverride\Install\Installer;
use Ebiggio\InvoiceDateOverride\Install\Uninstaller;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if ( ! defined('_PS_VERSION_')) exit;

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Invoice Date Override module main class.
 */
class Invoice_Date_Override extends Module
{
    public function __construct()
    {
        $this->name = 'invoice_date_override';
        $this->author = 'Enzo Biggio';
        $this->version = '0.0.1';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->tab = 'administration';
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('Invoice date override', [], 'Modules.InvoiceDateOverride.Admin');
        $this->description = $this->trans('Updates the invoice date of orders according to several rules.'
            , []
            , 'Modules.InvoiceDateOverride.Admin'
        );
    }

    public function install(): bool
    {
        $this->_clearCache('*');

        if ( ! parent::install()) {
            return false;
        }

        return (new Installer())->install($this);
    }

    public function uninstall(): bool
    {
        $this->_clearCache('*');

        if ( ! parent::uninstall()) {
            return false;
        }

        return (new Uninstaller())->uninstall();
    }

    /**
     * {@inheritDoc}
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Redirects the user to the module configuration page.
     *
     * @return void
     */
    public function getContent(): void
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('invoice_date_override_settings'));
    }
}