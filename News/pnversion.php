<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: pnversion.php 75 2009-02-24 04:51:52Z mateo $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

$modversion['name']        = 'News';
$modversion['displayname'] = _NEWS_DISPLAYNAME;
$modversion['description'] = _NEWS_DESCRIPTION;
$modversion['version']     = '2.4';

$modversion['credits']     = 'pndocs/credits.txt';
$modversion['help']        = 'pndocs/install.txt';
$modversion['changelog']   = 'pndocs/changelog.txt';
$modversion['license']     = 'pndocs/license.txt';
$modversion['official']    = 1;
$modversion['author']      = 'Mark West, Mateo Tibaquirá, Erik Spaan';
$modversion['contact']     = 'http://code.zikula.org/news';

$modversion['securityschema'] = array('Stories::Story' => 'Author ID::Story ID');
