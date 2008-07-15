<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: function.nextpostlink.php 22238 2007-06-18 16:43:33Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

/**
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package Zikula_Value_Addons
 * @subpackage News
 * @version $Id: function.nextpostlink.php 22238 2007-06-18 16:43:33Z markwest $
 * @author The Zikula development team
 * @link http://www.zikula.org The Zikula Home Page
 * @copyright Copyright (C) 2002 by the Zikula Development Team
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Smarty function to display a link to the next post
 *
 * Example
 * <!--[nextpostlink sid=$info.sid layout='%link% <span class="meta-nav">&raquo;</span>']-->
 *
 * @author Mark West
 * @since 20/10/03
 * @see function.nextpostlink.php::smarty_function_nextpostlink()
 * @param array $params All attributes passed to this function from the template
 * @param object &$smarty Reference to the Smarty object
 * @param integer $sid article id
 * @param string $layout HTML string in which to insert link
 * @return string the results of the module function
 */
function smarty_function_nextpostlink($params, &$smarty)
{
    if (!isset($params['sid'])) {
        // get the info template var
        $info = $smarty->get_template_vars('info');
        $params['sid'] = $info['sid'];
    }
    if (!isset($params['layout'])) {
        $params['layout'] = '';
    }

    $article = pnModAPIFunc('News', 'user', 'getall', array('query' => "pn_sid > $params[sid]", 'order' => 'ASC'));
    if (!$article) {
        return;
    }

    $articlelink = '<a href="'.DataUtil::formatForDisplay(pnModURL('News', 'user', 'display', array('sid' => $article[0]['sid']))).'">'.DataUtil::formatForDisplay($article[0]['title']).'</a>';
    $articlelink = str_replace('%link%', $articlelink, $params['layout']);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $articlelink);
    } else {
        return $articlelink;
    }
}
