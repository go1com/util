<?php

namespace go1\util\lo;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use HTMLPurifier_Config;
use PDO;

class LoHelper
{
    # configuration key for LO, which put under gc_lo.data
    # ---------------------
    const ENROLMENT_RE_ENROL         = 're_enrol';
    const ENROLMENT_RE_ENROL_DEFAULT = true;
    const MANUAL_PAYMENT             = 'manual_payment';
    const MANUAL_PAYMENT_RECIPIENT   = 'manual_payment_recipient';

    // GO1P-5665: Expiration for award.
    const AWARD      = 'award';
    const AWARD_TYPE = [
        'quantity'   => ['type' => 'bool', 'default' => false],
        'expiration' => ['type' => 'string', 'default' => '+ 1 year'],
    ];

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
                'SELECT lo.*, pricing.price, pricing.currency, pricing.tax, pricing.tax_included'
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
                'price'        => $lo->price ? (float) $lo->price : 0.00,
                'currency'     => $lo->currency ?: 'USD',
                'tax'          => $lo->tax ? (float) $lo->tax : 0.00,
                'tax_included' => $lo->tax_included ? true : false,
            ];
            unset($lo->price, $lo->currency, $lo->tax, $lo->tax_included);

            $lo->event = static::getEvent($db, $lo->id) ?: (empty($lo->event) ? (object) [] : json_decode($lo->event));
        }

        return $learningObjects;
    }

    public static function getEvent(Connection $db, int $loId)
    {
        $sql = "SELECT e.* FROM gc_event e";
        $sql .= "   INNER JOIN gc_ro r ON e.id = r.target_id";
        $sql .= "   WHERE r.source_id = ? AND r.type = ?";

        return $db->executeQuery($sql, [$loId, EdgeTypes::HAS_EVENT_EDGE])->fetch(DB::OBJ);
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
        $cnf = HTMLPurifier_Config::createDefault();
        $cnf->set('Cache.DefinitionImpl', null);
        $cnf->set('HTML.AllowedElements', [
            'b', 'code', 'del', 'dd', 'dl', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'sup', 'sub', 'div', 'p', 'blockquote', 'strong', 'i', 'kbd', 's',
            'strike', 'hr', 'tr', 'td', 'th', 'thead', 'tbody', 'tfoot', 'em', 'pre', 'br',
            'table', 'a', 'iframe', 'img', 'ul', 'li', 'ol', 'caption', 'span',
        ]);
        $cnf->set('HTML.AllowedAttributes', [
            'a.href', 'img.src', 'img.width', 'img.height', 'img.style',
            'table.width', 'table.cellspacing', 'table.cellpadding', 'table.height', 'table.align', 'table.summary', 'table.style',
            '*.class', '*.alt', '*.title', '*.border',
            'div.data-oembed-url', 'div.style', 'span.style',
            'iframe.src', 'iframe.allowfullscreen', 'iframe.width', 'iframe.height',
            'iframe.frameborder', 'iframe.mozallowfullscreen', 'iframe.webkitallowfullscreen',
        ]);
        $cnf->set('HTML.SafeIframe', true);
        $cnf->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

        $def = $cnf->getHTMLDefinition(true);
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('iframe', 'mozallowfullscreen', 'Bool');
        $def->addAttribute('iframe', 'webkitallowfullscreen', 'Bool');
        $def->addAttribute('div', 'data-oembed-url', 'CDATA');
        $def->addAttribute('table', 'height', 'Number');

        return $cnf;
    }

    public static function getTitlePurifyConfig()
    {
        $cnf = HTMLPurifier_Config::createDefault();
        $cnf->set('Cache.DefinitionImpl', null);
        $cnf->set('HTML.Allowed', '');
        $cnf->set('Core.HiddenElements', []);

        return $cnf;
    }

    public static function assessorIds(Connection $db, int $loId): array
    {
        return EdgeHelper
            ::select('target_id')
            ->get($db, [$loId], [], [EdgeTypes::COURSE_ASSESSOR], PDO::FETCH_COLUMN);
    }

    public static function hasActiveMembership(Connection $db, int $loId, int $instanceId): bool
    {
        $sql = 'SELECT 1 FROM gc_lo_group WHERE lo_id = ? AND instance_id = ?';

        return $db->fetchColumn($sql, [$loId, $instanceId]) ? true : false;
    }
}
