{if $readperm}
    <a href="{modurl modname='News' func='display' sid=$sid}">{$title|safehtml}</a> ({$from|dateformat:'datebrief'})
{else}
    {$title|safehtml} ({$from|dateformat:'datebrief'})
{/if}
