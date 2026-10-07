{extends file="layouts/base.tpl"}

{block name="content"}
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <a href="/">Главная</a>
        <span aria-hidden="true">/</span>
        <span>{$post.title|escape}</span>
    </nav>

    <article class="post-page">
        <header class="post-page__header">
            <div class="post-page__categories">
                {foreach $categories as $category}
                    <a href="/category/{$category.slug|escape:'url'}">
                        {$category.name|escape}
                    </a>
                {/foreach}
            </div>

            <h1>{$post.title|escape}</h1>
            <p class="post-page__description">{$post.description|escape}</p>

            <div class="post-page__meta">
                <time datetime="{$post.published_at|escape}">
                    {$post.published_at|date_format:"%d.%m.%Y"|escape}
                </time>
                <span>{$post.views_count|escape} просмотров</span>
            </div>
        </header>

        {if $post.image_path}
            <img
                class="post-page__image"
                src="{$post.image_path|escape}"
                alt="{$post.title|escape}"
                decoding="async"
            >
        {/if}

        <div class="post-page__content">
            {$post.content|escape|nl2br}
        </div>
    </article>

    {if $relatedPosts}
        <section class="related-posts">
            <header class="section-header">
                <p class="eyebrow">Продолжить чтение</p>
                <h2>Похожие статьи</h2>
            </header>

            <div class="posts-grid">
                {foreach $relatedPosts as $relatedPost}
                    {include
                        file="components/post-card.tpl"
                        post=$relatedPost
                    }
                {/foreach}
            </div>
        </section>
    {/if}
{/block}
