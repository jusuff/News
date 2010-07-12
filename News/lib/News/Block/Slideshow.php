<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: slideshow.php 77 2009-02-25 17:33:19Z espaan $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     msshams <ms.shams@gmail.com>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * initialise block
 *
 * @author       The Zikula Development Team
 */
function News_slideshowblock_init()
{
    SecurityUtil::registerPermissionSchema('Slideshowblock::', 'Block ID::');
}

/**
 * get information on block
 *
 * @author       The Zikula Development Team
 * @return       array       The block information
 */
function News_slideshowblock_info()
{
    $dom = ZLanguage::getModuleDomain('News');

    return array('module'          => 'News',
                 'text_type'       => ('slideshow'),
                 'text_type_long'  => ('Display news slideshow'),
                 'allow_multiple'  => false,
                 'form_content'    => false,
                 'form_refresh'    => false,
                 'show_preview'    => true,
                 'admin_tableless' => true);
}

/**
 * display block
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function News_slideshowblock_display($blockinfo)
{
    // security check
    if (!SecurityUtil::checkPermission('Slideshowblock::', $blockinfo['bid'].'::', ACCESS_OVERVIEW)) {
        return;
    }

    $dom = ZLanguage::getModuleDomain('News');

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (!isset($vars['limit'])) {
        $vars['limit'] = 4;
    }

    // work out the paraemters for the api all
    $apiargs = array();
    $apiargs['numitems'] = $vars['limit'];
    $apiargs['status'] = 0;
    $apiargs['ignorecats'] = true;

    if (isset($vars['category']) && !empty($vars['category'])) {
        if (!Loader::loadClass('CategoryUtil') || !Loader::loadClass('CategoryRegistryUtil')) {
            return LogUtil::registerError(__f('Error! Could not load [%s] class.', 'CategoryUtil | CategoryRegistryUtil', $dom));
        }
        $cat = CategoryUtil::getCategoryByID($vars['category']);
        $categories = CategoryUtil::getCategoriesByPath($cat['path'], '', 'path');
        $catstofilter = array();
        foreach ($categories as $category) {
            $catstofilter[] = $category['id'];
        }
        $apiargs['category'] = array('Main' => $catstofilter);
    }
    $apiargs['filterbydate'] = true;

    // call the api
    $items = pnModAPIFunc('News', 'user', 'getall', $apiargs);

    // check for an empty return
    if (empty($items)) {
        return;
    }

    // create the output object
    $render = & pnRender::getInstance('News', false);

    // loop through the items
    $picupload_uploaddir = pnModGetVar('News', 'picupload_uploaddir');
    $picupload_maxpictures = pnModGetVar('News', 'picupload_maxpictures');
    $slideshowoutput = array();
	$count = 0;
    foreach ($items as $item) {
		$count++;
        if ($item['pictures'] > 0) {
            $render->assign('readperm', SecurityUtil::checkPermission('News::', "$item[cr_uid]::$item[sid]", ACCESS_READ));
            $render->assign('count', $count);
            $render->assign('picupload_uploaddir', $picupload_uploaddir);
            $render->assign($item);
            $slideshowoutput[] = $render->fetch('news_block_slideshow_row.htm', $item['sid'], null, false, false);
        }
    }

    // assign the results
    $render->assign('slideshow', $slideshowoutput);
    $render->assign('dom', $dom);

    $blockinfo['content'] = $render->fetch('news_block_slideshow.htm');

    return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function News_slideshowblock_modify($blockinfo)
{
    $dom = ZLanguage::getModuleDomain('News');

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['limit'])) {
        $vars['limit'] = 4;
    }

    // Create output object
    $render = & pnRender::getInstance('News', false);

    // load the categories system
    if (!Loader::loadClass('CategoryRegistryUtil')) {
        return LogUtil::registerError(__f('Error! Could not load [%s] class.'), 'CategoryRegistryUtil', $dom);
    }
    $mainCat = CategoryRegistryUtil::getRegisteredModuleCategory('News', 'news', 'Main', 30); // 30 == /__SYSTEM__/Modules/Global
    $render->assign('mainCategory', $mainCat);
    $render->assign(pnModGetVar('News'));

    // assign the block vars
    $render->assign($vars);
    $render->assign('dom', $dom);

    // Return the output that has been generated by this function
    return $render->fetch('news_block_slideshow_modify.htm');
}

/**
 * update block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function News_slideshowblock_update($blockinfo)
{
    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // alter the corresponding variable
    $vars['category']    = FormUtil::getPassedValue('category', null, 'POST');
    $vars['limit']       = (int)FormUtil::getPassedValue('limit', null, 'POST');

    // write back the new contents
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    // clear the block cache
    $render = & pnRender::getInstance('News');
    $render->clear_cache('news_block_slideshow.htm');

    return $blockinfo;
}
