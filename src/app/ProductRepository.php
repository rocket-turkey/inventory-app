<?php
declare(strict_types=1);

final class ProductRepository
{
    public function __construct(private readonly PDO $db) {}

    public function all(): array
    {
        return $this->db->query('SELECT * FROM products ORDER BY name, id')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM products WHERE id = ?');

        $statement->execute([$id]);
        
        return $statement->fetch() ?: null;
    }

    public function create(string $sku, string $name, string $price, int $reorderLevel): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO products (sku, name, price, reorder_level) VALUES (?, ?, ?, ?)'
        );

        $statement->execute([$sku, $name, $price, $reorderLevel]);
    }

    public function update(int $id, string $sku, string $name, string $price, int $reorderLevel): void
    {
        $statement = $this->db->prepare(
            'UPDATE products SET sku = ?, name = ?, price = ?, reorder_level = ? WHERE id = ?'
        );

        $statement->execute([$sku, $name, $price, $reorderLevel, $id]);
    }

    public function changeQuantity(int $id, int $change): bool
    {
        $statement = $this->db->prepare(
            'UPDATE products SET quantity = quantity + ? WHERE id = ? AND quantity + ? >= 0'
        );

        $statement->execute([$change, $id, $change]);

        return $statement->rowCount() === 1;
    }
}
