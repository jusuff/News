{ajaxheader modname='News' filename='news_storiesextblock_modify.js' effects=true nobehaviour=true noscriptaculous=true}

<fieldset>
    <legend>{gt text='General settings' domain="module_news"}</legend>
    <div class="z-formrow">
        <label for="news_storiesextblock_show">{gt text='Articles to list' domain="module_news"}</label>
        <select id="news_storiesextblock_show" name="show">
            <option value="1"{if $show eq 1} selected="selected"{/if}>{gt text='Show all news articles' domain="module_news"}</option>
            <option value="2"{if $show eq 2} selected="selected"{/if}>{gt text='Show only articles set for index page listing' domain="module_news"}</option>
            <option value="3"{if $show eq 3} selected="selected"{/if}>{gt text='Show only articles not set for index page listing' domain="module_news"}</option>
        </select>
    </div>
    {if $enablecategorization}
    <div class="z-formrow">
        <label>{gt text='Choose categories' domain="module_news"} (<a href="{modurl modname='Categories' type='admin' func='editregistry'}">{gt text='Category registry' domain="module_news"}</a>)</label>
        {nocache}
        {foreach from=$catregistry key='prop' item='cat'}
        {array_field_isset assign='selectedValue' array=$category field=$prop returnValue=1}
        <div class="z-formnote">
            {selector_category category=$cat name="category[$prop]" multipleSize=5 selectedValue=$selectedValue}
            <input type="button" value="{gt text='All' domain="module_news"}" onclick="news_storiesextblock_selectAllOptions(this.form.category_{$prop}___, true);">
            <input type="button" value="{gt text='None' domain="module_news"}" onclick="news_storiesextblock_selectAllOptions(this.form.category_{$prop}___, false);">
        </div>
        {/foreach}
        {/nocache}
    </div>
    {/if}
    <div class="z-formrow">
        <label for="news_storiesextblock_limit">{gt text='Maximum number of news articles to display' domain="module_news"}</label>
        <input id="news_storiesextblock_limit" type="text" name="limit" size="5" value="{$limit|safetext}" />
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_status">{gt text='Status' domain="module_news"}</label>
        <select id="news_storiesextblock_status" name="status">
            <option value="0"{if $status eq 0} selected="selected"{/if}>{gt text='Published' domain="module_news"}</option>
            <option value="3"{if $status eq 3} selected="selected"{/if}>{gt text='Archived' domain="module_news"}</option>
            {* work out permissions ... option value="4"{if $status eq 4} selected="selected"{/if}>{gt text='Draft' domain="module_news"}</option> *}
        </select>
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_order">{gt text='Order articles by' domain="module_news"}</label>
        <select id="news_storiesextblock_order" name="order">
            <option value="0"{if $order eq 0} selected="selected"{/if}>{gt text='News publisher setting' domain="module_news"}</option>
            <option value="1"{if $order eq 1} selected="selected"{/if}>{gt text='Number of pageviews' domain="module_news"}</option>
            <option value="2"{if $order eq 2} selected="selected"{/if}>{gt text='Article weight' domain="module_news"}</option>
            <option value="3"{if $order eq 3} selected="selected"{/if}>{gt text='Random' domain="module_news"}</option>
        </select>
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_dayslimit">{gt text='Maximum age of articles' domain="module_news"}</label>
        <input id="news_storiesextblock_dayslimit" type="text" name="dayslimit" size="5" value="{$dayslimit|safetext}" />
        <em class="z-sub z-formnote">{gt text='(Number of days; 0 for no limit)' domain="module_news"}</em>
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_maxtitlelength">{gt text='Maximum length of titles' domain="module_news"}</label>
        <input id="news_storiesextblock_maxtitlelength" type="text" name="maxtitlelength" size="5" value="{$maxtitlelength|safetext}" />
        <em class="z-sub z-formnote">{gt text='(Number of characters; 0 for no limit)' domain="module_news"}</em>
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_titlewraptxt">{gt text='Suffix appended to truncated title' domain="module_news"}</label>
        <input id="news_storiesextblock_titlewraptxt" type="text" name="titlewraptxt" size="10" value="{$titlewraptxt|safetext}" />
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_showemptyresult">{gt text="Show 'No articles currently published' status message when no articles published" domain="module_news"}</label>
        <input id="news_storiesextblock_showemptyresult" type="checkbox" value="1" name="showemptyresult"{if $showemptyresult} checked="checked"{/if} />
    </div>
</fieldset>

