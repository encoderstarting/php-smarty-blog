<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;

final class PostController
{
    public function __construct(
        private PostRepository $postRepository,
        private CategoryRepository $categoryRepository,
        private View $view,
    ) {
    }

    public function show(string $slug): void
    {
        if (preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
            $this->notFound();
            return;
        }

        $post = $this->postRepository->findBySlug($slug);

        if ($post === false) {
            $this->notFound();
            return;
        }

        $postId = (int) $post['id'];
        $this->postRepository->incrementViews($postId);
        $post['views_count'] = (int) $post['views_count'] + 1;

        $categories = $this->categoryRepository->findForPost($postId);
        $relatedPosts = $this->postRepository->findRelated($postId, 3);

        $this->view->render('post.tpl', [
            'pageTitle' => $post['title'],
            'post' => $post,
            'categories' => $categories,
            'relatedPosts' => $relatedPosts,
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
