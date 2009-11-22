<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: pnadmin.php 82 2009-02-25 23:09:21Z mateo $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * the main administration function
 * 
 * @author Mark West
 * @return string HTML output
 */
function News_admin_main()
{
    // Security check
    if (!(SecurityUtil::checkPermission('News::', '::', ACCESS_EDIT) ||
          SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_EDIT))) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $render = & pnRender::getInstance('News', false);

    // Return the output that has been generated by this function
    return $render->fetch('news_admin_main.htm');
}

/**
 * create a new news article
 * this function is purely a wrapper for the output from news_user_new
 *
 * @author Mark West
 * @return string HTML string
 */
function News_admin_new()
{
    // Return the output that has been generated by this function
    return pnModFunc('News', 'user', 'new');
}

/**
 * modify a news article
 *
 * @param int 'sid' the id of the item to be modified
 * @param int 'objectid' generic object id maps to sid if present
 * @author Mark West
 * @return string HTML string
 */
function News_admin_modify($args)
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

    $dom = ZLanguage::getModuleDomain('News');

    // Get the news article in the db
    $dbitem = pnModAPIFunc('News', 'user', 'get', array('sid' => $sid));

    if ($dbitem === false) {
        return LogUtil::registerError(__('Error! No such article found.', $dom), 404);
    }

    // Security check
    if (!(SecurityUtil::checkPermission('News::', "$dbitem[aid]::$sid", ACCESS_EDIT) ||
          SecurityUtil::checkPermission('Stories::Story', "$dbitem[aid]::$sid", ACCESS_EDIT))) {
        return LogUtil::registerPermissionError();
    }

    // merge the data of the db and the preview if exist
    $item = $inpreview ? array_merge($dbitem, $item) : $dbitem;
    unset($dbitem);

    // Get the format types. 'home' string is bits 0-1, 'body' is bits 2-3.
    $item['hometextcontenttype'] = isset($item['hometextcontenttype']) ? $item['hometextcontenttype'] : ($item['format_type']%4);
    $item['bodytextcontenttype'] = isset($item['bodytextcontenttype']) ? $item['bodytextcontenttype'] : (($item['format_type']/4)%4);

    // Set the publishing date options.
    if (!$inpreview) {
        if (DateUtil::getDatetimeDiff_AsField($item['from'], $item['time'], 6) >= 0 && is_null($item['to'])) {
            $item['unlimited'] = 1;
            $item['tonolimit'] = 0;
        } elseif (DateUtil::getDatetimeDiff_AsField($item['from'], $item['time'], 6) < 0 && is_null($item['to'])) {
            $item['unlimited'] = 0;
            $item['tonolimit'] = 1;
        } else  {
            $item['unlimited'] = 0;
            $item['tonolimit'] = 0;
        }
    } else {
        $item['unlimited'] = isset($item['unlimited']) ? 1 : 0;
        $item['tonolimit'] = isset($item['tonolimit']) ? 1 : 0;
    }

    // Check if we need a preview
    $preview = '';
    if (isset($item['action']) && $item['action'] == 0) {
        $preview = pnModFunc('News', 'user', 'preview',
                             array('title' => $item['title'],
                                   'hometext' => $item['hometext'],
                                   'hometextcontenttype' => $item['hometextcontenttype'],
                                   'bodytext' => $item['bodytext'],
                                   'bodytextcontenttype' => $item['bodytextcontenttype'],
                                   'notes' => $item['notes']));
    }

    // Get the module configuration vars
    $modvars = pnModGetVar('News');

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit(__f('Error! Could not load [%s] class.', 'CategoryRegistryUtil', $dom));
        }
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');

        // check if the __CATEGORIES__ info needs a fix (when preview)
        if (isset($item['__CATEGORIES__'])) {
            foreach ($item['__CATEGORIES__'] as $prop => $catid) {
                if (is_numeric($catid)) {
                    $item['__CATEGORIES__'][$prop] = array('id' => $catid);
                }
            }
        }
    }

    // Create output object
    $render = & pnRender::getInstance('News', false);

    if (SecurityUtil::checkPermission('News::', '::', ACCESS_ADD) ||
        SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADD)) {
        $render->assign('accessadd', 1);
    } else {
        $render->assign('accessadd', 0);
    }

    if ($modvars['enablecategorization']) {
        $render->assign('catregistry', $catregistry);
    }

    // Pass the module configuration to the template
    $render->assign($modvars);

    // Assign the item to the template
    $render->assign($item);

    // Get the preview of the item
    $render->assign('preview', $preview);

    // Assign the content format
    $formattedcontent = pnModAPIFunc('News', 'user', 'isformatted', array('func' => 'modify'));
    $render->assign('formattedcontent', $formattedcontent);

    // Return the output that has been generated by this function
    return $render->fetch('news_admin_modify.htm');
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
 * @param int 'ihome' publish the article in the homepage
 * @author Mark West
 * @return bool true
 */
