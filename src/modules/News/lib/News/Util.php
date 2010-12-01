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
}