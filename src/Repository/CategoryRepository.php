<?php


declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;

final class CategoryRepository
{
    public function __construct(
        private PDO $pdo,
    )
    {
    }

    public function findBySlug(string $slug): array|false
    {
        $sql = '
            SELECT id, name, slug, description    
            FROM categories
            WHERE slug = :slug
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            throw new RuntimeException('Database error');
        }

        $statement->execute([
            'slug' => $slug,
        ]);

        $post = $statement->fetch();

        return is_array($post) ? $post : false;
    }
}

