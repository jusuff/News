<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: admin.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

// view template
define('_NEWS_ARCHIVED', 'Archived');
define('_NEWS_INHOMEQUESTION', 'Front Page');
define('_NEWS_NEWSPUBLISHER', 'News Publisher');
define('_NEWS_PENDING', 'Pending');
define('_NEWS_PUBLISHEDSTATUS', 'Status');
define('_NEWS_PUBLISHED', 'Published');
define('_NEWS_REJECTED', 'Rejected');
define('_NEWS_UNKNOWN', 'Unknown');

// menu
define('_NEWS_CONFIRMDELETE', 'Do you really want to delete this News Article?');
define('_NEWS_CREATE', 'Create a News Article');
define('_NEWS_DELETE', 'Delete News Article');
define('_NEWS_MODIFY', 'Edit News Article');
define('_NEWS_VIEW', 'View News Articles list');

// ajax modify
define('_NEWS_MAKEPENDING', 'Set Pending Status');

// modify config
define('_MODIFYNEWSCONFIG', 'News Publisher Settings');
define('_NEWS_DISPLAY', 'Display Settings');
define('_NEWS_STORIESHOME','Number of articles on news front page');
define('_NEWS_ITEMSONINDEXPAGE', 'Number of articles in news index');
define('_NEWS_STORIESORDER','Order news articles by');
define('_NEWS_STORIESORDER0','News ID');
define('_NEWS_STORIESORDER1','News Date/Time');
define('_NEWS_TOPICPROPERTY', 'Category to use for legacy Topic template variables');
define('_NEWS_URLS', 'Permalinks');
define('_NEWS_URLSCUSTOM', 'Custom');
define('_NEWS_URLSCUSTOMREQUIRED', 'Reminder: A custom structure must contain either %storyid% or %storytitle%.');
define('_NEWS_URLSCUSTOMSTRUCTURE', 'Custom structure');
define('_NEWS_URLSDATENAME', 'Date- and name-based');
define('_NEWS_URLSHELP', 'Select a predefined PermaLink format, or choose your own');
define('_NEWS_URLSNUMERIC' , 'Numeric');
define('_NEWS_URLVARS', 'Possible values for custom structure');
define('_NEWS_URLVARSDAY', 'Day');
define('_NEWS_URLVARSMONTHNUM', 'Month number (1-12)');
define('_NEWS_URLVARSMONTHNAME', 'Month name (jan-dec)');
define('_NEWS_URLVARSSTORYID', 'News Article ID');
define('_NEWS_URLVARSSTORYTITLE', 'News Article Title');
define('_NEWS_URLVARSYEAR', 'Year (including century)');
