<?php

declare(strict_types=1);

use App\Core\Database;
use App\Database\Seeders\DatabaseSeeder;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();

try {
    $pdo = (new Database())->connect();
    (new DatabaseSeeder($pdo))->run();

    fwrite(STDOUT, "Тестовые данные успешно добавлены.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Ошибка сидирования: {$exception->getMessage()}\n");
    exit(1);
}
