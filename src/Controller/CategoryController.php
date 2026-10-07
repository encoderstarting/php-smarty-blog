<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;

final class CategoryController
{
    private const POSTS_PER_PAGE = 6;
    private const ALLOWED_SORTS = ['date', 'views'];

    public function __construct(
        private CategoryRepository $categoryRepository,
        private PostRepository $postRepository,
        private View $view,
    ) {
    }

    public function show(string $slug): void
    {
        if (preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
            $this->notFound();
            return;
        }

        $category = $this->categoryRepository->findBySlug($slug);

        if ($category === false) {
            $this->notFound();
            return;
        }

        $sort = $_GET['sort'] ?? 'date';

        if (!is_string($sort) || !in_array($sort, self::ALLOWED_SORTS, true)) {
            $sort = 'date';
        }

        $page = filter_var(
            $_GET['page'] ?? 1,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );
        $page = $page === false ? 1 : $page;

        $totalPosts = $this->postRepository->countPublishedByCategory(
            (int) $category['id']
        );
        $totalPages = max(
            1,
            (int) ceil($totalPosts / self::POSTS_PER_PAGE),
        );

        if ($page > $totalPages) {
            $this->notFound();
            return;
        }

        $offset = ($page - 1) * self::POSTS_PER_PAGE;
        $posts = $this->postRepository->findByCategory(
            (int) $category['id'],
            $sort,
            self::POSTS_PER_PAGE,
            $offset,
        );

        $this->view->render('category.tpl', [
            'pageTitle' => $category['name'],
            'category' => $category,
            'posts' => $posts,
            'currentSort' => $sort,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'paginationPages' => range(1, $totalPages),
            'previousPage' => $page > 1 ? $page - 1 : null,
            'nextPage' => $page < $totalPages ? $page + 1 : null,
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->view->render('errors/404.tpl', [
            'pageTitle' => 'Страница не найдена',
        ]);
    }
}
