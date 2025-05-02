<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class OrderService
{
    private Connection $connection;
    private string $table;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->table = _DB_PREFIX_ . 'orders';
    }

    /**
     * Updates invoice_date of orders matching status list (and optional filters),
     * and optionally clears it from the rest.
     *
     * @param array $status_ids List of order status IDs to match
     * @param bool $ignore_previous_date Whether to consider orders with a previous invoice_date or not
     * @param int $min_id Filter by minimum order ID
     * @param int $max_id Filter by maximum order ID
     *
     * @return int Number of updated orders
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    public function updateInvoiceDates(
        array $status_ids,
        bool  $ignore_previous_date = false,
        int  $min_id = 0,
        int  $max_id = 0,
    ): int
    {
        // Step 1: Build SELECT query to find matching orders
        $qb = $this->connection->createQueryBuilder();
        $qb->select('o.id_order')
            ->from($this->table, 'o')
            ->where('o.current_state IN (:status_ids)')
            ->setParameter('status_ids', $status_ids, Connection::PARAM_INT_ARRAY);

        if ( ! $ignore_previous_date) {
            $qb->andWhere('o.invoice_date IS NULL OR o.invoice_date = 0');
        }

        if ($min_id !== 0) {
            $qb->andWhere('o.id_order >= :min_id')->setParameter('min_id', $min_id);
        }

        if ($max_id !== 0) {
            $qb->andWhere('o.id_order <= :max_id')->setParameter('max_id', $max_id);
        }

        $matching_orders = $qb->execute()->fetchAllAssociative();
        $matching_ids = array_column($matching_orders, 'id_order');

        // Step 2: Update invoice_date for matching orders to the order creation date
        $updated = 0;
        if ( ! empty($matching_ids)) {
            $updated = $this->connection->executeStatement(
                "UPDATE {$this->table} SET invoice_date = date_add WHERE id_order IN (:ids)",
                ['ids' => $matching_ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            );
        }

        return $updated;
    }
}
