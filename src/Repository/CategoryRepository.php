<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;

final class CategoryRepository
{
    public function __construct(
        private PDO $pdo,
    ) {
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

        $category = $statement->fetch();

        return is_array($category) ? $category : false;
    }

    /** @return list<array<string, mixed>> */
    public function findWithPublishedPosts(): array
    {
        $sql = '
            SELECT DISTINCT
                c.id,
                c.name,
                c.slug,
                c.description
            FROM categories AS c
            INNER JOIN category_post AS cp ON cp.category_id = c.id
            INNER JOIN posts AS p ON p.id = cp.post_id
            WHERE p.published_at <= NOW()
            ORDER BY c.name ASC
        ';

        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            throw new RuntimeException('Database error');
        }

        $statement->execute();

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function findForPost(int $postId): array
    {
        $sql = '
            SELECT
                c.id,
                c.name,
                c.slug
            FROM categories AS c
            INNER JOIN category_post AS cp ON cp.category_id = c.id
            WHERE cp.post_id = :post_id
            ORDER BY c.name ASC
        ';

        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            throw new RuntimeException('Database error');
        }

        $statement->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
