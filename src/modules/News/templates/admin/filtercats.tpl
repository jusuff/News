{ajaxheader module="News" ui=true}
{gt text="All These Categories" assign="allText"}
{nocache}
{foreach from=$catregistry key=property item=category}
    {array_field_isset assign="selectedValue" array=$selectedcategories field=$property returnValue=1}
    {selector_category
        editLink=0
        category=$category
        name="news[__CATEGORIES__][$property]"
        field="id"
        selectedValue=$selectedValue
        defaultValue="0"
        all=1
        allText=$allText
        allValue=0}
    <a href='' id='news___CATEGORIES____{$property}__open'>
        {img modname="core" src="edit_add.gif" set="icons/extrasmall" __alt="Select Multiple" __title="Select Multiple"}
    </a>
    <script type="text/javascript">
        var news___CATEGORIES____{{$property}}_ = new Zikula.UI.SelectMultiple(
            'news___CATEGORIES____{{$property}}_',
            {opener: 'news___CATEGORIES____{{$property}}__open',
            title: Zikula.__('Select multiple categories','module_News'),
            value: '{{news_implode value=$selectedValue}}',
            excludeValues: ['0']}
        );
    </script>
{/foreach}
{/nocache}