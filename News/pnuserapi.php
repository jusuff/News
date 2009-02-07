<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnuserapi.php 24425 2008-07-02 12:13:57Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

/**
 * Internal callback class used to check permissions to each News item
 * @package Zikula_Value_Addons
 * @subpackage News
*/
class news_result_checker
{
    var $enablecategorization;

    function news_result_checker()
    {
        $this->enablecategorization = pnModGetVar('News', 'enablecategorization');
    }

    // This method is called by DBUtil::selectObjectArrayFilter() for each and every search result.
    // A return value of true means "keep result" - false means "discard".
    function checkResult(&$item)
    {
        $ok = SecurityUtil::checkPermission( 'Stories::Story', "$item[aid]::$item[sid]", ACCESS_OVERVIEW);
        if ($this->enablecategorization)
        {
            ObjectUtil::expandObjectWithCategories($item, 'stories', 'sid');
            $ok = $ok && CategoryUtil::hasCategoryAccess($item['__CATEGORIES__'],'News');
        }
        return $ok;
    }
}


/**
 * get all news items
 * @author Mark West
 * @return mixed array of items, or false on failure
 */
function News_userapi_getall($args)
{
    // Optional arguments.
    if (!isset($args['startnum']) || empty($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['numitems']) || empty($args['numitems'])) {
        $args['numitems'] = -1;
    }
    if (!isset($args['ignoreml']) || !is_bool($args['ignoreml'])) {
        $args['ignoreml'] = false;
    }

    if (!is_numeric($args['startnum']) ||
        !is_numeric($args['numitems'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // create a empty result set
    $items = array();

    // Security check
    if (!SecurityUtil::checkPermission( 'Stories::Story', '::', ACCESS_READ)) {
        return $items;
    }

    $args['catFilter'] = array();
    if (isset($args['category']) && !empty($args['category'])){
        if (is_array($args['category'])) { 
            $args['catFilter'] = $args['category'];
        } elseif (isset($args['property'])) {
            $property = $args['property'];
            $args['catFilter'][$property] = $args['category'];
        }
    }

    // populate an array with each part of the where clause and then implode the array if there is a need.
    // credit to Jorg Napp for this technique - markwest
    $pntable = pnDBGetTables();
    $storiescolumn = $pntable['stories_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml']) {
        $queryargs[] = "($storiescolumn[alanguage]='" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $storiescolumn[alanguage]='')";
    }

    if (isset($args['status'])) {
        $queryargs[] = "$storiescolumn[published_status] = '" . DataUtil::formatForStore($args['status']) . "'";
    }

    if (isset($args['ihome'])) {
        $queryargs[] = "$storiescolumn[ihome] = '" . DataUtil::formatForStore($args['ihome']) . "'";
    }

    // Check for specific date interval
    // Note: If 'from' is null, to also is. 
    if (isset($args['from']) || isset($args['to'])) {
        // Both defined
        if (isset($args['from']) && isset($args['to'])) {
            $from = DataUtil::formatForStore($args['from']);
            $to   = DataUtil::formatForStore($args['to']);
            $queryargs[] = "(($storiescolumn[cr_date] >= '$from' AND $storiescolumn[cr_date] < '$to' AND $storiescolumn[from] IS NULL) OR ($storiescolumn[from] IS NOT NULL AND $storiescolumn[from] >= '$from' AND $storiescolumn[from] < '$to'))";
        // Only 'from' is defined
        } elseif (isset($args['from'])) {
            $date = DataUtil::formatForStore($args['from']);
            $queryargs[] = "(($storiescolumn[cr_date] >= '$date' AND $storiescolumn[from] IS NULL) OR ($storiescolumn[from] IS NOT NULL AND $storiescolumn[from] >= '$date' AND ($storiescolumn[to] IS NULL OR $storiescolumn[to] >= '$date')))";
        // Only 'to' is defined
        } elseif (isset($args['to'])) {
            $date = DataUtil::formatForStore($args['to']);
            $queryargs[] = "(($storiescolumn[cr_date] < '$date' AND $storiescolumn[from] IS NULL) OR ($storiescolumn[from] IS NOT NULL AND $storiescolumn[from] < '$date'))";
        }
    // or can filter with the current date
    } elseif (isset($args['filterbydate'])) {
        $date = adodb_strftime('%Y-%m-%d %H:%M:%S', time());
        $queryargs[] = "(($storiescolumn[from] IS NULL AND $storiescolumn[to] IS NULL) OR ('$date' >= $storiescolumn[from] AND ($storiescolumn[to] IS NULL OR '$date' <= $storiescolumn[to])))";
    }

    if (isset($args['tdate'])) {
        $queryargs[] = "$storiescolumn[time] LIKE '%{$args['tdate']}%'";
    }

    $where = '';
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }
	
    $orderby = '';
    // Handle the sort order
    if (!isset($args['order'])) {
        $args['order'] = pnModGetVar('News', 'storyorder');

        switch ($args['order']) {
            case 0:
                $order = 'sid';
                break;
            case 1:
            default:
                $order = 'time';
        }
    } else {
        $order = $args['order'];
    }
    if (!empty($order)) {
        $orderby = $storiescolumn[$order].' DESC';
    }

    $permChecker = new news_result_checker();
    $objArray = DBUtil::selectObjectArrayFilter('stories', $where, $orderby, $args['startnum']-1, $args['numitems'], null, $permChecker, $args['catFilter']);

    // Check for an error with the database code, and if so set an appropriate
    // error message and return
    if ($objArray === false) {
        return LogUtil::registerError (_GETFAILED);
    }

    // If 'from' (date) is set, change the publication time
    $ak = array_keys($objArray);
    foreach ($ak as $key) {
        if (isset($objArray[$key]['from'])) {
            $objArray[$key]['time'] = $objArray[$key]['from'];
        }
    }

    // need to do this here as the category expansion code can't know the
    // root category which we need to build the relative path component
    if (pnModGetVar('News', 'enablecategorization') && $objArray && isset($args['catregistry']) && $args['catregistry']) {
        ObjectUtil::postProcessExpandedObjectArrayCategories ($objArray, $args['catregistry']);
    }
    
    // Return the items
    return $objArray;
}

