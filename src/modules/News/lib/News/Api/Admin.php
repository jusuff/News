<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

class News_Api_Admin extends Zikula_Api
{
    /**
     * delete a News item
     *
     * @author Mark West
     * @param $args['sid'] ID of the item
     * @return bool true on success, false on failure
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['sid']) || !is_numeric($args['sid'])) {
            return LogUtil::registerArgsError();
        }

        // Get the news story
        $item = ModUtil::apiFunc('News', 'user', 'get', array('sid' => $args['sid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such article found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('News::', "$item[cr_uid]::$item[sid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        if (!DBUtil::deleteObjectByID('news', $args['sid'], 'sid')) {
            return LogUtil::registerError($this->__('Error! Could not delete article.'));
        }

        // delete News images
        $modvars = ModUtil::getVar('News');
        if ($modvars['picupload_enabled'] && $item['pictures'] > 0) {
            News_ImageUtil::deleteImagesBySID($modvars['picupload_uploaddir'], $item['sid'], $item['pictures']);
        }

        // Let any hooks know that we have deleted an item
        // TODO
        //$this->callHooks('item', 'delete', $args['sid'], array('module' => 'News'));

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update a News item
     *
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
     * @param int $args['hideonindex'] hide the article on the index page
     * @return bool true on update success, false on failiure
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['sid']) ||
                !isset($args['title']) ||
                !isset($args['hometext']) ||
                !isset($args['hometextcontenttype']) ||
                !isset($args['bodytext']) ||
                !isset($args['bodytextcontenttype']) ||
                !isset($args['notes']) ||
                !isset($args['from']) ||
                !isset($args['to'])) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['language'])) {
            $args['language'] = '';
        }

        // Get the news item
        $item = ModUtil::apiFunc('News', 'user', 'get', array('sid' => $args['sid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such article found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('News::', "$item[cr_uid]::$args[sid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // evaluates the input action
        $args['action'] = isset($args['action']) ? $args['action'] : null;
        switch ($args['action'])
        {
            case 1: // submitted => pending
                $args['published_status'] = 2;
                break;
            case 2: // published
            case 3: // rejected
            case 4: // pending
            case 5: // archived
            case 6: // draft
                $args['published_status'] = $args['action']-2;
                break;
        }

        // calculate the format type
        $args['format_type'] = ($args['bodytextcontenttype']%4)*4 + $args['hometextcontenttype']%4;

        // define the lowercase permalink, using the title as slug, if not present
        if (!isset($args['urltitle']) || empty($args['urltitle'])) {
            $args['urltitle'] = strtolower(DataUtil::formatPermalink($args['title']));
        }

        // The hideonindex table is inverted from what would seem logical
        if (!isset($args['hideonindex']) || $args['hideonindex'] == 1) {
            $args['hideonindex'] = 0;
        } else {
            $args['hideonindex'] = 1;
        }

        // Invert the value of disallowcomments, 1 in db means no comments allowed
        if (!isset($args['disallowcomments']) || $args['disallowcomments'] == 1) {
            $args['disallowcomments'] = 0;
        } else {
            $args['disallowcomments'] = 1;
        }

        // check the publishing date options
        if (!empty($args['unlimited'])) {
            $args['from'] = $item['cr_date'];
            $args['to'] = null;
        } elseif (!empty($args['tonolimit'])) {
            $args['from'] = DateUtil::formatDatetime($args['from']);
            $args['to'] = null;
        } else {
            $args['from'] = DateUtil::formatDatetime($args['from']);
            $args['to'] = DateUtil::formatDatetime($args['to']);
        }

        if (!DBUtil::updateObject($args, 'news', '', 'sid')) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        // Let any hooks know that we have updated an item.
        // TODO
        //$this->callHooks('item', 'update', $args['sid'], array('module' => 'News'));

        // The item has been modified, so we clear all cached pages of this item.
        $render = Zikula_View::getInstance('News');
        $render->clear_cache(null, $args['sid']);
        $render->clear_cache('user/view.tpl');

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Purge the permalink fields in the News table
     * @author Mateo Tibaquira
     * @return bool true on success, false on failure
     */
    public function purgepermalinks($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('News::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // disable categorization to do this (if enabled)
        $catenabled = ModUtil::getVar('News', 'enablecategorization');
        if ($catenabled) {
            ModUtil::setVar('News', 'enablecategorization', false);
            ModUtil::dbInfoLoad('News', 'News', true);
        }

        // get all the ID and permalink of the table
        $data = DBUtil::selectObjectArray('news', '', '', -1, -1, 'sid', null, null, array('sid', 'urltitle'));

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
            ModUtil::setVar('News', 'enablecategorization', true);
        }

        if (empty($data)) {
            return true;
            // store the modified permalinks
        } elseif (DBUtil::updateObjectArray($data, 'news', 'sid')) {
            // Let the calling process know that we have finished successfully
            return true;
        }

        return false;
    }

    /**
     * get available admin panel links
     *
     * @author Mark West
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('News::', '::', ACCESS_READ)) {
            $links[] = array('url'  => ModUtil::url('News', 'admin', 'view'),
                    'text' => $this->__('News articles list'),
                    'class' => 'z-icon-es-list',
                    'links' => array(
                        array('url' => ModUtil::url('News', 'admin', 'view'),
                            'text' => $this->__('All')),
                        array('url' => ModUtil::url('News', 'admin', 'view', array('news_status'=>0)),
                            'text' => $this->__('Published')),
                        array('url' => ModUtil::url('News', 'admin', 'view', array('news_status'=>1)),
                            'text' => $this->__('Rejected')),
                        array('url' => ModUtil::url('News', 'admin', 'view', array('news_status'=>2)),
                            'text' => $this->__('Pending Review')),
                        array('url' => ModUtil::url('News', 'admin', 'view', array('news_status'=>3)),
                            'text' => $this->__('Archived')),
                        array('url' => ModUtil::url('News', 'admin', 'view', array('news_status'=>4)),
                            'text' => $this->__('Draft')),
                        array('url' => ModUtil::url('News', 'admin', 'view', array('news_status'=>5)),
                            'text' => $this->__('Scheduled'))
                    ));
        }
        if (SecurityUtil::checkPermission('News::', '::', ACCESS_ADD)) {
            $links[] = array('url'  => ModUtil::url('News', 'admin', 'newitem'),
                    'text' =>  $this->__('Create new article'),
                    'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('News::', '::', ACCESS_ADMIN)) {
            $links[] = array('url'  => ModUtil::url('News', 'admin', 'view', array('purge' => 1)),
                    'text' => $this->__('Purge permalinks'),
                    'class' => 'z-icon-es-regenerate');

            $links[] = array('url'  => ModUtil::url('News', 'admin', 'modifyconfig'),
                    'text' => $this->__('Settings'),
                    'class' => 'z-icon-es-config');
        }

        return $links;
    }
}