<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Config;

class ModuleSettings
{
    /**
     * Module settings, with their default values.
     *
     * @var array
     */
    public const array SETTINGS = [
        'INVOICE_DATE_OVERRIDE_ORDER_STATUS'                             => [],
        'INVOICE_DATE_OVERRIDE_ON_NEW_ORDER'                             => false,
        'INVOICE_DATE_OVERRIDE_ON_STATUS_CHANGE'                         => false,
        'INVOICE_DATE_OVERRIDE_CLEAR_ON_UNSELECTED_STATUS_CHANGE'        => false
    ];
}