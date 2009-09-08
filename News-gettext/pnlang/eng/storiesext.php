<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id:  $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage News
*/

define('_STORIES_ADDITIONALINFO', 'Additional block information');

define('_STORIES_BASICINFO', 'Basic block information');

define('_STORIES_CATEGORIES_REGISTRY', 'Category Registry');
define('_STORIES_CATEGORY', 'Select 0 or more categories');
define('_STORIES_COUNTER', 'Number of reads');

define('_STORIES_DATEFORMAT', 'Formatting of the date');
define('_STORIES_DATEFORMATLINK', 'Description strftime');
define('_STORIES_DISPCOMMENTS', 'Show the number of comments');
define('_STORIES_DISPDATE', 'Show the article date');
define('_STORIES_DISPHOMETEXT', 'Show the article hometext');
define('_STORIES_DISPLAYALL', 'Show all news articles');
define('_STORIES_DISPLAYFRONTPAGE', 'Show only lead page articles');
define('_STORIES_DISPLAYNONFRONTPAGE', 'Show only articles not published on lead page');
define('_STORIES_DISPNEWIMAGE', 'Display an image for recent story titles');
define('_STORIES_DISPREADS', 'Show the number of reads');
define('_STORIES_DISPUNAME', 'Show the author name');

define('_STORIES_EMPTYRESULT', 'No News');

define('_STORIES_FADESCROLLING', 'Fading Scroller');

define('_STORIES_HOMETEXTCLASS', 'Optional CSS class for the hometext display'); 
define('_STORIES_HOMETEXTWARNING', 'When wrapping the hometext, incomplete HTML markups elements will be made complete again by the truncatehtml plugin.');
define('_STORIES_HOMETEXTWRAPTEXT', 'Suffix text used in hometext wrapping'); 

define('_STORIES_MARQUEESCROLLING', 'Marquee Scroller');
define('_STORIES_MAXDAYS', 'Maximum age of the articles in days (0 means no limit)');
define('_STORIES_MAXHOMETEXTLENGTH', 'Maximum hometext length in characters (0 means no limit)'); 
define('_STORIES_MAXNUM', 'Maximum number of news articles to display');
define('_STORIES_MAXTITLELENGTH', 'Maximum title length in characters (0 means no limit)');

define('_STORIES_NEWIMAGEALT', 'NEW');
define('_STORIES_NEWIMAGELIMIT', 'How many days is a story recent');
define('_STORIES_NEWIMAGESET', 'New image set (pnimg plugin)');
define('_STORIES_NEWIMAGESRC', 'New image filename (pnimg plugin)');
define('_STORIES_NEWSSETTING', 'News setting');
define('_STORIES_NOSCROLLING', 'No scrolling');

define('_STORIES_ORDER', 'Articles sorted by');

define('_STORIES_PAUSESCROLLING', 'Pausing Vertical Scroller');

define('_STORIES_SCROLLDELAY', 'Delay between scrolls/ startdelay for marquee (msec)');
define('_STORIES_SCROLLING', 'Use scrolling for the newsitems');
define('_STORIES_SCROLLINGDESC', 'The stories can be put in a scrolling box.<br />Pausing Vertical Scroller is based upon <a href="http://www.dynamicdrive.com/dynamicindex2/crosstick.htm">Pausing up/down</a>, the Fading Scroller (gradient wipe effect only in IE) is based upon <a href="http://www.dynamicdrive.com/dynamicindex2/memoryticker.htm">memoryticker</a> and the Marquee scroller is based upon <a href="http://www.dynamicdrive.com/dynamicindex2/cmarquee2.htm" target="_new">Marquee II</a>.');
define('_STORIES_SCROLLMSPEED', 'Speed of the marquee scroller 1-10');
define('_STORIES_SCROLLSTYLE', 'Stylesheet definition for the scrolling');
define('_STORIES_SELECTNONE', 'None');
define('_STORIES_SHOWEMPTYRESULT', 'Show \''._STORIES_EMPTYRESULT.'\' block output for no News instead of empty block');
define('_STORIES_SPLITCHAR', 'Seperation character for additional information');

define('_STORIES_TEMPLATE_BLOCK', 'Template for display of the block (leave empty for default)');
define('_STORIES_TEMPLATE_OVERRIDE', 'Specification of your own Templates');
define('_STORIES_TEMPLATE_OVERRIDE_INFO', 'If you don\'t want to use the default templates, you have to specify them here. The default row template is news_block_storiesext_row.htm. The default block template depends on the scroll setting, default static template is news_block_storiesext.htm and for scrolling news_block_storiesext_scrollNAME.htm is used.');
define('_STORIES_TEMPLATE_ROW', 'Template for layout of every row (leave empty for default)');
define('_STORIES_TITLEWRAPTXT', 'Suffix text when titles are wrapped');

define('_STORIES_WHICHSTORIES', 'Articles to display (lead page)');
