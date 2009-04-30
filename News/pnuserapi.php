<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: pnuserapi.php 75 2009-02-24 04:51:52Z mateo $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * Internal callback class used to check permissions to each News item
 * @author Jorn Wildt
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
        $ok = SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$item[sid]", ACCESS_OVERVIEW);
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
    if (!isset($args['status']) || (empty($args['status']) && $args['status'] !== 0)) {
        $args['status'] = null;
    }
    if (!isset($args['startnum']) || empty($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['numitems']) || empty($args['numitems'])) {
        $args['numitems'] = -1;
    }
    if (!isset($args['ignoreml']) || !is_bool($args['ignoreml'])) {
        $args['ignoreml'] = false;
    }
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    if ((!empty($args['status']) && !is_numeric($args['status'])) ||
        !is_numeric($args['startnum']) ||
        !is_numeric($args['numitems'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // create a empty result set
    $items = array();

    // Security check
    if (!SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_OVERVIEW)) {
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
        $args['catFilter']['__META__'] = array('module' => 'News');
    }

    // populate an array with each part of the where clause and then implode the array if there is a need.
    // credit to Jorg Napp for this technique - markwest
    $pntable = pnDBGetTables();
    $storiescolumn = $pntable['stories_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml'] && empty($args['language'])) {
        $queryargs[] = "($storiescolumn[language] = '" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $storiescolumn[language] = '')";
    } elseif (!empty($args['language'])) {
        $queryargs[] = "$storiescolumn[language] = '" . DataUtil::formatForStore($args['language']) . "'";
    }

    if (isset($args['status'])) {
        $queryargs[] = "$storiescolumn[published_status] = '" . DataUtil::formatForStore($args['status']) . "'";
    }

    if (isset($args['ihome'])) {
        $queryargs[] = "$storiescolumn[ihome] = '" . DataUtil::formatForStore($args['ihome']) . "'";
    }

    // Check for specific date interval
    if (isset($args['from']) || isset($args['to'])) {
        // Both defined
        if (isset($args['from']) && isset($args['to'])) {
            $from = DataUtil::formatForStore($args['from']);
            $to   = DataUtil::formatForStore($args['to']);
            $queryargs[] = "($storiescolumn[from] >= '$from' AND $storiescolumn[from] < '$to')";
        // Only 'from' is defined
        } elseif (isset($args['from'])) {
            $date = DataUtil::formatForStore($args['from']);
            $queryargs[] = "($storiescolumn[from] >= '$date' AND ($storiescolumn[to] IS NULL OR $storiescolumn[to] >= '$date'))";
        // Only 'to' is defined
        } elseif (isset($args['to'])) {
            $date = DataUtil::formatForStore($args['to']);
            $queryargs[] = "($storiescolumn[from] < '$date')";
        }
    // or can filter with the current date
    } elseif (isset($args['filterbydate'])) {
        $date = DateUtil::getDatetime();
        $queryargs[] = "('$date' >= $storiescolumn[from] AND ($storiescolumn[to] IS NULL OR '$date' <= $storiescolumn[to]))";
    }

    if (isset($args['tdate'])) {
        $queryargs[] = "$storiescolumn[from] LIKE '%{$args['tdate']}%'";
    }

    if (isset($args['query']) && is_array($args['query'])) {
        // query array with rows like {'field', 'op', 'value'}
        $allowedoperators = array('>', '>=', '=', '<', '<=', 'LIKE', '!=', '<>');
        foreach ($args['query'] as $row) {
            if (is_array($row) && count($row) == 3) {
                // validate fields and operators
                list($field, $op, $value) = $row;
                if (isset($storiescolumn[$field]) && in_array($op, $allowedoperators)) {
                    $queryargs[] = "$storiescolumn[$field] $op '".DataUtil::formatForStore($value)."'";
                }
            }
        }
    }

    // check for a specific author
    if (isset($args['uid']) && is_int($args['uid'])) {
        $queryargs[] = "$storiescolumn[aid] = '" . DataUtil::formatForStore($args['uid']) . "'";
    }

    $where = '';
    if (count($queryargs) > 0) {
        $where = implode(' AND ', $queryargs);
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
                $order = 'from';
        }
    } elseif (isset($storiescolumn[$args['order']])) {
        $order = $args['order'];
    }

    if (!empty($order)) {
        if (isset($args['orderdir']) && in_array(strtoupper($args['orderdir'], array('ASC', 'DESC')))) {
            $orderby = $storiescolumn[$order].' '.strtoupper($args['orderdir']);
        } else {
            $orderby = $storiescolumn[$order].' DESC';
        }
    }

    $permChecker = new news_result_checker();
    $objArray = DBUtil::selectObjectArrayFilter('stories', $where, $orderby, $args['startnum'] - 1, $args['numitems'], '', $permChecker, $args['catFilter']);

    // Check for an error with the database code, and if so set an appropriate
    // error message and return
    if ($objArray === false) {
        return LogUtil::registerError(_GETFAILED);
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

    // Check for caching of the DBUtil calls (needed for AJAX editing)
    if (!isset($args['SQLcache'])) {
        $args['SQLcache'] = true;
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
         $timestring = DateUtil::getDatetime(mktime(0, 0, 0, $args['monthnum'], $args['day'], $args['year']), '%Y-%m-%d');
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
        $item = DBUtil::selectObjectByID('stories', $args['sid'], 'sid', null, $permFilter, null, $args['SQLcache']);
    } elseif (isset($timestring)) {
        $where = "pn_urltitle = '".DataUtil::formatForStore($args['title'])."' AND pn_from LIKE '{$timestring}%'";
        $item = DBUtil::selectObject('stories', $where, null, $permFilter, null, $args['SQLcache']);
    } else {
        $item = DBUtil::selectObjectByID('stories', $args['title'], 'urltitle', null, $permFilter, null, $args['SQLcache']);
    }

    if (empty($item))
        return false;

    // Sanity check for the published status if required
    if (isset($args['status'])) {
        if ($item['published_status'] != $args['status']) {
            return false;
        }
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
 * Get all months and years with news. Used by archive overview
 * @author Philipp Niethammer
 * @return array Array of dates (one per month)
 */
function News_userapi_getMonthsWithNews($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_OVERVIEW)) {
        return false;
    }

    $pntable =& pnDBGetTables();
    $storiescolumn = $pntable['stories_column'];

    // TODO: Check syntax for other Databases (i.e. Postgres doesn't know YEAR_MONTH)
    $order = "GROUP BY EXTRACT(YEAR_MONTH FROM $storiescolumn[from]) ORDER BY $storiescolumn[from] DESC";

    $date = DateUtil::getDatetime();
    $where = "($storiescolumn[from] < '$date' AND $storiescolumn[published_status] = '0')";

    $dates = DBUtil::selectFieldArray('stories', 'from', $where, $order);

    return $dates;
}

