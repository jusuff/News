<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: pninit.php 81 2009-02-25 17:57:20Z espaan $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West <mark@zikula.org>
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */

/**
 * initialise the News module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance.
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
    pnModSetVar('News', 'storyorder', 1);
    pnModSetVar('News', 'itemsperpage', 25);
    pnModSetVar('News', 'permalinkformat', '%year%/%monthnum%/%day%/%storytitle%');
    pnModSetVar('News', 'enablecategorization', true);
    pnModSetVar('News', 'refereronprint', 0);
    pnModSetVar('News', 'enableattribution', false);

    // Initialisation successful
    return true;
}

/**
 * upgrade the News module from an old version
 *
 * This function can be called multiple times
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
            // import autonews and queue articles
            if (!_news_import_autonews_queue()) {
                return LogUtil::registerError (_UPDATEFAILED);
            }
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
            // drop the autonews and queue tables, articles are already imported
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
        case 2.2:
            pnModSetVar('News', 'refereronprint', pnConfigGetVar('refereronprint', 0));
            return News_upgrade(2.3);
        case 2.3:
            $tables = pnDBGetTables();
            // when from is not set, put it to the creation date
            $sqls[] = "UPDATE $tables[stories] SET pn_from = pn_cr_date WHERE pn_from IS NULL";
            // make sure we dont have an NULL hometext, since the tables permitted this before 2.4
            $sqls[] = "UPDATE $tables[stories] SET pn_hometext = '' WHERE pn_hometext IS NULL";
            foreach ($sqls as $sql) {
                if (!DBUtil::executeSQL($sql)) {
                    return LogUtil::registerError (_UPDATETABLEFAILED);
                }
            }
            pnModSetVar('News', 'enableattribution', false);
            // drop old legacy columns
            DBUtil::dropColumn('stories', array('pn_comments', 'pn_themeoverride'));
            return News_upgrade(2.4);
    }

    // Update successful
    return true;
}

/**
 * delete the News module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
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

/**
 * create the default category tree
 */
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

/**
 * create the Topics category tree
 */
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

/**
 * Import autonews and queue into stories
 */
function _news_import_autonews_queue()
{
    // pull all data from the autonews table and import into stories
    $prefix = pnConfigGetVar('prefix');
    $sql = "SELECT * FROM {$prefix}_autonews";
    $result = DBUtil::executeSQL($sql);
    $i = 0;
    for(; !$result->EOF; $result->MoveNext()) {
        list ( $obj['anid'],
               $obj['catid'],
               $obj['aid'],
               $obj['title'],
               $obj['time'],
               $obj['hometext'],
               $obj['bodytext'],
               $obj['topic'],
               $obj['informant'],
               $obj['notes'],
               $obj['ihome'],
               $obj['alanguage'],
               $obj['language'],
               $obj['withcomm']) = $result->fields;
    
        // set creation date and from to the time set in autonews
        $objj = array('cid'           => $obj['catid'],
                      'catid'         => $obj['catid'],
                      'aid'           => $obj['aid'],
                      'title'         => $obj['title'],
                      'time'          => $obj['time'],
                      'hometext'      => $obj['hometext'],
                      'bodytext'      => $obj['bodytext'],
                      'comments'      => 0,
                      'counter'       => 0,
                      'topic'         => $obj['topic'],
                      'informant'     => $obj['informant'],
                      'notes'         => $obj['notes'],
                      'ihome'         => $obj['ihome'],
                      'themeoverride' => '',
                      'alanguage'     => $obj['alanguage'],
                      'language'      => $obj['language'],
                      'withcomm'      => $obj['withcomm'],
                      'format_type'   => 0,
                      'from'          => $obj['time']);

        $ende = DBUtil::insertObject($objj, 'stories');
        $i++;
    }
    $result->Close();

    // pull all data from the queue table and import into stories
    $sql = "SELECT * FROM {$prefix}_queue";
    $result = DBUtil::executeSQL($sql);
    $i = 0;
    for(; !$result->EOF; $result->MoveNext()) {

        list ( $obj['qid'],
               $obj['uid'],
               $obj['arcd'],
               $obj['uname'],
               $obj['subject'],
               $obj['story'],
               $obj['timestamp'],
               $obj['topic'],
               $obj['alanguage'],
               $obj['language'],
               $obj['bodytext']) = $result->fields;
    
        // set published status to pending besides the regular fields
        $objj = array('cid'           => 0,
                      'catid'         => 0,
                      'aid'           => $obj['uid'],
                      'title'         => $obj['subject'],
                      'time'          => $obj['timestamp'],
                      'hometext'      => $obj['story'],
                      'bodytext'      => $obj['bodytext'],
                      'comments'      => 0,
                      'counter'       => 0,
                      'topic'         => $obj['topic'],
                      'informant'     => $obj['uname'],
                      'notes'         => '',
                      'ihome'         => 0,
                      'themeoverride' => '',
                      'alanguage'     => $obj['alanguage'],
                      'language'      => $obj['language'],
                      'withcomm'      => 0,
                      'format_type'   => 0,
                      'published_status' => 2);

        $ende = DBUtil::insertObject($objj, 'stories');
        $i++;
    }
    $result->Close();
    
    return true;
}