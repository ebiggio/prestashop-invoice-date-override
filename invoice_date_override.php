<?php
/**
 * Invoice date override module.
 *
 * Updates the invoice date of orders according to several rules.
 *
 * @author Enzo Biggio <ebiggio@gmail.com>
 * @version 1.0.0
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
        $this->version = '1.0.0';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->tab = 'administration';
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('Invoice date override', [], 'Modules.InvoiceDateOverride.Admin');
        $this->description = $this->trans(
            'Updates the invoice date of orders according to several rules.'
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

    /**
     * Hook that fires after an order is validated by PrestaShop.
     *
     * @param array $params The parameters passed to the hook.
     *
     * @return void
     */
    public function hookActionValidateOrderAfter(array $params): void
    {
        if ( ! Configuration::get('INVOICE_DATE_OVERRIDE_ON_NEW_ORDER')) {
            return;
        }

        $order = $params['order'];
        $order_status = $params['orderStatus'];

        if ( ! in_array(
            (string)$order_status->id,
            json_decode(Configuration::get('INVOICE_DATE_OVERRIDE_ORDER_STATUS'), true),
            true)) {
            return;
        }

        $order->invoice_date = $order->date_add;
        $order->update();
    }


    /**
     * Hook that fires after an order history is added, which is when the order status changes.
     *
     * @param array $params The parameters passed to the hook.
     *
     * @return void
     * @throws Exception
     */
    public function hookActionOrderHistoryAddAfter(array $params): void
    {
        if ( ! Configuration::get('INVOICE_DATE_OVERRIDE_ON_STATUS_CHANGE')
            && ! Configuration::get('INVOICE_DATE_OVERRIDE_CLEAR_ON_UNSELECTED_STATUS_CHANGE')) {
            return;
        }

        $order_history = $params['order_history'];

        $order = new Order((int)$order_history->id_order);
        $new_order_status_id = (string)$order_history->id_order_state;
        $is_selected_status = in_array(
            $new_order_status_id,
            json_decode(Configuration::get('INVOICE_DATE_OVERRIDE_ORDER_STATUS'), true),
            true
        );

        if (Configuration::get('INVOICE_DATE_OVERRIDE_ON_STATUS_CHANGE') && $is_selected_status) {
            $order->invoice_date = $order_history->date_add;
            $order->update();

            return;
        }

        if (Configuration::get('INVOICE_DATE_OVERRIDE_CLEAR_ON_UNSELECTED_STATUS_CHANGE') && ! $is_selected_status) {
            $order->invoice_date = null;
            $order->update();
        }
    }
}