function News_admin_update($args)
{
    $story = FormUtil::getPassedValue('story', isset($args['story']) ? $args['story'] : null, 'POST');
    if (!empty($story['objectid'])) {
        $story['sid'] = $story['objectid'];
    }

    // Validate the essential parameters
    if (empty($story['sid'])) {
        return LogUtil::registerArgsError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('News', 'admin', 'view'));
    }

    $dom = ZLanguage::getModuleDomain('News');

    // Get the unedited news article for the permissions check
    $item = pnModAPIFunc('News', 'user', 'get', array('sid' => $story['sid']));
    if ($item === false) {
        return LogUtil::registerError(__('Error! No such article found.', $dom), 404);
    }

    // Security check
    if (!(SecurityUtil::checkPermission('News::', "$item[aid]::$item[sid]", ACCESS_EDIT) ||
          SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$item[sid]", ACCESS_EDIT))) {
        return LogUtil::registerPermissionError();
    }

    // Validate the input
    $validationerror = false;
    if ($story['action'] != 0 && empty($story['title'])) {
        $validationerror = __f('Error! You did not enter a %s.', __('title', $dom), $dom);
    }
    // both text fields can't be empty
    if ($story['action'] != 0 && empty($story['hometext']) && empty($story['bodytext'])) {
        $validationerror = __f('Error! You did not enter the minimum necessary %s.', __('article content', $dom), $dom);
    }

    // Reformat the attributes array
    // from {0 => {name => '...', value => '...'}} to {name => value}
    if (isset($story['attributes'])) {
        $attributes = array();
        foreach ($story['attributes'] as $attr) {
            if (!empty($attr['name']) && !empty($attr['value'])) {
                $attributes[$attr['name']] = $attr['value'];
            }
        }
        unset($story['attributes']);
        $story['__ATTRIBUTES__'] = $attributes;
    }

    // if the user has selected to preview the article we then route them back
    // to the new function with the arguments passed here
    if ($story['action'] == 0 || $validationerror !== false) {
        // log the error found if any
        if ($validationerror !== false) {
            LogUtil::registerError($validationerror);
        }
        // back to the referer form
        SessionUtil::setVar('newsitem', $story);
        return pnRedirect(pnModURL('News', 'admin', 'modify'));

    } else {
        // As we're not previewing the item let's remove it from the session
        SessionUtil::delVar('newsitem');
    }

    // Check if the article goes from not published to published
    if ($item['published_status'] != 0 && $story['published_status'] == 0) {
        $story['approver'] = SessionUtil::getVar('uid');
    }

    // Update the story
    if (pnModAPIFunc('News', 'admin', 'update',
                    array('sid' => $story['sid'],
                          'title' => $story['title'],
                          'urltitle' => $story['urltitle'],
                          '__CATEGORIES__' => isset($story['__CATEGORIES__']) ? $story['__CATEGORIES__'] : null,
                          '__ATTRIBUTES__' => isset($story['__ATTRIBUTES__']) ? $story['__ATTRIBUTES__'] : null,
                          'language' => isset($story['language']) ? $story['language'] : '',
                          'hometext' => isset($story['hometext']) ? $story['hometext'] : '',
                          'hometextcontenttype' => $story['hometextcontenttype'],
                          'bodytext' => isset($story['bodytext']) ? $story['bodytext'] : '',
                          'bodytextcontenttype' => $story['bodytextcontenttype'],
                          'notes' => $story['notes'],
                          'ihome' => isset($story['ihome']) ? $story['ihome'] : 0,
                          'withcomm' => isset($story['withcomm']) ? $story['withcomm'] : 0,
                          'unlimited' => isset($story['unlimited']) ? $story['unlimited'] : null,
                          'from' => mktime($story['fromHour'], $story['fromMinute'], 0, $story['fromMonth'], $story['fromDay'], $story['fromYear']),
                          'tonolimit' => isset($story['tonolimit']) ? $story['tonolimit'] : null,
                          'to' => mktime($story['toHour'], $story['toMinute'], 0, $story['toMonth'], $story['toDay'], $story['toYear']),
                          'approver' => $story['approver'],
                          'action' => $story['action']))) {
        // Success
        LogUtil::registerStatus(__('Done! Saved your changes.', $dom));
    }

    return pnRedirect(pnModURL('News', 'admin', 'view'));
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
function News_admin_delete($args)
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

    $dom = ZLanguage::getModuleDomain('News');

    // Get the news story
    $item = pnModAPIFunc('News', 'user', 'get', array('sid' => $sid));

    if ($item == false) {
        return LogUtil::registerError(__('Error! No such article found.', $dom), 404);
    }

    // Security check
    if (!(SecurityUtil::checkPermission('News::', "$item[aid]::$item[sid]", ACCESS_DELETE) ||
          SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$item[sid]", ACCESS_DELETE))) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet
        // Create output object
        $render = & pnRender::getInstance('News', false);

        // Add News story ID
        $render->assign('sid', $sid);

        // Return the output that has been generated by this function
        return $render->fetch('news_admin_delete.htm');
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('News', 'admin', 'view'));
    }

    // Delete
    if (pnModAPIFunc('News', 'admin', 'delete', array('sid' => $sid))) {
        // Success
        LogUtil::registerStatus(__('Done! Deleted article.', $dom));
    }

    return pnRedirect(pnModURL('News', 'admin', 'view'));
}