/**
 * get a specific item
 * @author Mark West
 * @param $args['sid'] id of news item to get
 * @return mixed item array, or false on failure
 */
function News_userapi_get($args)
{
    // optional arguments
    if (isset($args['objectid'])) {
       $args['sid'] = $args['objectid'];
    }

    // Argument check
    if ((!isset($args['sid']) || !is_numeric($args['sid'])) &&
         !isset($args['title'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // form a date using some ofif present...
    // step 1 - convert month name into 
    if (isset($args['monthname']) && !empty($args['monthname'])) {
         $months = explode(' ', _MONTH_SHORT);
         $keys = array_flip($months);
         $args['monthnum'] = $keys[ucfirst($args['monthname'])] + 1;
    }
    // step 2 - convert to a timestamp and back to a db format
    if (isset($args['year']) && !empty($args['year']) && isset($args['monthnum']) &&
        !empty($args['monthnum']) && isset($args['day']) && !empty($args['day'])) {
         $timestamp = mktime(0, 0, 0, $args['monthnum'], $args['day'], $args['year']);
         $timestring = adodb_strftime('%Y-%m-%d', $timestamp);
    }

    // define the permissions filter to apply
    $permFilter = array();
    $permFilter[] = array('realm' => 0,
                          'component_left'   => 'Stories',
                          'component_middle' => '',
                          'component_right'  => 'Story',
                          'instance_left'    => 'aid',
                          'instance_middle'  => '',
                          'instance_right'   => 'sid',
                          'level'            => ACCESS_READ);

    if (isset($args['sid']) && is_numeric($args['sid'])) {
        $item = DBUtil::selectObjectByID('stories', $args['sid'], 'sid', null, $permFilter);
    } elseif (isset($timestring)) {
        $where = "pn_urltitle = '".DataUtil::formatForStore($args['title'])."' AND pn_cr_date LIKE '{$timestring}%'";
        $item = DBUtil::selectObject('stories', $where, null, $permFilter);
    } else {
        $item = DBUtil::selectObjectByID('stories', $args['title'], 'urltitle', null, $permFilter);
    }

    if (empty($item))
        return false;

    // If 'from' (date) is set, change the publication time
    if (isset($item['from'])) {
        $item['time'] = $item['from'];
    }

    // process the relative paths of the categories
    if (pnModGetVar('News', 'enablecategorization') && !empty($item['__CATEGORIES__'])) {
        static $registeredCats;
        if (!isset($registeredCats)) {
            if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
                pn_exit (pnML('_UNABLETOLOADCLASS', array('s' => 'CategoryRegistryUtil')));
            }
            $registeredCats  = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'stories');
        }
    	ObjectUtil::postProcessExpandedObjectCategories($item['__CATEGORIES__'], $registeredCats);

        if (!CategoryUtil::hasCategoryAccess($item['__CATEGORIES__'],'News'))
            return false;
    }

    return $item;
}

/**
 * utility function to count the number of items held by this module
 * @author Mark West
 * @return int number of items held by this module
 */
function News_userapi_countitems($args)
{
    $args['catFilter'] = array();
    if (isset($args['category']) && !empty($args['category'])){
        if (is_array($args['category'])) { 
            $args['catFilter'] = $args['category'];
	    } elseif (isset($args['property'])) {
            $property = $args['property'];
            $args['catFilter'][$property] = $args['category'];
        }
    }

    // Get optional arguments a build the where conditional
    // Credit to Jorg Napp for this superb technique.
    $pntable = pnDBGetTables();
    $storiescolumn = $pntable['stories_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && isset($args['ignoreml']) && !$args['ignoreml']) {
        $queryargs[] = "($storiescolumn[alanguage]='" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $storiescolumn[alanguage]='')";
    }

    if (isset($args['status'])) {
        $queryargs[] = "$storiescolumn[published_status] = '" . DataUtil::formatForStore($args['status']) . "'";
    }

    if (isset($args['ihome'])) {
        $queryargs[] = "$storiescolumn[ihome] = '" . DataUtil::formatForStore($args['ihome']) . "'";
    }

    if (isset($args['from']) && isset($args['to'])) {
        $queryargs[] = "$storiescolumn[cr_date] >= '" . DataUtil::formatForStore($args['from']) . "'";
        $queryargs[] = "$storiescolumn[cr_date] < '" . DataUtil::formatForStore($args['to']) . "'";
    } elseif (isset($args['filterbydate'])) {
        $date = adodb_strftime('%Y-%m-%d %H:%M:%S', time());
        $queryargs[] = "(($storiescolumn[from] IS NULL AND $storiescolumn[to] IS NULL) OR ('$date' >= $storiescolumn[from] AND ($storiescolumn[to] IS NULL OR '$date' <= $storiescolumn[to])))";
    }

    $where = '';
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }

    return DBUtil::selectObjectCount ('stories', $where, 'sid', false, $args['catFilter']);
}

