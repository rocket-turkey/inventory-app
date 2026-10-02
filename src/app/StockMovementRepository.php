<?php
declare(strict_types=1);

final class StockMovementRepository
{
    public function __construct(private readonly PDO $db) {}

    public function recent(int $limit = 10): array
    {
        $statement = $this->db->prepare(
            'SELECT m.*, p.sku FROM stock_movements m JOIN products p ON p.id = m.product_id ORDER BY m.id DESC LIMIT ?'
        );
        $statement->bindValue(1, $limit, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll();
    }

    public function record(int $productId, int $change, string $note): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO stock_movements (product_id, change_amount, note) VALUES (?, ?, ?)'
        );

        $statement->execute([$productId, $change, $note]);
    }
}
