<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: pnadminapi.php 75 2009-02-24 04:51:52Z mateo $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * delete a News item
 * @author Mark West
 * @param $args['sid'] ID of the item
 * @return bool true on success, false on failure
 */
function News_adminapi_delete($args)
{
    // Argument check
    if (!isset($args['sid']) || !is_numeric($args['sid'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // Get the news story
    $item = pnModAPIFunc('News', 'user', 'get', array('sid' => $args['sid']));

    if ($item == false) {
        return LogUtil::registerError (_NOSUCHITEM);
    }

    // Security check
    if (!SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$item[sid]", ACCESS_DELETE)) {
        return LogUtil::registerError (_MODULENOAUTH);
    }

    if (!DBUtil::deleteObjectByID('stories', $args['sid'], 'sid')) {
        return LogUtil::registerError (_DELETEFAILED);
    }

    // Let any hooks know that we have deleted an item
    pnModCallHooks('item', 'delete', $args['sid'], array('module' => 'News'));

    // Let the calling process know that we have finished successfully
    return true;
}

/**
 * update a News item
 * @author Mark West
 * @param int $args['sid'] the id of the item to be updated
 * @param int $args['objectid'] generic object id maps to sid if present
 * @param string $args['title'] the title of the news item
 * @param string $args['urltitle'] the title of the news item formatted for the url
 * @param string $args['language'] the language of the news item
 * @param string $args['hometext'] the summary text of the news item
 * @param int $args['hometextcontenttype'] the content type of the summary text
 * @param string $args['bodytext'] the body text of the news item
 * @param int $args['bodytextcontenttype'] the content type of the body text
 * @param string $args['notes'] any administrator notes
 * @param int $args['published_status'] the published status of the item
 * @param int $args['ihome'] publish the article in the homepage
 * @return bool true on update success, false on failiure
 */
function News_adminapi_update($args)
{
    // Argument check
    if (!isset($args['sid']) ||
        !isset($args['title']) ||
        !isset($args['hometext']) ||
        !isset($args['hometextcontenttype']) ||
        !isset($args['bodytext']) ||
        !isset($args['bodytextcontenttype']) ||
        !isset($args['notes']) ||
        !isset($args['published_status']) ||
        !isset($args['from']) ||
        !isset($args['to'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // Get the news item
    $item = pnModAPIFunc('News', 'user', 'get', array('sid' => $args['sid']));

    if ($item == false) {
        return LogUtil::registerError (_NOSUCHITEM);
    }

    // Security check
    if (!SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$args[sid]", ACCESS_EDIT)) {
        return LogUtil::registerError (_MODULENOAUTH);
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
    if (!empty($args['unlimited'])) {
        $args['from'] = null;
        $args['to'] = null;
    } elseif (!empty($args['tonolimit'])) {
        $args['from'] = adodb_strftime('%Y-%m-%d %H:%M:%S', $args['from']);
        $args['to'] = null;
    } else {
    	$args['from'] = adodb_strftime('%Y-%m-%d %H:%M:%S', $args['from']);
        $args['to'] = adodb_strftime('%Y-%m-%d %H:%M:%S', $args['to']);
    }

    if (!DBUtil::updateObject($args, 'stories', '', 'sid')) {
        return LogUtil::registerError (_UPDATEFAILED);
    }

    // Let any hooks know that we have updated an item.
    pnModCallHooks('item', 'update', $args['sid'], array('module' => 'News'));

    // The item has been modified, so we clear all cached pages of this item.
    $pnRender = pnRender::getInstance('News');
    $pnRender->clear_cache(null, $args['sid']);
    $pnRender->clear_cache('news_user_view.htm');

    // Let the calling process know that we have finished successfully
    return true;
}

/**
 * Purge the permalink fields in the News table
 * @author Mateo Tibaquira
 * @return bool true on success, false on failure
 */
function News_adminapi_purgepermalinks($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_MODULENOAUTH);
    }

    // disable categorization to do this (if enabled)
    $catenabled = pnModGetVar('News', 'enablecategorization');
    if ($catenabled) {
        pnModSetVar('News', 'enablecategorization', false);
        pnModDBInfoLoad('News', 'News', true);
    }

    // get all the ID and permalink of the table
    $data = DBUtil::selectObjectArray('stories', '', '', -1, -1, 'sid', null, null, array('sid', 'urltitle'));

    // loop the data searching for non equal permalinks
    $perma = '';
    foreach (array_keys($data) as $sid) {
        $perma = strtolower(DataUtil::formatPermalink($data[$sid]['urltitle']));
        if ($data[$sid]['urltitle'] != $perma) {
            $data[$sid]['urltitle'] = $perma;
        } else {
            unset($data[$sid]);
        }
    }

    // restore the categorization if was enabled
    if ($catenabled) {
        pnModSetVar('News', 'enablecategorization', true);
    }

    if (empty($data)) {
        return true;
    // store the modified permalinks
    } elseif (DBUtil::updateObjectArray($data, 'stories', 'sid')) {
        // Let the calling process know that we have finished successfully
        return true;
    } else {
        return false;
    }
}

/**
 * get available admin panel links
 *
 * @author Mark West
 * @return array array of admin links
 */
function news_adminapi_getlinks()
{
    $links = array();

    pnModLangLoad('News', 'admin');

    if (SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_READ)) {
        $links[] = array('url' => pnModURL('News', 'admin', 'view'), 'text' => _NEWS_VIEW);
    }
    if (SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADD)) {
        $links[] = array('url' => pnModURL('News', 'admin', 'new'), 'text' =>  _NEWS_CREATE);
    }
    if (SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('News', 'admin', 'view', array('purge' => 1)), 'text' => _PURGEPERMALINKS);
        $links[] = array('url' => pnModURL('News', 'admin', 'modifyconfig'), 'text' => _MODIFYNEWSCONFIG);
    }

    return $links;
}
