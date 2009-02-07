<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pninit.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage News
*/

/**
 * initialise the News module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance.
 * This function MUST exist in the pninit file for a module
 *
 * @author       Xiaoyu Huang
 * @return       bool       true on success, false otherwise
 */
function News_init()
{
    // Create table
    if (!DBUtil::createTable('stories')) {
        return false;
    }

    // create our default category
    if (!_news_createdefaultcategory()) {
        return LogUtil::registerError (_CREATEFAILED);
    }


    // Set up config variables
    pnModSetVar('News', 'storyhome', 10);
    pnModSetVar('News', 'storyorder', 0);
    pnModSetVar('News', 'itemsperpage', '25');
    pnModSetVar('News', 'permalinkformat', '%year%/%monthnum%/%day%/%storytitle%');
    pnModSetVar('News', 'enablecategorization', true);

    // Initialisation successful
    return true;
}

/**
 * upgrade the News module from an old version
 *
 * This function can be called multiple times
 * This function MUST exist in the pninit file for a module
 *
 * @author       Xiaoyu Huang
 * @return       bool       true on success, false otherwise
 */
function News_upgrade($oldversion)
{
    // upgrade table
    if (!DBUtil::changeTable('stories')) {
        return false;
    }

    // Upgrade dependent on old version number
    switch($oldversion) {
        case 1.3:
        case 1.4:
            pnModSetVar('News', 'storyhome', pnConfigGetVar('storyhome'));
            pnConfigDelVar('storyhome');
            pnModSetVar('News', 'storyorder', pnConfigGetVar('storyorder'));
            pnConfigDelVar('storyorder');
            pnModSetVar('News', 'itemsperpage', 25);
            return News_upgrade(1.5);
        case 1.5:
            $tables = pnDBGetTables();
            $shorturlsep = pnConfigGetVar('shorturlsseparator');			
            // move the data from the author uid to creator and updator uid
            $sqls[] = "UPDATE $tables[stories] SET pn_cr_uid = pn_aid";
            $sqls[] = "UPDATE $tables[stories] SET pn_lu_uid = pn_aid";
            // move the data from the time field to the creation and update datestamp
            $sqls[] = "UPDATE $tables[stories] SET pn_cr_date = pn_time";
            $sqls[] = "UPDATE $tables[stories] SET pn_lu_date = pn_time";
            $sqls[] = "UPDATE $tables[stories] SET pn_urltitle = REPLACE(pn_title, ' ', '{$shorturlsep}')";
            foreach ($sqls as $sql) {
                if (!DBUtil::executeSQL($sql)) {
                    return LogUtil::registerError (_UPDATETABLEFAILED);
                }
            }
            // drop the old columns
            DBUtil::dropColumn('stories', array('pn_aid'));
            DBUtil::dropColumn('stories', array('pn_time'));
            pnModSetVar('News', 'permalinkformat', '%year%/%monthnum%/%day%/%storytitle%');
            return News_upgrade(2.0);
        case 2.0:
            // migrate the comments to ezcomments
            if (pnModAvailable('Comments')) {
                // check for the ezcomments module
                if (!pnModAvailable('EZComments')) {
                    return LogUtil::registerError (pnML('_MODULENOTAVAILABLE', array('m' => 'EZComments')));
                }
                //  drop the comments table if successful
                if (pnModAPIFunc('EZComments', 'migrate', 'news')) {
                    // drop table
                    if (!DBUtil::dropTable('comments')) {
                        return LogUtil::registerError (_DELETETABLEFAILED);
                    }
                    // remove the Comments module
                    pnModAPIFunc('Modules', 'admin', 'remove', array('id' => pnModGetIDFromName('Comments')));
                }
            }
            // drop table
            if (!DBUtil::dropTable('autonews')) {
                return LogUtil::registerError (_DELETETABLEFAILED);
            }
            if (!DBUtil::dropTable('queue')) {
                return LogUtil::registerError (_DELETETABLEFAILED);
            }
            // remove the AddStory and Submit_News modules
            pnModAPIFunc('Modules', 'admin', 'remove', array('id' => pnModGetIDFromName('AddStory')));
            pnModAPIFunc('Modules', 'admin', 'remove', array('id' => pnModGetIDFromName('Submit_News')));
            return News_upgrade(2.1);
        case 2.1:
            pnModSetVar('News', 'enablecategorization', true);
            pnModDBInfoLoad('News', 'News', true);

            if (!_news_migratecategories()) {
                return LogUtil::registerError (_UPDATEFAILED);
            }
            return News_upgrade(2.2);
    }

    // Update successful
    return true;
}

/**
 * delete the News module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * This function MUST exist in the pninit file for a module
 *
 * @author       Xiaoyu Huang
 * @return       bool       true on success, false otherwise
 */
function News_delete()
{
    // drop table
    if (!DBUtil::dropTable('stories')) {
        return false;
    }

    // Delete module variables
    pnModDelVar('News');

    // Delete entries from category registry 
    pnModDBInfoLoad ('Categories');
    Loader::loadArrayClassFromModule('Categories', 'CategoryRegistry');
    $registry = new PNCategoryRegistryArray();
    $registry->deleteWhere ('crg_modname=\'News\'');

    // Deletion successful
    return true;
}

/**
 * migrate old local categories to the categories module
 */