/**
 * increment the item read count
 * @author Mark West
 * @return bool true on success, false on failiure
 */
function News_userapi_incrementreadcount($args)
{
    if ((!isset($args['sid']) || !is_numeric($args['sid'])) &&
         !isset($args['title'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    if (isset($args['sid'])) {
        return DBUtil::incrementObjectFieldByID('stories', 'counter', $args['sid'], 'sid');
    } else {
        return DBUtil::incrementObjectFieldByID('stories', 'counter', $args['title'], 'urltitle');
    }
}

/**
 * Generate an array of links for a given article
 * Requires info to have previously gone through
 * genArticleInfo() and meet the prerequisites
 * for it
 * @author unknown
 */
function News_userapi_getArticleLinks($info) 
{
    // Component and instance
    $component = 'Stories::Story';
    $instance = "$info[aid]::$info[sid]";

    $commentextra = pnUserGetCommentOptions();

    // Allowed to comment?
    if (SecurityUtil::checkPermission( $component, $instance, ACCESS_COMMENT) && pnModAvailable('EZComments') &&  pnModIsHooked('EZComments', 'News')) {
        $postcomment = DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $info['sid']), null, 'commentform'));
        $comment     = DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $info['sid']), null, 'comments'));
    } else {
        $postcomment = '';
        $comment     = '';
    }

    // Allowed to read full article?
    if (SecurityUtil::checkPermission( $component, $instance, ACCESS_READ)) {
        $fullarticle = DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $info['sid'])));
    } else {
        $fullarticle = '';
    }

    // Link to topic if there is a topic
    if (!empty($info['topicpath'])) {
        $topicField = _News_getTopicField();
        // check which variable to use for the topic
        if (pnConfigGetVar('shorturls') && pnConfigGetVar('shorturlstype') == 0) {
            $searchtopic = DataUtil::formatForDisplay(pnModURL('News', 'user', 'view', array('prop' => $topicField, 'cat' => $info['topicpath'])));
        } else {
            $searchtopic = DataUtil::formatForDisplay(pnModURL('News', 'user', 'view', array('prop' => $topicField, 'cat' => $info['tid'])));
        }
    } else {
        $searchtopic = '';
    }

    // Link to all the categories
    $categories = array();
    if (!empty($info['categories']) && is_array($info['categories']) && pnModGetVar('News', 'enablecategorization')) {
        // check which variable to use for the category
        if (pnConfigGetVar('shorturls') && pnConfigGetVar('shorturlstype') == 0) {
            $field = 'path_relative';
        } else {
            $field = 'id';
        }
        $properties = array_keys($info['categories']);
        foreach ($properties as $prop) {
            $categories[$prop] = DataUtil::formatForDisplay(pnModURL('News', 'user', 'view', array('prop' => $prop, 'cat' => $info['categories'][$prop][$field])));
        }
    }

    // Set up the array itself
    $links = array ('category'        => DataUtil::formatForDisplay(pnModURL('News', 'user', 'view', array('prop' => 'Main', 'cat' => $info['catvar']))),
                    'categories'      => $categories,
                    'permalink'       => DataUtil::formatForDisplayHTML(pnModURL('News', 'user', 'display', array('sid' => $info['sid']), null, null, true)),
                    'postcomment'     => $postcomment,
                    'comment'         => $comment,
                    'fullarticle'     => $fullarticle,
                    'searchtopic'     => $searchtopic,
                    'print'           => DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $info['sid'], 'theme' => 'Printer'))),
                    'commentrssfeed'  => DataUtil::formatForDisplay(pnModURL('EZComments', 'user', 'feed', array('mod' => 'News', 'objectid' => $info['sid']))),
                    'commentatomfeed' => DataUtil::formatForDisplay(pnModURL('EZComments', 'user', 'feed', array('mod' => 'News', 'objectid' => $info['sid']))),
                    'author'          => DataUtil::formatForDisplay(pnModURL('Profile', 'user', 'view', array('uname' => $info['informant']))),
                    'version'         => 1);

    return $links;
}

