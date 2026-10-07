<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;

final class HomeController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private PostRepository $postRepository,
        private View $view,
    ) {
    }

    public function index(): void
    {
        $categories = $this->categoryRepository
            ->findWithPublishedPosts();

        $sections = [];

        foreach ($categories as $category) {
            $sections[] = [
                'category' => $category,
                'posts' => $this->postRepository->findLatestByCategory(
                    (int) $category['id'],
                    3,
                ),
            ];
        }

        $this->view->render('home.tpl', [
            'pageTitle' => 'Главная',
            'sections' => $sections,
        ]);
    }
}
