{if !empty($xtech_categories)}
    <div class="xtech-categories">
        <div class="category-list mt-4">
            {foreach from=$xtech_categories item=category}
                <div class="category-item">
                    <a  href="{$category.url}" class="row">
                        <p class="category-title text-left" >{$category.name}</p>
                    </a>
                </div>
            {/foreach}
        </div>
    </div>
{/if}
