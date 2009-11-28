/*
 *  $Id: news.js 75 2009-02-24 04:51:52Z mateo $ 
 */
 
var editing = false;

/**
 * create the onload function to enable the respective functions
 *
 */
Event.observe(window, 
              'load', 
              news_init_check,
              false);

function news_init_check() 
{
    if ($('news_loadnews')) {
        Element.hide('news_loadnews');
    }
    if ($('news_editlinks')) {
        Element.remove('news_editlinks');
    }
    if ($('news_editlinks_ajax')) {
        Element.removeClassName($('news_editlinks_ajax'), 'hidelink'); 
    }
/*
    if ($('news_sample_urltitle')) {
        news_title_init();
    }
*/    
    if ($('news_meta_collapse')) {
        news_meta_init();
    }
    if ($('news_notes_collapse')) {
        news_notes_init();
    }
    if ($('news_expiration_details')) {
        news_expiration_init();
    }
    if ($('news_publication_collapse')) {
        news_publication_init();
    }
    if ($('news_attributes_collapse')) {
        news_attributes_init();
    }
    if ($('news_multicategory_filter')) {
        news_filter_init(); 
    } 
}


/**
 * Start the editing/updating process by calling the appropriate Ajax function
 *
 *@params sid    the story id;
 *@params page   the page id;
 *@return none;
 *@author Frank Schummertz
 */
function editnews(sid, page)
{
    if(editing==false) {
        Element.show('news_loadnews');
        var pars = 'module=News&func=modify&sid=' + sid  + '&page=' + page;
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+'ajax.php', 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: editnews_init
            });
    }
}

/**
 * This functions gets called when the Ajax request initiated in editnews() returns. 
 * It hides the news story and shows the modify html as defined in news_ajax_modify.htm
 *
 *@params req   response from Ajax request;
 *@return none;
 *@author Frank Schummertz
 */
