{if $enablecategorization}
<div class="z-formrow">
    <label for="category">{gt text='Category'}</label>
    <div>
        {gt text='Choose category' assign='lblDef'}
        {nocache}
        {selector_category category=$mainCategory name='category' field='id' selectedValue=$category defaultValue='0' defaultText=$lblDef}
        {/nocache}
    </div>
</div>
{/if}

<p class="z-formnote z-informationmsg">{gt text='Notice: the news publisher slideshow block is developed for the case where every article has images. Furthermore it only works fine if the sizing is set to adaptive resizing, which gives fixed dimensions.'}</p>
<div class="z-formrow">
    <label for="slideshowblock_limit">{gt text='Maximum number of articles to display'}</label>
    <input id="slideshowblock_limit" type="text" name="limit" size="2" value="{$limit|safetext}" />
</div>
