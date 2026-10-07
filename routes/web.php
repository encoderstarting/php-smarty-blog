<?php

declare(strict_types=1);

use App\Controller\HomeController;
use App\Controller\CategoryController;
use App\Core\Database;
use App\Core\Router;
use App\Core\View;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;

return static function (Router $router, View $view): void {
    $pdo = (new Database())->connect();

    $categoryRepository = new CategoryRepository($pdo);
    $postRepository = new PostRepository($pdo);

    $homeController = new HomeController(
        $categoryRepository,
        $postRepository,
        $view,
    );
    $categoryController = new CategoryController(
        $categoryRepository,
        $postRepository,
        $view,
    );

    $router->get('/', [$homeController, 'index']);
    $router->get('/category/{slug}', [$categoryController, 'show']);
};
