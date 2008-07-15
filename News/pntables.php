<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pntables.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

/**
 * Populate pntables array for News module
 * 
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the pnModDBInfoLoad() API function.
 * 
 * @author       Xiaoyu Huang
 * @return       array       The table information.
 */
function News_pntables()
{
    // Initialise table array
    $pntable = array();

    // Full table definition
    $pntable['stories'] = DBUtil::getLimitedTablename('stories');
    $pntable['stories_column'] = array ('sid'              => 'pn_sid',
                                        'aid'              => 'pn_cr_uid',   // for back compat
                                        'title'            => 'pn_title',
                                        'urltitle'         => 'pn_urltitle',
                                        'time'             => 'pn_cr_date',  // for back compat
                                        'hometext'         => 'pn_hometext',
                                        'bodytext'         => 'pn_bodytext',
                                        'comments'         => 'pn_comments',
                                        'counter'          => 'pn_counter',
                                        'informant'        => 'pn_informant',
                                        'notes'            => 'pn_notes',
                                        'ihome'            => 'pn_ihome',
                                        'themeoverride'    => 'pn_themeoverride',
                                        'alanguage'        => 'pn_language',
                                        'language'         => 'pn_language',
                                        'withcomm'         => 'pn_withcomm',
                                        'format_type'      => 'pn_format_type',
                                        'published_status' => 'pn_published_status',
                                        'from'             => 'pn_from',
                                        'to'               => 'pn_to');
    $pntable['stories_column_def'] = array('sid'              => 'I NOTNULL AUTO PRIMARY',
                                           'title'            => 'C(255) DEFAULT NULL',
                                           'urltitle'         => 'C(255) DEFAULT NULL',
                                           'hometext'         => 'X',
                                           'bodytext'         => 'X NOTNULL',
                                           'comments'         => "I DEFAULT '0'",
                                           'counter'          => 'I DEFAULT NULL',
                                           'informant'        => "C(20) NOTNULL DEFAULT ''",
                                           'notes'            => "X NOTNULL",
                                           'ihome'            => "I1 NOTNULL DEFAULT '0'",
                                           'themeoverride'    => "C(30) NOTNULL DEFAULT ''",
                                           'language'         => "C(30) NOTNULL DEFAULT ''",
                                           'withcomm'         => "I1 NOTNULL DEFAULT '0'",
                                           'format_type'      => "I1 NOTNULL DEFAULT '0'",
                                           'published_status' => "I1 DEFAULT '0'",
                                           'from'             => 'T DEFAULT NULL',
                                           'to'               => 'T DEFAULT NULL');

    // Enable categorization services
    $pntable['stories_db_extra_enable_categorization'] = pnModGetVar('News', 'enablecategorization');
    $pntable['stories_primary_key_column'] = 'sid';

    // add standard data fields
    ObjectUtil::addStandardFieldsToTableDefinition ($pntable['stories_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['stories_column_def']);

    // old comments table for upgrade
    $pntable['comments'] = DBUtil::getLimitedTablename('comments');
    $pntable['autonews'] = DBUtil::getLimitedTablename('autonews');
    $pntable['queue'] = DBUtil::getLimitedTablename('queue');
    $pntable['stories_cat'] = DBUtil::getLimitedTablename('stories_cat');
    $pntable['topics'] = DBUtil::getLimitedTablename('topics');

    return $pntable;
}