/**
 * Generate raw information for a given article
 * Requires row to have previously gone through
 * getArticles() and meet the prerequisites
 * for it
 * @author unknown
 */
function News_userapi_getArticleInfo($info)
{
    // Dates
    $info['unixtime']      = strtotime($info['time']);
    $info['longdatetime']  = DateUtil::getDatetime($info['unixtime'], _DATETIMELONG);
    $info['briefdatetime'] = DateUtil::getDatetime($info['unixtime'], _DATETIMEBRIEF);
    $info['longdate']      = DateUtil::getDatetime($info['unixtime'], _DATELONG);
    $info['briefdate']     = DateUtil::getDatetime($info['unixtime'], _DATEBRIEF);

    // Work out name of story submitter
    if ($info['aid'] == 0) {
        $anonymous = pnConfigGetVar('anonymous');
        if (empty($info['informant'])) {
            $info['informant'] = $anonymous;
        }
    } else {
        $info['informant'] = pnUserGetVar('uname', $info['aid']);
    }

    // Change the __CATEGORIES__ field to a more usable name
    if (isset($info['__CATEGORIES__'])) {
        $info['categories'] = $info['__CATEGORIES__'];
        unset($info['__CATEGORIES__']);
    }

    // For legacy reasons we add some hardwired category and topic variables
    if (!empty($info['categories']) && pnModGetVar('News', 'enablecategorization')) {
        $lang = pnUserGetLang();
        $categoryField = _News_getCategoryField();
        $topicField = _News_getTopicField();

        if (isset($info['categories'][$categoryField])) {
            $info['catid']      = $info['categories'][$categoryField]['id'];
            $info['cat']        = $info['categories'][$categoryField]['id'];
            $info['cattitle']   = isset($info['categories'][$categoryField]['display_name'][$lang]) ? $info['categories'][$categoryField]['display_name'][$lang] : $info['categories'][$categoryField]['name'];
            $info['catpath']    = $info['categories'][$categoryField]['path_relative'];
        } else {
            $info['catid']      = null;
            $info['cat']        = null;
            $info['cattitle']   = '';
            $info['catpath']    = '';
        }

        if (isset($info['categories'][$topicField])) {
            $info['topic'] = $info['categories'][$topicField]['id'];
            $info['tid']   = $info['categories'][$topicField]['id'];
            $info['topicname'] = isset($info['categories'][$topicField]['display_name'][$lang]) ? $info['categories'][$topicField]['display_name'][$lang] : $info['categories'][$topicField]['name'];
            // set the topic image if exists
            if (isset($info['categories'][$topicField]['__ATTRIBUTES__']) && isset($info['categories'][$topicField]['__ATTRIBUTES__']['topic_image'])) {
                $info['topicimage'] = $info['categories'][$topicField]['__ATTRIBUTES__']['topic_image'];
            } else {
                $info['topicimage'] = '';
            }
            // set the topic description if exists
            if (isset($info['categories'][$topicField]['display_desc'][$lang])) {
                $info['topictext'] = $info['categories'][$topicField]['display_desc'][$lang];
            } else {
                $info['topictext'] = '';
            }
            // set the path of the Topic
            $info['topicpath']  = $info['categories'][$topicField]['path_relative'];
        } else {
            $info['topic']      = null;
            $info['tid']        = null;
            $info['topicname']  = '';
            $info['topicimage'] = '';
            $info['topictext']  = '';
            $info['topicpath']  = '';
        }
    } else {
        $info['catid']      = null;
        $info['cat']        = null;
        $info['cattitle']   = '';
        $info['catpath']    = '';
        $info['topic']      = null;
        $info['tid']        = null;
        $info['topicname']  = '';
        $info['topicimage'] = '';
        $info['topictext']  = '';
        $info['topicpath']  = '';
    }

    // check which variable to use for the category
    if (pnConfigGetVar('shorturls') && pnConfigGetVar('shorturlstype') == 0) {
        $info['catvar'] = $info['catpath'];
    } else {
        $info['catvar'] = $info['catid'];
    }

    // Title should not have any URLs in it
    $info['title']    = strip_tags($info['title']);
    $info['title']    = DataUtil::formatForDisplay($info['title']);
    $info['hometext'] = DataUtil::formatForDisplayHTML($info['hometext']);
    $info['bodytext'] = DataUtil::formatForDisplayHTML($info['bodytext']);
    $info['notes']    = DataUtil::formatForDisplayHTML($info['notes']);
    $info['cattitle'] = DataUtil::formatForDisplayHTML($info['cattitle']);

    // Hooks filtering should be after formatForDisplay to allow Hook transforms
    list($info['title'],
         $info['hometext'],
         $info['bodytext'],
         $info['notes']) = pnModCallHooks('item', 'transform', '',
                                          array($info['title'],
                                                $info['hometext'],
                                                $info['bodytext'],
                                                $info['notes']));

    // Create 'Category: title'-style header -- Credit to Rabbit for the older theme compatibility.
    if ($info['catid']) {
        $info['catandtitle'] = $info['cattitle'].': '.$info['title'];
    } else {
        $info['catandtitle'] = $info['title'];
    }

    $info['maintext'] = $info['hometext']."\n".$info['bodytext'];
    if (!empty($info['notes'])) {
        $info['fulltext'] = $info['maintext']."\n".$info['notes'];
    } else {
        $info['fulltext'] = $info['maintext'];
    }

    if (pnModAvailable('EZComments') && pnModIsHooked('EZComments', 'News')) {
        $items  = pnModAPIFunc('EZComments', 'user', 'getall',
                               array('mod' => 'News',
                                     'status' => 0,
                                     'objectid' => $info['sid']));

        $info['commentcount'] = count($items);
    }

    return($info);
}