/**
 * view items
 * @param int 'startnum' starting number for paged output
 * @author Mark West
 * @return string HTML string
 */
function News_admin_view($args)
{
    // Security check
    if (!(SecurityUtil::checkPermission('News::', '::', ACCESS_EDIT) ||
          SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_EDIT))) {
        return LogUtil::registerPermissionError();
    }

    $startnum    = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
    $news_status = FormUtil::getPassedValue('news_status', isset($args['news_status']) ? $args['news_status'] : null, 'GETPOST');
    $language    = FormUtil::getPassedValue('language', isset($args['language']) ? $args['language'] : null, 'POST');
    $property    = FormUtil::getPassedValue('news_property', isset($args['news_property']) ? $args['news_property'] : null, 'GETPOST');
    $category    = FormUtil::getPassedValue("news_{$property}_category", isset($args["news_{$property}_category"]) ? $args["news_{$property}_category"] : null, 'GETPOST');
    $clear       = FormUtil::getPassedValue('clear', false, 'POST');
    $purge       = FormUtil::getPassedValue('purge', false, 'GET');
    $order       = FormUtil::getPassedValue('order', isset($args['order']) ? $args['order'] : 'from', 'GETPOST');
    //$monthyear   = FormUtil::getPassedValue('monthyear', isset($args['monthyear']) ? $args['monthyear'] : null, 'POST');

    $dom = ZLanguage::getModuleDomain('News');

    if ($purge) {
        if (pnModAPIFunc('News', 'admin', 'purgepermalinks')) {
            LogUtil::registerStatus(__('Done! Purged permalinks.', $dom));
        } else {
            LogUtil::registerError(__('Error! Could not purge permalinks.', $dom));
        }
        return pnRedirect(strpos(pnServerGetVar('HTTP_REFERER'), 'purge') ? pnModURL('News', 'admin', 'view') : pnServerGetVar('HTTP_REFERER'));
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
    $modvars = pnModGetVar('News');

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!Loader::loadClass('CategoryRegistryUtil')) {
            pn_exit(__f('Error! Could not load [%s] class.', 'CategoryRegistryUtil', $dom));
        }
        $catregistry  = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');
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

    $multilingual = pnConfigGetVar('multilingual', false);

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
    $items = pnModAPIFunc('News', 'user', 'getall',
                          array('startnum' => $startnum,
                                'status'   => $status,
                                'numitems' => $modvars['itemsperpage'],
                                'ignoreml' => ($multilingual ? false : true),
                                'language' => $language,
                                'order'    => isset($order) ? $order : 'from',
                                'from'     => isset($from) ? $from : null,
                                'to'       => isset($to) ? $to : null,
                                'category' => isset($catFilter) ? $catFilter : null,
                                'catregistry' => isset($catregistry) ? $catregistry : null));

    // Set the possible status for later use
    $itemstatus = array (
        '' => __('All', $dom), 
        0  => __('Published', $dom),
        1  => __('Rejected', $dom),
        2  => __('Pending', $dom),
        3  => __('Archived', $dom),
        4  => __('Draft', $dom),
        5  => __('Scheduled', $dom)
    );

