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
define('_NEWS_AUTHOR', 'Author');

// singular/plural
define('_NEWS_STORY', 'News Article');
define('_NEWS_STORY_LC', 'news article');
define('_NEWS_STORIES', 'News Articles');

// status
define('_NEWS_ARCHIVED', 'Archived');
define('_NEWS_DRAFT', 'Draft');
define('_NEWS_PENDING', 'Pending Review');
define('_NEWS_PUBLISHED', 'Published');
define('_NEWS_REJECTED', 'Rejected');
define('_NEWS_SCHEDULED', 'Scheduled');

// common defines
define('_NEWS_ALLOWCOMMENTS', 'Allow commenting for this article');
define('_NEWS_NOCOMMENTS', 'No commenting');
define('_NEWS_COMMENT', '1 comment');
define('_NEWS_COMMENTS', '%count% comments');
define('_NEWS_COMMENTSQ', 'Comments?');
define('_NEWS_POSTEDBYANDON', 'Posted by %username% on %date%');
define('_NEWS_READ', '1 read');
define('_NEWS_READS', '%count% reads');
define('_NEWS_SELFWRITTEN', ', %count% self-written');
define('_NEWS_NOARTICLES', 'no articles');
define('_NEWS_RSSFEED', 'RSS Feed');
define('_NEWS_NOARTICLESFOUND', 'No news articles currently published');
define('_NEWS_NOARTICLESFOUNDINCAT', 'No news articles published in category %cat%');

// date format nice defines
define('_NEWS_DAYSAGO', '%days% days ago');
define('_NEWS_DAYSFROMNOW', '%days% days from now');
define('_NEWS_HOURSAGO', '%hours% hours ago');
define('_NEWS_HOURSFROMNOW', '%hours% hours from now');
define('_NEWS_MINSAGO', '%mins% mins ago');
define('_NEWS_MINSFROMNOW', '%mins% mins from now');
define('_NEWS_SECSAGO', '%secs% secs ago');
define('_NEWS_SECSFROMNOW', '%secs% secs from now');
define('_NEWS_YESTERDAY', 'Yesterday');
define('_NEWS_TOMORROW', 'Tomorrow');

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
define('_NEWS_PUBLICATIONSHOW', 'Show Publishing Options');
define('_NEWS_PUBLICATIONHIDE', 'Hide Publishing Options');
define('_NEWS_NOLIMIT', 'No end date');
define('_NEWS_TO', 'End date');
define('_NEWS_UNLIMITED', 'No time limit');