/**
 * utility function to count the number of items held by this module
 * @author Mark West
 * @return int number of items held by this module
 */
function News_userapi_countitems($args)
{
    // Optional arguments.
    if (!isset($args['status']) || (empty($args['status']) && $args['status'] !== 0)) {
        $args['status'] = null;
    }
    if (!isset($args['ignoreml']) || !is_bool($args['ignoreml'])) {
        $args['ignoreml'] = false;
    }
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    $args['catFilter'] = array();
    if (isset($args['category']) && !empty($args['category'])){
        if (is_array($args['category'])) {
            $args['catFilter'] = $args['category'];
        } elseif (isset($args['property'])) {
            $property = $args['property'];
            $args['catFilter'][$property] = $args['category'];
        }
        $args['catFilter']['__META__'] = array('module' => 'News');
    }

    // Get optional arguments a build the where conditional
    // Credit to Jorg Napp for this superb technique.
    $pntable = pnDBGetTables();
    $storiescolumn = $pntable['stories_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml'] && empty($args['language'])) {
        $queryargs[] = "($storiescolumn[language] = '" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $storiescolumn[language] = '')";
    } elseif (!empty($args['language'])) {
        $queryargs[] = "$storiescolumn[language] = '" . DataUtil::formatForStore($args['language']) . "'";
    }

    if (isset($args['status'])) {
        $queryargs[] = "$storiescolumn[published_status] = '" . DataUtil::formatForStore($args['status']) . "'";
    }

    if (isset($args['ihome'])) {
        $queryargs[] = "$storiescolumn[ihome] = '" . DataUtil::formatForStore($args['ihome']) . "'";
    }

    // Check for specific date interval
    if (isset($args['from']) || isset($args['to'])) {
        // Both defined
        if (isset($args['from']) && isset($args['to'])) {
            $from = DataUtil::formatForStore($args['from']);
            $to   = DataUtil::formatForStore($args['to']);
            $queryargs[] = "($storiescolumn[from] >= '$from' AND $storiescolumn[from] < '$to')";
        // Only 'from' is defined
        } elseif (isset($args['from'])) {
            $date = DataUtil::formatForStore($args['from']);
            $queryargs[] = "($storiescolumn[from] >= '$date' AND ($storiescolumn[to] IS NULL OR $storiescolumn[to] >= '$date'))";
        // Only 'to' is defined
        } elseif (isset($args['to'])) {
            $date = DataUtil::formatForStore($args['to']);
            $queryargs[] = "($storiescolumn[from] < '$date')";
        }
    // or can filter with the current date
    } elseif (isset($args['filterbydate'])) {
        $date = DateUtil::getDatetime();
        $queryargs[] = "('$date' >= $storiescolumn[from] AND ($storiescolumn[to] IS NULL OR '$date' <= $storiescolumn[to]))";
    }

    if (isset($args['tdate'])) {
        $queryargs[] = "$storiescolumn[from] LIKE '%{$args['tdate']}%'";
    }

    if (isset($args['query']) && is_array($args['query'])) {
        // query array with rows like {'field', 'op', 'value'}
        $allowedoperators = array('>', '>=', '=', '<', '<=', 'LIKE', '!=', '<>');
        foreach ($args['query'] as $row) {
            if (is_array($row) && count($row) == 3) {
                // validate fields and operators
                extract($row);
                if (isset($storiescolumn[$field]) && in_array($op, $allowedoperators)) {
                    $queryargs[] = "$storiescolumn[$field] $op '".DataUtil::formatForStore($value)."'";
                }
            }
        }
    }

    // check for a specific author
    if (isset($args['uid']) && is_int($args['uid'])) {
        $queryargs[] = "$storiescolumn[cr_uid] = '" . DataUtil::formatForStore($args['uid']) . "'";
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
    if (pnModAvailable('EZComments') &&  pnModIsHooked('EZComments', 'News') && $info['withcomm'] == 0) {
        $comment = DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $info['sid']), null, 'comments'));
        if (SecurityUtil::checkPermission($component, $instance, ACCESS_COMMENT)) {
            $postcomment = DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $info['sid']), null, 'commentform'));
        }
    } else {
        $comment     = '';
        $postcomment = '';
    }

    // Allowed to read full article?
    if (SecurityUtil::checkPermission($component, $instance, ACCESS_READ)) {
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

    $author = $info['informant'];
    $profileModule = pnConfigGetVar('profilemodule', '');
    if (!empty($profileModule) && pnModAvailable($profileModule)) {
        $author = pnModURL($profileModule, 'user', 'view', array('uname' => $author));
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
                    'author'          => DataUtil::formatForDisplay($author),
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
    $info['unixtime']      = strtotime($info['from']);
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

    // also the __ATTRIBUTES__ field
    if (isset($info['__ATTRIBUTES__'])) {
        $info['attributes'] = $info['__ATTRIBUTES__'];
        unset($info['__ATTRIBUTES__']);
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
    list($info['hometext'],
         $info['bodytext'],
         $info['notes']) = pnModCallHooks('item', 'transform', '',
                                          array($info['hometext'],
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

    // Get the format types. 'home' string is bits 0-1, 'body' is bits 2-3.
    $info['hometype'] = ($info['format_type']%4);
    $info['bodytype'] = (($info['format_type']/4)%4);
    unset($info['format_type']);

    // Check for comments
    if (pnModAvailable('EZComments') && pnModIsHooked('EZComments', 'News') && $info['withcomm'] == 0) {
        $info['commentcount'] = pnModAPIFunc('EZComments', 'user', 'countitems',
                                             array('mod' => 'News',
                                                   'objectid' => $info['sid'],
                                                   'status' => 0));
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

    // Preformat the text according the article format type
    $hometext = $info['hometype'] ? $info['hometext'] : nl2br($info['hometext']);
    $bodytext = $info['bodytype'] ? $info['bodytext'] : nl2br($info['bodytext']);

    // Only bother with readmore if there is more to read
    $bytesmore = strlen($info['bodytext']);
    $readmore = '';
    $bytesmorelink = '';
    if ($bytesmore > 0) {
        if (SecurityUtil::checkPermission($component, $instance, ACCESS_READ)) {
            $title =  pnML('_NEWS_FULLTEXTOFARTICLE', array('title' => $info['title']));
            $readmore = '<a title="'.$title.'" href="'.$links['fullarticle'].'">'.$title.'</a>';
        }
        $bytesmorelink = pnML('_NEWS_BYTESMORE', array('bytes' => $bytesmore));
    }

    // Allowed to read full article?
    if (SecurityUtil::checkPermission($component, $instance, ACCESS_READ)) {
        $title = '<a href="'.$links['fullarticle'].'">'.$info['title'].'</a>';
        $print = '<a class="news_printlink" href="'.$links['print'].'"><img src="images/global/print.gif" alt="'._NEWS_PRINTER.'" /></a>';
    } else {
        $title = $info['title'];
        $print = '';
    }

    $postcomment = '';
    $comment = '';
    $commentlink = '';
    if (pnModAvailable('EZComments') && pnModIsHooked('EZComments', 'News') && $info['withcomm'] == 0) {
        // Work out how to say 'comment(s)(?)' correctly
        if ($info['commentcount'] == 0) {
            $comment = _NEWS_COMMENTSQ;
        } else if ($info['commentcount'] == 1) {
            $comment = _NEWS_COMMENT;
        } else {
            $comment = pnML('_NEWS_COMMENTS', array('count' => $info['commentcount']));
        }

        // Allowed to comment?
        if (SecurityUtil::checkPermission($component, $instance, ACCESS_COMMENT)) {
            $postcomment = '<a href="'.$links['postcomment'].'">'._NEWS_COMMENTSQ.'</a>';
            $commentlink = '<a title="'.pnML('_NEWS_COMMENTSFORARTICLE', array('comments' => $info['commentcount'], 'title' => $info['title'])).'" href="'.$links['comment'].'">'.$comment.'</a>';
        } else if (SecurityUtil::checkPermission($component, $instance, ACCESS_READ)) {
            $commentlink = $comment;
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
                       'category'    => '<a href="'.$links['category'].'">'.$info['cattitle'].'</a>',
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
        $catimagepath = pnModGetVar('News', 'catimagepath');
        $preformat['searchtopic'] = '<a href="'.DataUtil::formatForDisplay($links['searchtopic']).'"><img src="'.$catimagepath.$info['topicimage'] .'" title="'.$info['topictext'].'" alt="'.$info['topictext'].'" /></a>';
    } else {
        $preformat['searchtopic'] = '';
    }

    // More complex extras - use values in the array
    $preformat['more'] = '';
    if ($bytesmore > 0) {
        $preformat['more'] .= $preformat['readmore'].' ('.$preformat['bytesmore'].') ';
    }
    $preformat['more'] .= $preformat['comment'].' '.$preformat['print'];

    if ($info['cat']) {
        $preformat['catandtitle'] = $preformat['category'].': '.$preformat['title'];
    } else {
        $preformat['catandtitle'] = $preformat['title'];
    }

    if (!empty($preformat['bodytext'])) {
        $preformat['maintext'] = '<div>'.$preformat['hometext'].'</div><div>'.$preformat['bodytext'].'</div>';
    } else {
        $preformat['maintext'] = '<div>'.$preformat['hometext'].'</div>';
    }
    if (!empty($preformat['notes'])) {
        $preformat['fulltext'] = '<div>'.$preformat['maintext'].'</div><div>'.$preformat['notes'].'</div>';
    } else {
        $preformat['fulltext'] = $preformat['maintext'];
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
    if (!isset($args['title']) || empty($args['title']) ||
        !isset($args['hometext']) ||
        !isset($args['hometextcontenttype']) ||
        !isset($args['bodytext']) ||
        !isset($args['bodytextcontenttype']) ||
        !isset($args['notes'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // Security check
    if (!SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_COMMENT)) {
        return LogUtil::registerError (_MODULENOAUTH);
    } elseif (SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADD)) {
        if (!isset($args['published_status'])) {
            $args['published_status'] = 0;
        }
    } else {
        $args['published_status'] = 2;
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

    // Invert the value of withcomm, 1 in db means no comments allowed
    if (!isset($args['withcomm']) || $args['withcomm'] == 1) {
        $args['withcomm'] = 0;
    } else {
        $args['withcomm'] = 1;
    }

    // check the publishing date options
    if ((!isset($args['from']) && !isset($args['to'])) || (isset($args['unlimited']) && !empty($args['unlimited']))) {
        $args['from'] = null;
        $args['to'] = null;
    } elseif (isset($args['from']) && (isset($args['tonolimit']) && !empty($args['tonolimit']))) {
        $args['from'] = DateUtil::getDatetime($args['from']);
        $args['to'] = null;
    } else {
        $args['from'] = DateUtil::getDatetime($args['from']);
        $args['to'] = DateUtil::getDatetime($args['to']);
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

    if (!($obj = DBUtil::insertObject($args, 'stories', 'sid'))) {
        return LogUtil::registerError(_CREATEFAILED);
    }

    // update the from field to the same cr_date if it's null
    if (is_null($args['from'])) {
        $obj = array(
            'sid'  => $obj['sid'],
            'from' => $obj['cr_date']
        );

        if (!DBUtil::updateObject($obj, 'stories', '', 'sid')) {
            LogUtil::registerError(_UPDATEFAILED);
        }
    }

    // Let any hooks know that we have created a new item
    pnModCallHooks('item', 'create', $args['sid'], array('module' => 'News'));

    // An item was created, so we clear all cached pages of the items list.
    $renderer = pnRender::getInstance('News');
    $renderer->clear_cache('news_user_view.htm');

    // Return the id of the newly created item to the calling process
    return $args['sid'];
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
        $modinfo = pnModGetInfo(pnModGetIDFromName('scribite'));
        if (version_compare($modinfo['version'], '2.2', '>=')) {
            $apiargs = array('modulename' => 'News'); // parameter handling corrected in 2.2
        } else {
            $apiargs = 'News'; // old direct parameter
        }

        $modconfig = pnModAPIFunc('scribite', 'user', 'getModuleConfig', $apiargs);
        if (in_array($args['func'], (array)$modconfig['modfuncs']) && $modconfig['modeditor'] != '-') {
            return true;
        }
    }
    return false;
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
