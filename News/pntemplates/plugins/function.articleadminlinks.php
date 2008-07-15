<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: function.articleadminlinks.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

/**
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Zikula_Value_Addons
 * @subpackage   News
 * @version      $Id: function.articleadminlinks.php 24342 2008-06-06 12:03:14Z markwest $
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * Smarty function to display edit and delete links for a news article
 *
 * Example
 * <!--[articleadminlinks sid="1" start="[" end="]" seperator="|" class="pn-sub"]-->
 *
 * @author       Mark West
 * @since        20/10/03
 * @see          function.articleadminlinks.php::smarty_function_articleadminlinks()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        integer     $sid         article id
 * @param        string      $start       start string
 * @param        string      $end         end string
 * @param        string      $seperator   link seperator
 * @param        string      $class       CSS class
 * @return       string      the results of the module function
 */
function smarty_function_articleadminlinks($params, &$smarty)
{
    // get the info template var
    $info = $smarty->get_template_vars('info');

    if (!isset($params['sid'])) {
        $params['sid'] = $info['sid'];
    }
    if (!isset($params['page'])) {
        $params['page'] = $smarty->get_template_vars('page');
    }

    // set some defaults
    if (!isset($params['start'])) {
        $params['start'] = '[';
    }
    if (!isset($params['end'])) {
        $params['end'] = ']';
    }
    if (!isset($params['seperator'])) {
        $params['seperator'] = '|';
    }
    if (!isset($params['class'])) {
        $params['class'] = 'pn-sub';
    }
    if (isset($params['type']) && $params['type'] <> 'ajax') {
        $params['type'] = '';
    }

    $articlelinks = '';
    if (SecurityUtil::checkPermission('Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_EDIT)) {
        // load our ajax files into the header
        require_once $smarty->_get_plugin_filepath('function','pnajaxheader');
        smarty_function_pnajaxheader(array('modname' => 'News', 'filename' => 'news.js'), $smarty);
        smarty_function_pnajaxheader(array('modname' => 'News', 'filename' => 'sizecheck.js'), $smarty);
        if (isset($params['type']) && $params['type'] == 'ajax') {
            $articlelinks .= '<img id="news_loadnews" src="'.pnGetBaseURL().'images/ajax/circle-ball-dark-antialiased.gif" alt="" /><span class="' . $params['class'] . '"> ' . $params['start'] . ' <a onclick="editnews(' . $params['sid'] . ',' . $params['page'] . ')" href="javascript:void(0);">' . _EDIT . '</a> ' . $params['end'] . "</span>\n";
        } else {
            $articlelinks .= "<span class=\"" . $params['class'] . "\"> " . $params['start'] . " <a href=\"" . DataUtil::formatForDisplayHTML(pnModURL('News', 'admin', 'modify', array('sid' => $params['sid']))) . '">' . _EDIT . '</a>';
            if (SecurityUtil::checkPermission('Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_DELETE)) {
                $articlelinks .= " " . $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplay(pnModURL('News', 'admin', 'delete', array('sid' => $params['sid']))) . '">' . _DELETE . '</a>';
            }
            $articlelinks .= " " . $params['end'] . "</span>\n";
        }
    }

    return $articlelinks;
}
