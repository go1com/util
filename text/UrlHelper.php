<?php

namespace go1\util\text;

class UrlHelper
{
    protected static $allowedProtocols = ['http', 'https'];

    public static function filterBadProtocol($string) {
        // Get the plain text representation of the attribute value (i.e. its
        // meaning).
        $string = Html::decodeEntities($string);
        return Html::escape(static::stripDangerousProtocols($string));
    }

    public static function stripDangerousProtocols($uri) {
        $allowed_protocols = array_flip(static::$allowedProtocols);

        // Iteratively remove any invalid protocol found.
        do {
            $before = $uri;
            $colonpos = strpos($uri, ':');
            if ($colonpos > 0) {
                // We found a colon, possibly a protocol. Verify.
                $protocol = substr($uri, 0, $colonpos);
                // If a colon is preceded by a slash, question mark or hash, it cannot
                // possibly be part of the URL scheme. This must be a relative URL, which
                // inherits the (safe) protocol of the base document.
                if (preg_match('![/?#]!', $protocol)) {
                    break;
                }
                // Check if this is a disallowed protocol. Per RFC2616, section 3.2.3
                // (URI Comparison) scheme comparison must be case-insensitive.
                if (!isset($allowed_protocols[strtolower($protocol)])) {
                    $uri = substr($uri, $colonpos + 1);
                }
            }
        } while ($before != $uri);

        return $uri;
    }
}
