<div class="z-formrow">
    <label for="storiesblock_storiestype">{gt text='Scope of news article listing' domain="module_news"}</label>
    <select id="storiesblock_storiestype" name="storiestype">
        <option value="2"{if $storiestype eq 2} selected="selected"{/if}>{gt text='Show all news articles' domain="module_news"}</option>
        <option value="3"{if $storiestype eq 3} selected="selected"{/if}>{gt text='Show only articles set for index page listing' domain="module_news"}</option>
        <option value="1"{if $storiestype eq 1} selected="selected"{/if}>{gt text='Show only articles not set for index page listing' domain="module_news"}</option>
    </select>
</div>

{if $enablecategorization}
<div class="z-formrow">
    <label for="category">{gt text='Category' domain="module_news"}</label>
    <div>
        {gt text='Choose category' domain="module_news" assign='lblDef'}
        {nocache}
        {selector_category category=$mainCategory name='category' field='id' selectedValue=$category defaultValue='0' defaultText=$lblDef}
        {/nocache}
    </div>
</div>
{/if}

<div class="z-formrow">
    <label for="storiesblock_limit">{gt text='Maximum number of articles to display' domain="module_news"}</label>
    <input id="storiesblock_limit" type="text" name="limit" size="2" value="{$limit|safetext}" />
</div>
