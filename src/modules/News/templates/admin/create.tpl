{ajaxheader modname='News' filename='news.js' effects=true dragdrop=true noscriptaculous=true}
{pageaddvar name='javascript' value='modules/News/javascript/sizecheck.js'}
{if $modvars.News.enableattribution}
{pageaddvar name="javascript" value="javascript/helpers/Zikula.itemlist.js"}
{/if}
{pageaddvar name='javascript' value='modules/News/javascript/prototype-base-extensions.js'}
{pageaddvar name='javascript' value='modules/News/javascript/prototype-date-extensions.js'}
{pageaddvar name='javascript' value='modules/News/javascript/datepicker.js'}
{pageaddvar name='javascript' value='modules/News/javascript/datepicker-locale.js'}
{pageaddvar name='stylesheet' value='modules/News/style/datepicker.css'}
{if $modvars.News.picupload_enabled AND $modvars.News.picupload_maxpictures gt 1}
{pageaddvar name='javascript' value='modules/News/javascript/multifile.js'}
{/if}

{gt text='Create new article' assign='templatetitle'}
{include file='admin/menu.tpl'}

{assign value='#'|cat:$smarty.ldelim|cat:'chars'|cat:$smarty.rdelim var='charstr'}
<script type="text/javascript">
    // <![CDATA[
    var bytesused = "{{gt text='%s characters out of 4,294,967,295' tag1=$charstr}}";
    var string_show = "{{gt text='Show'}}";
    var string_hide = "{{gt text='Hide'}}";
    var string_publish = "{{gt text='Publish'}}";
    var string_schedule = "{{gt text='Schedule'}}";
    var string_saveasdraft = "{{gt text='Save as draft'}}";
    var string_updatedraft = "{{gt text='Update draft'}}";
    var string_savingdraft = "{{gt text='Saving draft...'}}";
    var string_emptytitle = "{{gt text='<strong>Title is empty, draft not saved!</strong>'}}";
    var string_remove = "{{gt text='Remove'}}";
    var string_picture = "{{gt text='Picture'}}";
    {{if $modvars.News.enableattribution}}
    var itemlist_news_attributes = null;
    Event.observe(window, 'load', function() {
        itemlist_news_attributes = new Zikula.itemlist('news_attributes');
    }, false);
    {{/if}}
    // ]]>
</script>