function _news_migratecategories()
{
    // load the admin language file
    // pull all data from the old tables
    $prefix = pnConfigGetVar('prefix');
    $sql = "SELECT pn_catid, pn_title FROM {$prefix}_stories_cat";
    $result = DBUtil::executeSQL($sql);
    $categories = array(array(0, 'Articles'));
    for (; !$result->EOF; $result->MoveNext()) {
        $categories[] = $result->fields;
    }
    $sql = "SELECT pn_topicid, pn_topicname, pn_topicimage, pn_topictext FROM {$prefix}_topics";
    $result = DBUtil::executeSQL($sql);
    $topics = array();
    for (; !$result->EOF; $result->MoveNext()) {
        $topics[] = $result->fields;
    }

    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $lang = pnUserGetLang();

    // create the Main category and entry in the categories registry
    _news_createdefaultcategory('/__SYSTEM__/Modules/News');

    // create the Topics category and entry in the categories registry
    _news_createtopicscategory('/__SYSTEM__/Modules/Topics');

    // get the category path for which we're going to insert our upgraded News categories
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/News');

    // migrate our main categories
    $categorymap = array();
    foreach ($categories as $category) {
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', $category[1]);
        $cat->setDataField('display_name', array($lang => $category[1]));
        $cat->setDataField('display_desc', array($lang => $category[1]));
        if (!$cat->validate('admin')) {
            return false;
        }
        $cat->insert();
        $cat->update();
        $categorymap[$category[0]] = $cat->getDataField('id');
    }

    // get the category path for which we're going to insert our upgraded Topics categories
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Topics');

    // migrate our topic categories
    $topicsmap = array();
    foreach ($topics as $topic) {
        $cat = new PNCategory ();
        $data = $cat->getData();
        $data['parent_id']                     = $rootcat['id'];
        $data['name']                          = $topic[1];
        $data['value']                         = -1;
        $data['display_name']                  = array($lang => $topic[3]);
        $data['display_desc']                  = array($lang => $topic[3]);
        $data['__ATTRIBUTES__']['topic_image'] = $topic[2];
        $cat->setData ($data);
        if (!$cat->validate('admin')) {
            return false;
        }
        $cat->insert();
        $cat->update();
        $topicsmap[$topic[0]] = $cat->getDataField('id');
    }

    // After an upgrade we want the legacy topic template variables to point to the Topic property
    pnModSetVar('News', 'topicproperty', 'Topic');

    // migrate page category assignments
    $sql = "SELECT pn_sid, pn_catid, pn_topic FROM {$prefix}_stories";
    $result = DBUtil::executeSQL($sql);
    $pages = array();
    for (; !$result->EOF; $result->MoveNext()) {
        $pages[] = array('sid' => $result->fields[0],
                         '__CATEGORIES__' => array('Main' => $categorymap[$result->fields[1]],
                                                   'Topic' => $topicsmap[$result->fields[2]]),
                         '__META__' => array('module' => 'News'));
    }
    foreach ($pages as $page) {
        if (!DBUtil::updateObject($page, 'stories', '', 'sid')) {
            return LogUtil::registerError (_UPDATEFAILED);
        }
    }

    // drop old table
    DBUtil::dropTable('stories_cat');
    // we don't drop the topics table - this is the job of the topics module

    // finally drop the secid column
    DBUtil::dropColumn('stories', 'pn_catid');
    DBUtil::dropColumn('stories', 'pn_topic');

    return true;
}

function _news_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
{
    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $lang = pnUserGetLang();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $nCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/News');

    if (!$nCat) {
        // create placeholder for all our migrated categories
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'News');
        $cat->setDataField('display_name', array($lang => _NEWS_NAME));
        $cat->setDataField('display_desc', array($lang => _NEWS_DESCRIPTION));
        if (!$cat->validate('admin')) {
            return false;
        }
        $cat->insert();
        $cat->update();
    }

    // get the category path for which we're going to insert our upgraded News categories
    $rootcat = CategoryUtil::getCategoryByPath($regpath);
    if ($rootcat) {
        // create an entry in the categories registry to the Main property
        $registry = new PNCategoryRegistry();
        $registry->setDataField('modname', 'News');
        $registry->setDataField('table', 'stories');
        $registry->setDataField('property', 'Main');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    } else {
        return false;
    }

    return true;
}

function _news_createtopicscategory($regpath = '/__SYSTEM__/Modules/Topics')
{
    // get the language file
    $lang = pnUserGetLang();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');

    // create placeholder for all the migrated topics
    $tCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Topics');

    if (!$tCat) {
        // create placeholder for all our migrated categories
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'Topics');
        // pnModLangLoad doesn't handle type 1 modules
        //pnModLangLoad('Topics', 'version'); 
        Loader::includeOnce("modules/Topics/lang/{$lang}/version.php");
        $cat->setDataField('display_name', array($lang => _TOPICS_DISPLAYNAME));
        $cat->setDataField('display_desc', array($lang => _TOPICS_DESCRIPTION));
        if (!$cat->validate('admin')) {
            return false;
        }
        $cat->insert();
        $cat->update();
    }

    // get the category path for which we're going to insert our upgraded News categories
    $rootcat = CategoryUtil::getCategoryByPath($regpath);
    if ($rootcat) {
        // create an entry in the categories registry to the Topic property
        $registry = new PNCategoryRegistry();
        $registry->setDataField('modname', 'News');
        $registry->setDataField('table', 'stories');
        $registry->setDataField('property', 'Topic');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    } else {
        return false;
    }

    return true;
}