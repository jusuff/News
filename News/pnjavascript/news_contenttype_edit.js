/**
 * create the onload function to enable the respective functions
 *
 */
Event.observe(window,
              'load',
              news_contenttype_edit_init,
              false);

function news_contenttype_edit_init()
{
    Event.observe('news_contenttype_disphometext_yes', 'click', news_contenttype_disphometext_onchange);
    Event.observe('news_contenttype_disphometext_no', 'click', news_contenttype_disphometext_onchange);
    Event.observe('dispnewimage', 'click', news_contenttype_dispnewimage_onchange);
    news_contenttype_disphometext_onchange();
    news_contenttype_dispnewimage_onchange();
}

function news_contenttype_disphometext_onchange()
{
    if ( $('news_contenttype_disphometext_yes').checked == true) {
      $('news_contenttype_disphometext_container').show();
    } else {
      $('news_contenttype_disphometext_container').hide();
    }
}

function news_contenttype_dispnewimage_onchange()
{
    if ( $('dispnewimage').checked == 'checked') {
      $('news_contenttype_dispnewimage_container').show();
    } else {
      $('news_contenttype_dispnewimage_container').hide();
    }
}
