<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnversion.php 24821 2008-11-05 16:01:47Z Guite $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

$modversion['name'] = 'News';
$modversion['displayname'] = _NEWS_DISPLAYNAME;
$modversion['description'] = _NEWS_DESCRIPTION;
$modversion['version'] = '2.2';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/install.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Mark West';
$modversion['contact'] = 'http://www.markwest.me.uk/';
$modversion['securityschema'] = array('Stories::Story' => 'Author ID::Story ID');
