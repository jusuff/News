/*
 *  $Id: news_admin_modifyconfig.js 34 2008-12-24 00:07:35Z Guite $ 
 */

/**
 * create the onload function to enable the respective functions
 *
 */
Event.observe(window, 'load', news_modifyconfig_init_check);

function news_modifyconfig_init_check() 
{
    if ($('news_permalink_datename')) {
        news_permalink_onclick();
    }
    if ($('news_category_details')) {
        news_category_init();
    }
    if ($('news_morearticles_details')) {
        news_morearticles_init();
    }
    if ($('news_ajaxedit_details')) {
        news_ajaxedit_init();
    }
    if ($('news_notifyonpending_details')) {
        news_notifyonpending_init();
    }
    if ($('news_pdflink_details')) {
        news_pdflink_init();
    }
    if ($('news_picupload_details')) {
        news_picupload_init();
    }
    if ($('news_permalink_custom_details')) {
        news_permalink_custom_init();
    }
}

function news_ajaxedit_init()
{
    if ($('news_enableajaxedit').checked == false) {
        $('news_ajaxedit_details').hide();
    }
    $('news_enableajaxedit').observe('change', news_ajaxedit_onchange);
}
function news_ajaxedit_onchange()
{
    switchdisplaystate('news_ajaxedit_details');
}

function news_category_init()
{
    if ($('news_enablecategorization').checked == false) {
        $('news_category_details').hide();
    }
    $('news_enablecategorization').observe('click', news_category_onchange);
}
function news_category_onchange()
{
    switchdisplaystate('news_category_details');
}

function news_morearticles_init()
{
    if ($('news_enablemorearticlesincat').checked == false) {
        $('news_morearticles_details').hide();
    }
    $('news_enablemorearticlesincat').observe('click', news_morearticles_onchange);
}
function news_morearticles_onchange()
{
    switchdisplaystate('news_morearticles_details');
}

function news_notifyonpending_init()
{
    if ($('news_notifyonpending').checked == false) {
        $('news_notifyonpending_details').hide();
    }
    $('news_notifyonpending').observe('click', news_notifyonpending_onchange);
}
function news_notifyonpending_onchange()
{
    switchdisplaystate('news_notifyonpending_details');
}

function news_pdflink_init()
{
    if ($('news_pdflink').checked == false) {
        $('news_pdflink_details').hide();
    }
    $('news_pdflink').observe('click', news_pdflink_onchange);
}
function news_pdflink_onchange()
{
    switchdisplaystate('news_pdflink_details');
}

function news_picupload_init()
{
    if ($('news_picupload_enabled').checked == false) {
        $('news_picupload_details').hide();
    }
    $('news_picupload_enabled').observe('click', news_picupload_onchange);
}
function news_picupload_onchange()
{
    switchdisplaystate('news_picupload_details');
}

function news_permalink_custom_init()
{
    if ($('news_permalink_custom').checked == false) {
        $('news_permalink_custom_details').hide();
    }
}

function news_permalink_onclick()
{
    var news_permalink_datename = $('news_permalink_datename')
    var news_permalink_numeric = $('news_permalink_numeric')
    var news_permalink_custom = $('news_permalink_custom')
    var news_permalink_format = $('news_permalink_format')
    var news_permalink_customformat = $('news_permalink_customformat')

    if ( news_permalink_datename.checked == true) {
        news_permalink_format.value = news_permalink_datename.value;
        news_permalink_format.disabled = true
        $('news_permalink_custom_details').hide();
    } else if ( news_permalink_numeric.checked == true) {
        news_permalink_format.value = news_permalink_numeric.value;
        news_permalink_format.disabled = true
        $('news_permalink_custom_details').hide();
    } else {
        news_permalink_format.value = news_permalink_customformat.value;
        news_permalink_format.disabled = false
        $('news_permalink_custom_details').show();
    }
}
