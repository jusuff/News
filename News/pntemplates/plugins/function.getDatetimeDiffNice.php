<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: function.articleadminlinks.php 75 2009-02-24 04:51:52Z mateo $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage News
*/

/**
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Zikula_3rdParty_Modules
 * @subpackage   News
 * @version      $Id: function.articleadminlinks.php 75 2009-02-24 04:51:52Z mateo $
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * Smarty function to display a difference between dates in a more readable form
 *
 * Example
 * <!--[getDatetimeDiffNice date1=$now date2=$futuredate]-->
 *
 * @author       Erik Spaan
 * @since        05/03/09
 * @see          function.getDatetimeDiffNice.php::smarty_function_getDatetimeDiffNice()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $date1       first DateTime
 * @param        string      $date2       second DateTime
 * @return       string      the results of the module function
 */
function smarty_function_getDatetimeDiffNice($params, &$smarty)
{
    Loader::loadClass('DateUtil');

    if (!isset($params['date1'])) {
        $params['date1'] = DateUtil::getDatetime();
    }
    if (!isset($params['date2'])) {
        $params['date2'] = DateUtil::getDatetime();
    }

    $diff = DateUtil::getDatetimeDiff($params['date1'], $params['date2']);
    $res = '';
    if ($diff['d'] > 0) {
        $res = $diff['d'] . ' days from now';
    } elseif ($diff['d'] < 0) {
        $res = DateUtil::formatDatetime($params['date2'], '%x');
    } elseif ($diff['h'] > 1 || $diff['h'] < -1) {
        $res = abs($diff['h']) . ' hours ago';
    } elseif ($diff['m'] > 1 || $diff['m'] < -1) {
        $res = abs($diff['m']) . ' mins ago';
    } else {
        $res = $diff['s'] . ' secs ago';
    }
    
    if (isset($params['assign']) && $params['assign']) {
        $smarty->assign ($params['assign'], $res);
    } else {
        return $res;
    }
}
