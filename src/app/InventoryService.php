<?php
declare(strict_types=1);

final class InventoryService
{
    public function __construct(
        private readonly PDO $db,
        private readonly ProductRepository $products,
        private readonly StockMovementRepository $movements
    ) {}

    public function adjustStock(int $productId, int $change, string $note): void
    {
        $this->db->beginTransaction();
        try {
            if (!$this->products->changeQuantity($productId, $change)) {
                throw new InvalidArgumentException('Product missing or insufficient stock.');
            }
            $this->movements->record($productId, $change, $note);
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }
}
