<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id$
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * decode the custom url string
 *
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function News_userapi_decodeurl($args)
{
    // check we actually have some vars to work with...
    if (!isset($args['vars'])) {
        return LogUtil::registerArgsError();
    }

    // define the available user functions
    $funcs = array('main', 'new', 'create', 'view', 'archives', 'display', 'categorylist', 'displaypdf');
    // set the correct function name based on our input
    if (empty($args['vars'][2])) {
        System::queryStringSetVar('func', 'main');
        $nextvar = 3;
    } elseif ($args['vars'][2] == 'page') {
        System::queryStringSetVar('func', 'main');
        $nextvar = 3;
    } elseif (!in_array($args['vars'][2], $funcs)) {
        System::queryStringSetVar('func', 'display');
        $nextvar = 2;
    } else {
        System::queryStringSetVar('func', $args['vars'][2]);
        $nextvar = 3;
    }

    $func = FormUtil::getPassedValue('func', 'main', 'GET');

    // for now let the core handle the view function
    if (($func == 'view' || $func == 'main') && isset($args['vars'][$nextvar])) {
        System::queryStringSetVar('page', (int)$args['vars'][$nextvar]);
    }

    // add the category info
    if ($func == 'view' && isset($args['vars'][$nextvar])) {
        if ($args['vars'][$nextvar] == 'page') {
            System::queryStringSetVar('page', (int)$args['vars'][$nextvar+1]);
        } else {
            System::queryStringSetVar('prop', $args['vars'][$nextvar]);
            if (isset($args['vars'][$nextvar+1])) {
                $numargs = count($args['vars']);
                if ($args['vars'][$numargs-2] == 'page' && is_numeric($args['vars'][$numargs-1])) {
                    System::queryStringSetVar('cat', (string)implode('/', array_slice($args['vars'], $nextvar+1, -2)));
                    System::queryStringSetVar('page', (int)$args['vars'][$numargs-1]);
                } else {
                    System::queryStringSetVar('cat', (string)implode('/', array_slice($args['vars'], $nextvar+1)));
                    System::queryStringSetVar('page', 1);
                }
            }
        }
    }

    // identify the correct parameter to identify the news article
    if ($func == 'display' || $func == 'displaypdf') {
        // check the permalink structure and obtain any missing vars
        $permalinkkeys = array_flip(explode('/', ModUtil::getVar('News', 'permalinkformat')));
        // get rid of unused vars
        $args['vars'] = array_slice($args['vars'], $nextvar);

        // remove any category path down to the leaf category
        $permalinkkeycount = count($permalinkkeys);
        $varscount = count($args['vars']);
          ($args['vars'][$varscount-2] == 'page') ? $pagersize = 2 : $pagersize = 0 ;
          if (($permalinkkeycount + $pagersize) != $varscount) {
            array_splice($args['vars'], $permalinkkeys['%category%'],  $varscount - $permalinkkeycount);
        }
        
        // get the story id or title
        foreach ($permalinkkeys as $permalinkvar => $permalinkkey) {
             System::queryStringSetVar(str_replace('%', '', $permalinkvar), $args['vars'][$permalinkkey]);
        }

        if (isset($permalinkkeys['%storyid%']) && isset($args['vars'][$permalinkkeys['%storyid%']]) && is_numeric($args['vars'][$permalinkkeys['%storyid%']])) {
            System::queryStringSetVar('sid', $args['vars'][$permalinkkeys['%storyid%']]);
            $nextvar = $permalinkkeys['%storyid%']+1;
        } else {
            System::queryStringSetVar('title', $args['vars'][$permalinkkeys['%storytitle%']]);
            $nextvar = $permalinkkeys['%storytitle%']+1;
        }
        if (isset($args['vars'][$nextvar]) && $args['vars'][$nextvar] == 'page') {
            System::queryStringSetVar('page', (int)$args['vars'][$nextvar+1]);
        }
    }

    // handle news archives
    if ($func == 'archives') {
        if (isset($args['vars'][$nextvar])) {
            System::queryStringSetVar('year', $args['vars'][$nextvar]);
            if (isset($args['vars'][$nextvar+1])) {
                System::queryStringSetVar('month', $args['vars'][$nextvar+1]);
            }
        }
    }

    return true;
}
