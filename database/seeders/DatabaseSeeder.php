<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use PDO;
use Throwable;

final class DatabaseSeeder
{
    private const IMAGE_PATH = '/assets/images/post-placeholder.svg';

    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function run(): void
    {
        $this->clearTables();
        $this->pdo->beginTransaction();

        try {
            $categoryIds = $this->seedCategories();
            $this->seedPosts($categoryIds);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    private function clearTables(): void
    {
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            $this->pdo->exec('TRUNCATE TABLE category_post');
            $this->pdo->exec('TRUNCATE TABLE posts');
            $this->pdo->exec('TRUNCATE TABLE categories');
        } finally {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /** @return array<string, int> */
    private function seedCategories(): array
    {
        $categories = [
            ['name' => 'PHP', 'slug' => 'php', 'description' => 'Практические материалы о PHP и серверной разработке.'],
            ['name' => 'MySQL', 'slug' => 'mysql', 'description' => 'Запросы, индексы и проектирование реляционных баз данных.'],
            ['name' => 'Backend', 'slug' => 'backend', 'description' => 'Архитектура и устройство серверной части веб-приложений.'],
            ['name' => 'Docker', 'slug' => 'docker', 'description' => 'Контейнеры и воспроизводимое окружение разработки.'],
            ['name' => 'Веб-разработка', 'slug' => 'web-development', 'description' => 'Интерфейсы, шаблоны и практика создания сайтов.'],
            ['name' => 'Без публикаций', 'slug' => 'empty-category', 'description' => 'Категория для проверки пустых состояний.'],
        ];

        $statement = $this->pdo->prepare(
            'INSERT INTO categories (name, slug, description, created_at, updated_at)
             VALUES (:name, :slug, :description, NOW(), NOW())'
        );

        $categoryIds = [];

        foreach ($categories as $category) {
            $statement->execute($category);
            $categoryIds[$category['slug']] = (int) $this->pdo->lastInsertId();
        }

        return $categoryIds;
    }

    /** @param array<string, int> $categoryIds */
    private function seedPosts(array $categoryIds): void
    {
        $posts = $this->posts();

        $postStatement = $this->pdo->prepare(
            'INSERT INTO posts (
                image_path,
                title,
                slug,
                description,
                content,
                views_count,
                published_at,
                created_at,
                updated_at
             ) VALUES (
                :image_path,
                :title,
                :slug,
                :description,
                :content,
                :views_count,
                :published_at,
                NOW(),
                NOW()
             )'
        );

        $relationStatement = $this->pdo->prepare(
            'INSERT INTO category_post (category_id, post_id)
             VALUES (:category_id, :post_id)'
        );

        foreach ($posts as $post) {
            $categorySlugs = $post['categories'];
            unset($post['categories']);

            $postStatement->execute($post);
            $postId = (int) $this->pdo->lastInsertId();

            foreach ($categorySlugs as $categorySlug) {
                $relationStatement->execute([
                    'category_id' => $categoryIds[$categorySlug],
                    'post_id' => $postId,
                ]);
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function posts(): array
    {
        return [
            $this->post('PDO без магии', 'pdo-without-magic', 'Как PHP безопасно общается с MySQL через PDO.', 'Разбираем подключение, подготовку запроса, передачу параметров и получение результата.', 1280, '2025-12-15 10:00:00', ['php', 'mysql', 'backend']),
            $this->post('Строгие типы в PHP', 'strict-types-in-php', 'Зачем проекту declare(strict_types=1).', 'Строгие типы помогают раньше замечать ошибки в аргументах и возвращаемых значениях методов.', 870, '2025-12-08 12:30:00', ['php', 'backend']),
            $this->post('Первый шаблон Smarty', 'first-smarty-template', 'Передаём данные из PHP в шаблон.', 'Контроллер подготавливает данные, а Smarty отвечает только за безопасное отображение HTML.', 640, '2025-11-30 09:15:00', ['php', 'web-development']),
            $this->post('Индексы без страха', 'mysql-indexes', 'Какие запросы ускоряют индексы.', 'Рассматриваем индексы для slug, дат публикации, просмотров и внешних ключей.', 1520, '2025-11-22 14:00:00', ['mysql', 'backend']),
            $this->post('Связь many-to-many', 'many-to-many', 'Почему статье может понадобиться несколько категорий.', 'Связующая таблица хранит пары идентификаторов и не дублирует данные основных сущностей.', 1110, '2025-11-15 16:45:00', ['mysql', 'backend']),
            $this->post('Пагинация через LIMIT и OFFSET', 'pagination-limit-offset', 'Делим длинный список статей на страницы.', 'Считаем общее количество записей, вычисляем смещение и передаём числа как параметры PDO.', 980, '2025-11-07 11:20:00', ['mysql', 'php']),
            $this->post('Docker Compose для PHP', 'docker-compose-for-php', 'Запускаем PHP, Nginx и MySQL вместе.', 'Описываем сервисы приложения в одном compose-файле и получаем одинаковое окружение разработки.', 1740, '2025-10-29 08:40:00', ['docker', 'backend']),
            $this->post('Как работают PHP-FPM и Nginx', 'php-fpm-and-nginx', 'Разбираем путь HTTP-запроса до PHP.', 'Nginx принимает запрос, передаёт PHP-файл процессу PHP-FPM и возвращает сформированный ответ.', 1340, '2025-10-21 13:10:00', ['docker', 'php', 'backend']),
            $this->post('Данные MySQL в Docker Volume', 'mysql-docker-volume', 'Почему база не исчезает после перезапуска контейнера.', 'Именованный volume хранит файлы MySQL отдельно от жизненного цикла контейнера.', 760, '2025-10-14 17:25:00', ['docker', 'mysql']),
            $this->post('Доступная карточка статьи', 'accessible-post-card', 'Семантическая разметка и понятные ссылки.', 'Карточка должна иметь логичную структуру заголовков, альтернативный текст и видимый фокус.', 520, '2025-10-06 10:50:00', ['web-development']),
            $this->post('Как организовать SCSS', 'organizing-scss', 'Разделяем переменные, компоновку и компоненты.', 'Небольшие тематические файлы помогают поддерживать стили без копирования одинаковых правил.', 690, '2025-09-28 15:30:00', ['web-development']),
            $this->post('Читаемые URL со slug', 'readable-urls-with-slug', 'Заменяем технические идентификаторы понятными адресами.', 'Slug делает URL читаемым, но всё равно должен проходить проверку и иметь уникальный индекс.', 830, '2025-09-19 12:00:00', ['web-development', 'backend']),
            $this->post('Минимальный маршрутизатор', 'minimal-php-router', 'Связываем URL с контроллерами без фреймворка.', 'Маршрутизатор определяет обработчик запроса и передаёт ему параметры динамического пути.', 1430, '2025-09-11 09:35:00', ['php', 'backend']),
            $this->post('Обработка ошибок приложения', 'application-error-handling', 'Разделяем сообщения для разработчика и пользователя.', 'Исключения записываются для диагностики, а посетитель получает безопасную страницу ошибки.', 930, '2025-09-03 18:15:00', ['php', 'backend']),
            $this->post('Простая архитектура небольшого блога', 'simple-blog-architecture', 'Контроллеры, репозитории и шаблоны без лишних слоёв.', 'Каждая часть приложения получает одну понятную ответственность, поэтому код проще проверять и объяснять.', 1880, '2025-08-25 11:45:00', ['backend', 'php']),
        ];
    }

    /** @param list<string> $categories */
    private function post(
        string $title,
        string $slug,
        string $description,
        string $content,
        int $viewsCount,
        string $publishedAt,
        array $categories,
    ): array {
        return [
            'image_path' => self::IMAGE_PATH,
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'content' => $content,
            'views_count' => $viewsCount,
            'published_at' => $publishedAt,
            'categories' => $categories,
        ];
    }
}
