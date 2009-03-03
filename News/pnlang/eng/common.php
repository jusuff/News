<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: common.php 75 2009-02-24 04:51:52Z mateo $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage News
*/

// general
define('_NEWS', 'News');

// singular/plural
define('_NEWS_STORY', 'News Article');
define('_NEWS_STORY_LC', 'news article');
define('_NEWS_STORIES', 'News Articles');

// status
define('_NEWS_ARCHIVED', 'Archived');
define('_NEWS_PENDING', 'Pending');
define('_NEWS_PUBLISHED', 'Published');
define('_NEWS_REJECTED', 'Rejected');

// common defines
define('_NEWS_COMMENT', '1 comment');
define('_NEWS_COMMENTS', '%count% comments');
define('_NEWS_COMMENTSQ', 'Comments?');
define('_NEWS_POSTEDBYANDON', 'Posted by %username% on %date%');
define('_NEWS_READ', '1 read');
define('_NEWS_READS', '%count% reads');

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

define('_NEWS_ATTRIBUTES', 'Article attributes');
define('_NEWS_ATTRIBUTE_NEW', 'New article attribute');
define('_NEWS_ENABLEATTRIBUTION', 'Enable attribution');
define('_NEWS_FORMATTEDTEXT', 'Formatted Text');
define('_NEWS_FROM', 'Start date');
define('_NEWS_HOMETEXT', 'Lead page teaser');
define('_NEWS_INHOME', 'Publish on news lead page');
define('_NEWS_MAXCHARS', '(Limit: 65536 characters)');
define('_NEWS_NOTES', 'Show Footnote');
define('_NEWS_OVERVIEW', 'Heading Info');
define('_NEWS_ARTICLECONTENT', 'Article Content');
define('_NEWS_PLAINTEXT', 'Plain Text');
define('_NEWS_POSTORPREVIEW', 'Action');
define('_NEWS_POSTSTORY', 'Publish');
define('_NEWS_PUBLISHEDSTATUS', 'Status');
define('_NEWS_SUBMIT', 'Submit Article');
define('_NEWS_PREVIEW', 'Preview');
define('_NEWS_PREVIEWSTORY', 'Preview');
define('_NEWS_PUBLICATION', 'Show Publishing Options');
define('_NEWS_NOLIMIT', 'No end date');
define('_NEWS_TO', 'End date');
define('_NEWS_UNLIMITED', 'No time limit');
