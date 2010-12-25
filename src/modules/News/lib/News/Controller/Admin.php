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
class News_Controller_Admin extends Zikula_Controller
{

    public function postInitialize()
    {
        $this->view->setCaching(false);
    }

    /**
     * the main administration function
     *
     * @author Mark West
     * @return string HTML output
     */
    public function main()
    {
        // Security check
        if (!SecurityUtil::checkPermission('News::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        // Return the output that has been generated by this function
        return $this->view->fetch('admin/main.tpl');
    }

    /**
     * create a new news article
     * this function is purely a wrapper for the output from news_user_new
     *
     * @author Mark West
     * @return string HTML string
     */
    public function newitem()
    {
        // Return the output that has been generated by this function
        return ModUtil::func('News', 'user', 'newitem');
    }

    /**
     * modify a news article
     *
     * @param int 'sid' the id of the item to be modified
     * @param int 'objectid' generic object id maps to sid if present
     * @author Mark West
     * @return string HTML string
     */
    public function modify($args)
    {
        $sid = FormUtil::getPassedValue('sid', isset($args['sid']) ? $args['sid'] : null, 'GETPOST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
        // At this stage we check to see if we have been passed $objectid
        if (!empty($objectid)) {
            $sid = $objectid;
        }

        // Check if we're redirected to preview
        $inpreview = false;
        $item = SessionUtil::getVar('newsitem');
        if (!empty($item) && isset($item['sid'])) {
            $inpreview = true;
            $sid = $item['sid'];
        }

        // Validate the essential parameters
        if (empty($sid)) {
            return LogUtil::registerArgsError();
        }

        // Get the news article in the db
        $dbitem = ModUtil::apiFunc('News', 'user', 'get', array('sid' => $sid));

        if ($dbitem === false) {
            return LogUtil::registerError($this->__('Error! No such article found.'), 404);
        }

        // Security check
        if (!SecurityUtil::checkPermission('News::', "{$dbitem['cr_uid']}::$sid", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // merge the data of the db and the preview if exist
        $item = $inpreview ? array_merge($dbitem, $item) : $dbitem;
        unset($dbitem);

        // Get the format types. 'home' string is bits 0-1, 'body' is bits 2-3.
        $item['hometextcontenttype'] = isset($item['hometextcontenttype']) ? $item['hometextcontenttype'] : ($item['format_type'] % 4);
        $item['bodytextcontenttype'] = isset($item['bodytextcontenttype']) ? $item['bodytextcontenttype'] : (($item['format_type'] / 4) % 4);

        // Set the publishing date options.
        if (!$inpreview) {
            if (DateUtil::getDatetimeDiff_AsField($item['from'], $item['cr_date'], 6) == 0 && is_null($item['to'])) {
                $item['unlimited'] = 1;
                $item['tonolimit'] = 1;
            } elseif (DateUtil::getDatetimeDiff_AsField($item['from'], $item['cr_date'], 6) <> 0 && is_null($item['to'])) {
                $item['unlimited'] = 0;
                $item['tonolimit'] = 1;
            } else {
                $item['unlimited'] = 0;
                $item['tonolimit'] = 0;
            }
        } else {
            $item['unlimited'] = isset($item['unlimited']) ? 1 : 0;
            $item['tonolimit'] = isset($item['tonolimit']) ? 1 : 0;
        }

        // if article is pending than set the publishing 'from' date to now
        if ($item['published_status'] == 2) {
            $nowts = time();
            $now = DateUtil::getDatetime($nowts);
            // adjust 'to', since it is before the new 'from' set above
            if (!is_null($item['to']) && DateUtil::getDatetimeDiff_AsField($now, $item['to'], 6) < 0) {
                $item['to'] = DateUtil::getDatetime($nowts + DateUtil::getDatetimeDiff_AsField($item['from'], $item['to']));
            }
            $item['from'] = $now;
            $item['unlimited'] = 0;
        }

        // Check if we need a preview
        $preview = '';
        if (isset($item['action']) && $item['action'] == 0) {
            $preview = ModUtil::func('News', 'user', 'preview',
                            array('title' => $item['title'],
                                'hometext' => $item['hometext'],
                                'hometextcontenttype' => $item['hometextcontenttype'],
                                'bodytext' => $item['bodytext'],
                                'bodytextcontenttype' => $item['bodytextcontenttype'],
                                'notes' => $item['notes'],
                                'sid' => $item['sid']));
        }

        // Get the module configuration vars
        $modvars = ModUtil::getVar('News');

        if ($modvars['enablecategorization']) {
            $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');

            // check if the __CATEGORIES__ info needs a fix (when preview)
            if (isset($item['__CATEGORIES__'])) {
                foreach ($item['__CATEGORIES__'] as $prop => $catid) {
                    if (is_numeric($catid)) {
                        $item['__CATEGORIES__'][$prop] = array('id' => $catid);
                    }
                }
            }

            // Add article attribute morearticlesincat when not existing yet and functionality is enabled.
            if ($modvars['enablemorearticlesincat'] && $modvars['morearticlesincat'] == 0 && !array_key_exists('morearticlesincat', $info['__ATTRIBUTES__'])) {
                $item['__ATTRIBUTES__']['morearticlesincat'] = 0;
            }
        }

        if (SecurityUtil::checkPermission('News::', '::', ACCESS_ADD)) {
            $this->view->assign('accessadd', 1);
        } else {
            $this->view->assign('accessadd', 0);
        }

        if ($modvars['enablecategorization']) {
            $this->view->assign('catregistry', $catregistry);
        }

        // Assign the default languagecode
        $this->view->assign('lang', ZLanguage::getLanguageCode());

        // Pass the module configuration to the template
        $this->view->assign($modvars);

        // Assign the item to the template
        $this->view->assign('item', $item);

        // Get the preview of the item
        $this->view->assign('preview', $preview);

        // Assign the content format
        $formattedcontent = ModUtil::apiFunc('News', 'user', 'isformatted', array('func' => 'modify'));
        $this->view->assign('formattedcontent', $formattedcontent);

        // Return the output that has been generated by this function
        return $this->view->fetch('admin/modify.tpl');
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by News_admin_modify() to update a current item
     *
     * @param int 'sid' the id of the item to be updated
     * @param int 'objectid' generic object id maps to sid if present
     * @param string 'title' the title of the news item
     * @param string 'urltitle' the title of the news item formatted for the url
     * @param string 'language' the language of the news item
     * @param string 'bodytext' the summary text of the news item
     * @param int 'bodytextcontenttype' the content type of the summary text
     * @param string 'extendedtext' the body text of the news item
     * @param int 'extendedtextcontenttype' the content type of the body text
     * @param string 'notes' any administrator notes
     * @param int 'published_status' the published status of the item
     * @param int 'hideonindex' hide the article on the index page
     * @author Mark West
     * @return bool true
     */
    public function update($args)
    {
        $story = FormUtil::getPassedValue('story', isset($args['story']) ? $args['story'] : null, 'POST');
        $files = News_ImageUtil::reArrayFiles(FormUtil::getPassedValue('news_files', null, 'FILES'));

        if (!empty($story['objectid'])) {
            $story['sid'] = $story['objectid'];
        }

        // Validate the essential parameters
        if (empty($story['sid'])) {
            return LogUtil::registerArgsError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('News', 'admin', 'view'));
        }

        // Get the unedited news article for the permissions check
        $item = ModUtil::apiFunc('News', 'user', 'get', array('sid' => $story['sid']));
        if ($item === false) {
            return LogUtil::registerError($this->__('Error! No such article found.'), 404);
        }

        // Security check
        if (!SecurityUtil::checkPermission('News::', "{$item['cr_uid']}::{$item['sid']}", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Reformat the attributes array
        if (isset($story['attributes'])) {
            $story['__ATTRIBUTES__'] = News_Util::reformatAttributes($story['attributes']);
            unset($story['attributes']);
        }

        // Validate the input
        $validationerror = News_Util::validateArticle($this, $story);

        // if the user has selected to preview the article we then route them back
        // to the new function with the arguments passed here
        if ($story['action'] == 0 || $validationerror !== false) {
            // log the error found if any
            if ($validationerror !== false) {
                LogUtil::registerError($validationerror);
            }
            // back to the referer form
            SessionUtil::setVar('newsitem', $story);
            return System::redirect(ModUtil::url('News', 'admin', 'modify'));
        } else {
            // As we're not previewing the item let's remove it from the session
            SessionUtil::delVar('newsitem');
        }

        // Check if the article goes from pending to published
        if ($item['published_status'] == 2 && $story['published_status'] == 0) {
            $story['approver'] = SessionUtil::getVar('uid');
        }

        $modvars = ModUtil::getVar('News');

        // Handle Images
        if ($modvars['picupload_enabled']) {
            if (isset($story['del_pictures']) && !empty($story['del_pictures'])) {
                $deletedPics = News_ImageUtil::deleteImagesByName($modvars['picupload_uploaddir'], $story['del_pictures']);
                $story['pictures'] = $story['pictures'] - $deletedPics;
            }
            if (isset($deletedPics) && ($deletedPics > 0)) {
                $nextImageId = News_ImageUtil::renumberImages($item['pictures'], $story['sid'], $modvars);
            } else {
                $nextImageId = isset($story['pictures']) ? $story['pictures'] : 0;
            }
            if (isset($files) && !empty($files)) {
                list($files, $story) = News_ImageUtil::validateImages($files, $story, $modvars);
                $story['pictures'] = News_ImageUtil::resizeImages($story['sid'], $files, $modvars, $nextImageId); // resize and move the uploaded pics
            }
        }


        // Update the story
        if (ModUtil::apiFunc('News', 'admin', 'update', array(
                    'sid' => $story['sid'],
                    'title' => $story['title'],
                    'urltitle' => $story['urltitle'],
                    '__CATEGORIES__' => isset($story['__CATEGORIES__']) ? $story['__CATEGORIES__'] : null,
                    '__ATTRIBUTES__' => isset($story['__ATTRIBUTES__']) ? $story['__ATTRIBUTES__'] : null,
                    'language' => isset($story['language']) ? $story['language'] : '',
                    'hometext' => isset($story['hometext']) ? $story['hometext'] : '',
                    'hometextcontenttype' => $story['hometextcontenttype'],
                    'bodytext' => isset($story['bodytext']) ? $story['bodytext'] : '',
                    'bodytextcontenttype' => $story['bodytextcontenttype'],
                    'notes' => isset($story['notes']) ? $story['notes'] : '',
                    'hideonindex' => isset($story['hideonindex']) ? $story['hideonindex'] : 0,
                    'disallowcomments' => isset($story['disallowcomments']) ? $story['disallowcomments'] : 0,
                    'unlimited' => isset($story['unlimited']) ? $story['unlimited'] : null,
                    'from' => $story['from'],
                    'tonolimit' => isset($story['tonolimit']) ? $story['tonolimit'] : null,
                    'to' => $story['to'],
                    'approver' => $story['approver'],
                    'weight' => isset($story['weight']) ? $story['weight'] : 0,
                    'pictures' => $story['pictures'],
                    'action' => $story['action']))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Saved your changes.'));
        }

        // Let any hooks know that we have edited an item.
        $this->notifyHooks('news.hook.articles.process.edit', $story, $story['sid']);

        $this->view->clear_cache();
        return System::redirect(ModUtil::url('News', 'admin', 'view'));
    }

    /**
     * delete item
     *
     * @param int 'sid' the id of the news item to be deleted
     * @param int 'objectid' generic object id maps to sid if present
     * @param int 'confirmation' confirmation that this news item can be deleted
     * @author Mark West
     * @return mixed HTML string if no confirmation, true if delete successful, false otherwise
     */
    public function delete($args)
    {
        $sid = FormUtil::getPassedValue('sid', isset($args['sid']) ? $args['sid'] : null, 'REQUEST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $sid = $objectid;
        }

        // Validate the essential parameters
        if (empty($sid)) {
            return LogUtil::registerArgsError();
        }

        // Get the news story
        $item = ModUtil::apiFunc('News', 'user', 'get', array('sid' => $sid));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such article found.'), 404);
        }

        // Security check
        if (!SecurityUtil::checkPermission('News::', "{$item['cr_uid']}::{$item['sid']}", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // Add News story ID
            $this->view->assign('sid', $sid);
            $this->view->assign('item', $item);

            // Return the output that has been generated by this function
            return $this->view->fetch('admin/delete.tpl');
        }

        // If we get here it means that the user has confirmed the action
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('News', 'admin', 'view'));
        }

        // Delete
        if (ModUtil::apiFunc('News', 'admin', 'delete', array('sid' => $sid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted article.'));

            // Let any hooks know that we have deleted an item
            $this->notifyHooks('news.hook.articles.process.delete', $item, $sid);
        }

        return System::redirect(ModUtil::url('News', 'admin', 'view'));
    }

    /**
     * view items
     * @param int 'startnum' starting number for paged output
     * @author Mark West
     * @return string HTML string
     */
    public function view($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('News::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
        $news_status = FormUtil::getPassedValue('news_status', isset($args['news_status']) ? $args['news_status'] : null, 'GETPOST');
        $language = FormUtil::getPassedValue('language', isset($args['language']) ? $args['language'] : null, 'POST');
        $property = FormUtil::getPassedValue('news_property', isset($args['news_property']) ? $args['news_property'] : null, 'GETPOST');
        $category = FormUtil::getPassedValue("news_{$property}_category", isset($args["news_{$property}_category"]) ? $args["news_{$property}_category"] : null, 'GETPOST');
        $clear = FormUtil::getPassedValue('clear', false, 'POST');
        $purge = FormUtil::getPassedValue('purge', false, 'GET');
        $order = FormUtil::getPassedValue('order', isset($args['order']) ? $args['order'] : 'from', 'GETPOST');
        //$monthyear   = FormUtil::getPassedValue('monthyear', isset($args['monthyear']) ? $args['monthyear'] : null, 'POST');

        if ($purge) {
            if (ModUtil::apiFunc('News', 'admin', 'purgepermalinks')) {
                LogUtil::registerStatus($this->__('Done! Purged permalinks.'));
            } else {
                LogUtil::registerError($this->__('Error! Could not purge permalinks.'));
            }
            return System::redirect(strpos(System::serverGetVar('HTTP_REFERER'), 'purge') ? ModUtil::url('News', 'admin', 'view') : System::serverGetVar('HTTP_REFERER'));
        }

        if ($clear) {
            // reset the filter
            $property = null;
            $category = null;
            $news_status = null;
            $order = 'from';
        }

        // clean the session preview data
        SessionUtil::delVar('newsitem');

        // get module vars for later use
        $modvars = $this->getVars();

        if ($modvars['enablecategorization']) {
            $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');
            $properties = array_keys($catregistry);

            // Validate and build the category filter - mateo
            if (!empty($property) && in_array($property, $properties) && !empty($category)) {
                $catFilter = array($property => $category);
            }

            // Assign a default property - mateo
            if (empty($property) || !in_array($property, $properties)) {
                $property = $properties[0];
            }

            // plan ahead for ML features
            $propArray = array();
            foreach ($properties as $prop) {
                $propArray[$prop] = $prop;
            }
        }

        $multilingual = System::getVar('multilingual', false);

        $now = DateUtil::getDatetime();
        $status = null;
        if (isset($news_status) && $news_status != '') {
            if ($news_status == 0) {
                $status = 0;
                $to = $now;
            } elseif ($news_status == 5) {
                // scheduled is actually the published status, but in the future
                $status = 0;
                $from = $now;
            } else {
                $status = $news_status;
            }
        }

        // Get all news story
        $items = ModUtil::apiFunc('News', 'user', 'getall',
                        array('startnum' => $startnum,
                            'status' => $status,
                            'numitems' => $modvars['itemsperpage'],
                            'ignoreml' => ($multilingual ? false : true),
                            'language' => $language,
                            'order' => isset($order) ? $order : 'from',
                            'from' => isset($from) ? $from : null,
                            'to' => isset($to) ? $to : null,
                            'filterbydate' => false,
                            'category' => isset($catFilter) ? $catFilter : null,
                            'catregistry' => isset($catregistry) ? $catregistry : null));

        // Set the possible status for later use
        $itemstatus = array(
            '' => $this->__('All'),
            0 => $this->__('Published'),
            1 => $this->__('Rejected'),
            2 => $this->__('Pending Review'),
            3 => $this->__('Archived'),
            4 => $this->__('Draft'),
            5 => $this->__('Scheduled')
        );

        /*
          // Load localized month names
          $months = explode(' ', $this->__('January February March April May June July August September October November December'));
          $newsmonths = array();
          // get all matching news stories
          $monthsyears = ModUtil::apiFunc('News', 'user', 'getMonthsWithNews');
          foreach ($monthsyears as $monthyear) {
          $month = DateUtil::getDatetime_Field($monthyear, 2);
          $year  = DateUtil::getDatetime_Field($monthyear, 1);
          $linktext = $months[$month-1]." $year";
          $newsmonths[$monthyear] = $linktext;
          }
         */

        $newsitems = array();
        foreach ($items as $item)
        {
            $options = array();
            if (System::getVar('shorturls') && System::getVar('shorturlstype') == 0) {
                $options[] = array('url' => ModUtil::url('News', 'user', 'display', array('sid' => $item['sid'], 'from' => $item['from'], 'urltitle' => $item['urltitle'])),
                    'image' => '14_layer_visible.gif',
                    'title' => $this->__('View'));
            } else {
                $options[] = array('url' => ModUtil::url('News', 'user', 'display', array('sid' => $item['sid'])),
                    'image' => '14_layer_visible.gif',
                    'title' => $this->__('View'));
            }

            if (SecurityUtil::checkPermission('News::', "{$item['cr_uid']}::{$item['sid']}", ACCESS_EDIT)) {
                if ($item['published_status'] == 2) {
                    $options[] = array('url' => ModUtil::url('News', 'admin', 'modify', array('sid' => $item['sid'])),
                        'image' => 'editcut.gif',
                        'title' => $this->__('Review'));
                } else {
                    $options[] = array('url' => ModUtil::url('News', 'admin', 'modify', array('sid' => $item['sid'])),
                        'image' => 'xedit.gif',
                        'title' => $this->__('Edit'));
                }

                if (($item['published_status'] != 2 &&
                        (SecurityUtil::checkPermission('News::', "{$item['cr_uid']}::{$item['sid']}", ACCESS_DELETE))) ||
                        SecurityUtil::checkPermission('News::', "{$item['cr_uid']}::{$item['sid']}", ACCESS_ADMIN)) {
                    $options[] = array('url' => ModUtil::url('News', 'admin', 'delete', array('sid' => $item['sid'])),
                        'image' => '14_layer_deletelayer.gif',
                        'title' => $this->__('Delete'));
                }
            }
            $item['options'] = $options;

            if (in_array($item['published_status'], array_keys($itemstatus))) {
                $item['status'] = $itemstatus[$item['published_status']];
            } else {
                $item['status'] = $this->__('Unknown');
            }

            if ($item['hideonindex'] == 0) {
                $item['hideonindex'] = $this->__('Yes');
            } else {
                $item['hideonindex'] = $this->__('No');
            }

            $item['infuture'] = DateUtil::getDatetimeDiff_AsField($item['from'], DateUtil::getDatetime(), 6) < 0;
            $newsitems[] = $item;
        }

        // Assign the items and modvars to the template
        $this->view->assign('newsitems', $newsitems);
        $this->view->assign($modvars);

        // Assign the default and selected language
        $this->view->assign('lang', ZLanguage::getLanguageCode());
        $this->view->assign('language', $language);

        // Assign the current status filter and the possible ones
        $this->view->assign('news_status', $news_status);
        $this->view->assign('itemstatus', $itemstatus);
        $this->view->assign('order', $order);
        $this->view->assign('orderoptions', array('from' => $this->__('Article date/time'),
            'sid' => $this->__('Article ID'),
            'weight' => $this->__('Article weight')));

        //$this->view->assign('monthyear', $monthyear);
        //$this->view->assign('newsmonths', $newsmonths);
        // Assign the categories information if enabled
        if ($modvars['enablecategorization']) {
            $this->view->assign('catregistry', $catregistry);
            $this->view->assign('numproperties', count($propArray));
            $this->view->assign('properties', $propArray);
            $this->view->assign('property', $property);
            $this->view->assign('category', $category);
        }

        // Count the items for the selected status and category
        $statuslinks = array();
        // Counts with a tolerance of 3 seconds
        $now = DateUtil::getDatetime(time() + 3);

        $statuslinks[] = array('count' => ModUtil::apiFunc('News', 'user', 'countitems',
                    array('category' => isset($catFilter) ? $catFilter : null,
                        'status' => 0,
                        'to' => $now)),
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_status' => 0,
                        'news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('Published'));

        $statuslinks[] = array('count' => ModUtil::apiFunc('News', 'user', 'countitems',
                    array('category' => isset($catFilter) ? $catFilter : null,
                        'status' => 0,
                        'from' => $now)),
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_status' => 5,
                        'news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('Scheduled'));

        $statuslinks[] = array('count' => ModUtil::apiFunc('News', 'user', 'countitems',
                    array('category' => isset($catFilter) ? $catFilter : null,
                        'status' => 2)),
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_status' => 2,
                        'news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('Pending Review'));

        $statuslinks[] = array('count' => ModUtil::apiFunc('News', 'user', 'countitems',
                    array('category' => isset($catFilter) ? $catFilter : null,
                        'status' => 4)),
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_status' => 4,
                        'news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('Draft'));

        $statuslinks[] = array('count' => ModUtil::apiFunc('News', 'user', 'countitems',
                    array('category' => isset($catFilter) ? $catFilter : null,
                        'status' => 3)),
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_status' => 3,
                        'news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('Archived'));

        $statuslinks[] = array('count' => ModUtil::apiFunc('News', 'user', 'countitems',
                    array('category' => isset($catFilter) ? $catFilter : null,
                        'status' => 1)),
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_status' => 1,
                        'news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('Rejected'));

        $alllink = array('count' => $statuslinks[0]['count'] + $statuslinks[1]['count'] + $statuslinks[2]['count'] + $statuslinks[3]['count'] + $statuslinks[4]['count'] + $statuslinks[5]['count'],
            'url' => ModUtil::url('News', 'admin', 'view',
                    array('news_property' => $property,
                        'news_' . $property . '_category' => isset($category) ? $category : null)),
            'title' => $this->__('All'));

        $this->view->assign('statuslinks', $statuslinks);
        $this->view->assign('alllink', $alllink);

        // Assign the values for the smarty plugin to produce a pager
        $this->view->assign('pager', array('numitems' => ModUtil::apiFunc('News', 'user', 'countitems', array('category' => isset($catFilter) ? $catFilter : null)),
            'itemsperpage' => $modvars['itemsperpage']));

        // Return the output that has been generated by this function
        return $this->view->fetch('admin/view.tpl');
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     * @author Mark West
     * @return string HTML string
     */
    public function modifyconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('News::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');
        $properties = array_keys($catregistry);
        $propertyName = ModUtil::getVar('News', 'topicproperty');
        $propertyIndex = empty($propertyName) ? 0 : array_search($propertyName, $properties);

        // assign the module variables
        $this->view->assign($this->getVars());
        $this->view->assign('properties', $properties);
        $this->view->assign('property', $propertyIndex);

        // Return the output that has been generated by this function
        return $this->view->fetch('admin/modifyconfig.tpl');
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     * @author Mark West
     * @param int 'itemsperpage' number of articles per page
     * @return bool true
     */
    public function updateconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('News::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('News', 'admin', 'view'));
        }

        // Update module variables
        $modvars = array();

        $refereronprint = (int) FormUtil::getPassedValue('refereronprint', 0, 'POST');
        if ($refereronprint != 0 && $refereronprint != 1) {
            $refereronprint = 0;
        }
        $modvars['refereronprint'] = $refereronprint;
        $modvars['itemsperpage'] = (int) FormUtil::getPassedValue('itemsperpage', 25, 'POST');
        $modvars['storyhome'] = (int) FormUtil::getPassedValue('storyhome', 10, 'POST');
        $modvars['storyorder'] = (int) FormUtil::getPassedValue('storyorder', 1, 'POST');
        $modvars['enablecategorization'] = (bool) FormUtil::getPassedValue('enablecategorization', false, 'POST');
        $modvars['enableattribution'] = (bool) FormUtil::getPassedValue('enableattribution', false, 'POST');
        $catimagepath = FormUtil::getPassedValue('catimagepath', '/images/categories/', 'POST');
        if (substr($catimagepath, -1) != '/') {
            $catimagepath .= '/'; // add slash if needed
        }
        $modvars['catimagepath'] = $catimagepath;
        $modvars['enableajaxedit'] = (bool) FormUtil::getPassedValue('enableajaxedit', false, 'POST');
        $modvars['enablemorearticlesincat'] = (bool) FormUtil::getPassedValue('enablemorearticlesincat', false, 'POST');
        $modvars['morearticlesincat'] = (int) FormUtil::getPassedValue('morearticlesincat', 0, 'POST');
        $modvars['enabledescriptionvar'] = (bool) FormUtil::getPassedValue('enabledescriptionvar', false, 'POST');
        $modvars['descriptionvarchars'] = (int) FormUtil::getPassedValue('descriptionvarchars', 250, 'POST');
        $modvars['enablecategorybasedpermissions'] = (bool) FormUtil::getPassedValue('enablecategorybasedpermissions', false, 'POST');

        $modvars['notifyonpending'] = (bool) FormUtil::getPassedValue('notifyonpending', false, 'POST');
        $modvars['notifyonpending_fromname'] = FormUtil::getPassedValue('notifyonpending_fromname', '', 'POST');
        $modvars['notifyonpending_fromaddress'] = FormUtil::getPassedValue('notifyonpending_fromaddress', '', 'POST');
        $modvars['notifyonpending_toname'] = FormUtil::getPassedValue('notifyonpending_toname', '', 'POST');
        $modvars['notifyonpending_toaddress'] = FormUtil::getPassedValue('notifyonpending_toaddress', '', 'POST');
        $modvars['notifyonpending_subject'] = FormUtil::getPassedValue('notifyonpending_subject', '', 'POST');
        $modvars['notifyonpending_html'] = (bool) FormUtil::getPassedValue('notifyonpending_html', true, 'POST');

        $modvars['pdflink'] = (bool) FormUtil::getPassedValue('pdflink', false, 'POST');
        $modvars['pdflink_tcpdfpath'] = FormUtil::getPassedValue('pdflink_tcpdfpath', '', 'POST');
        $modvars['pdflink_tcpdflang'] = FormUtil::getPassedValue('pdflink_tcpdflang', '', 'POST');
        $modvars['pdflink_headerlogo'] = FormUtil::getPassedValue('pdflink_headerlogo', '', 'POST');
        $modvars['pdflink_headerlogo_width'] = FormUtil::getPassedValue('pdflink_headerlogo_width', '', 'POST');

        $modvars['picupload_enabled'] = (bool) FormUtil::getPassedValue('picupload_enabled', false, 'POST');
        $modvars['picupload_allowext'] = str_replace(array(' ', '.'), '', FormUtil::getPassedValue('picupload_allowext', 'jpg,gif,png', 'POST'));
        $modvars['picupload_index_float'] = FormUtil::getPassedValue('picupload_index_float', 'left', 'POST');
        $modvars['picupload_article_float'] = FormUtil::getPassedValue('picupload_article_float', 'left', 'POST');
        $modvars['picupload_maxfilesize'] = (int) FormUtil::getPassedValue('picupload_maxfilesize', '500000', 'POST');
        $modvars['picupload_maxpictures'] = (int) FormUtil::getPassedValue('picupload_maxpictures', 3, 'POST');
        $modvars['picupload_sizing'] = FormUtil::getPassedValue('picupload_sizing', '0', 'POST');
        $modvars['picupload_picmaxwidth'] = (int) FormUtil::getPassedValue('picupload_picmaxwidth', 600, 'POST');
        $modvars['picupload_picmaxheight'] = (int) FormUtil::getPassedValue('picupload_picmaxheight', 600, 'POST');
        $modvars['picupload_thumbmaxwidth'] = (int) FormUtil::getPassedValue('picupload_thumbmaxwidth', 150, 'POST');
        $modvars['picupload_thumbmaxheight'] = (int) FormUtil::getPassedValue('picupload_thumbmaxheight', 150, 'POST');
        $modvars['picupload_thumb2maxwidth'] = (int) FormUtil::getPassedValue('picupload_thumb2maxwidth', 200, 'POST');
        $modvars['picupload_thumb2maxheight'] = (int) FormUtil::getPassedValue('picupload_thumb2maxheight', 200, 'POST');
        $modvars['picupload_uploaddir'] = FormUtil::getPassedValue('picupload_uploaddir', '', 'POST');
        $createfolder = (bool) FormUtil::getPassedValue('picupload_createfolder', false, 'POST');

        // create picture upload folder if needed
        if ($modvars['picupload_enabled']) {
            if ($createfolder && !empty($modvars['picupload_uploaddir'])) {
                if ($modvars['picupload_uploaddir'][0] == '/') {
                    LogUtil::registerError($this->__f("Warning! The image upload directory at [%s] appears to be 'above' the DOCUMENT_ROOT. Please choose a path relative to the webserver (e.g. images/news_picupload).", $modvars['picupload_uploaddir']));
                } else {
                    if (is_dir($modvars['picupload_uploaddir'])) {
                        if (!is_writable($modvars['picupload_uploaddir'])) {
                            LogUtil::registerError($this->__f('Warning! The image upload directory at [%s] exists but is not writable by the webserver.', $modvars['picupload_uploaddir']));
                        }
                    } else {
                        // Try to create the specified directory
                        if (FileUtil::mkdirs($modvars['picupload_uploaddir'], 0777)) {
                            // write a htaccess file in the image upload directory
                            $htaccessContent = FileUtil::readFile('modules' . DIRECTORY_SEPARATOR . 'News' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'htaccess');

                            if (FileUtil::writeFile($modvars['picupload_uploaddir'] . DIRECTORY_SEPARATOR . '.htaccess', $htaccessContent)) {
                                LogUtil::registerStatus($this->__f('News publisher created the image upload directory successfully at [%s] and wrote an .htaccess file there for security.', $modvars['picupload_uploaddir']));
                            } else {
                                LogUtil::registerStatus($this__f('News publisher created the image upload directory successfully at [%s], but could not write the .htaccess file there.', $modvars['picupload_uploaddir']));
                            }
                        } else {
                            LogUtil::registerStatus($this->__f('Warning! News publisher could not create the specified image upload directory [%s]. Try to create it yourself and make sure that this folder is accessible via the web and writable by the webserver.', $modvars['picupload_uploaddir']));
                        }
                    }
                }
            }
        }

        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');
        $properties = array_keys($catregistry);
        $topicproperty = FormUtil::getPassedValue('topicproperty', null, 'POST');
        $modvars['topicproperty'] = $properties[$topicproperty];

        $permalinkformat = FormUtil::getPassedValue('permalinkformat', null, 'POST');
        if ($permalinkformat == 'custom') {
            $permalinkformat = FormUtil::getPassedValue('permalinkstructure', null, 'POST');
        }
        $modvars['permalinkformat'] = $permalinkformat;

        $this->setVars($modvars);

        // Let any other modules know that the modules configuration has been updated
        $this->notifyHooks('news.hook.config.process.edit', null, null);
        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module settings.'));

        return System::redirect(ModUtil::url('News', 'admin', 'main'));
    }
}