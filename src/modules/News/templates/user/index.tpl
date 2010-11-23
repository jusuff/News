<div class="news_index">
    <h3 class="news_title">{$preformat.title}</h3>
    <span class="news_meta z-sub">{gt text='Contributed'} {gt text='by %1$s on %2$s' tag1=$info.contributor tag2=$info.from|dateformat:'datetimebrief'}</span>

    <div class="news_body">
        {if $picupload_enabled AND $info.pictures gt 0}
        <div class="news_photoindex news_thumbsindex" style="float:{$picupload_index_float}">
            {if $shorturls AND $shorturlstype eq 0}
                <a href="{modurl modname='News' type='user' func='display' sid=$info.sid from=$info.from urltitle=$info.urltitle}">{*<span></span>*}<img src="{$picupload_uploaddir}/pic_sid{$info.sid}-0-thumb.jpg" alt="{gt text='Picture %s for %s' tag1='0' tag2=$info.title}" /></a>
            {else}
                <a href="{modurl modname='News' type='user' func='display' sid=$info.sid}">{*<span></span>*}<img src="{$picupload_uploaddir}/pic_sid{$info.sid}-0-thumb.jpg" alt="{gt text='Picture %s for %s' tag1='0' tag2=$info.title}" /></a>
            {/if}
        </div>
        {/if}
        {$preformat.hometext|safehtml}
    </div>

    {if $preformat.notes neq ''}
    <p class="news_meta">{$preformat.notes}</p>
    {/if}

    <p class="news_footer">
        {if !empty($info.categories)}
        {gt text='Filed under:'}
        {foreach name='categorylinks' from=$preformat.categories item='categorylink'}
        {$categorylink}{if $smarty.foreach.categorylinks.last neq true},&nbsp;{/if}
        {/foreach}
        <span class="text_separator">|</span>
        {/if}
        {if !empty($preformat.readmore)}
          {$preformat.readmore}
          <span class="text_separator">|</span>
        {/if}
        {*
        {recommend modname='News' itemid=$info.sid}
        <span class="text_separator">|</span>
        *}
        {$preformat.print}
        {if $pdflink}
        <span class="text_separator">|</span>
        <a title="PDF" href="{modurl modname='News' type='user' func='displaypdf' sid=$info.sid}" target="_blank">PDF <img src="modules/News/images/pdf.gif" width="16" height="16" alt="PDF" /></a>
        {/if}
    </p>
</div>