/**
 * Generate an array of preformatted HTML bites for a given article
 * Requires info to have previously gone through
 * genArticleInfo() and meet the prerequisites for it
 * Requires links to have been generated from
 * genArticleLinks()
 * @author unknown
 */
function News_userapi_getArticlePreformat($args)
{
    $info = $args['info'];
    $links = $args['links'];

    // Component and instance
    $component = 'Stories::Story';
    $instance = "$info[aid]::$info[sid]";

    $hometext = $info['hometext'];
    $bodytext = $info['bodytext'];

    // Only bother with readmore if there is more to read
    $bytesmore = strlen($info['bodytext']);
    $readmore = '';
    $bytesmorelink = '';
    if ($bytesmore > 0) {
        if (SecurityUtil::checkPermission( $component, $instance, ACCESS_READ)) {
            $title =  pnML('_NEWS_FULLTEXTOFARTICLE', array('title' => $info['title']));
            $readmore = '<a title="' . $title . "\" href=\"$links[fullarticle]\">".$title.'</a>';
        }
        $bytesmorelink = pnML('_NEWS_BYTESMORE', array('bytes' => $bytesmore));
    }

    // Allowed to read full article?
    if (SecurityUtil::checkPermission( $component, $instance, ACCESS_READ)) {
        $title = "<a href=\"$links[fullarticle]\">$info[title]</a>";
        $print = "[<a href=\"$links[print]\"><img src=\"images/global/print.gif\" alt=\""._NEWS_PRINTER.'" /></a>]';
    } else {
        $title = $info['title'];
        $print = '';
    }

    $postcomment = '';
    $comment = '';
    $commentlink = '';
    if (pnModAvailable('EZComments') && pnModIsHooked('EZComments', 'News')) {
        // Work out how to say 'comment(s)(?)' correctly
        if ($info['commentcount'] == 0) {
            $comment = _NEWS_COMMENTSQ;
        } else if ($info['commentcount'] == 1) {
            $comment = _NEWS_COMMENT;
        } else {
            $comment = pnML('_NEWS_COMMENTS', array('count' => $info['commentcount']));
        }

        // Allowed to comment?
        if (SecurityUtil::checkPermission( $component, $instance, ACCESS_COMMENT)) {
			$postcomment = "<a href=\"$links[postcomment]\">"._NEWS_COMMENTSQ.'</a>';
            $commentlink = '<a title="' . pnML('_NEWS_COMMENTSFORARTICLE', array('comments' => $info['commentcount'], 'title' => $info['title']))."\" href=\"$links[comment]\">$comment</a>";
        } else if (SecurityUtil::checkPermission( $component, $instance, ACCESS_READ)) {
            $commentlink = "$comment";
        }
    }

    // Notes, if there are any
    if (isset($info['notes']) && !empty($info['notes'])) {
        $notes = pnML('_NEWS_FOOTNOTES', array('notes' => $info['notes']), true);
    } else {
        $notes = '';
    }

    // Build the categories preformated content
    $categories = array();
    if (!empty($links['categories']) && is_array($links['categories']) && pnModGetVar('News', 'enablecategorization')) {
        $lang = pnUserGetLang();
        $properties = array_keys($links['categories']);
        foreach ($properties as $prop) {
            $catname = isset($info['categories'][$prop]['display_name'][$lang]) ? $info['categories'][$prop]['display_name'][$lang] : $info['categories'][$prop]['name'];
            $categories[$prop] = '<a href="'.$links['categories'][$prop].'">'.$catname.'</a>';
        }
    }

    // Set up the array itself
    $preformat = array('bodytext'    => $bodytext,
                       'bytesmore'   => $bytesmorelink,
                       'category'    => "<a href=\"$links[category]\">$info[cattitle]</a>",
                       'categories'  => $categories,
                       'postcomment' => $postcomment,
                       'comment'     => $comment,
                       'commentlink' => $commentlink,
                       'hometext'    => $hometext,
                       'notes'       => $notes,
                       'print'       => $print,
                       'readmore'    => $readmore,
                       'title'       => $title,
                       'version'     => 1);

    if (!empty($info['topicimage'])) {
        $preformat['searchtopic'] = '<a href="'.DataUtil::formatForDisplay($links['searchtopic']).'"><img src="images/topics/'.$info['topicimage'] .'" title="'.$info['topictext'].'" alt="'.$info['topictext'].'" /></a>';
    } else {
        $preformat['searchtopic'] = '';
    }

    // More complex extras - use values in the array
    $preformat['more'] = '';
    if ($bytesmore > 0) {
        $preformat['more'] .= "$preformat[readmore] ($preformat[bytesmore]) ";
    }
    $preformat['more'] .= "$preformat[comment] $preformat[print]";

    if ($info['cat']) {
        $preformat['catandtitle'] = "$preformat[category]: $preformat[title]";
    } else {
        $preformat['catandtitle'] = $preformat['title'];
    }

    if (!empty($preformat['bodytext'])) {
        $preformat['maintext'] = "<div>$preformat[hometext]</div><div>$preformat[bodytext]</div>";
    } else {
        $preformat['maintext'] = "<div>$preformat[hometext]</div>";
    }
    if (!empty($preformat['notes'])) {
        $preformat['fulltext'] = "<div>$preformat[maintext]</div><div>$preformat[notes]</div>";
    } else {
        $preformat['fulltext'] = "$preformat[maintext]";
    }

    return $preformat;
}

