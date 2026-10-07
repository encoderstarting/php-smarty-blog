<?php

declare(strict_types=1);

use App\Core\Database;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();

try {
    $migrationFiles = glob($root . '/database/migrations/*.sql');

    if ($migrationFiles === false) {
        throw new RuntimeException('Не удалось получить список миграций.');
    }

    if ($migrationFiles === []) {
        throw new RuntimeException('SQL-файлы миграций не найдены.');
    }

    $pdo = (new Database())->connect();

    foreach ($migrationFiles as $migrationFile) {
        $sql = file_get_contents($migrationFile);

        if ($sql === false) {
            throw new RuntimeException(
                "Не удалось прочитать миграцию: {$migrationFile}"
            );
        }

        if (trim($sql) === '') {
            throw new RuntimeException(
                "Файл миграции пуст: {$migrationFile}"
            );
        }

        $pdo->exec($sql);

        fwrite(
            STDOUT,
            'Выполнена миграция: ' . basename($migrationFile) . PHP_EOL,
        );
    }

    fwrite(STDOUT, "Миграции успешно выполнены.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Ошибка миграции: {$exception->getMessage()}\n");
    exit(1);
}