<fieldset>
    <legend>{gt text='Custom templates' domain="module_news"}</legend>
    <p class="z-formnote z-informationmsg">{gt text="Notice: You can use your own custom templates instead of the default templates. You must specify them here.The default template for block rows is 'block/storiesext/row.tpl'. The default block template depends on the scroll setting. The default static template is 'block/storiesext/main.tpl'. For scrolling content, 'block/storiesext/scrollNAME.tpl' is used." domain="module_news"}</p>
    <div class="z-formrow">
        <label for="news_storiesextblock_blocktemplate">{gt text='Custom block template' domain="module_news"}</label>
        <input id="news_storiesextblock_blocktemplate" type="text" name="blocktemplate" size="30" value="{$blocktemplate|safetext}" />
        <em class="z-sub z-formnote">{gt text='(Default template is used if this box is left blank)' domain="module_news"}</em>
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_rowtemplate">{gt text='Custom row template' domain="module_news"}</label>
        <input id="news_storiesextblock_rowtemplate" type="text" name="rowtemplate" size="30" value="{$rowtemplate|safetext}" />
        <em class="z-sub z-formnote">{gt text='(Default template is used if this box is left blank)' domain="module_news"}</em>
    </div>
</fieldset>

<fieldset>
    <legend>{gt text='Advanced block settings' domain="module_news"}</legend>
    <div class="z-formrow">
        <label for="news_storiesextblock_dispuname">{gt text='Show contributor\'s name' domain="module_news"}</label>
        <input id="news_storiesextblock_dispuname" type="checkbox" value="1" name="dispuname"{if $dispuname} checked="checked"{/if} />
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_dispdate">{gt text='Show article creation date' domain="module_news"}</label>
        <input id="news_storiesextblock_dispdate" type="checkbox" value="1" name="dispdate"{if $dispdate} checked="checked"{/if} />
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_dateformat">{gt text='Date format' domain="module_news"}</label>
        <input id="news_storiesextblock_dateformat" type="text" name="dateformat" size="10" value="{$dateformat|safetext}" />
        <em class="z-sub z-formnote">(<a href="http://www.php.net/manual/en/function.strftime.php" target="_new">{gt text='Click here to read \'strftime\' description in PHP documentation' domain="module_news"}</a>)</em>
    </div>
    <div class="z-formrow">
        <label for="news_storiesextblock_dispreads">{gt text='Show number of pageviews' domain="module_news"}</label>
        <input id="news_storiesextblock_dispreads" type="checkbox" value="1" name="dispreads"{if $dispreads} checked="checked"{/if} />
    </div>
    {modavailable modname='EZComments' assign='EZComments'}
    {if $EZComments}
    <div class="z-formrow">
        <label for="news_storiesextblock_dispcomments">{gt text='Show number of comments' domain="module_news"}</label>
        <input id="news_storiesextblock_dispcomments" type="checkbox"value="1"  name="dispcomments"{if $dispcomments} checked="checked"{/if} />
    </div>
    {/if}
    <div class="z-formrow">
        <label for="news_storiesextblock_splitchar">{gt text='Separator character for additional information' domain="module_news"}</label>
        <input id="news_storiesextblock_splitchar" type="text" name="dispsplitchar" size="10" value="{$dispsplitchar|safetext}" />
    </div>
</fieldset>

