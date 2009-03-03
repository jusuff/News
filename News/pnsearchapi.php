<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: pnsearchapi.php 75 2009-02-24 04:51:52Z mateo $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * Search plugin info
 **/
function news_searchapi_info()
{
    return array('title' => 'News', 
                 'functions' => array('News' => 'search'));
}

/**
 * Search form component
 **/
function news_searchapi_options($args)
{
    if (SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_READ)) {
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $renderer = pnRender::getInstance('News');
        $renderer->assign('active',(isset($args['active'])&&isset($args['active']['News']))||(!isset($args['active'])));
        return $renderer->fetch('news_search_options.htm');
    }

    return '';
}

/**
 * Search plugin main function
 **/
function news_searchapi_search($args)
{
    if (!SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_READ)) {
        return true;
    }

    pnModDBInfoLoad('Search');
    $pntable = pnDBGetTables();
    $storiestable  = $pntable['stories'];
    $storiescolumn = $pntable['stories_column'];
    $searchTable   = $pntable['search_result'];
    $searchColumn  = $pntable['search_result_column'];

    $where = search_construct_where($args, 
                                    array($storiescolumn['title'], 
                                          $storiescolumn['hometext'], 
                                          $storiescolumn['bodytext']), 
                                          $storiescolumn['language']);

    $sessionId = session_id();

    $insertSql = 
"INSERT INTO $searchTable
  ($searchColumn[title],
   $searchColumn[text],
   $searchColumn[extra],
   $searchColumn[module],
   $searchColumn[created],
   $searchColumn[session])
VALUES ";

    pnModAPILoad('News', 'user');

    $permChecker = new news_result_checker();
    $stories = DBUtil::selectObjectArrayFilter('stories', $where, null, null, null, '', $permChecker, null);

    foreach ($stories as $story)
    {
          $sql = $insertSql . '(' 
                 . '\'' . DataUtil::formatForStore($story['title']) . '\', '
                 . '\'' . DataUtil::formatForStore($story['hometext']) . '\', '
                 . '\'' . DataUtil::formatForStore($story['sid']) . '\', '
                 . '\'' . 'News' . '\', '
                 . '\'' . DataUtil::formatForStore($story['from']) . '\', '
                 . '\'' . DataUtil::formatForStore($sessionId) . '\')';
          $insertResult = DBUtil::executeSQL($sql);
          if (!$insertResult) {
              return LogUtil::registerError (_GETFAILED);
          }
    }

    return true;
}


/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found user
 */
function news_searchapi_search_check(&$args)
{
    $datarow = &$args['datarow'];
    $storyId = $datarow['extra'];

    $datarow['url'] = pnModUrl('News', 'user', 'display', array('sid' => $storyId));

    return true;
}

