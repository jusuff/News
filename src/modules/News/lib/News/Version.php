<?php

/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Mark West [markwest]
 * @author     Mateo Tibaquira [mateo]
 * @author     Erik Spaan [espaan]
 * @category   Zikula_3rdParty_Modules
 * @package    Content_Management
 * @subpackage News
 */
class News_Version extends Zikula_Version
{

    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('News publisher');
        $meta['description'] = $this->__('Provides the ability to publish and manage news articles contributed by site users, with support for news categories and various associated blocks.');
        $meta['version'] = '3.0.1';
        //! this defines the module's url
        $meta['url'] = $this->__('news');
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        $meta['securityschema'] = array('News::' => 'Contributor ID::Article ID',
                'News:pictureupload:' => '::',
                'News:publicationdetails:' => '::');
        return $meta;
    }

    protected function setupHookBundles()
    {
        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.news.articles', $this->__('News Articles Hooks'));
        $bundle->addType('ui.view', 'news.hook.articles.ui.view');
        $bundle->addType('ui.edit', 'news.hook.articles.ui.edit');
        $bundle->addType('ui.delete', 'news.hook.articles.ui.delete');
        $bundle->addType('validate.edit', 'news.hook.articles.validate.edit');
        $bundle->addType('validate.delete', 'news.hook.articles.validate.delete');
        $bundle->addType('process.edit', 'news.hook.articles.process.edit');
        $bundle->addType('process.delete', 'news.hook.articles.process.delete');
        $bundle->addType('config.ui.edit', 'news.hook.articles.config.ui.edit');
        $bundle->addType('config.process.edit', 'news.hook.articles.config.process.edit');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.news.articlesfilter', $this->__('News Display Hooks'));
        $bundle->addType('ui.filter', 'news.hook.articlesfilter.ui.filter');
        $this->registerHookSubscriberBundle($bundle);
    }

}