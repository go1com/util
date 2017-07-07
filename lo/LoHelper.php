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
    const ENROLMENT_ALLOW            = 'allow_enrolment';
    const ENROLMENT_ALLOW_DEFAULT    = 'allow';
    const ENROLMENT_ALLOW_DISABLE    = 'disable';
    const ENROLMENT_ALLOW_ENQUIRY    = 'enquiry';
    const ENROLMENT_RE_ENROL         = 're_enrol';
    const ENROLMENT_RE_ENROL_DEFAULT = true;
    const MANUAL_PAYMENT             = 'manual_payment';
    const MANUAL_PAYMENT_RECIPIENT   = 'manual_payment_recipient';
    const SEQUENCE_ENROL             = 'requiredSequence';

    // GO1P-5665: Expiration for award.
    const AWARD      = 'award';
    const AWARD_TYPE = [
        'quantity'   => ['type' => 'bool', 'default' => false],
        'expiration' => ['type' => 'string', 'default' => '+ 1 year'],
    ];

    public static function load(Connection $db, int $id, int $instanceId = null)
    {
        return ($learningObjects = static::loadMultiple($db, [$id], $instanceId))
            ? $learningObjects[0]
            : false;
    }

    /**
     * @param Connection $db
     * @param  []int     $ids
     * @param   int      $instanceId
     * @return []stdClass
     */
    public static function loadMultiple(Connection $db, array $ids, int $instanceId = null): array
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

        $loIds = [];
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

            $lo->event = new \stdClass();
            $loIds[] = $lo->id;
        }

        if ($instanceId && $loIds) {
            # Load custom tags.
            $q = 'SELECT lo_id, tag FROM gc_lo_tag WHERE status = 1 AND instance_id = ? AND lo_id IN (?)';
            $q = $db->executeQuery($q, [$instanceId, $loIds], [DB::INTEGER, DB::INTEGERS]);
            while ($row = $q->fetch(DB::OBJ)) {
                foreach ($learningObjects as &$lo) {
                    if ($lo->id == $row->lo_id) {
                        $lo->custom_tags[] = $row->tag;
                    }
                }
            }
        }

        # Load events.
        $learningObjects && static::attachEvents($db, $learningObjects, $loIds);

        return $learningObjects;
    }

    private static function attachEvents(Connection $db, array &$los, array &$loIds)
    {
        $put = function (\stdClass &$node, array $event) use (&$put) {
            if ($node->id == $event['loId']) {
                $node->event = (object) [
                    'start'           => $event['start'],
                    'end'             => $event['end'],
                    'timezone'        => $event['timezone'],
                    'seats'           => $event['seats'],
                    'created'         => $event['created'],
                    'updated'         => $event['updated'],
                    'data'            => !empty($event['data']) ? json_decode($event['data']) : $event['data'],
                ];

                $node->event->locations = [];
                # Get location from gc_event table
                if (!empty($event['loc_country'])) {
                    $node->event->locations = [[
                        'country'                   => $event['loc_country'],
                        'administrative_area'       => $event['loc_administrative_area'],
                        'sub_administrative_area'   => $event['loc_sub_administrative_area'],
                        'locality'                  => $event['loc_locality'],
                        'dependent_locality'        => $event['loc_dependent_locality'],
                        'thoroughfare'              => $event['loc_thoroughfare'],
                        'premise'                   => $event['loc_premise'],
                        'sub_premise'               => $event['loc_sub_premise'],
                        'organisation_name'         => $event['loc_organisation_name'],
                        'name_line'                 => $event['loc_name_line'],
                        'postal_code'               => $event['loc_postal_code'],
                    ]];
                }
                # Get location from gc_location table
                else if (!empty($event['country'])) {
                    $node->event->locations = [[
                        'id'                        => $event['locationId'],
                        'title'                     => $event['title'],
                        'country'                   => $event['country'],
                        'administrative_area'       => $event['administrative_area'],
                        'sub_administrative_area'   => $event['sub_administrative_area'],
                        'locality'                  => $event['locality'],
                        'dependent_locality'        => $event['dependent_locality'],
                        'thoroughfare'              => $event['thoroughfare'],
                        'premise'                   => $event['premise'],
                        'sub_premise'               => $event['sub_premise'],
                        'organisation_name'         => $event['organisation_name'],
                        'name_line'                 => $event['name_line'],
                        'postal_code'               => $event['postal_code'],
                    ]];
                }
            }
        };

        $sql = 'SELECT gc_location.*, event.*, ro.source_id as loId, gc_location.id as locationId FROM gc_event event';
        $sql .= ' INNER JOIN gc_ro ro ON event.id = ro.target_id AND ro.type = ?';
        $sql .= ' LEFT JOIN gc_ro ro_location ON ro_location.source_id = ro.target_id AND ro_location.type = ?';
        $sql .= ' LEFT JOIN gc_location gc_location ON ro_location.target_id = gc_location.id';
        $sql .= ' WHERE ro.source_id IN (?)';
        $events = $db->executeQuery(
            $sql,
            [EdgeTypes::HAS_EVENT_EDGE, EdgeTypes::HAS_LOCATION, $loIds],
            [DB::INTEGER, DB::INTEGER, DB::INTEGERS]
        );

        while ($event = $events->fetch()) {
            foreach ($los as &$lo) {
                $put($lo, $event);
            }
        }
    }

    /**
     * @deprecated 
     */
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
            'a.href', 'a.rel', 'a.target', 'a.type',
            'img.src', 'img.width', 'img.height', 'img.style',
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

    public static function parentIds(Connection $db, int $loId): array
    {
        $q = 'SELECT source_id FROM gc_ro WHERE type IN (?) AND target_id = ?';
        $q = $db->executeQuery($q, [EdgeTypes::LO_HAS_CHILDREN, $loId], [DB::INTEGERS, DB::INTEGER]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $ids = array_merge($ids, static::parentIds($db, $id));
            $ids[] = (int) $id;
        }

        return array_unique($ids);
    }

    public static function parentsAuthorIds(Connection $db, int $loId, array $parentLoIds = NULL): array
    {
        $authorIds = [];
        if (!isset($parentLoIds)) {
            $parentLoIds = static::parentIds($db, $loId);
        }
        foreach ($parentLoIds as $parentLoId) {
            $authorIds = array_merge($authorIds, LoChecker::authorIds($db, $parentLoId));
        }

        $authorIds = array_values(array_unique($authorIds));
        return array_map('intval', $authorIds);
    }

    public static function childIds(Connection $db, int $loId): array
    {
        $q = 'SELECT target_id FROM gc_ro WHERE type IN (?) AND source_id = ?';
        $q = $db->executeQuery($q, [EdgeTypes::LO_HAS_CHILDREN, $loId], [DB::INTEGERS, DB::INTEGER]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $ids[] = (int) $id;
        }

        return $ids;
    }

    public static function isBelongToGroup(Connection $db, int $loId, int $instanceId) : bool
    {
        $sql = 'SELECT 1 FROM gc_lo_group WHERE lo_id = ? AND instance_id = ?';

        return $db->fetchColumn($sql, [$loId, $instanceId]) ? true : false;
    }

    public static function countEnrolment(Connection $db, int $loId)
    {
        $sql = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id = ?';
        return $db->fetchColumn($sql, [$loId]);
    }
}
