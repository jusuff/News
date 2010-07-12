<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: past.php 75 2009-02-24 04:51:52Z mateo $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * initialise block
 *
 * @author       The Zikula Development Team
 */
function News_pastblock_init()
{
    SecurityUtil::registerPermissionSchema('Pastblock::', 'Block ID::');
}

/**
 * get information on block
 *
 * @author       The Zikula Development Team
 * @return       array       The block information
 */
function News_pastblock_info()
{
    $dom = ZLanguage::getModuleDomain('News');

    return array('module'         => 'News',
                 'text_type'      => __('Past articles', $dom),
                 'text_type_long' => __('Display past articles', $dom),
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true);
}

/**
 * display block
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function News_pastblock_display($blockinfo)
{
    // security check
    if (!SecurityUtil::checkPermission('Pastblock::', "$blockinfo[bid]::", ACCESS_READ)) {
        return;
    }

    $dom = ZLanguage::getModuleDomain('News');

    // get the number of stories shown on the frontpage
    $storyhome = ModUtil::getVar('News', 'storyhome', 10);

    // Break out options from our content field
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['limit'])) {
        $vars['limit'] = 10;
    }

    // call the API
    $articles = ModUtil::apiFunc('News', 'user', 'getall',
                             array('hideonindex'    => 0,
                                   'order'    => 'from',
                                   'status'   => 0,
                                   'startnum' => $storyhome,
                                   'numitems' => $vars['limit']));

    if ($articles === false) {
        return;
    }

    // loop round the return articles grouping by date
    $count        = 0;
    $news         = array();
    $newscumul    = array();
    $limitreached = false;
    foreach ($articles as $article)
    {
        $info  = ModUtil::apiFunc('News', 'user', 'getArticleInfo', $article);
        $links = ModUtil::apiFunc('News', 'user', 'getArticleLinks', $info);
        if (SecurityUtil::checkPermission('News::', "$info[cr_uid]::$info[sid]", ACCESS_READ)) {
            $preformat['title'] = "<a href=\"$links[fullarticle]\">$info[title]</a>";
        } else {
            $preformat['title'] = $info['title'];
        }

        $daydate = DateUtil::formatDatetime(strtotime($info['from']), '%Y-%m-%d');

        // Reset the time
        if (!isset($currentday)) {
            $currentday = $daydate;
        }

        // If it's a different date, save the cumul and continue
        if ($currentday != $daydate) {
            $news[$currentday] = $newscumul;
            $newscumul = array();
            $currentday = $daydate;
        }
        $newscumul[] = array('info'      => $info,
                             'links'     => $links,
                             'preformat' => $preformat);
    }

    if (!isset($news[$currentday])) {
        $news[$currentday] = $newscumul;
    }

    $render = Zikula_View::getInstance('News');
    $render->assign('news', $news);

    $render->assign('dom', $dom);

    if (empty($blockinfo['title'])) {
        //! default past block title
        $blockinfo['title'] = __('Past articles', $dom);
    }

    $blockinfo['content'] = $render->fetch('news_block_past.htm');

    return BlockUtil::themeBlock($blockinfo);
}

/**
 * modify block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function News_pastblock_modify($blockinfo)
{
    $dom = ZLanguage::getModuleDomain('News');

    // Break out options from our content field
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['limit'])) {
        $vars['limit'] = 10;
    }

    // Create output object
    $render = Zikula_View::getInstance('News');

    // As Admin output changes often, we do not want caching.
    $render->caching = false;

    // assign the approriate values
    $render->assign($vars);

    $render->assign('dom', $dom);

    // Return the output that has been generated by this function
    return $render->fetch('news_block_past_modify.htm');
}

/**
 * update block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function News_pastblock_update($blockinfo)
{
    // Get current content
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

    // alter the corresponding variable
    $vars['limit'] = (int)FormUtil::getPassedValue('limit', null, 'POST');

    // write back the new contents
    $blockinfo['content'] = BlockUtil::varsToContent($vars);

    // clear the block cache
    $render = Zikula_View::getInstance('News');
    $render->clear_cache('news_block_past.htm');

    return $blockinfo;
}
