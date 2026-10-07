{extends file="layouts/base.tpl"}

{block name="content"}
    <header class="page-header">
        <p class="eyebrow">PHP Blog</p>
        <h1>Последние публикации</h1>
        <p>Практические материалы о PHP, MySQL и серверной веб-разработке.</p>
    </header>

    <div class="category-list">
        {foreach $sections as $section}
            <section class="category-section">
                <header class="category-section__header">
                    <div>
                        <h2>{$section.category.name|escape}</h2>
                        <p>{$section.category.description|escape}</p>
                    </div>

                    <a
                        class="button-link"
                        href="/category/{$section.category.slug|escape:'url'}"
                    >
                        Все статьи
                    </a>
                </header>

                <div class="posts-grid">
                    {foreach $section.posts as $post}
                        {include
                            file="components/post-card.tpl"
                            post=$post
                        }
                    {foreachelse}
                        <p class="empty-state">В этой категории пока нет статей.</p>
                    {/foreach}
                </div>
            </section>
        {foreachelse}
            <p class="empty-state">Публикаций пока нет.</p>
        {/foreach}
    </div>
{/block}
