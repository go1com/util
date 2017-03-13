<?php

namespace go1\util\text;

class Html
{
    public static function decodeEntities($text) {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