<fieldset>
    <legend>{gt text='Index page teaser settings' domain="module_news"}</legend>
    <div class="z-formrow">
        <label>{gt text='Display article\'s index page teaser' domain="module_news"}</label>
        <div id="news_storiesextblock_disphometext">
            <input id="news_storiesextblock_disphometext_yes" type="radio" name="disphometext" value="1" {if $disphometext eq 1}checked="checked"{/if} /> <label for="news_storiesextblock_disphometext_yes">{gt text='Yes' domain="module_news"}</label>
            <input id="news_storiesextblock_disphometext_no" type="radio" name="disphometext" value="0" {if $disphometext eq 0}checked="checked"{/if} /> <label for="news_storiesextblock_disphometext_no">{gt text='No' domain="module_news"}</label>
        </div>
    </div>
    <div id="news_storiesextblock_disphometext_container">
        <p class="z-formnote z-informationmsg">{gt text='Notice: When truncating the index page teaser text, incomplete HTML mark-up elements will be completed by the \'truncatehtml\' plug-in.' domain="module_news"}</p>
        <div class="z-formrow">
            <label for="news_storiesextblock_maxhometextlength">{gt text='Maximum displayed length of index page teaser' domain="module_news"}</label>
            <input id="news_storiesextblock_maxhometextlength" type="text" name="maxhometextlength" size="5" value="{$maxhometextlength|safetext}" />
            <em class="z-sub z-formnote">{gt text='(Number of characters; 0 for no limit)' domain="module_news"}</em>
        </div>
        <div class="z-formrow">
            <label for="news_storiesextblock_hometextwraptxt">{gt text='Suffix appended to truncated index page teaser' domain="module_news"}</label>
            <input id="news_storiesextblock_hometextwraptxt" type="text" name="hometextwraptxt" size="10" value="{$hometextwraptxt|safetext}" />
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>{gt text='Article recency settings' domain="module_news"}</legend>
    <div class="z-formrow">
        <label>{gt text='Display image for recent article titles' domain="module_news"}</label>
        <div id="news_storiesextblock_dispnewimage">
            <input id="news_storiesextblock_dispnewimage_yes" type="radio" name="dispnewimage" value="1" {if $dispnewimage eq 1}checked="checked"{/if} /> <label for="news_storiesextblock_dispnewimage_yes">{gt text='Yes' domain="module_news"}</label>
            <input id="news_storiesextblock_dispnewimage_no" type="radio" name="dispnewimage" value="0" {if $dispnewimage eq 0}checked="checked"{/if} /> <label for="news_storiesextblock_dispnewimage_no">{gt text='No' domain="module_news"}</label>
        </div>
    </div>
    <div id="news_storiesextblock_dispnewimage_container">
        <div class="z-formrow">
            <label for="news_storiesextblock_newimagelimit">{gt text='Number of days during which article is considered recent' domain="module_news"}</label>
            <input id="news_storiesextblock_newimagelimit" type="text" name="newimagelimit" size="5" value="{$newimagelimit|safetext}" />
        </div>
        <div class="z-formrow">
            <label for="news_storiesextblock_newimageset">{gt text='Image set for \'New\' image' domain="module_news"}</label>
            <input id="news_storiesextblock_newimageset" type="text" name="newimageset" size="40" value="{$newimageset|safetext}" />
            <em class="z-sub z-formnote">{gt text='(Used by \'pnimg\' plug-in)' domain="module_news"}</em>
        </div>
        <div class="z-formrow">
            <label for="news_storiesextblock_newimagesrc">{gt text='File name of \'New\' image from image set' domain="module_news"}</label>
            <input id="news_storiesextblock_newimagesrc" type="text" name="newimagesrc" size="40" value="{$newimagesrc|safetext}" />
            <em class="z-sub z-formnote">{gt text='(Used by \'pnimg\' plug-in)' domain="module_news"}</em>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>{gt text='News item scroll settings' domain="module_news"}</legend>
    <p class="z-formnote z-informationmsg">{gt text='Notice: News articles can shown in a scrolling box. The provided scrollers are based upon code from the <a href="http://www.dynamicdrive.com/dynamicindex2/crosstick.htm">dynamicdrive.com \'crosstick\'</a> upward and downward pauseable vertical scroller, the <a href="http://www.dynamicdrive.com/dynamicindex2/memoryticker.htm">dynamicdrive.com \'memoryticker\'</a> fading scroller (gradient wipe effect only works in IE), and the <a href="http://www.dynamicdrive.com/dynamicindex2/cmarquee2.htm" target="_new">dynamicdrive.com \'marquee II\'</a> marquee scroller.' domain="module_news"}</p>
    <div class="z-formrow">
        <label for="news_storiesextblock_scrolling">{gt text='Choose scroller' domain="module_news"}</label>
        <select id="news_storiesextblock_scrolling" name="scrolling">
            <option value="1"{if $scrolling eq 1} selected="selected"{/if}>{gt text='No scrolling' domain="module_news"}</option>
            <option value="2"{if $scrolling eq 2} selected="selected"{/if}>{gt text='Pauseable vertical scroller' domain="module_news"}</option>
            <option value="3"{if $scrolling eq 3} selected="selected"{/if}>{gt text='Fading scroller' domain="module_news"}</option>
            <option value="4"{if $scrolling eq 4} selected="selected"{/if}>{gt text='Marquee scroller' domain="module_news"}</option>
        </select>
    </div>
    <div id="news_storiesextblock_scrolling_container">
        <div class="z-formrow">
            <label for="news_storiesextblock_scrollstyle">{gt text='Scroller CSS styles definition' domain="module_news"}</label>
            <textarea id="news_storiesextblock_scrollstyle" name="scrollstyle" rows="8" cols="40">{$scrollstyle|safetext}</textarea>
        </div>
        <div class="z-formrow">
            <label for="news_storiesextblock_scrolldelay">{gt text='Delay between scrolls and starting delay for marquee' domain="module_news"}</label>
            <input id="news_storiesextblock_scrolldelay" type="text" name="scrolldelay" size="10" value="{$scrolldelay|safetext}" />
            <em class="z-sub z-formnote">{gt text='(Number of milliseconds)' domain="module_news"}</em>
        </div>
        <div class="z-formrow">
            <label for="news_storiesextblock_scrollmspeed">{gt text='Marquee scroller speed' domain="module_news"}</label>
            <input id="news_storiesextblock_scrollmspeed" type="text" name="scrollmspeed" size="10" value="{$scrollmspeed|safetext}" />
            <em class="z-sub z-formnote">{gt text='(Number from 1 to 10)' domain="module_news"}</em>
        </div>
    </div>
</fieldset>
