{if !empty($xtech_categories)}
    <div class="xtech-categories">
        <div class="category-list">
            {foreach from=$xtech_categories item=category}
                <div class="category-item">
                    <a  href="{$category.url}" class="row">
                    {if $category.image}
                        <img src="{$category.image}" alt="{$category.name}" class="category-image col-md-2">
                    {/if}
                        <p class="category-title" class="col-md-10">{$category.name}</p>
                    </a>
                    
                </div>
            {/foreach}
        </div>
    </div>
{/if}
