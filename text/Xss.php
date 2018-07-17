<?php

namespace go1\util\text;

class Xss
{
    protected static $htmlTags = ['a', 'em', 'strong', 'cite', 'blockquote', 'code', 'ul', 'ol', 'li', 'dl', 'dt', 'dd'];

    public static function filter($string, array $htmlTags = null)
    {
        if (is_null($htmlTags)) {
            $htmlTags = static::$htmlTags;
        }

        // Only operate on valid UTF-8 strings. This is necessary to prevent cross
        // site scripting issues on Internet Explorer 6.
        if (!Unicode::validateUtf8($string)) {
            return '';
        }

        // Remove NULL characters (ignored by some browsers).
        $string = str_replace(chr(0), '', $string);
        // Remove Netscape 4 JS entities.
        $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
        $string = str_replace('&', '&amp;', $string); # Defuse all HTML entities.
        // Change back only well-formed entities in our whitelist:
        // Decimal numeric entities.
        $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
        $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string); # Hexadecimal numeric entities.
        $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string); # Named entities.
        $htmlTags = array_flip($htmlTags);
        // Late static binding does not work inside anonymous functions.
        $class = get_called_class();
        $splitter = function ($matches) use ($htmlTags, $class) {
            return $class::split($matches[1], $htmlTags, $class);
        };

        // Strip any tags that are not in the whitelist.
        return preg_replace_callback('%
      (
      <(?=[^a-zA-Z!/])  # a lone <
      |                 # or
      <!--.*?-->        # a comment
      |                 # or
      <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
      |                 # or
      >                 # just a >
      )%x', $splitter, $string);
    }

    protected static function split($string, $html_tags, $class)
    {
        if (substr($string, 0, 1) != '<') {
            // We matched a lone ">" character.
            return '&gt;';
        }

        if (strlen($string) == 1) {
            return '&lt;'; # We matched a lone "<" character.
        }

        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9\-]+)\s*([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
            // Seriously malformed.
            return '';
        }

        $slash = trim($matches[1]);
        $elem = &$matches[2];
        $attrList = &$matches[3];
        $comment = &$matches[4];

        if ($comment) {
            $elem = '!--';
        }

        // When in whitelist mode, an element is disallowed when not listed.
        if ($class::needsRemoval($html_tags, $elem)) {
            return '';
        }

        if ($comment) {
            return $comment;
        }

        if ($slash != '') {
            return "</$elem>";
        }

        // Is there a closing XHTML slash at the end of the attributes?
        $attrList = preg_replace('%(\s?)/\s*$%', '\1', $attrList, -1, $count);
        $xhtml_slash = $count ? ' /' : '';

        // Clean up attributes.
        $attr2 = implode(' ', $class::attributes($attrList));
        $attr2 = preg_replace('/[<>]/', '', $attr2);
        $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

        return "<$elem$attr2$xhtml_slash>";
    }

    protected static function needsRemoval($html_tags, $elem)
    {
        return !isset($html_tags[strtolower($elem)]);
    }

    protected static function attributes($attributes)
    {
        $attributesArray = [];
        $mode = 0;
        $attributeName = '';
        $skip = false;
        $skipProtocolFiltering = false;

        while (strlen($attributes) != 0) {
            $working = 0; # Was the last operation successful?

            switch ($mode) {
                case 0:
                    // Attribute name, href for instance.
                    if (preg_match('/^([-a-zA-Z][-a-zA-Z0-9]*)/', $attributes, $match)) {
                        $attributeName = strtolower($match[1]);
                        $skip = ($attributeName == 'style' || substr($attributeName, 0, 2) == 'on');

                        // Values for attributes of type URI should be filtered for
                        // potentially malicious protocols (for example, an href-attribute
                        // starting with "javascript:"). However, for some non-URI
                        // attributes performing this filtering causes valid and safe data
                        // to be mangled. We prevent this by skipping protocol filtering on
                        // such attributes.
                        // @see \Drupal\Component\Utility\UrlHelper::filterBadProtocol()
                        // @see http://www.w3.org/TR/html4/index/attributes.html
                        $skipProtocolFiltering = substr($attributeName, 0, 5) === 'data-' || in_array($attributeName, [
                                'title',
                                'alt',
                                'rel',
                                'property',
                            ]);

                        $working = $mode = 1;
                        $attributes = preg_replace('/^[-a-zA-Z][-a-zA-Z0-9]*/', '', $attributes);
                    }
                    break;

                case 1:
                    // Equals sign or valueless ("selected").
                    if (preg_match('/^\s*=\s*/', $attributes)) {
                        $working = 1;
                        $mode = 2;
                        $attributes = preg_replace('/^\s*=\s*/', '', $attributes);
                        break;
                    }

                    if (preg_match('/^\s+/', $attributes)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $attributesArray[] = $attributeName;
                        }
                        $attributes = preg_replace('/^\s+/', '', $attributes);
                    }
                    break;

                case 2:
                    // Attribute value, a URL after href= for instance.
                    if (preg_match('/^"([^"]*)"(\s+|$)/', $attributes, $match)) {
                        $thisval = $skipProtocolFiltering ? $match[1] : UrlHelper::filterBadProtocol($match[1]);

                        if (!$skip) {
                            $attributesArray[] = "$attributeName=\"$thisval\"";
                        }
                        $working = 1;
                        $mode = 0;
                        $attributes = preg_replace('/^"[^"]*"(\s+|$)/', '', $attributes);
                        break;
                    }

                    if (preg_match("/^'([^']*)'(\s+|$)/", $attributes, $match)) {
                        $thisval = $skipProtocolFiltering ? $match[1] : UrlHelper::filterBadProtocol($match[1]);

                        if (!$skip) {
                            $attributesArray[] = "$attributeName='$thisval'";
                        }
                        $working = 1;
                        $mode = 0;
                        $attributes = preg_replace("/^'[^']*'(\s+|$)/", '', $attributes);
                        break;
                    }

                    if (preg_match("%^([^\s\"']+)(\s+|$)%", $attributes, $match)) {
                        $thisval = $skipProtocolFiltering ? $match[1] : UrlHelper::filterBadProtocol($match[1]);

                        if (!$skip) {
                            $attributesArray[] = "$attributeName=\"$thisval\"";
                        }
                        $working = 1;
                        $mode = 0;
                        $attributes = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attributes);
                    }
                    break;
            }

            if ($working == 0) {
                // Not well formed; remove and try again.
                $attributes = preg_replace('/
          ^
          (
          "[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
          |               # or
          \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
          |               # or
          \S              # - a non-whitespace character
          )*              # any number of the above three
          \s*             # any number of whitespaces
          /x', '', $attributes);
                $mode = 0;
            }
        }

        // The attribute list ends with a valueless attribute like "selected".
        if ($mode == 1 && !$skip) {
            $attributesArray[] = $attributeName;
        }

        return $attributesArray;
    }
}
