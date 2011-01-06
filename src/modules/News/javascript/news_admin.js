/**
 * create the onload function to enable the respective functions
 *
 */
Event.observe(window, 'load', news_admin_init_check);

function news_admin_init_check() 
{
    if ($('news_multicategory_filter')) {
        news_admin_filter_init(); 
    }
    if ($('news_select_all') && $('news_deselect_all')) {
        news_admin_selectall_init(); 
    }
    if ($('news_bulkaction_select')) {
        news_admin_bulkaction_init();
    }
}

function news_admin_filter_init()
{
    $('news_property').observe('change', news_admin_property_onchange);
    news_admin_property_onchange();
    $('news_multicategory_filter').show();
}

// Show the correct category selector for the chosen category property
function news_admin_property_onchange()
{
    $$('div#news_category_selectors select').each(function(select){
        select.hide();
    });
    var id = "news_"+$('news_property').value+"_category";
    $(id).show();
}

// Initialize and process the (de)select all functions
function news_admin_selectall_init()
{
    $('news_select_all').observe('click', function(e){
        Zikula.toggleInput('news_bulkaction_form', true);
        e.stop();
    });
    $('news_deselect_all').observe('click', function(e){
        Zikula.toggleInput('news_bulkaction_form', false);
        e.stop();
    });
}

// Initialize and process bulkactions on selected articles
function news_admin_bulkaction_init()
{
    $('news_bulkaction_select').observe('change', function(event){
        var values=$$('input:checked[type=checkbox][name=news_selected_articles\[\]]').pluck('value');
        values.sort(function(a,b){return a - b});
        var valuescount=values.length;
        var action=$F('news_bulkaction_select');
        var actionmap=new Array(5);
        actionmap[0]=null;
        actionmap[1]=Zikula.__('delete','module_News');
        actionmap[2]=Zikula.__('archive','module_News');
        actionmap[3]=Zikula.__('publish','module_News');
        actionmap[4]=Zikula.__('reject','module_News');
        var actionword=actionmap[action];
        if ((action>0) && (valuescount>0)) {
            var options = {overlayOpacity:0.7,modal:true,draggable:false};
            var conf=Zikula.UI.Confirm(
                Zikula._fn('Are you sure you want to %s the following article',
                    'Are you sure you want to %s the following articles',
                    valuescount,
                    ['<strong>'+actionword+'</strong>'],
                    'module_News')+': '+values,
                Zikula.__('Confirm Bulk Action','module_News'),
                function(res){
                    if(res) {
                        $('news_bulkaction_form').submit();
                    } else {
                        // action cancelled
                        $('news_bulkaction_select').selectedIndex=0;
                    }
                },
                options
            );
        } else {
            $('news_bulkaction_select').selectedIndex=0;
            Zikula.UI.Alert(
                Zikula.__f('Please select at least one article to %s.',actionword,'module_News'),
                Zikula.__('Bulk action error','module_News')
            );
        }
    });
}