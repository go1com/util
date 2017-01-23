<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class LoHelper
{
    public static function load(Connection $db, int $id)
    {
        return ($learningObjects = static::loadMultiple($db, [$id]))
            ? $learningObjects[0]
            : false;
    }

    /**
     * @param Connection $db
     * @param  []int      $ids
     * @return []stdClass
     */
    public static function loadMultiple(Connection $db, array $ids): array
    {
        $learningObjects = !$ids ? [] : $db
            ->executeQuery(
                'SELECT lo.*, pricing.price, pricing.currency, pricing.tax'
                . ' FROM gc_lo lo'
                . ' LEFT JOIN gc_lo_pricing pricing ON lo.id = pricing.id'
                . ' WHERE lo.id IN (?)',
                [$ids],
                [DB::INTEGERS]
            )
            ->fetchAll(DB::OBJ);

        foreach ($learningObjects as &$lo) {
            if (!$lo->data = json_decode($lo->data)) {
                unset($lo->data);
            }

            $lo->pricing = (object) [
                'price'    => $lo->price ? (float) $lo->price : 0.00,
                'currency' => $lo->currency ?: 'USD',
                'tax'      => $lo->tax ? (float) $lo->tax : 0.00,
            ];
            unset($lo->price, $lo->currency, $lo->tax);

            $lo->event = empty($lo->event) ? (object) [] : json_decode($lo->event);
        }

        return $learningObjects;
    }

    public static function findIds(array &$items, array &$ids = [])
    {
        foreach ($items as &$item) {
            $ids[] = $item['id'];

            if (!empty($item['items'])) {
                static::findIds($item['items'], $ids);
            }
        }
    }

    /**
     * Filter learning object description by below elements
     * Iframe: allow YouTube and Vimeo
     */
    public static function descriptionPurifierConfig()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedElements', [
            'b', 'code', 'del', 'dd', 'dl', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'sup', 'sub', 'div', 'p', 'blockquote', 'strong', 'i', 'kbd', 's',
            'strike', 'hr', 'tr', 'td', 'th', 'thead', 'tbody', 'tfoot', 'em', 'pre', 'br',
            'table', 'a', 'iframe', 'img', 'ul', 'li', 'ol', 'caption'
        ]);
        $config->set('HTML.AllowedAttributes', [
            'a.href', 'img.src', 'img.width', 'img.height',
            'table.width', 'table.cellspacing', 'table.cellpadding', 'table.height', 'table.align', 'table.summary', 'table.style',
            '*.class', '*.alt', '*.title', '*.border',
            'div.data-oembed-url',
            'iframe.src', 'iframe.allowfullscreen', 'iframe.width', 'iframe.height',
            'iframe.frameborder', 'iframe.mozallowfullscreen', 'iframe.webkitallowfullscreen'
        ]);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('iframe', 'mozallowfullscreen', 'Bool');
        $def->addAttribute('iframe', 'webkitallowfullscreen', 'Bool');
        $def->addAttribute('div', 'data-oembed-url', 'CDATA');
        $def->addAttribute('table', 'height', 'Number');

        return $config;
    }
}