<div class="z-admincontainer">
    {if $preview neq ''}
    <div class="news_article news_preview">{$preview}</div>
    {/if}

    <div class="z-adminpageicon">{img modname='core' src='filenew.gif' set='icons/large' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {if $modvars.News.picupload_enabled}
    <form id="news_admin_newform" class="z-form" action="{modurl modname='News' type='user' func='create'}" method="post" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="{$modvars.News.picupload_maxfilesize|safetext}" />
    {else}
    <form id="news_admin_newform" class="z-form" action="{modurl modname='News' type='user' func='create'}" method="post" enctype="application/x-www-form-urlencoded">
    {/if}
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='News'}" />
            {if $accessadd neq 1}
            <input type="hidden" name="story[hideonindex]" value="1" />
            <input type="hidden" name="story[notes]" value="" />
            <input type="hidden" name="story[disallowcomments]" value="1" />
            <input type="hidden" name="story[weight]" value="0" />
            {/if}
            {if $formattedcontent eq 1}
            <input type="hidden" name="story[hometextcontenttype]" value="1" />
            <input type="hidden" name="story[bodytextcontenttype]" value="1" />
            {/if}

            <fieldset>
                <legend>{gt text='Title'}</legend>

                <div class="z-formrow">
                    <label for="news_title">{gt text='Title text'}<span class="z-mandatorysym">*</span></label>
                    <input id="news_title" name="story[title]" type="text" size="32" maxlength="255" value="{$title|safetext}" />
                </div>
                {*
                <div class="z-formrow" style="margin-top:-5px;" id="news_urltitle_details">
                <label for="news_urltitle" class="z-sub">{gt text='Permalink'}</label>
                <div class="z-formnote"><span class="z-sub" id="news_sample_urltitle">&nbsp;</span> <a onclick="javascript:editpermalink()" href="javascript:void(0);" id="news_sample_urltitle_edit">{gt text='Edit'}</a></div>
                <input id="news_urltitle" name="story[urltitle]" readonly="readonly" value="" />
            </div>
            *}
            <div class="z-formrow">
                <label for="news_urltitle">{gt text='Permalink URL'}</label>
                <input id="news_urltitle" name="story[urltitle]" type="text" size="32" maxlength="255" value="{$urltitle|safetext}" />
                <em class="z-sub z-formnote">{gt text='(Generated automatically if left blank)'}</em>
            </div>

            {if $modvars.News.enablecategorization}
            <div class="z-formrow">
                <label>{gt text='Category'}</label>
                {gt text='Choose category' assign='lblDef'}
                {nocache}
                {foreach from=$catregistry key='property' item='category'}
                {array_field_isset assign='selectedValue' array=$__CATEGORIES__ field=$property returnValue=1}
                <div class="z-formnote">{selector_category category=$category name="story[__CATEGORIES__][$property]" field='id' selectedValue=$selectedValue defaultValue='0' defaultText=$lblDef}</div>
                {/foreach}
                {/nocache}
            </div>
            {/if}

            {if $modvars.ZConfig.multilingual}
            <div class="z-formrow">
                <label for="news_language">{gt text='Language'}</label>
                {html_select_languages id="news_language" name="story[language]" installed=1 all=1 selected=$modvars.ZConfig.language_i18n|default:''}
            </div>
            {/if}
            <div style="float:right" id="news_status_info">
                <span id="news_saving_draft">{img modname='core' src='circle-ball-dark-antialiased.gif' set='ajax'}</span>
                <span id="news_status_text" >statustext</span>
            </div>
        </fieldset>

        <fieldset class="z-linear">
            <legend>{gt text='Article'}</legend>
            <div class="z-formrow">
                {if $formattedcontent eq 0}
                <div class="z-warningmsg">{gt text='Permitted HTML tags'}: {news_allowedhtml}</div>
                {/if}
                <div class="z-informationmsg" style='margin-bottom:0 !important;'><span class="z-mandatorysym">*</span> {gt text='You must enter either <b>teaser text</b> or <b>body text</b>.'}</div>
            </div>
            <div class="z-formrow">
                <label for="news_hometext"><strong>{gt text='Index page teaser text'}</strong></label>
                <textarea id="news_hometext" name="story[hometext]" cols="40" rows="10">{$hometext|safetext}</textarea>
                {if $formattedcontent eq 0}<span id="news_hometext_remaining" class="z-formnote z-sub">{gt text='(Limit: %s characters)' tag1='4,294,967,295'}</span>{/if}
            </div>

            {if $formattedcontent eq 0}
            <div class="z-formrow">
                <label for="news_hometextcontenttype">{gt text='Index page teaser format'}</label>
                <select id="news_hometextcontenttype" name="story[hometextcontenttype]">
                    <option value="0"{if $hometextcontenttype eq 0} selected="selected"{/if}>{gt text='Plain text'}</option>
                    <option value="1"{if $hometextcontenttype eq 1} selected="selected"{/if}>{gt text='Text formatted with mark-up language'}</option>
                </select>
            </div>
            {/if}

            <div class="z-formrow">
                <label for="news_bodytext"><strong>{gt text='Article body text'}</strong></label>
                <textarea id="news_bodytext" name="story[bodytext]" cols="40" rows="10">{$bodytext|safetext}</textarea>
                {if $formattedcontent eq 0}<span id="news_bodytext_remaining" class="z-formnote z-sub">{gt text='(Limit: %s characters)' tag1='4,294,967,295'}</span>{/if}
            </div>

            {if $formattedcontent eq 0}
            <div class="z-formrow">
                <label for="news_bodytextcontenttype">{gt text='Article body format'}</label>
                <select id="news_bodytextcontenttype" name="story[bodytextcontenttype]">
                    <option value="0"{if $bodytextcontenttype eq 0} selected="selected"{/if}>{gt text='Plain text'}</option>
                    <option value="1"{if $bodytextcontenttype eq 1} selected="selected"{/if}>{gt text='Text formatted with mark-up language'}</option>
                </select>
            </div>
            {/if}

            {if $accessadd eq 1}
            <div class="z-formrow">
                <label for="news_notes"><a id="news_notes_collapse" href="javascript:void(0);"><span id="news_notes_showhide">{gt text='Show'}</span> {gt text='Footnote'}</a></label>
                <p id="news_notes_details">
                    <textarea id="news_notes" name="story[notes]" cols="40" rows="6">{$notes|safetext}</textarea>
                    <span class="z-formnote z-sub">{gt text='(Limit: %s characters)' tag1='65,536'}</span>
                </p>
            </div>
            {/if}
        </fieldset>

        {if $modvars.News.picupload_enabled}
        <fieldset>
            <legend>{gt text='Pictures'}</legend>
			<label for="news_files_element">{gt text='Select a picture (max. %s kB per picture)' tag1="`$modvars.News.picupload_maxfilesize/1000`"}</label>
            {if $modvars.News.picupload_maxpictures eq 1}
			<input id="news_files_element" name="news_files[0]" type="file">
            {else}
			<input id="news_files_element" name="news_files" type="file"><br>
            <span class="z-sub">{gt text='(max files %s, first picture is used as thumbnail in the index teaser page for this article.)' tag1=$modvars.News.picupload_maxpictures}</span>
            <div id="news_files_list"></div>
            <script type="text/javascript">
                // <![CDATA[
                var multi_selector = new MultiSelector( document.getElementById( 'news_files_list' ), {{$modvars.News.picupload_maxpictures}} );
                multi_selector.addElement( document.getElementById( 'news_files_element' ) );
                // ]]>
            </script>
            {/if}
        </fieldset>
        {/if}

        {if $accessadd eq 1}
        <fieldset>
            <legend><a id="news_publication_collapse" href="javascript:void(0);"><span id="news_publication_showhide">{gt text='Show'}</span> {gt text='Publishing options'}</a></legend>
            <div id="news_publication_details">
                <div class="z-formrow">
                    <label for="news_hideonindex">{gt text='Publish on news index page'}</label>
                    <input id="news_hideonindex" name="story[hideonindex]" type="checkbox" value="1" {if $hideonindex eq 1}checked="checked" {/if}/>
                </div>
                <div class="z-formrow">
                    <label for="news_weight">{gt text='Article weight'}</label>
                    <div>
                        <input id="news_weight" name="story[weight]" type="text" size="5" value="{$weight|safetext}" />
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_unlimited">{gt text='No time limit'}</label>
                    <input id="news_unlimited" name="story[unlimited]" type="checkbox" value="1" {if $unlimited eq 1}checked="checked" {/if}/>
                </div>

                <div id="news_expiration_details">
                    <div class="z-formrow">
                        <label>{gt text='Start date'}</label>
                        <div>
                            <input id="news_from" class="datepicker" name="story[from]" type="text" size="18" value="{$from}" />
                        </div>
                    </div>
                    <div class="z-formrow">
                        <label for="news_tonolimit">{gt text='No end date'}</label>
                        <input id="news_tonolimit" name="story[tonolimit]" type="checkbox" value="1" {if $tonolimit eq 1}checked="checked" {/if} />
                    </div>
                    <div id="news_expiration_date">
                        <div class="z-formrow">
                            <label>{gt text='End date'}</label>
                            <div>
                                <input id="news_to" class="datepicker" name="story[to]" type="text" size="18" value="{$to}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_disallowcomments">{gt text='Allow comments on this article'}</label>
                    <input id="news_disallowcomments" name="story[disallowcomments]" type="checkbox" value="1" {if $disallowcomments eq 1}checked="checked" {/if}/>
                </div>
                <div class="z-formrow">
                    <label for="news_sid">{gt text='Article ID'}</label>
                    <input id="news_sid" readonly="readonly" name="story[sid]" size="5" value="{$sid}" />
                </div>
            </div>
        </fieldset>

        <script type="text/javascript">
            // <![CDATA[
            lang = '{{$lang}}';
            if (Control.DatePicker.Language[lang]) {
                if (!Control.DatePicker.Locale[lang+'_iso8601']) {
                    with (Control.DatePicker) Locale[lang+'_iso8601'] = i18n.createLocale('iso8601', lang);
                }
                new Control.DatePicker('news_from', {locale: lang+'_iso8601', use24hrs: true, icon: '{{$baseurl}}modules/News/images/calendar.png', timePicker: true, timePickerAdjacent: true});
                new Control.DatePicker('news_to', {locale: lang+'_iso8601', use24hrs: true, icon: '{{$baseurl}}modules/News/images/calendar.png', timePicker: true, timePickerAdjacent: true});
            } else {
                new Control.DatePicker('news_from', {locale: 'en_iso8601', use24hrs: true, icon: '{{$baseurl}}modules/News/images/calendar.png', timePicker: true, timePickerAdjacent: true});
                new Control.DatePicker('news_to', {locale: 'en_iso8601', use24hrs: true, icon: '{{$baseurl}}modules/News/images/calendar.png', timePicker: true, timePickerAdjacent: true});
            }
            // ]]>
        </script>

        {if $modvars.News.enableattribution}
        <fieldset>
            <legend><a id="news_attributes_collapse" href="javascript:void(0);"><span id="news_attributes_showhide">{gt text='Show'}</span> {gt text='Article attributes'}</a></legend>
            <div id="news_attributes_details">
                <div class="z-formrow">
                    <div class="z-itemlist_newitemdiv">
                        <a onclick="javascript:itemlist_news_attributes.appenditem();" href="javascript:void(0);">{img src='insert_table_row.gif' modname='core' set='icons/extrasmall' alt='' __title='Create new attribute'} {gt text='Create new attribute'}</a>
                    </div>
                    <ul id="news_attributes" class="z-itemlist">
                        {if isset($__ATTRIBUTES__)}
                        {counter name='news_attributes' reset=true print=false start=0}
                        {foreach from=$__ATTRIBUTES__ key='name' item='value'}
                        {counter name='news_attributes' print=false assign='attrnum'}
                        <li id="listitem_news_attributes_{$attrnum}" class="sortable z-clearfix {cycle values='z-odd,z-even'}">
                            <span class="z-itemcell z-w04">&nbsp;</span>
                            <span class="z-itemcell z-w40">
                                <input type="text" id="story_attributes_{$attrnum}_name" name="story[attributes][{$attrnum}][name]" size="25" maxlength="255" value="{$name}" />
                            </span>
                            <span class="z-itemcell z-w40">
                                <input type="text" id="story_attributes_{$attrnum}_value" name="story[attributes][{$attrnum}][value]" size="25" maxlength="255" value="{$value}" />
                            </span>
                            <span class="z-itemcell z-w07">
                                <button type="button" id="buttondelete_news_attributes_{$attrnum}" class="buttondelete">{img src='14_layer_deletelayer.gif' modname='core' set='icons/extrasmall' __alt='Delete'  __title='Delete this attribute' }</button>
                            </span>
                        </li>
                        {foreachelse}
                        <li>&nbsp;</li>
                        {/foreach}
                        {else}
                        <li>&nbsp;</li>
                        {/if}
                    </ul>
                    <ul style="display:none">
                        <li id="news_attributes_emptyitem" class="sortable z-clearfix">
                            <span class="z-itemcell z-w04">&nbsp;</span>
                            <span class="z-itemcell z-w40">
                                <input type="text" id="story_attributes_X_name" name="dummy[]" size="25" maxlength="255" value="" />
                            </span>
                            <span class="z-itemcell z-w40">
                                <input type="text" id="story_attributes_X_value" name="dummy[]" size="25" maxlength="255" value="" />
                            </span>
                            <span class="z-itemcell z-w07">
                                <button type="button" id="buttondelete_news_attributes_X" class="buttondelete">{img src='14_layer_deletelayer.gif' modname='core' set='icons/extrasmall' __alt='Delete'  __title='Delete this attribute' }</button>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </fieldset>
        {/if}

        {notifydisplayhooks eventname='news.hook.articles.ui.edit' area='modulehook_area.news.articles' subject=null id=null caller="News"}
        {/if}

        <div class="z-buttonrow z-buttons z-center">
            {if $accessadd neq 1}
            <button id="news_button_submit" class="z-btgreen" type="submit" name="story[action]" value="1" title="{gt text='Submit this article'}">{img src='button_ok.gif' modname='core' set='icons/extrasmall' __alt='Submit' __title='Submit this article'} {gt text='Submit'}</button>
            {else}
            <button id="news_button_publish" class="z-btgreen" type="submit" name="story[action]" value="2" title="{gt text='Publish this article'}">{img src='button_ok.gif' modname='core' set='icons/extrasmall' __alt='Publish' __title='Publish this article' }<span id="news_button_text_publish"> {gt text='Publish'}</span></button>
            <span id="news_button_savedraft_nonajax">
                <button id="news_button_draft_nonajax" type="submit" name="story[action]" value="6" title="{gt text='Save this article as draft'}">{img src='edit.gif' modname='core' set='icons/extrasmall' __alt='Save as draft' __title='Save this article as draft'} {gt text='Save as draft'}</button>
            </span>
            <span id="news_button_savedraft_ajax" class="hidelink">
                <a id="news_button_draft" href="javascript:void(0);" onclick="savedraft();">{img src='edit.gif' modname='core' set='icons/extrasmall' __alt='Save as draft'  __title='Save this article as draft'}
                    <span id="news_button_text_draft"> {gt text='Save as draft'}</span>
                </a>
            </span>
            <button id="news_button_pending" type="submit" name="story[action]" value="4" title="{gt text='Mark this article as pending'}">{img src='queue.gif' modname='core' set='icons/extrasmall' __alt='Pending' __title='Mark this article as pending'} {gt text='Pending'}</button>
            {/if}
            <button id="news_button_preview" type="submit" name="story[action]" value="0" title="{gt text='Preview this article'}">{img src='14_layer_visible.gif' modname='core' set='icons/extrasmall' __alt='Preview' __title='Preview this article'} {gt text='Preview'}</button>
            <a id="news_button_cancel" href="{modurl modname='News' type='admin' func='view'}" class="z-btred">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
</div>