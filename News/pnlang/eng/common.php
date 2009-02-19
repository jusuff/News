<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: userapi.php 21401 2007-02-16 11:52:12Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

// general
define('_NEWS', 'News');

// singular/plural
define('_NEWS_STORY', 'News Article');
define('_NEWS_STORY_LC', 'news article');
define('_NEWS_STORIES', 'News Articles');

// new/modify templates
// these are located in the common file to support user submission
// the following string is used in javascript, #{chars} is like %chars% in pnML
// for more information see http://prototypejs.org/api/template
// modules/News/pnjavascript/sizecheck.js
define('_NEWS_CHARSUSED', '#{chars} chars out of 65536'); 
define('_NEWS_CONTENTTYPE', 'Content format type');
define('_NEWS_EXTENDEDTEXT', 'Article body');
define('_ARTICLETITLE_FLC', 'Article Title');
define('_NEWS_NEWSARTICLEPREVIEW', 'Article Preview');

define('_NEWS_FORMATTEDTEXT', 'Formatted Text');
define('_NEWS_FROM', 'Start date');
define('_NEWS_HOMETEXT', 'Front page teaser');
define('_NEWS_INHOME', 'Publish on news lead page');
define('_NEWS_MAXCHARS', '(Limit: 65536 characters)');
define('_NEWS_NOTES', 'Foot notes');
define('_NEWS_OVERVIEW', 'Heading Info');
define('_NEWS_ARTICLECONTENT', 'Article Content');
define('_NEWS_PLAINTEXT', 'Plain Text');
define('_NEWS_POSTORPREVIEW', 'Action');
define('_NEWS_POSTSTORY', 'Publish');
define('_NEWS_SUBMIT', 'Submit Article');
define('_NEWS_PREVIEW', 'Preview');
define('_NEWS_PREVIEWSTORY', 'Preview');
define('_NEWS_PUBLICATION', 'Publishing Options');
define('_NEWS_NOLIMIT', 'No end date');
define('_NEWS_TO', 'End date');
define('_NEWS_UNLIMITED', 'No time limit');