/*
    // Load localized month names
    $months = explode(' ', __('January February March April May June July August September October November December', $dom));
    $newsmonths = array();
    // get all matching news stories
    $monthsyears = pnModAPIFunc('News', 'user', 'getMonthsWithNews');
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
        $options[] = array('url'   => pnModURL('News', 'user', 'display', array('sid' => $item['sid'])),
                           'image' => 'demo.gif',
                           'title' => __('View', $dom));

        if (SecurityUtil::checkPermission('News::', "$item[aid]::$item[sid]", ACCESS_EDIT) ||
            SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$item[sid]", ACCESS_EDIT)) {
            $options[] = array('url'   => pnModURL('News', 'admin', 'modify', array('sid' => $item['sid'])),
                               'image' => 'xedit.gif',
                               'title' => __('Edit', $dom));

            if (SecurityUtil::checkPermission('Stories::Story', "$item[aid]::$item[sid]", ACCESS_DELETE)) {
                $options[] = array('url'   => pnModURL('News', 'admin', 'delete', array('sid' => $item['sid'])),
                                   'image' => '14_layer_deletelayer.gif',
                                   'title' => __('Delete', $dom));
            }
        }
        $item['options'] = $options;

        if (in_array($item['published_status'], array_keys($itemstatus))) {
            $item['status'] = $itemstatus[$item['published_status']];
        } else {
            $item['status'] = __('Unknown', $dom);
        }

        if ($item['ihome'] == 0) {
            $item['ihome'] = __('Yes', $dom);
        } else {
            $item['ihome'] = __('No', $dom);
        }

        $item['infuture'] = DateUtil::getDatetimeDiff_AsField($item['from'], DateUtil::getDatetime(), 6) < 0;
        $newsitems[] = $item;
    }

    // Create output object
    $render = & pnRender::getInstance('News', false);

    // Assign the items and modvars to the template
    $render->assign('newsitems', $newsitems);
    $render->assign($modvars);

    // Assign the default and selected language
    $render->assign('lang', ZLanguage::getLanguageCode());
    $render->assign('language', $language);

    // Assign the current status filter and the possible ones
    $render->assign('news_status', $news_status);
    $render->assign('itemstatus', $itemstatus);
    $render->assign('order', $order);
    $render->assign('orderoptions', array('from' => __('Article date/time', $dom), 
                                          'sid'  => __('Article ID', $dom)));

    //$render->assign('monthyear', $monthyear);
    //$render->assign('newsmonths', $newsmonths);

    // Assign the categories information if enabled
    if ($modvars['enablecategorization']) {
        $render->assign('catregistry', $catregistry);
        $render->assign('numproperties', count($propArray));
        $render->assign('properties', $propArray);
        $render->assign('property', $property);
        $render->assign('category', $category);
    }

    // Count the items for the selected status and category
    $statuslinks = array();
    // Counts with a tolerance of 3 seconds
    $now = DateUtil::getDatetime(time()+3);

    $statuslinks[] = array('count' => pnModAPIFunc('News', 'user', 'countitems',
                                                   array('category' => isset($catFilter) ? $catFilter : null,
                                                         'status' => 0,
                                                         'to' => $now)),
                           'url' => pnModURL('News', 'admin', 'view',
                                             array('news_status' => 0,
                                                   'news_property' => $property,
                                                   'news_'.$property.'_category' => isset($category) ? $category : null)),
                           'title' => __('Published', $dom));

    $statuslinks[] = array('count' => pnModAPIFunc('News', 'user', 'countitems',
                                                    array('category' => isset($catFilter) ? $catFilter : null,
                                                          'status' => 0,
                                                          'from' => $now)),
                            'url' => pnModURL('News', 'admin', 'view',
                                              array('news_status' => 5,
                                                    'news_property' => $property,
                                                    'news_'.$property.'_category' => isset($category) ? $category : null)),
                            'title' => __('Scheduled', $dom));

    $statuslinks[] = array('count' => pnModAPIFunc('News', 'user', 'countitems',
                                                    array('category' => isset($catFilter) ? $catFilter : null,
                                                          'status' => 2)),
                            'url' => pnModURL('News', 'admin', 'view',
                                              array('news_status' => 2,
                                                    'news_property' => $property,
                                                    'news_'.$property.'_category' => isset($category) ? $category : null)),
                            'title' => __('Pending', $dom));

    $statuslinks[] = array('count' => pnModAPIFunc('News', 'user', 'countitems',
                                                    array('category' => isset($catFilter) ? $catFilter : null,
                                                          'status' => 4)),
                            'url' => pnModURL('News', 'admin', 'view',
                                              array('news_status' => 4,
                                                    'news_property' => $property,
                                                    'news_'.$property.'_category' => isset($category) ? $category : null)),
                            'title' => __('Draft', $dom));

    $statuslinks[] = array('count' => pnModAPIFunc('News', 'user', 'countitems',
                                                    array('category' => isset($catFilter) ? $catFilter : null,
                                                          'status' => 3)),
                            'url' => pnModURL('News', 'admin', 'view',
                                              array('news_status' => 3,
                                                    'news_property' => $property,
                                                    'news_'.$property.'_category' => isset($category) ? $category : null)),
                            'title' => __('Archived', $dom));

    $statuslinks[] = array('count' => pnModAPIFunc('News', 'user', 'countitems',
                                                    array('category' => isset($catFilter) ? $catFilter : null,
                                                          'status' => 1)),
                            'url' => pnModURL('News', 'admin', 'view',
                                              array('news_status' => 1,
                                                    'news_property' => $property,
                                                    'news_'.$property.'_category' => isset($category) ? $category : null)),
                            'title' => __('Rejected', $dom));

    $alllink = array('count' => $statuslinks[0]['count'] + $statuslinks[1]['count'] + $statuslinks[2]['count'] + $statuslinks[3]['count'] + $statuslinks[4]['count'] + $statuslinks[5]['count'],
                     'url' => pnModURL('News', 'admin', 'view',
                                       array('news_property' => $property,
                                             'news_'.$property.'_category' => isset($category) ? $category : null)),
                     'title' => __('All', $dom));

    $render->assign('statuslinks', $statuslinks);
    $render->assign('alllink', $alllink);
  
    // Assign the values for the smarty plugin to produce a pager
    $render->assign('pager', array('numitems' => pnModAPIFunc('News', 'user', 'countitems', array('category' => isset($catFilter) ? $catFilter : null)),
                                   'itemsperpage' => $modvars['itemsperpage']));

    // Return the output that has been generated by this function
    return $render->fetch('news_admin_view.htm');
}

/**
 * This is a standard function to modify the configuration parameters of the
 * module
 * @author Mark West
 * @return string HTML string
 */
