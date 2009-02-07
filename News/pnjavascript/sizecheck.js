/*
 *  $Id: sizecheck.js 22690 2007-09-17 16:23:09Z landseer $ 
 */

/**
 * function that starts monitoring the textareas for updating the information span 
 * must be declared this way, we need to call the function after the ajax
 * request for inline editing has returned
 */
 
var template;
 
sizecheckinit =  function() 
                 {
                     template = new Template(bytesused);
                     
                     if($('news_hometext_remaining') && $('news_hometext')) {
                         Event.observe($('news_hometext'), 'keyup', updatehometext, false);
                         updatehometext();
                     } 
                     if($('news_bodytext_remaining') && $('news_bodytext')) {
                         Event.observe($('news_bodytext'), 'keyup', updatebodytext, false);
                         updatebodytext();
                     } 
                     if($('news_notes_remaining') && $('news_notes')) {
                         Event.observe($('news_notes'), 'keyup', updatenotes, false);
                         updatenotes();
                     } 
                 }

/**
 * onload event handler for window
 *
 */
Event.observe(window, 
              'load', 
              sizecheckinit,
              false);

/**
 * the update functions
 * when changing the templates make sure that the id's don't change!!
 *
 */
function updatehometext()
{
    Element.update($('news_hometext_remaining'), template.evaluate({chars: $('news_hometext').value.length}));
}

function updatebodytext()
{
    Element.update($('news_bodytext_remaining'), template.evaluate({chars: $('news_bodytext').value.length}));
}

function updatenotes()
{
    Element.update($('news_notes_remaining'), template.evaluate({chars: $('news_notes').value.length}));
}
