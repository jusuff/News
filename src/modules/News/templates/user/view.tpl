{nocache}{include file='user/menu.tpl'}{/nocache}
{insert name='getstatusmsg'}

{section name='newsview' loop=$newsitems}
    {$newsitems[newsview]}
    {if $smarty.section.newsview.last neq true}
    <hr />
    {/if}
{/section}

{if $newsitems}
{pager display='page' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='page'}
{/if}

{modurl modname='News' func='view' startnum=$startnum assign='returnurl'}
{* there is no ID because this is a collection *}
{notifydisplayhooks eventname='news.hook.articles.ui.view' area='module_area.news.articles' subject=$newsitems id=null caller="news"}
