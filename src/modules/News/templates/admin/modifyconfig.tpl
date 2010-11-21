{ajaxheader modname='News' filename='news_admin_modifyconfig.js' effects=true nobehaviour=true noscriptaculous=true}

{include file='admin/menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='configure.gif' set='icons/large' __alt='Settings'}</div>

    <h2>{gt text='Settings'}</h2>
    <p class="z-warningmsg">{gt text='Notice: Your theme could be using template overrides for the News publisher module (in themes/YourThemeName/templates/modules/News/...). They might lack behind in functionality to the current default News publisher templates, please remove them or check them carefully against the default News publisher templates (in modules/News/pntemplates/...).'}</p>

    <form class="z-form" action="{modurl modname='News' type='admin' func='updateconfig'}" method="post" enctype="application/x-www-form-urlenpred">
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='News'}" />
            <fieldset>
                <legend>{gt text='General settings'}</legend>
                <div class="z-formrow">
                    <label for="news_enableattribution">{gt text='Enable article attributes'}</label>
                    <input id="news_enableattribution" type="checkbox" name="enableattribution"{if $enableattribution} checked="checked"{/if} />
                </div>
            </fieldset>

            <fieldset>
                <legend>{gt text='Category settings'}</legend>
                <div class="z-formrow">
                    <label for="news_enablecategorization">{gt text='Enable categorisation'}</label>
                    <input id="news_enablecategorization" type="checkbox" name="enablecategorization"{if $enablecategorization} checked="checked"{/if} />
                </div>
                <div id="news_category_details">
                    <div class="z-formrow">
                        <label for="topicproperty">{gt text='Category to use for legacy \'Topics\' module template variables'}</label>
                        {html_options id='topicproperty' name='topicproperty' options=$properties selected=$property}
                    </div>
                    <div class="z-formrow">
                        <label for="settings_catimagepath">{gt text='Category image path (with trailing /)'}</label>
                        <input id="settings_catimagepath" type="text" name="catimagepath" value="{$catimagepath|safetext}" size="40" maxlength="80" />
                        <div class="z-informationmsg z-formnote">{gt text='Notice: You can associate an image with each article category. The image must be located in the category image path entered in \'Category image path\'. You must also go to the Categories manager in the site admin panel and then define the associated image. To do this, add a category attribute named \'topic_image\' to each article category, and then enter the associated image path and name.'}</div>
                    </div>
                    <div class="z-formrow">
                        <label for="news_enablecategorybasedpermissions">{gt text='Enable category based permission checks'}</label>
                        <input id="news_enablecategorybasedpermissions" type="checkbox" name="enablecategorybasedpermissions"{if $enablecategorybasedpermissions} checked="checked"{/if} />
                        <div class="z-informationmsg z-formnote">{gt text="Notice: You can use category based permission checks (Categories::Category | Category ID:Category Path:Category IPath) for the display of each and every article. If you don't need to select access based on the articles categories, you can uncheck this setting for a gain in speed (e.g. less database queries)."}</div>                    
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>{gt text='Display settings'}</legend>
                <div class="z-formrow">
                    <label for="settings_storyorder">{gt text='Order article listings by'}</label>
                    <select id="settings_storyorder" name="storyorder" size="1">
                        <option value="0"{if $storyorder eq 0} selected="selected"{/if}>{gt text='Article ID'}</option>
                        <option value="1"{if $storyorder eq 1} selected="selected"{/if}>{gt text='Article date/time'}</option>
                        <option value="2"{if $storyorder eq 2} selected="selected"{/if}>{gt text='Article weight'}</option>
                    </select>
                </div>
                <div class="z-formrow">
                    <label for="settings_storyhome">{gt text='Number of articles in news index page'}</label>
                    <input id="settings_storyhome" type="text" name="storyhome" value="{$storyhome|safetext}" size="5" maxlength="5" />
                </div>
                <div class="z-formrow">
                    <label for="news_itemsperpage">{gt text='Number of articles in archive page'}</label>
                    <input id="news_itemsperpage" type="text" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="news_refereronprint">{gt text='Check referer on printer-friendly pages'}</label>
                    <div id="news_refereronprint">
                        <input type="radio" id="refereronprintyes" name="refereronprint" value="1"{if $refereronprint eq 1} checked="checked"{/if} /> <label for="refereronprintyes">{gt text='Yes'}</label>
                        <input type="radio" id="refereronprintno" name="refereronprint" value="0"{if $refereronprint eq 0} checked="checked"{/if} /> <label for="refereronprintno">{gt text='No'}</label>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_enableajaxedit">{gt text='Enable in-line editing of articles via JavaScript/Ajax controls'}</label>
                    <input id="news_enableajaxedit" type="checkbox" name="enableajaxedit"{if $enableajaxedit} checked="checked"{/if} />
                </div>
                <div id="news_ajaxedit_details">
                    <div class="z-informationmsg z-formnote">{gt text='When Scribite! is being used for editing, the <strong>display</strong> function needs to be added to the list of module functions that Scribite! uses for the News Publisher module.'}</div>
                </div>
                <div class="z-formrow">
                    <label for="news_enablemorearticlesincat">{gt text='Enable \'More articles in category\' when displaying an article'}</label>
                    <input id="news_enablemorearticlesincat" type="checkbox" name="enablemorearticlesincat"{if $enablemorearticlesincat} checked="checked"{/if} />
                </div>
                <div id="news_morearticles_details">
                    <div class="z-formrow">
                        <label for="news_morearticlesincat">{gt text='Number of \'More articles in category\' for every article'}</label>
                        <input id="news_morearticlesincat" type="text" name="morearticlesincat" size="3" value="{$morearticlesincat|safetext}" />
                        <div class="z-informationmsg z-formnote">{gt text='When displaying an article, a number of additional articletitles in the same category can be shown.<br />To show the additional articletitles for every article set the value above to a number larger than 0. When the value is set to 0, the number of additional articletitles can be set per article by means of the article attribute \'morearticlesincat\'. You need to enable \'article attributes\' yourself. <br />When the setting above or the article attribute is set to 0, no titles will be extracted from the database.'}</div>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_notifyonpending">{gt text='Notify moderators when a new pending article is submitted for review'}</label>
                    <input id="news_notifyonpending" type="checkbox" name="notifyonpending"{if $notifyonpending} checked="checked"{/if} />
                </div>
                <div id="news_notifyonpending_details">
                    <div class="z-formrow">
                        <div class="z-informationmsg z-formnote">{gt text='Whenever a new article is submitted as Pending Review, the admin or a list of E-mail addresses can be informed of this event with a notification E-mail.'}</div>
                        <label for="news_notifyonpending_fromname">{gt text='From name (leave empty for sitename)'}</label>
                        <input id="news_notifyonpending_fromname" type="text" name="notifyonpending_fromname" value="{$notifyonpending_fromname|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_notifyonpending_fromaddress">{gt text='From address (leave empty for admin E-mail address)'}</label>
                        <input id="news_notifyonpending_fromaddress" type="text" name="notifyonpending_fromaddress" value="{$notifyonpending_fromaddress|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_notifyonpending_toname">{gt text='To name (leave empty for sitename)'}</label>
                        <input id="news_notifyonpending_toname" type="text" name="notifyonpending_toname" value="{$notifyonpending_toname|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_notifyonpending_toaddress">{gt text='To address (comma seperated list of E-Mail addresses, leave empty for admin E-mail address)'}</label>
                        <input id="news_notifyonpending_toaddress" type="text" name="notifyonpending_toaddress" value="{$notifyonpending_toaddress|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_notifyonpending_subject">{gt text='E-mail subject'}</label>
                        <input id="news_notifyonpending_subject" type="text" name="notifyonpending_subject" value="{$notifyonpending_subject|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_notifyonpending_html">{gt text='Send E-mail as HTML '}</label>
                        <input id="news_notifyonpending_html" type="checkbox" name="notifyonpending_html"{if $notifyonpending_html} checked="checked"{/if} />
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_pdflink">{gt text='Display a PDF link for the articles in the index page'}</label>
                    <input id="news_pdflink" type="checkbox" name="pdflink"{if $pdflink} checked="checked"{/if} />
                </div>
                <div id="news_pdflink_details">
                    <div class="z-formrow">
                        <label for="news_pdflink_tcpdfpath">{gt text='Path to the TCPDF main file'}</label>
                        <input id="news_pdflink_tcpdfpath" type="text" name="pdflink_tcpdfpath" value="{$pdflink_tcpdfpath|safetext}" />
                        <div class="z-informationmsg z-formnote">{gt text='The PDF link is based on <a href="http://www.tcpdf.org">TCPDF</a>. You have to install the files yourself into the path specified above, when you want to use the pdflink. Download TCPDF from the link and place the \'tcpdf\' folder into the correct location.'}</div>
                    </div>
                    <div class="z-formrow">
                        <label for="news_pdflink_tcpdflang">{gt text='TCPDF language file'}</label>
                        <input id="news_pdflink_tcpdflang" type="text" name="pdflink_tcpdflang" value="{$pdflink_tcpdflang|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_pdflink_headerlogo">{gt text='TCPDF Header logo image (absolute path or relative to tcpdf)'}</label>
                        <input id="news_pdflink_headerlogo" type="text" name="pdflink_headerlogo" value="{$pdflink_headerlogo|safetext}" />
                        <div class="z-informationmsg z-formnote">{gt text='Default Header logo is defined by TCPDF and in PathToTCPDF/images folder. tcpdf_logo.jpg with a width of 30'}</div>
                    </div>
                    <div class="z-formrow">
                        <label for="news_pdflink_headerlogo_width">{gt text='TCPDF header logo width in mm'}</label>
                        <input id="news_pdflink_headerlogo_width" type="text" name="pdflink_headerlogo_width" value="{$pdflink_headerlogo_width|safetext}" />
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_picupload_enabled">{gt text='Allow article picture(s) uploading'}</label>
                    <input id="news_picupload_enabled" type="checkbox" name="picupload_enabled"{if $picupload_enabled} checked="checked"{/if} />
                </div>
                <div id="news_picupload_details">
                    <div class="z-formrow">
                        <label for="news_picupload_allowext">{gt text='Allowed picture extension'}</label>
                        <input id="news_picupload_allowext" type="text" name="picupload_allowext" value="{$picupload_allowext|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_index_float">{gt text='Image float (left/right/none) on the index page'}</label>
                        <input id="news_picupload_index_float" type="text" name="picupload_index_float" value="{$picupload_index_float|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_article_float">{gt text='Image float (left/right/none) on the article display page'}</label>
                        <input id="news_picupload_article_float" type="text" name="picupload_article_float" value="{$picupload_article_float|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_maxpictures">{gt text='How many pictures are allowed'}</label>
                        <input id="news_picupload_maxpictures" type="text" name="picupload_maxpictures" value="{$picupload_maxpictures|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_maxfilesize">{gt text='Maximum file size of the pictures (in Bytes)'}</label>
                        <input id="news_picupload_maxfilesize" type="text" name="picupload_maxfilesize" value="{$picupload_maxfilesize|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_sizing">{gt text='What thumbnail sizing to use (0=best-fit / 1=adaptive resizing'}</label>
                        <input id="news_picupload_sizing" type="text" name="picupload_sizing" value="{$picupload_sizing|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_picmaxwidth">{gt text='Maximum width of the full size pictures (in pixels)'}</label>
                        <input id="news_picupload_picmaxwidth" type="text" name="picupload_picmaxwidth" value="{$picupload_picmaxwidth|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_picmaxheight">{gt text='Maximum height of the full size pictures (in pixels)'}</label>
                        <input id="news_picupload_picmaxheight" type="text" name="picupload_picmaxheight" value="{$picupload_picmaxheight|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_thumbmaxwidth">{gt text='Maximum width of the thumbnails (in pixels)'}</label>
                        <input id="news_picupload_thumbmaxwidth" type="text" name="picupload_thumbmaxwidth" value="{$picupload_thumbmaxwidth|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_thumbmaxheight">{gt text='Maximum height of the thumbnails (in pixels)'}</label>
                        <input id="news_picupload_thumbmaxheight" type="text" name="picupload_thumbmaxheight" value="{$picupload_thumbmaxheight|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_thumb2maxwidth">{gt text='Maximum width of the thumbnail in the article display intro text (in pixels)'}</label>
                        <input id="news_picupload_thumb2maxwidth" type="text" name="picupload_thumb2maxwidth" value="{$picupload_thumb2maxwidth|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_thumb2maxheight">{gt text='Maximum height of the thumbnail in the article display intro text (in pixels)'}</label>
                        <input id="news_picupload_thumb2maxheight" type="text" name="picupload_thumb2maxheight" value="{$picupload_thumb2maxheight|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="news_picupload_uploaddir">{gt text='Directory where the images are uploaded (leaving empty will create the default directory images/news_picupload)'}</label>
                        <input id="news_picupload_uploaddir" type="text" name="picupload_uploaddir" value="{$picupload_uploaddir|safetext}" />
                    </div>
                </div>
              </fieldset>

            {configgetvar name='shorturls' assign='shorturls'}
            {if $shorturls eq true}
            <fieldset>
                <legend>{gt text='Permalinks'}</legend>
                <p class="z-informationmsg">{gt text='You can select a pre-defined permalink format, or define your custom format.'}</p>
                <input id="news_permalink_customformat" name="customformat" type="hidden"  value="{$permalinkformat|default:'%year%/%monthnum%/%day%/%articletitle%/'}" size="50" />
                <div class="z-formrow">
                    <label for="news_permalink_datename">{gt text='Format based on date and name'}</label>
                    <div>
                        <input id="news_permalink_datename" onclick="news_permalink_onclick()" name="permalinkformat" type="radio" value="%year%/%monthnum%/%day%/%articletitle%"{if $permalinkformat eq '%year%/%monthnum%/%day%/%articletitle%'}checked="checked"{/if} />
                        <span>{$baseurl}{modgetinfo modname=News info=displayname}/{datetime format="%Y/%m/%d"}/{gt text='your-article-title'}/</span>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_permalink_numeric">{gt text='Numeric format'}</label>
                    <div>
                        <input id="news_permalink_numeric" onclick="news_permalink_onclick()" name="permalinkformat" type="radio" value="%articleid%" {if $permalinkformat eq '%articleid%'}checked="checked"{/if} />
                        <span>{$baseurl}{modgetinfo modname='News' info='displayname'}/123</span>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="news_permalink_custom">{gt text='Custom format'}</label>
                    <div>
                        <input id="news_permalink_custom" onclick="news_permalink_onclick()" name="permalinkformat" type="radio" value="custom" {if $permalinkformat neq '%articleid%' and $permalinkformat neq '%year%/%monthnum%/%day%/%articletitle%'}checked="checked"{/if} />
                    </div>
                </div>
                <div id="news_permalink_custom_details">
                    <div class="z-formrow">
                        <label for="news_permalink_format">{gt text='Custom format definition'}</label>
                        <input id="news_permalink_format" onclick="news_permalink_onclick()" name="permalinkstructure" type="text"  value="{$permalinkformat|default:'%year%/%monthnum%/%day%/%articletitle%/'}" size="50" />
                        <em class="z-sub z-formnote">{gt text='Notice: A custom format definition must contain at least either \'%articleid%\' or \'%articletitle%\', in order to be able to identify the article.'}</em>
                    </div>
                    <h4>{gt text='Acceptable values for use in custom format definition:'}</h4>
                    <ul>
                        <li>%year% - {gt text='Year of publication (including century numerals)'}</li>
                        <li>%monthnum% - {gt text='Month of publication, as a number (ergo 1 to 12)'}</li>
                        <li>%monthname% - {gt text='Month of publication as a name (ergo January to December)'}</li>
                        <li>%day% - {gt text='Day'}</li>
                        <li>%articletitle% - {gt text='Article title'}</li>
                        <li>%articleid% - {gt text='Article ID'} </li>
                        <li>%category% - {gt text='Article category'} </li>
                    </ul>
                </div>
            </fieldset>
            {/if}

            {* modcallhooks hookobject='module' hookaction='modifyconfig' hookid='News' module='News' *}

            <div class="z-formbuttons z-buttons">
                {button src='button_ok.gif' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
                <a href="{modurl modname='News' type='admin' func='view'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel'  __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
</div>