function News_admin_modifyconfig()
{
    // Security check
    if (!(SecurityUtil::checkPermission('News::', '::', ACCESS_ADMIN) ||
          SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADMIN))) {
        return LogUtil::registerPermissionError();
    }

    $dom = ZLanguage::getModuleDomain('News');

    if (!Loader::loadClass('CategoryRegistryUtil')) {
        pn_exit(__f('Error! Could not load [%s] class.', 'CategoryRegistryUtil', $dom));
    }
    $catregistry   = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');
    $properties    = array_keys($catregistry);
    $propertyName  = pnModGetVar('News', 'topicproperty');
    $propertyIndex = empty($propertyName) ? 0 : array_search($propertyName, $properties);

    // Create output object
    $render = & pnRender::getInstance('News', false);

    // Number of items to display per page
    $render->assign(pnModGetVar('News'));

    $render->assign('properties', $properties);
    $render->assign('property', $propertyIndex);

    // Return the output that has been generated by this function
    return $render->fetch('news_admin_modifyconfig.htm');
}

/**
 * This is a standard function to update the configuration parameters of the
 * module given the information passed back by the modification form
 * @author Mark West
 * @param int 'itemsperpage' number of articles per page
 * @return bool true
 */
function News_admin_updateconfig()
{
    // Security check
    if (!(SecurityUtil::checkPermission('News::', '::', ACCESS_ADMIN) ||
          SecurityUtil::checkPermission('Stories::Story', '::', ACCESS_ADMIN))) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('News', 'admin', 'view'));
    }

    // Update module variables
    $modvars = array();

    $refereronprint = (int)FormUtil::getPassedValue('refereronprint', 0, 'POST');
    if ($refereronprint != 0 && $refereronprint != 1) {
        $refereronprint = 0;
    }
    $modvars['refereronprint'] = $refereronprint;
    $modvars['itemsperpage'] = (int)FormUtil::getPassedValue('itemsperpage', 25, 'POST');
    $modvars['storyhome'] = (int)FormUtil::getPassedValue('storyhome', 10, 'POST');
    $modvars['storyorder'] = (int)FormUtil::getPassedValue('storyorder', 1, 'POST');
    $modvars['enablecategorization'] = (bool)FormUtil::getPassedValue('enablecategorization', false, 'POST');
    $modvars['enableattribution'] = (bool)FormUtil::getPassedValue('enableattribution', false, 'POST');
    $catimagepath = FormUtil::getPassedValue('catimagepath', '/images/categories/', 'POST');
    if (substr($catimagepath, -1) != '/') {
        $catimagepath .= '/'; // add slash if needed
    }
    $modvars['catimagepath'] = $catimagepath;
    $modvars['enableajaxedit'] = (bool)FormUtil::getPassedValue('enableajaxedit', false, 'POST');

    if (!Loader::loadClass('CategoryRegistryUtil')) {
        pn_exit(__f('Error! Could not load [%s] class.', 'CategoryRegistryUtil', $dom));
    }
    $catregistry   = CategoryRegistryUtil::getRegisteredModuleCategories('News', 'news');
    $properties    = array_keys($catregistry);
    $topicproperty = FormUtil::getPassedValue('topicproperty', null, 'POST');
    $modvars['topicproperty'] = $properties[$topicproperty];

    $permalinkformat = FormUtil::getPassedValue('permalinkformat', null, 'POST');
    if ($permalinkformat == 'custom') {
        $permalinkformat = FormUtil::getPassedValue('permalinkstructure', null, 'POST');
    }
    $modvars['permalinkformat'] = $permalinkformat;

    pnModSetVars('News', $modvars);

    // Let any other modules know that the modules configuration has been updated
    pnModCallHooks('module','updateconfig','News', array('module' => 'News'));

    // the module configuration has been updated successfuly
    LogUtil::registerStatus(__('Done! Saved module settings.', $dom));

    return pnRedirect(pnModURL('News', 'admin', 'main'));
}
