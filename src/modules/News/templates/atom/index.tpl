<entry>
    <title>{$info.title|safehtml}</title>
    <link rel="alternate" type="text/html" href="{modurl modname='News' type='user' func='display' sid=$info.sid}" />
    <author><name>{$info.contributor}</name></author>
    <id>{modurl modname='News' type='user' func='display' sid=$info.sid fqurl=true}</id>
    <published>{$info.from|published}</published>
    <updated>{$info.lu_date|updated}</updated>
    <summary type="html">{$info.hometext|notifyfilters:'news.hook.articlesfilter.ui.filter'|safehtml|htmlentities}</summary>
</entry>