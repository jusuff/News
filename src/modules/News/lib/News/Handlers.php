<?php

/**
 * post pending content to pending_content Event handler
 *
 * @author Craig Heydenburg
 */
class News_Handlers {

    public static function pendingContent(Zikula_Event $event)
    {
        $dom = ZLanguage::getModuleDomain('News');
        ModUtil::dbInfoLoad('News');
        $count = DBUtil::selectObjectCount('news', 'WHERE pn_published_status=2');
        if ($count > 0) {
            $collection = new Zikula_Collection_Container('News');
            $collection->add(new Zikula_Provider_AggregateItem('submission', _n('News article', 'News articles', $count, $dom), $count, 'admin', 'view', array('news_status'=>2)));
            $event->getSubject()->add($collection);
        }
    }

    public static function getTypes(Zikula_Event $event) {
        $types = $event->getSubject();
        $types->add('News_ContentType_NewsArticles');
    }
}