<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\View;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

try {
    $view = new View($root);
    $router = new Router();

    (require $root . '/routes/web.php')($router, $view);

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);

    if (isset($view)) {
        try {
            $view->render('errors/500.tpl', [
                'pageTitle' => 'Ошибка сервера',
            ]);
            return;
        } catch (Throwable) {
            // Переходим к безопасному ответу без шаблонизатора.
        }
    }

    echo '500 — внутренняя ошибка сервера';
}
