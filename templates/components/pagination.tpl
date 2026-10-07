{if $totalPages > 1}
    <nav class="pagination" aria-label="Навигация по страницам">
        <ul class="pagination__list">
            {if $previousPage}
                <li>
                    <a
                        href="/category/{$categorySlug|escape:'url'}?sort={$currentSort|escape:'url'}&amp;page={$previousPage|escape:'url'}"
                        rel="prev"
                    >
                        Назад
                    </a>
                </li>
            {/if}

            {foreach $paginationPages as $page}
                <li>
                    {if $page === $currentPage}
                        <span aria-current="page">{$page|escape}</span>
                    {else}
                        <a href="/category/{$categorySlug|escape:'url'}?sort={$currentSort|escape:'url'}&amp;page={$page|escape:'url'}">
                            {$page|escape}
                        </a>
                    {/if}
                </li>
            {/foreach}

            {if $nextPage}
                <li>
                    <a
                        href="/category/{$categorySlug|escape:'url'}?sort={$currentSort|escape:'url'}&amp;page={$nextPage|escape:'url'}"
                        rel="next"
                    >
                        Вперёд
                    </a>
                </li>
            {/if}
        </ul>
    </nav>
{/if}
