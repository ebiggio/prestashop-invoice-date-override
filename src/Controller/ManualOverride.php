<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Controller;

use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Configuration;
use Context;
use Exception;

class ManualOverride extends FrameworkBundleAdminController
{
    /**
     * @throws ContainerNotFoundException
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        $request_data = json_decode($request->getContent(), true);

        $ignore_previous_date = (bool)$request_data['ignore_previous_date'] ?? false;
        $order_id_min_range = (int)$request_data['order_id_min_range'] ?? null;
        $order_id_max_range = (int)$request_data['order_id_max_range'] ?? null;
        $status_ids = json_decode(Configuration::get('INVOICE_DATE_OVERRIDE_ORDER_STATUS'), true);

        // If no status were selected, return an error
        if (empty($status_ids)) {
            return new JsonResponse(['message' => 'No status selected to override'], 400);
        }

        // Perform simple validations for the ranges
        if ($order_id_min_range < 0 || $order_id_max_range < 0) {
            return new JsonResponse(['message' => 'Invalid order ID range'], 400);
        }

        if ($order_id_min_range > $order_id_max_range) {
            return new JsonResponse(['message' => 'Invalid order ID range'], 400);
        }

        $context = Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();
        $order_service = $container->get('ebiggio.invoice_date_override.service.order_service');

        $updated_orders = $order_service->updateInvoiceDates(
            $status_ids,
            $ignore_previous_date,
            $order_id_min_range,
            $order_id_max_range
        );

        if ($updated_orders > 0) {
            return new JsonResponse(['message' => 'Invoice dates updated successfully. Total orders updated: ' . $updated_orders]);
        } else {
            return new JsonResponse(['message' => 'The process ran successfully, but no orders were updated.']);
        }
    }
}