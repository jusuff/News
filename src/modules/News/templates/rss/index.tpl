<item>
    <title>{$info.title|safetext}</title>
    <link>{modurl modname='News' type='user' func='display' sid=$info.sid title=$info.urltitle fqurl=true}</link>
    <description>{$info.hometext|notifyfilters:'news.hook.articlesfilter.ui.filter'|safehtml}</description>
    <pubDate>{$info.from|updated|published}</pubDate>
    <guid>{modurl modname='News' type='user' func='display' sid=$info.sid title=$info.urltitle fqurl=true}</guid>
</item>
