<?php

declare(strict_types=1);

namespace App\Repository;

use InvalidArgumentException;
use PDO;
use RuntimeException;

final class PostRepository
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function findBySlug(string $slug): array|false
    {
        $sql = '
            SELECT
                id,
                title,
                slug,
                description,
                content,
                image_path,
                views_count,
                published_at
            FROM posts
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

    /** @return list<array<string, mixed>> */
    public function findLatestByCategory(
        int $categoryId,
        int $limit = 3,
    ): array {
        if ($limit < 1) {
            throw new InvalidArgumentException(
                'Количество статей должно быть больше нуля.'
            );
        }

        $sql = '
            SELECT
                p.id,
                p.image_path,
                p.title,
                p.slug,
                p.description,
                p.views_count,
                p.published_at
            FROM posts AS p
            INNER JOIN category_post AS cp ON cp.post_id = p.id
            WHERE cp.category_id = :category_id
              AND p.published_at <= NOW()
            ORDER BY p.published_at DESC, p.id DESC
            LIMIT :limit
        ';

        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            throw new RuntimeException('Database error');
        }

        $statement->bindValue(
            ':category_id',
            $categoryId,
            PDO::PARAM_INT,
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