/**
 * create a new News item
 * @param $args['name'] name of the item
 * @param $args['number'] number of the item
 * @return mixed News item ID on success, false on failure
 */
function News_userapi_create($args)
{
    // Argument check
    if (!isset($args['title']) ||
        !isset($args['hometext']) ||
        !isset($args['hometextcontenttype']) ||
        !isset($args['bodytext']) ||
        !isset($args['bodytextcontenttype']) ||
        !isset($args['notes'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Stories::Story', '::', ACCESS_COMMENT)) {
        return LogUtil::registerError (_MODULENOAUTH);
    } else if ( SecurityUtil::checkPermission( 'Stories::Story', '::', ACCESS_ADD)) {
        $args['published_status'] = 0;
    } else {
        $args['published_status'] = 1;
    }

    // calculate the format type
    $args['format_type'] = ($args['bodytextcontenttype']%4)*4 + $args['hometextcontenttype']%4;

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
    }

    // The ihome table is inverted from what would seem logical
    if (!isset($args['ihome']) || $args['ihome'] == 1) {
        $args['ihome'] = 0;
    } else {
        $args['ihome'] = 1;
    }

    // check the publishing date options
    if ((!isset($args['from']) && !isset($args['to'])) || !empty($args['unlimited'])) {
        $args['from'] = null;
        $args['to'] = null;
    } elseif (isset($args['from']) && !empty($args['tonolimit'])) {
        $args['from'] = adodb_strftime('%Y-%m-%d %H:%M:%S', $args['from']);
        $args['to'] = null;
    } else {
    	$args['from'] = adodb_strftime('%Y-%m-%d %H:%M:%S', $args['from']);
        $args['to'] = adodb_strftime('%Y-%m-%d %H:%M:%S', $args['to']);
    }

    // Work out name of story submitter
    if (!pnUserLoggedIn()) {
        $anonymous = pnConfigGetVar('anonymous');
        if (empty($args['informant'])) {
            $args['informant'] = $anonymous;
        }
    } else {
        $args['informant'] = pnUserGetVar('uname');
    }

    $args['counter'] = 0;
    $args['comments'] = 0;

    if (!DBUtil::insertObject($args, 'stories', 'sid')) {
        return LogUtil::registerError (_CREATEFAILED);
    }

    // Let any hooks know that we have created a new item
    pnModCallHooks('item', 'create', $args['sid'], array('module' => 'News'));

    // An item was created, so we clear all cached pages of the items list.
    $pnRender = pnRender::getInstance('News');
    $pnRender->clear_cache('news_user_view.htm');

    // Return the id of the newly created item to the calling process
    return $args['sid'];
}

/**
 * form custom url string
 *
 * @author Mark West
 * @return string custom url string
 */
function News_userapi_encodeurl($args)
{
    // check we have the required input
    if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    if (!isset($args['type'])) {
        $args['type'] = 'user';
    }

    // create an empty string ready for population
    $vars = '';

    // for the display function use the dfined permalink structure
    if ($args['func'] == 'display') {
        // check for the generic object id parameter
        if (isset($args['args']['objectid'])) {
            $args['args']['sid'] = $args['args']['objectid'];
        }
        // check the permalink structure and obtain any missing vars
        $permalinkformat = pnModGetVar('News', 'permalinkformat');
        // get the item (will be cached by DBUtil)
        $item = pnModAPIFunc('News', 'user', 'get', array('sid' => $args['args']['sid']));
        // replace the vars to form the permalink
        $date = getdate(strtotime($item['cr_date']));
        $in = array('%category%', '%storyid%', '%storytitle%', '%year%', '%monthnum%', '%monthname%', '%day%');
        $out = array(@$item['__CATEGORIES__']['Main']['path_relative'], $item['sid'], $item['urltitle'], $date['year'], $date['mon'], strtolower(substr($date['month'], 0 , 3)), $date['mday']);
        $vars = str_replace($in, $out, $permalinkformat);
        if (isset($args['args']['page']) && $args['args']['page'] != 1) {
            $vars .= '/page/'.$args['args']['page'];
        }
    }

    // for the archives use year/month
    if ($args['func'] == 'archives' && isset($args['args']['year']) && isset($args['args']['month'])) {
        $vars = "{$args['args']['year']}/{$args['args']['month']}";
    }

    // add the category name to the view link
    if ($args['func'] == 'view' && isset($args['args']['prop'])) {
        $vars = $args['args']['prop'];
        $vars .= isset($args['args']['cat']) ? '/'.$args['args']['cat'] : '';
    }

    // view, main or now function pager
    if (isset($args['args']['page']) && is_numeric($args['args']['page']) &&
	    ($args['func'] == '' || $args['func'] == 'main' || $args['func'] == 'view')) {
        if (!empty($vars)) {
            $vars .= "/page/{$args['args']['page']}";
        } else {
            $vars = "page/{$args['args']['page']}";
        }
    }

    // don't display the function name if either displaying an article or the normal overview
    if ($args['func'] == 'main' || $args['func'] == 'display') {
        $args['func'] = '';
    }

    // construct the custom url part
    if (empty($args['func']) && empty($vars)) {
        return $args['modname'] . '/';
    } elseif (empty($args['func'])) {
        return $args['modname'] . '/' . $vars . '/';
    } elseif (empty($vars)) {
        return $args['modname'] . '/' . $args['func'] . '/';
    } else {
        return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
    }
}

/**
 * decode the custom url string
 *
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function News_userapi_decodeurl($args)
{
    // check we actually have some vars to work with...
    if (!isset($args['vars'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // define the available user functions
    $funcs = array('main', 'new', 'create', 'view', 'archives', 'display');
    // set the correct function name based on our input
    if (empty($args['vars'][2])) {
        pnQueryStringSetVar('func', 'main');
        $nextvar = 3;
    } elseif ($args['vars'][2] == 'page') {
        pnQueryStringSetVar('func', 'main');
        $nextvar = 3;
    } elseif (!in_array($args['vars'][2], $funcs)) {
        pnQueryStringSetVar('func', 'display');
        $nextvar = 2;
    } else {
        pnQueryStringSetVar('func', $args['vars'][2]);
        $nextvar = 3;
    }
    
    $func = FormUtil::getPassedValue('func', 'main', 'GET');

    // for now let the core handle the view function
    if (($func == 'view' || $func == 'main') && isset($args['vars'][$nextvar])) {
        pnQueryStringSetVar('page', (int)$args['vars'][$nextvar]);
    }

    // add the category info
    if ($func == 'view' && isset($args['vars'][$nextvar])) {
        if ($args['vars'][$nextvar] == 'page') {
            pnQueryStringSetVar('page', (int)$args['vars'][$nextvar+1]);
        } else {
        	pnQueryStringSetVar('prop', $args['vars'][$nextvar]);
        	if (isset($args['vars'][$nextvar+1])) {
        		$numargs = count($args['vars']);
        		if ($args['vars'][$numargs-2] == 'page' && is_numeric($args['vars'][$numargs-1])) {
        		    pnQueryStringSetVar('cat', (string)implode('/', array_slice($args['vars'], $nextvar+1, -2)));
        		    pnQueryStringSetVar('page', (int)$args['vars'][$numargs-1]);
        		} else {
        	        pnQueryStringSetVar('cat', (string)implode('/', array_slice($args['vars'], $nextvar+1)));
        	        pnQueryStringSetVar('page', 1);
        		}
        	}
        }
    }

    // identify the correct parameter to identify the news article
    if ($func == 'display') {
        // check the permalink structure and obtain any missing vars
        $permalinkkeys = array_flip(explode('/', pnModGetVar('News', 'permalinkformat')));
        // get rid of unused vars
        $args['vars'] = array_slice($args['vars'], $nextvar);

        // remove any category path down to the leaf category
        $permalinkkeycount = count($permalinkkeys);
        $varscount = count($args['vars']);
      	($args['vars'][$varscount-2] == 'page') ? $pagersize = 2 : $pagersize = 0 ;
      	if (($permalinkkeycount + $pagersize) != $varscount) {
            array_splice($args['vars'], $permalinkkeys['%category%'],  $varscount - $permalinkkeycount);
        }
        
        // get the story id or title
        foreach ($permalinkkeys as $permalinkvar => $permalinkkey) {
             pnQueryStringSetVar(str_replace('%', '', $permalinkvar), $args['vars'][$permalinkkey]);
        }

        if (isset($permalinkkeys['%storyid%']) && isset($args['vars'][$permalinkkeys['%storyid%']]) && is_numeric($args['vars'][$permalinkkeys['%storyid%']])) {
            pnQueryStringSetVar('sid', $args['vars'][$permalinkkeys['%storyid%']]);
            $nextvar = $permalinkkeys['%storyid%']+1;
        } else {
            pnQueryStringSetVar('title', $args['vars'][$permalinkkeys['%storytitle%']]);
            $nextvar = $permalinkkeys['%storytitle%']+1;
        }
        if (isset($args['vars'][$nextvar]) && $args['vars'][$nextvar] == 'page') {
            pnQueryStringSetVar('page', (int)$args['vars'][$nextvar+1]);
        }
    }

    // handle news archives
    if ($func == 'archives') {
        if (isset($args['vars'][$nextvar])) {
            pnQueryStringSetVar('year', $args['vars'][$nextvar]);
            if (isset($args['vars'][$nextvar+1])) {
                pnQueryStringSetVar('month', $args['vars'][$nextvar+1]);
            }
        }
    }
    return true;
}

/**
 * analize if the News module has an Scribite! editor assigned
 *
 */
function News_userapi_isformatted($args)
{
    if (!isset($args['func'])) {
        $args['func'] = 'all';
    }

    if (pnModAvailable('scribite')) {
        $modconfig = pnModAPIFunc('scribite', 'user', 'getModuleConfig', 'News');
        if (in_array($args['func'], $modconfig['modfuncs']) && $modconfig['modeditor']!='-') {
            return true;
        }
    }
    return false;
}

/**
 * get meta data for the module
 *
 */
function News_userapi_getmodulemeta()
{
   return array('viewfunc'    => 'view',
                'displayfunc' => 'display',
                'newfunc'     => 'new',
                'createfunc'  => 'create',
                'modifyfunc'  => 'modify',
                'updatefunc'  => 'update',
                'deletefunc'  => 'delete',
                'titlefield'  => 'title',
                'itemid'      => 'sid');
}


function _News_getCategoryField()
{
  return 'Main';
}

function _News_getTopicField()
{
  $prop = pnModGetVar('News', 'topicproperty');
  return empty($prop) ? 'Main' : $prop;
}

