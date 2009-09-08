/*
 *  $Id: news_admin_modifyconfig.js 34 2008-12-24 00:07:35Z Guite $ 
 */

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
    } else if ( news_permalink_numeric.checked == true) {
        news_permalink_format.value = news_permalink_numeric.value;
        news_permalink_format.disabled = true
    } else {
        news_permalink_format.value = news_permalink_customformat.value;
        news_permalink_format.disabled = false
    }
}

Event.observe(window, 
              'load', 
              news_permalink_onclick,
              false);
