{if $xtech_icons}
    <div class="xtech-icons">
        {foreach from=$xtech_icons item=icon}
            <div class="xtech-icon row">
                <div class='col-md-3'>
                    <img src="{$icon.image}" alt="{$icon.title|escape:'html'}" />
                </div>
                <div class='col-md-9 d-flex-center'>
                    <h3>{$icon.title|escape:'html'}</h3>
                    {if $icon.description}
                        <p>{$icon.description|escape:'html'}</p>
                    {/if}
                    {if $icon.url}
                        <a href="{$icon.url|escape:'html'}" target="_blank">Learn More</a>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
{/if}
