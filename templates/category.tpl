{extends file="layouts/base.tpl"}

{block name="content"}
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <a href="/">Главная</a>
        <span aria-hidden="true">/</span>
        <span>{$category.name|escape}</span>
    </nav>

    <header class="page-header">
        <p class="eyebrow">Категория</p>
        <h1>{$category.name|escape}</h1>
        <p>{$category.description|escape}</p>
    </header>

    <nav class="sort-links" aria-label="Сортировка статей">
        <span>Сортировать:</span>
        <a
            href="/category/{$category.slug|escape:'url'}?sort=date"
            {if $currentSort === 'date'}aria-current="page"{/if}
        >
            По дате
        </a>
        <a
            href="/category/{$category.slug|escape:'url'}?sort=views"
            {if $currentSort === 'views'}aria-current="page"{/if}
        >
            По просмотрам
        </a>
    </nav>

    <div class="posts-grid">
        {foreach $posts as $post}
            {include file="components/post-card.tpl" post=$post}
        {foreachelse}
            <p class="empty-state">В этой категории пока нет статей.</p>
        {/foreach}
    </div>

    {include
        file="components/pagination.tpl"
        categorySlug=$category.slug
        currentSort=$currentSort
        currentPage=$currentPage
        totalPages=$totalPages
        paginationPages=$paginationPages
        previousPage=$previousPage
        nextPage=$nextPage
    }
{/block}
