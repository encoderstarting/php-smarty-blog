<article class="post-card">
    {if $post.image_path}
        <a
            class="post-card__image-link"
            href="/post/{$post.slug|escape:'url'}"
            aria-label="Открыть статью «{$post.title|escape}»"
        >
            <img
                class="post-card__image"
                src="{$post.image_path|escape}"
                alt="{$post.title|escape}"
                loading="lazy"
                decoding="async"
            >
        </a>
    {/if}

    <div class="post-card__body">
        <div class="post-card__meta">
            <time datetime="{$post.published_at|escape}">
                {$post.published_at|date_format:"%d.%m.%Y"|escape}
            </time>
            <span>{$post.views_count|escape} просмотров</span>
        </div>

        <h3 class="post-card__title">
            <a href="/post/{$post.slug|escape:'url'}">
                {$post.title|escape}
            </a>
        </h3>

        <p class="post-card__description">
            {$post.description|escape}
        </p>
    </div>
</article>
