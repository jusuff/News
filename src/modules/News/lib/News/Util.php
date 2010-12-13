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
     * @param object $controller controller object
     * @param array $story article array
     * @return boolean
     */
    public static function validateArticle($controller, $story) {

        // Validate the input
        $validationerror = false;
        if ($story['action'] != 0 && empty($story['title'])) {
            $validationerror = $controller->__f('Error! You did not enter a %s.', $controller->__('title'));
        }
        // both text fields can't be empty
        if ($story['action'] != 0 && empty($story['hometext']) && empty($story['bodytext'])) {
            $validationerror = $controller->__f('Error! You did not enter the minimum necessary %s.', $controller->__('article content'));
        }
        // validate hook data
        $sid = isset($story['sid']) ? $story['sid'] : null;
        $eventargs = array(
            'id' => $sid,
            'caller' => 'News',
            'controller' => $controller);
        $event = new Zikula_Event('news.hook.articles.validate.edit', $story, $eventargs, new Zikula_Collection_HookValidationProviders());
        $validators = EventUtil::notify($event)->getData();
        if ($validators->hasErrors()) {
            $validationerror = $controller->__('Error! You did not enter hooked data correctly.');
        }

        return $validationerror;
    }

}