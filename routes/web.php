<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\View;

return static function (Router $router, View $view): void {
    $router->get('/', static function () use ($view): void {
        $view->render('home.tpl', [
            'pageTitle' => 'PHP + Smarty Blog',
        ]);
    });
};

