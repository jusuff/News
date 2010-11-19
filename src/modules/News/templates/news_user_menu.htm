{formutil_getpassedvalue name='func' default='main' noprocess=true assign='func'}
{formutil_getpassedvalue name='theme' default='' noprocess=true assign='theme'}
{checkpermission component='News::' instance='::' level='ACCESS_OVERVIEW' assign='authoverview'}
{checkpermission component='News::' instance='::' level='ACCESS_COMMENT' assign='authcomm'}
{checkpermission component='News::' instance='::' level='ACCESS_EDIT' assign='authedit'}

{* assign the page title if News is the current module *}
{modgetname assign='module'}
{if $module eq 'News'}
  {if $func eq 'main'}
    {configgetvar name='entrypoint' default='index.php' assign='entrypoint'}
    {servergetvar name='REQUEST_URI' default='/' assign='requesturi'}
    {assign var='requesturi' value=$requesturi|replace:$baseuri:''}
    {if $requesturi neq '/' AND $requesturi neq "/$entrypoint"}
      {pagesetvar name='title' __value='News'}
    {/if}
  {elseif $func eq 'view' AND $catname|default:'' neq ''}
    {pagesetvar name='title' value=$catname}
  {/if}
{/if}

<h2>{gt text='News'}{if $func eq 'view' AND $catname|default:'' neq ''} &raquo; {$catname}{/if}</h2>
{if $theme neq 'Printer' AND $authoverview}
<div class="z-menu">
    <span class="z-menuitem-title">
        [
        {if $func neq 'main'}
        <a href="{modurl modname='News' type='user' func='main'}">{gt text='News index page'}</a>
        {else}
        <a href="{modurl modname='News' type='user' func='view' theme='rss'}">{img modname='core' set='feeds' src='feed-icon-12x12.png' __alt='RSS feed' __title='RSS feed'}</a> |
        {/if}

        {if $enablecategorization AND $func neq 'categorylist'}
        {if $func neq 'main'} | {/if}
        <a href="{modurl modname='News' type='user' func='categorylist'}">{gt text='News categories'}</a>
        {/if}

        {if $func neq 'archives'}
        {if $func neq 'main' OR $enablecategorization AND $func neq 'categorylist'} | {/if}
        <a href="{modurl modname='News' type='user' func='archives'}">{gt text='News archive'}</a>
        {/if}

        {if $authcomm AND $func neq 'new'}
        |
        <a href="{modurl modname='News' type='user' func='newitem'}">{gt text='Submit an article'}</a>
        {/if}

        {if $authedit}
        {modapifunc modname='News' type='user' func='countitems' status='2' assign='pendingcount'}
        {if $pendingcount gt 0}
        |
        <a href="{modurl modname='News' type='admin' func='view' news_status='2'}">{gt text='%s pending article' plural='%s pending articles' count=$pendingcount tag1=$pendingcount}</a>
        {else}
        |
        <a href="{modurl modname='News' type='admin' func='view'}">{gt text="Admin"}</a>
        {/if}
        {/if}
        ]
    </span>
</div>
{/if}