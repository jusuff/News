{ajaxheader modname='News' filename='news.js' effects=true dragdrop=true}
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

{gt text='Edit news article' assign='templatetitle'}

{* Add editing / deleting of own (draft) articles *}
{checkpermission component='News::' instance="$item.cr_uid::$item.sid" level='ACCESS_DELETE' assign='mayDelete'}

{admincategorymenu}
<div class="z-adminbox">
    <h1>{$modinfo.displayname}</h1>
    {modulelinks modname='News' type='admin'}
</div>

<script type="text/javascript">
    // <![CDATA[
    var bytesused = Zikula.__f('%s characters out of 4,294,967,295','#{chars}','module_News');
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
    <div class="news_article news_preview" style="background-image: url({img modname='News' src='bg_preview.png' retval='src'})">{$preview}</div>
    {/if}

    <div class="z-adminpageicon">{img modname='core' src='edit.gif' set='icons/large' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {if $modvars.News.picupload_enabled}
    <form id="news_user_modifyform" class="z-form" action="{modurl modname='News' type='admin' func='update'}" method="post" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="{$modvars.News.picupload_maxfilesize|safetext}" />
    {else}
    <form id="news_user_modifyform" class="z-form" action="{modurl modname='News' type='admin' func='update'}" method="post" enctype="application/x-www-form-urlencoded">
    {/if}
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='News'}" />
            <input type="hidden" name="story[sid]" id='news_sid' value="{$item.sid|safetext}" />
            <input type="hidden" name="story[approver]" value="{$item.approver|safetext}" />
            <input type="hidden" name="story[pictures]" value="{$item.pictures|safetext}" />
            {if $formattedcontent eq 1}
            <input type="hidden" name="story[hometextcontenttype]" value="1" />
            <input type="hidden" name="story[bodytextcontenttype]" value="1" />
            {/if}

            <fieldset>
                <legend>{gt text='Title'}</legend>

                <div class="z-formrow">
                    <label for="news_title">{gt text='Title text'}<span class="z-mandatorysym">*</span></label>
                    <input id="news_title" name="story[title]" type="text" size="32" maxlength="255" value="{$item.title|safetext}" />
                </div>

                <div class="z-formrow">
                    <label for="news_urltitle">{gt text='Permalink URL'}</label>
                    <input id="news_urltitle" name="story[urltitle]" type="text" size="32" maxlength="255" value="{$item.urltitle|safetext}" />
                    <em class="z-sub z-formnote">{gt text='(Generated automatically if left blank)'}</em>
                </div>

                {if $modvars.News.enablecategorization}
                <div class="z-formrow">
                    <label>{gt text='Category'}</label>
                    {gt text='Choose category' assign='lblDef'}
                    {nocache}
                    {foreach from=$catregistry key='property' item='category'}
                    {array_field_isset array=$item.__CATEGORIES__ field=$property assign='catExists'}
                    {if $catExists}
                    {array_field_isset array=$item.__CATEGORIES__.$property field='id' returnValue=1 assign='selectedValue'}
                    {else}
                    {assign var='selectedValue' value='0'}
                    {/if}
                    <div class="z-formnote">{selector_category category=$category name="story[__CATEGORIES__][$property]" field='id' selectedValue=$selectedValue defaultValue='0' defaultText=$lblDef}</div>
                    {/foreach}
                    {/nocache}
                </div>
                {/if}

                {if $modvars.ZConfig.multilingual}
                <div class="z-formrow">
                    <label for="news_language">{gt text='Language(s) for which article should be displayed'}</label>
                    {html_select_languages id="news_language" name="story[language]" installed=1 all=1 selected=$item.language|default:''}
                </div>
                {/if}
            </fieldset>

            <fieldset class="z-linear">
                <legend>{gt text='Article'}</legend>
                <div class="z-formrow">
                    {if $formattedcontent eq 0}
                    <div class="z-warningmsg">{gt text='Permitted HTML tags'}: {news_allowedhtml}</div>
                    {/if}
                    <div class="z-informationmsg" style='margin-bottom:0 !important;'><span class="z-mandatorysym">*</span> {gt text='You must enter either <strong>teaser text</strong> or <strong>body text</strong>.'}</div>
                </div>
                <div class="z-formrow">
                    <label for="news_hometext"><strong>{gt text='Index page teaser text'}</strong></label>
                    <textarea id="news_hometext" name="story[hometext]" cols="40" rows="10">{$item.hometext|safetext}</textarea>
                    {if $formattedcontent eq 0}<span id="news_hometext_remaining" class="z-formnote z-sub">{gt text='(Limit: %s characters)' tag1='4,294,967,295'}</span>{/if}
                </div>

                {if $formattedcontent eq 0}
                <div class="z-formrow">
                    <label for="news_hometextcontenttype">{gt text='Index page teaser format'}</label>
                    <select id="news_hometextcontenttype" name="story[hometextcontenttype]">
                        <option value="0"{if $item.hometextcontenttype eq 0} selected="selected"{/if}>{gt text='Plain text'}</option>
                        <option value="1"{if $item.hometextcontenttype eq 1} selected="selected"{/if}>{gt text='Text formatted with mark-up language'}</option>
                    </select>
                </div>
                {/if}

                <div class="z-formrow">
                    <label for="news_bodytext"><strong>{gt text='Article body text'}</strong></label>
                    <textarea id="news_bodytext" name="story[bodytext]" cols="40" rows="10">{$item.bodytext|safetext}</textarea>
                    {if $formattedcontent eq 0}<span id="news_bodytext_remaining" class="z-formnote z-sub">{gt text='(Limit: %s characters)' tag1='4,294,967,295'}</span>{/if}
                </div>

                {if $formattedcontent eq 0}
                <div class="z-formrow">
                    <label for="news_bodytextcontenttype">{gt text='Article body format'}</label>
                    <select id="news_bodytextcontenttype" name="story[bodytextcontenttype]">
                        <option value="0"{if $item.bodytextcontenttype eq 0} selected="selected"{/if}>{gt text='Plain text'}</option>
                        <option value="1"{if $item.bodytextcontenttype eq 1} selected="selected"{/if}>{gt text='Text formatted with mark-up language'}</option>
                    </select>
                </div>
                {/if}

                <div class="z-formrow">
                    <label for="news_notes"><a id="news_notes_collapse" href="javascript:void(0);"><span id="news_notes_showhide">{gt text='Show'}</span> {gt text='Footnote'}</a></label>
                    <p id="news_notes_details">
                        <textarea id="news_notes" name="story[notes]" cols="40" rows="10">{$item.notes|safetext}</textarea>
                        <span class="z-formnote z-sub">{gt text='(Limit: %s characters)' tag1='65,536'}</span>
                    </p>
                </div>
            </fieldset>

            {if $modvars.News.picupload_enabled}
            <fieldset>
                <legend>{gt text='Pictures'}</legend>
                <label for="news_files_element">{gt text='Select a picture (max. %s kB per picture)' tag1=`$modvars.News.picupload_maxfilesize/1000`}</label>
                {if $modvars.News.picupload_maxpictures eq 1}
                <input id="news_files_element" name="news_files[0]" type="file">
                {else}
                <input id="news_files_element" name="news_files" type="file"><br>
                <span class="z-sub">{gt text='(max files %s, first picture is used as thumbnail in the index teaser page for this article.)' tag1=$modvars.News.picupload_maxpictures}</span>
                <div id="news_files_list"></div>
                <script type="text/javascript">
                    // <![CDATA[
                    var multi_selector = new MultiSelector(document.getElementById('news_files_list'), {{$modvars.News.picupload_maxpictures}}, {{$item.pictures}});
                    multi_selector.addElement(document.getElementById('news_files_element'));
                    // ]]>
                </script>
                {/if}

                {if $item.pictures gt 0}
                <div><br>
                    {section name=counter start=0 loop=$item.pictures step=1}
                        <img src="{$modvars.News.picupload_uploaddir}/pic_sid{$item.sid}-{$smarty.section.counter.index}-thumb.jpg" width="80" /> <input type="checkbox" id="story_del_picture_{$smarty.section.counter.index}" name="story[del_pictures][]" value="pic_sid{$item.sid}-{$smarty.section.counter.index}"><label for="story_del_picture_{$smarty.section.counter.index}">{gt text='Delete this picture'}</label><br />
                    {/section}
                </div>
                {/if}
            </fieldset>
            {/if}

            <fieldset>
                <legend><a id="news_publication_collapse" href="javascript:void(0);"><span id="news_publication_showhide">{gt text='Show'}</span> {gt text='Publishing options'}</a></legend>
                <div id="news_publication_details">
                    <div class="z-formrow">
                        <label for="news_hideonindex">{gt text='Publish on news index page'}</label>
                        <input id="news_hideonindex" name="story[hideonindex]" type="checkbox" value="1" {if $item.hideonindex eq 0}checked="checked" {/if}/>
                    </div>
                    <div class="z-formrow">
                        <label for="news_weight">{gt text='Article weight'}</label>
                        <div>
                            <input id="news_weight" name="story[weight]" type="text" size="10" maxlength="10" value="{$item.weight|safetext}" />
                        </div>
                    </div>
                    <div class="z-formrow">
                        <label for="news_unlimited">{gt text='No time limit'}</label>
                        <input id="news_unlimited" name="story[unlimited]" type="checkbox" value="1" {if $item.unlimited eq 1}checked="checked" {/if}/>
                    </div>

                    <div id="news_expiration_details">
                        <div class="z-formrow">
                            <label>{gt text='Start date'}</label>
                            <div>
                                <input id="news_from" class="datepicker" name="story[from]" type="text" size="18" value="{$item.from}" />
                            </div>
                        </div>
                        <div class="z-formrow">
                            <label for="news_tonolimit">{gt text='No end date'}</label>
                            <input id="news_tonolimit" name="story[tonolimit]" type="checkbox" value="1" {if $item.tonolimit eq 1}checked="checked" {/if}/>
                        </div>
                        <div id="news_expiration_date">
                            <div class="z-formrow">
                                <label>{gt text='End date'}</label>
                                <div>
                                    <input id="news_to" class="datepicker" name="story[to]" type="text" size="18" value="{$item.to}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="z-formrow">
                        <label for="news_disallowcomments">{gt text='Allow comments on this article'}</label>
                        <input id="news_disallowcomments" name="story[disallowcomments]" type="checkbox" value="1" {if $item.disallowcomments eq 0}checked="checked" {/if}/>
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
                            {if isset($item.__ATTRIBUTES__)}
                            {counter name='news_attributes' reset=true print=false start=0}
                            {foreach from=$item.__ATTRIBUTES__ key='name' item='value'}
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

            <fieldset>
                <legend><a id="news_meta_collapse" href="javascript:void(0);">{gt text='Meta data'}</a></legend>
                <div id="news_meta_details">
                    <ul>
                        {usergetvar name='uname' uid=$item.cr_uid assign='username'}
                        <li>
                            {gt text='Contributed by'} <span id='news_contributor'>{$item.contributor}</span> {gt text='on'} {$item.cr_date|dateformat} <a id="news_cr_uid_edit" href="{modurl modname='News' type='admin' func='selectuser' id=$item.cr_uid}">{img modname='core' set='icons/extrasmall' src='xedit.gif' __title='Edit' __alt='Edit'}</a>
                            <input type="hidden" id="news_cr_uid" name="story[cr_uid]" value="{$item.cr_uid}" />
                            <script type="text/javascript">
                                var options = {overlayOpacity:0.7,modal:true,draggable:false,resizable:false,initMaxHeight:220,title:Zikula.__('Article Author','module_News')};
                                var userselectwindow = new Zikula.UI.FormDialog($('news_cr_uid_edit'),executeuserselectform,options);
                            </script>
                        </li>
                        {usergetvar name='uname' uid=$item.lu_uid assign='username'}
                        <li>{gt text='Last edited'} {gt text='by %1$s on %2$s' tag1=$username tag2=$item.lu_date|dateformat}</li>
                        {if $item.published_status eq 0}
                        {usergetvar name='uname' uid=$item.approver|safetext assign='approvername'}
                        <li>{gt text='Approved by %s' tag1=$approvername}</li>
                        {/if}
                        <li>{gt text='Status %s' tag1=$item.published_status|news_getstatustext}</li>
                        <li>{gt text='Article ID: %s' tag1=$item.sid}</li>
                    </ul>
                </div>
            </fieldset>

            {notifydisplayhooks eventname='news.hook.articles.ui.edit' area='modulehook_area.news.articles' subject=$item id=$item.sid caller="News"}

            <div class="z-buttonrow z-buttons z-center">
                {if $item.published_status eq 2}
                <button id="news_button_publish" class="z-btgreen" type="submit" name="story[action]" value="2" title="{gt text='Approve and publish this article'}">{img src='button_ok.gif' modname='core' set='icons/extrasmall' __alt='Approve and publish this article'  __title='Approve and publish this article' }{gt text='Approve and'} <span id="news_button_text_publish"> {gt text='Publish'}</span></button>
                {elseif $item.published_status eq 0}
                <button id="news_button_publish" class="z-btgreen" type="submit" name="story[action]" value="2" title="{gt text='Update this article'}">{img src='button_ok.gif' modname='core' set='icons/extrasmall' __alt='Update'  __title='Update this article' } {gt text='Update'}</button>
                {else}
                <button id="news_button_publish" class="z-btgreen" type="submit" name="story[action]" value="2" title="{gt text='Publish this article'}">{img src='button_ok.gif' modname='core' set='icons/extrasmall' __alt='Publish'  __title='Publish this article' }<span id="news_button_text_publish"> {gt text='Publish'}</span></button>
                {/if}
                <button id="news_button_preview" type="submit" name="story[action]" value="0" title="{gt text='Preview this article'}">{img src='14_layer_visible.gif' modname='core' set='icons/extrasmall' __alt='Preview' __title='Preview this article'} {gt text='Preview'}</button>
                {if $accessadd neq 1}
                <button id="news_button_submit" class="z-btgreen" type="submit" name="story[action]" value="1" title="{gt text='Submit this article'}">{img src='button_ok.gif' modname='core' set='icons/extrasmall' __alt='Submit' __title='Submit this article'} {gt text='Submit'}</button>
                {else}
                {if $item.published_status eq 4}
                <button id="news_button_draft" type="submit" name="story[action]" value="6" title="{gt text='Update draft'}">{img src='edit.gif' modname='core' set='icons/extrasmall' __alt='Update draft' __title='Update draft'} {gt text='Update draft'}</button>
                {else}
                <button id="news_button_draft" type="submit" name="story[action]" value="6" title="{gt text='Save this article as draft'}">{img src='edit.gif' modname='core' set='icons/extrasmall' __alt='Save as draft' __title='Save this article as draft'} {gt text='Save as draft'}</button>
                {/if}
                {if $item.published_status neq 2}
                <button id="news_button_pending" type="submit" name="story[action]" value="4" title="{gt text='Mark this article as pending'}">{img src='queue.gif' modname='core' set='icons/extrasmall' __alt='Pending' __title='Mark this article as pending'} {gt text='Pending'}</button>
                {/if}
                {if $item.published_status neq 3}
                <button id="news_button_archive" type="submit" name="story[action]" value="5" title="{gt text='Archive this article'}">{img src='folder_yellow.gif' modname='core' set='icons/extrasmall' __alt='Archive'  __title='Archive this article' } {gt text='Archive'}</button>
                {/if}
                {if $item.published_status eq 2}
                <button id="news_button_reject" class="z-btred" type="submit" name="story[action]" value="3" title="{gt text='Reject this article'}">{img src='locked.gif' modname='core' set='icons/extrasmall' __alt='Reject'  __title='Reject this article' } {gt text='Reject'}</button>
                {/if}
                {/if}
                {if $mayDelete}
                <a id="news_button_delete" href="{modurl modname='News' type='admin' func='delete' sid=$item.sid}" class="z-btred">{img modname='core' src='editdelete.gif' set='icons/extrasmall' __alt='Delete' __title='Delete this article'} {gt text='Delete'}</a>
                {/if}
                <a id="news_button_cancel" href="{modurl modname='News' type='admin' func='view'}" class="z-btred">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
</div>