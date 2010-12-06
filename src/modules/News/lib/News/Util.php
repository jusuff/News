<?php

class News_Util {

    /**
     * Reformat the attributes array
     * from {0 => {name => '...', value => '...'}} to {name => value}
     *
     * @param array $incoming Attribute array indexed by integer
     * @return array attribute array indexed by name
     */
    public static function reformatAttributes($incoming) {
        $attributes = array();
        foreach ($incoming as $attr) {
            if (!empty($attr['name']) && !empty($attr['value'])) {
                $attributes[$attr['name']] = $attr['value'];
            }
        }
        return $attributes;
    }

    /**
     * Validate article data
     *
     * @param array $story
     * @return boolean
     */
    public static function validateArticle($story) {
        $dom = ZLanguage::getModuleDomain('News');

        // Validate the input
        $validationerror = false;
        if ($story['action'] != 0 && empty($story['title'])) {
            $validationerror = __f('Error! You did not enter a %s.', __('title', $dom), $dom);
        }
        // both text fields can't be empty
        if ($story['action'] != 0 && empty($story['hometext']) && empty($story['bodytext'])) {
            $validationerror = __f('Error! You did not enter the minimum necessary %s.', __('article content', $dom), $dom);
        }
        // validate hook data
        $sid = isset($story['sid']) ? $story['sid'] : null;
        $event = new Zikula_Event('news.hook.articles.validate.edit', $story, array('id' => $sid, 'module' => 'News'), new Zikula_Collection_HookValidationProviders());
        $validators = EventUtil::notify($event)->getData();
        if ($validators->hasErrors()) {
            $validationerror = __('Error! You did not enter hooked data correctly.', $dom);
        }

        return $validationerror;
    }
}