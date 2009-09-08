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
 * Smarty modifier to format datetimes in a more Human Readable form
 * (like tomorow, 4 days from now, 6 hours ago)
 *
 * Example
 * <!--[$futuredate|dateformatHuman:'%x':'2']-->
 *
 * @author   Erik Spaan
 * @since    05/03/09
 * @param    string   $string   input datetime string
 * @param    string   $format   The format of the regular date output (default %x)
 * @param    string   $niceval  [1|2|3|4] Choose the nice value of the output (default 2)
 *                                    1 = full human readable
 *                                    2 = past date > 1 day with dateformat, otherwise human readable
 *                                    3 = within 1 day human readable, otherwise dateformat
 *                                    4 = only use the specified format
 * @return   string   the modified output
 */
function smarty_modifier_dateformatHuman($string, $format='%x', $niceval=2)
{
    $dom = ZLanguage::getModuleDomain('News');
    if (empty($format)) {
        $format = '%x';
    }
    // store the current datetime in a variable
    $now = DateUtil::getDatetime();

    if (empty($string)) {
        return DateUtil::formatDatetime($now, $format);
    }
    if (empty($niceval)) {
        $niceval = 2;
    }

    // now format the date with respect to the current datetime
    $res = '';
    $diff = DateUtil::getDatetimeDiff($now, $string);
    //TODO A All this needs to be removed... we just use plurals here - drak
    if ($diff['d'] < 0) {
        if ($niceval == 1) {
            $res = __f('%days% days ago';, array('days' => abs($diff['d'])));
        } elseif ($niceval < 4 && $diff['d'] == -1) {
            $res = pnML('_NEWS_YESTERDAY');
        } else {
            $res = DateUtil::formatDatetime($string, $format);
        }
    } elseif ($diff['d'] > 0) {
        if ($niceval > 2) {
            $res = DateUtil::formatDatetime($string, $format);
        } elseif ($diff['d'] == 1) {
            $res = pnML('_NEWS_TOMORROW');
        } else {
            $res = __f('%days% days from now';, array('days' => $diff['d']));
        }
    } else {
        // no day difference
        if ($diff['h'] < 0) {
            $res = __f('%hours% hours ago';, array('hours' => abs($diff['h'])));
        } elseif ($diff['h'] > 0) {
            $res = __f('%hours% hours from now';, array('hours' => $diff['h']));
        } else {
            // no hour difference
            if ($diff['m'] < 0) {
                $res = __f('%mins% mins ago';, array('mins' => abs($diff['m'])));
            } elseif ($diff['m'] > 0) {
                $res = __f('%mins% mins from now';, array('mins' => $diff['m']));
            } else {
                // no min difference
                if ($diff['s'] < 0) {
                    $res = __f('%secs% secs ago';, array('secs' => abs($diff['s'])));
                } else {
                    $res = __f('%secs% secs from now';, array('secs' => $diff['s']));
                }
            }
        }
    }
    return $res;
}
