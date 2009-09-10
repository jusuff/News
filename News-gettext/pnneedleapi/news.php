<?php
// $Id:  $
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Frank Schummertz
// Purpose of file:  MultiHook needle API
// ----------------------------------------------------------------------

/**
 * News needle
 * @param $args['nid'] needle id
 * @return array()
 */
function News_needleapi_news($args)
{
    $mhdom = ZLanguage::getModuleDomain('News');
    // Get arguments from argument array
    $nid = $args['nid'];
    unset($args);
    
    // cache the results
    static $cache;
    if(!isset($cache)) {
        $cache = array();
    } 

    pnModLangLoad('MultiHook', 'news');
    if(!empty($nid)) {
        if(!isset($cache[$nid])) {
            // not in cache array

            if(pnModAvailable('News')) {
                // nid is the sid
                
                $obj = pnModAPIFunc('News', 'user', 'get', array('sid' => $nid));

                if($obj != false) {
                    $url   = DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $nid)));
                    $title = DataUtil::formatForDisplay($obj['title']);
                    $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                } else {
                    $cache[$nid] = '<em>' . DataUtil::formatForDisplay(__('unknown page', $mhdom) . ' (' . $nid . ')') . '</em>';
                }
        
            } else {
                $cache[$nid] = '<em>' . DataUtil::formatForDisplay(__('News not available', $mhdom)) . '</em>';
            }
        }
        $result = $cache[$nid];
    } else {
        $result = '<em>' . DataUtil::formatForDisplay(__('no needle id', $mhdom)) . '</em>';
    }
    return $result;
    
}