function editnews_init(req) 
{
    Element.hide('news_loadnews');
    if(req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    editing = true;
    // Fill the news_modify div with rendered template news_ajax_modify.htm
    Element.update('news_modify', json.result);
    Element.hide('news_savenews');
    Element.hide('news_articlecontent');
    sizecheckinit();
    news_init_check();
    
    // Manual start of the Xinha editor
    //editors = Xinha.makeEditors(xinha_editorsarray, xinha_config, xinha_plugins);
    //Xinha.startEditors(editors);
    
    return;
}

/**
 * Cancel the edit process: Remove the modify html and re-enable the original story
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function editnews_cancel()
{
    Element.update('news_modify', '&nbsp;');
    Element.show('news_articlecontent');
    editing = false;
    return;
}

/**
 * Send the story information via Ajax request to the server for storing in the database
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function editnews_save(action)
{
    if (editing == true) {
        editing = false;
        Element.show('news_savenews');

        // A manual onsubmit for xinha to update the textarea data again.
        //$('news_ajax_modifyform').onsubmit();
        
        var pars = 'module=News&func=update&action='+ action +'&' + Form.serialize('news_ajax_modifyform');
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+'ajax.php', 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: editnews_saveresponse
            });
    }
    return;
}

/**
 * This functions gets called then the Ajax request in editnews_save() returns.
 * It removes the update html and the article html as well. The new article content
 * (the pnRendered news_user_articlecontent.htm) gets returned as part of the JSON result.
 * Depending on the action performed it *might* initiate a page reload! This is necessary
 * when the story has been deleted or set to pending state which means the sid in the url
 * is no longer valid.
 *
 *@params req   response from Ajax request;
 *@return none;
 *@author Frank Schummertz
 */
function editnews_saveresponse(req)
{
    Element.hide('news_savenews');
    editing = false;

    if(req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);

    Element.update('news_modify', '&nbsp;');
    Element.update('news_articlecontent', json.result);
    if($('news_editlinks_ajax')) {
        Element.hide('news_loadnews');
        Element.remove('news_editlinks');
        Element.removeClassName($('news_editlinks_ajax'), 'hidelink'); 
    } 
    Element.show('news_articlecontent');
    switch(json.action) {
        case 'update':
            // reload if necessary (e.g. urltitle change)
            if (json.reloadurl != '') {
                location.replace(json.reloadurl);
            }
            break;
        case 'delete':
        case 'pending':
            // redirect to the news index
            location.replace(json.reloadurl);
            break;
        default:
    }

    return;
}


/**
 * Admin panel functions
 * Functions to enable watching for changes in  the optional divs and show and hide these divs 
 * with the switchdisplaystate funtion of javascript/ajax/pnajax.js. This function uses BlindDown and BlindUp
 * when scriptaculous Effects is loaded and otherwise show and hide of prototype.
 */
/*
function news_title_init()
{
//    Event.observe('news_title', 'change', savedraft);
    $('news_sample_urltitle_edit').hide();
    $('news_status_info').hide();
}
*/

function news_filter_init()
{
    Event.observe('news_property', 'change', news_property_onchange);
    news_property_onchange();
    $('news_multicategory_filter').show();
}

function news_property_onchange()
{
    $$('div#news_category_selectors select').each(function(select){
        select.hide();
    });
    var id = "news_"+$('news_property').value+"_category";
    $(id).show();
}


function news_expiration_init()
{
    if ($('news_tonolimit').checked == true) 
        switchdisplaystate('news_expiration_date');
    if ($('news_unlimited').checked == true) 
        switchdisplaystate('news_expiration_details');
    Event.observe('news_unlimited', 'change', news_unlimited_onchange);
    Event.observe('news_tonolimit', 'change', news_tonolimit_onchange);
}

function news_unlimited_onchange()
{
    switchdisplaystate('news_expiration_details');
    if ($('news_expiration_details').style.display != "none") {
        $('news_button_text_publish').update(string_publish);
    } else {
        $('news_button_text_publish').update(string_schedule);
    }
}

function news_tonolimit_onchange()
{
    switchdisplaystate('news_expiration_date');
}


function news_publication_init()
{
    Event.observe('news_publication_collapse', 'click', news_publication_click);
    $('news_publication_collapse').addClassName('pn-toggle-link');
    // show the publication details when unlimited is not set
    if ($('news_unlimited').checked == true) {
        $('news_publication_details').parentNode.addClassName('pn-collapsed');
        $('news_publication_details').hide();
    } else {
        $('news_publication_collapse').addClassName('pn-toggle-link-open');
        $('news_publication_details').parentNode.removeClassName('pn-collapsed');
        $('news_publication_details').show();
        $('news_button_text_publish').update(string_schedule);
    }
}

function news_publication_click()
{
    if ($('news_publication_details').style.display != "none") {
        Element.addClassName.delay(0.9, $('news_publication_details').parentNode, 'pn-collapsed');
        Element.removeClassName.delay(0.9, $('news_publication_collapse'), 'pn-toggle-link-open');
        $('news_publication_showhide').update(string_show);
    } else {
        Element.removeClassName($('news_publication_details').parentNode, 'pn-collapsed');
        $('news_publication_collapse').addClassName('pn-toggle-link-open');
        $('news_publication_showhide').update(string_hide);
    }
    switchdisplaystate('news_publication_details');
}


function news_attributes_init()
{
    Event.observe('news_attributes_collapse', 'click', news_attributes_click);
    $('news_attributes_collapse').addClassName('pn-toggle-link');
    $('news_attributes_collapse').addClassName('pn-toggle-link-open');
}

function news_attributes_click()
{
    if ($('news_attributes_details').style.display != "none") {
        Element.addClassName.delay(0.9, $('news_attributes_details').parentNode, 'pn-collapsed');
        Element.removeClassName.delay(0.9, $('news_attributes_collapse'), 'pn-toggle-link-open');
    } else {
        Element.removeClassName($('news_attributes_details').parentNode, 'pn-collapsed');
        $('news_attributes_collapse').addClassName('pn-toggle-link-open');
    }
    switchdisplaystate('news_attributes_details');
}


function news_notes_init()
{
    Event.observe('news_notes_collapse', 'click', news_notes_click);
    $('news_notes_collapse').addClassName('pn-toggle-link');
    news_notes_click();
}

function news_notes_click()
{
    if ($('news_notes_details').style.display != "none") {
        Element.removeClassName.delay(0.9, $('news_notes_collapse'), 'pn-toggle-link-open');
    } else {
        $('news_notes_collapse').addClassName('pn-toggle-link-open');
    }
    switchdisplaystate('news_notes_details');
}


function news_meta_init()
{
    Event.observe('news_meta_collapse', 'click', news_meta_click);
    $('news_meta_collapse').addClassName('pn-toggle-link');
    news_meta_click();
}

function news_meta_click()
{
    if ($('news_meta_details').style.display != "none") {
        Element.addClassName.delay(0.9, $('news_meta_details').parentNode, 'pn-collapsed');
        Element.removeClassName.delay(0.9, $('news_meta_collapse'), 'pn-toggle-link-open');
    } else {
        Element.removeClassName($('news_meta_details').parentNode, 'pn-collapsed');
        $('news_meta_collapse').addClassName('pn-toggle-link-open');
    }
    switchdisplaystate('news_meta_details');
}