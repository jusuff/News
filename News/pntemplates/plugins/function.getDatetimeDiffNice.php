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
 * Smarty function to display a difference between a date and now() (date - now()) in a human readable form
 *
 * Example
 * <!--[getDatetimeDiffNice date=$futuredate]-->
 *
 * @author       Erik Spaan
 * @since        05/03/09
 * @see          function.getDatetimeDiffNice.php::smarty_function_getDatetimeDiffNice()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $date        The DateTime
 * @param        string      $datenice    [1|2|3] Choose the nice value of the output (default 2)
 *                                        1 = full human readable
 *                                        2 = past date > 1 day with dateformat, otherwise human readable
 *                                        3 = within 1 day human readable, otherwise dateformat
 * @param        string      $dateformat  The format of the regular date output (default %x)
 * @return       string      the results of the module function
 */
function smarty_function_getDatetimeDiffNice($params, &$smarty)
{
    Loader::loadClass('DateUtil');

    // store the current datetime in a variable
    $now = DateUtil::getDatetime();

    if (!isset($params['dateformat'])) {
        $params['dateformat'] = '%x';
    }
    if (!isset($params['date']) || (isset($params['date']) && strtotime($params['date']) - strtotime($now) == 0)) {
        return DateUtil::formatDatetime($now, $params['dateformat']);
    }
    $params['datenice'] = isset($params['datenice']) ? (int)$params['datenice'] : 2;
    
    
    // now format the date with respect to now
    $res = '';
    $diff = DateUtil::getDatetimeDiff($now, $params['date']);
    if ($diff['d'] < 0) {
        if ($params['datenice'] > 1) {
            $res = DateUtil::formatDatetime($params['date'], $params['dateformat']);
        } else {
            $res = pnML('_NEWS_DAYSAGO', array('days' => abs($diff['d'])));
        }
    } elseif ($diff['d'] > 0) {
        if ($params['datenice'] > 2) {
            $res = DateUtil::formatDatetime($params['date'], $params['dateformat']);
        } else {
            $res = pnML('_NEWS_DAYSFROMNOW', array('days' => $diff['d']));
        }
    } else {
        // no day difference
        if ($diff['h'] < 0) {
            $res = pnML('_NEWS_HOURSAGO', array('hours' => abs($diff['h'])));
        } elseif ($diff['h'] > 0) {
            $res = pnML('_NEWS_HOURSFROMNOW', array('hours' => $diff['h']));
        } else {
            // no hour difference
            if ($diff['m'] < 0) {
                $res = pnML('_NEWS_MINSAGO', array('mins' => abs($diff['m'])));
            } elseif ($diff['m'] > 0) {
                $res = pnML('_NEWS_MINSFROMNOW', array('mins' => $diff['m']));
            } else {
                // no min difference
                if ($diff['s'] < 0) {
                    $res = pnML('_NEWS_SECSAGO', array('secs' => abs($diff['s'])));
                } else {
                    $res = pnML('_NEWS_SECSFROMNOW', array('secs' => $diff['s']));
                }
            }
        }
    }
    
    if (isset($params['assign']) && $params['assign']) {
        $smarty->assign ($params['assign'], $res);
    } else {
        return $res;
    }
}
