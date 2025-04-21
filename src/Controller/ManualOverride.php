<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Configuration;


class ManualOverride extends FrameworkBundleAdminController
{
    public function index(Request $request): JsonResponse
    {
        $request_data = json_decode($request->getContent(), true);

        $ignore_previous_date = (bool)$request_data['ignore_previous_date'] ?? false;
        $clear_on_unselected_status_change = (bool)$request_data['clear_on_unselected_status_change'] ?? false;
        $order_id_min_range = (int)$request_data['order_id_min_range'] ?? 0;
        $order_id_max_range = (int)$request_data['order_id_max_range'] ?? 0;

        // If no status were selected, return an error
        if (empty($selected_status)) {
            return new JsonResponse(['message' => 'No status selected to override'], 400);
        }

        // Perform simple validations for the ranges
        if ($order_id_min_range < 0 || $order_id_max_range < 0) {
            return new JsonResponse(['message' => 'Invalid order ID range'], 400);
        }

        if ($order_id_min_range > $order_id_max_range) {
            return new JsonResponse(['message' => 'Invalid order ID range'], 400);
        }

        // TODO: Implement the logic to select orders based on the selected status and the invoice date
        return new JsonResponse(['success' => 'Manual override executed successfully.']);
    }
}