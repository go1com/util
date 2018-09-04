<?php

namespace go1\util\lo;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\user\UserHelper;
use HTMLPurifier_Config;
use PDO;
use stdClass;

class LoHelper
{
    # configuration key for LO, which put under gc_lo.data
    # ---------------------
    const DISCUSSION_ALLOW           = 'allow_discussion';
    const ENROLMENT_ALLOW            = 'allow_enrolment';
    /** @deprecated */
    const ENROLMENT_ALLOW_DEFAULT    = 'allow';
    const ASSIGNMENT_ALLOW_RESUBMIT  = 'allow_resubmit';
    /** @deprecated */
    const ENROLMENT_ALLOW_DISABLE    = 'disable';
    /** @deprecated */
    const ENROLMENT_ALLOW_ENQUIRY    = 'enquiry';
    const ENROLMENT_RE_ENROL         = 're_enrol';
    const ENROLMENT_RE_ENROL_DEFAULT = true;
    const MANUAL_PAYMENT             = 'manual_payment';
    const MANUAL_PAYMENT_RECIPIENT   = 'manual_payment_recipient';
    const SEQUENCE_ENROL             = 'requiredSequence';
    const SUGGESTED_COMPLETION_TIME  = 'suggested_completion_time';
    const SUGGESTED_COMPLETION_UNIT  = 'suggested_completion_unit';
    const PASS_RATE                  = 'pass_rate';
    const SINGLE_LI                  = 'single_li';
    const ALLOW_REUSE_ENROLMENT      = 'allow_reuse_enrolment'; // Use existing enrollments for reused content

    // GO1P-5665: Expiration for award.
    const AWARD      = 'award';
    const AWARD_TYPE = [
        'quantity'   => ['type' => 'bool', 'default' => false],
        'expiration' => ['type' => 'string', 'default' => '+ 1 year'],
    ];

    public static function load(Connection $db, int $id, int $instanceId = null, bool $expensiveTree = false)
    {
        return ($learningObjects = static::loadMultiple($db, [$id], $instanceId, $expensiveTree)) ? $learningObjects[0] : false;
    }

    /**
     * Load multiple learning objects.
     *
     * @param Connection $db
     * @param  []int     $ids
     * @param   int      $instanceId
     * @param bool       $expensiveTree
     * @return array
     */
    public static function loadMultiple(Connection $db, array $ids, int $instanceId = null, bool $expensiveTree = false): array
    {
        $ids = array_map('intval', $ids);
        $learningObjects = !$ids ? [] : $db
            ->executeQuery(
                'SELECT lo.*, pricing.price, pricing.currency, pricing.tax, pricing.tax_included, pricing.recurring'
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
                'recurring'    => $lo->recurring ? json_decode($lo->recurring) : null,
            ];
            unset($lo->price, $lo->currency, $lo->tax, $lo->tax_included);

            $lo->event = new stdClass;
            $loIds[] = $lo->id;
        }

        if ($loIds) {
            if ($instanceId) {
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

            if ($expensiveTree) {
                $load = function (array &$nodes, array &$nodeIds, array $edgeTypes) use (&$db, $instanceId) {
                    $itemIds = [];
                    $q = 'SELECT source_id, target_id FROM gc_ro WHERE source_id IN (?) AND type IN (?) ORDER BY weight';
                    $q = $db->executeQuery($q, [$nodeIds, $edgeTypes], [DB::INTEGERS, DB::INTEGERS]);

                    while ($edge = $q->fetch(DB::OBJ)) {
                        foreach ($nodes as &$node) {
                            if ($node->id == $edge->source_id) {
                                $itemIds[] = (int) $edge->target_id;
                                $node->items[] = (object) ['id' => (int) $edge->target_id];
                            }
                        }
                    }

                    if ($itemIds && $items = self::loadMultiple($db, $itemIds, $instanceId, true)) {
                        foreach ($items as &$item) {
                            foreach ($nodes as &$node) {
                                if (!empty($node->items)) {
                                    foreach ($node->items as &$_) {
                                        if ($_->id == $item->id) {
                                            $_ = $item;
                                        }
                                    }
                                }
                            }
                        }
                    }
                };

                $courses = $courseIds = $modules = $moduleIds = [];

                foreach ($learningObjects as &$lo) {
                    if (LoTypes::COURSE == $lo->type) {
                        $courses[] = &$lo;
                        $courseIds[] = (int) $lo->id;
                    }

                    if (LoTypes::MODULE == $lo->type) {
                        $modules[] = &$lo;
                        $moduleIds[] = (int) $lo->id;
                    }
                }

                $courseIds && $load($courses, $courseIds, [EdgeTypes::HAS_ELECTIVE_LO, EdgeTypes::HAS_MODULE, EdgeTypes::HAS_EVENT_EDGE, EdgeTypes::HAS_ELECTIVE_LI, EdgeTypes::HAS_LI]);
                $moduleIds && $load($modules, $moduleIds, [EdgeTypes::HAS_ELECTIVE_LI, EdgeTypes::HAS_LI]);
            }
        }

        # Load events.
        $learningObjects && static::attachEvents($db, $learningObjects, $loIds);

        return $learningObjects;
    }

    private static function attachEvents(Connection $db, array &$los, array &$loIds)
    {
        $put = function (stdClass &$node, array $event) use (&$put) {
            if ($node->id == $event['loId']) {
                $node->event = (object) [
                    'id'       => $event['id'],
                    'start'    => $event['start'],
                    'end'      => $event['end'],
                    'timezone' => $event['timezone'],
                    'seats'    => $event['seats'],
                    'created'  => $event['created'],
                    'updated'  => $event['updated'],
                    'data'     => !empty($event['data']) ? json_decode($event['data']) : $event['data'],
                ];

                $node->event->locations = [];
                # Get location from gc_event table
                if (!empty($event['loc_country'])) {
                    $node->event->locations = [
                        [
                            'country'                 => $event['loc_country'],
                            'administrative_area'     => $event['loc_administrative_area'],
                            'sub_administrative_area' => $event['loc_sub_administrative_area'],
                            'locality'                => $event['loc_locality'],
                            'dependent_locality'      => $event['loc_dependent_locality'],
                            'thoroughfare'            => $event['loc_thoroughfare'],
                            'premise'                 => $event['loc_premise'],
                            'sub_premise'             => $event['loc_sub_premise'],
                            'organisation_name'       => $event['loc_organisation_name'],
                            'name_line'               => $event['loc_name_line'],
                            'postal_code'             => $event['loc_postal_code'],
                        ],
                    ];
                }
                # Get location from gc_location table
                else if (!empty($event['country'])) {
                    $node->event->locations = [
                        [
                            'id'                      => $event['locationId'],
                            'title'                   => $event['title'],
                            'country'                 => $event['country'],
                            'administrative_area'     => $event['administrative_area'],
                            'sub_administrative_area' => $event['sub_administrative_area'],
                            'locality'                => $event['locality'],
                            'dependent_locality'      => $event['dependent_locality'],
                            'thoroughfare'            => $event['thoroughfare'],
                            'premise'                 => $event['premise'],
                            'sub_premise'             => $event['sub_premise'],
                            'organisation_name'       => $event['organisation_name'],
                            'name_line'               => $event['name_line'],
                            'postal_code'             => $event['postal_code'],
                        ],
                    ];
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
            'a.href', 'a.rel', 'a.target',
            'img.src', 'img.width', 'img.height', 'img.style',
            'table.width', 'table.cellspacing', 'table.cellpadding', 'table.height', 'table.align', 'table.summary', 'table.style',
            '*.class', '*.alt', '*.title', '*.border',
            'div.data-oembed-url', 'div.style', 'span.style',
            'iframe.src', 'iframe.allowfullscreen', 'iframe.width', 'iframe.height',
            'iframe.frameborder', 'iframe.mozallowfullscreen', 'iframe.webkitallowfullscreen',
        ]);
        $cnf->set('HTML.SafeIframe', true);
        $cnf->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|fast\.wistia\.net\/embed/)%');
        $cnf->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);

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

    public static function enrolmentAssessorIds(Connection $db, int $loId, int $learnerProfileId): array
    {
        if ($enrolmentId = EnrolmentHelper::enrolmentId($db, $loId, $learnerProfileId)) {
            return EdgeHelper
                ::select('source_id')
                ->get($db, [], [$enrolmentId], [EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE], PDO::FETCH_COLUMN);
        }

        return [];
    }

    public static function hasActiveMembership(Connection $db, int $loId, int $instanceId): bool
    {
        $sql = 'SELECT 1 FROM gc_lo_group WHERE lo_id = ? AND instance_id = ?';

        return $db->fetchColumn($sql, [$loId, $instanceId]) ? true : false;
    }

    public static function activeMembershipIds(Connection $social, int $loId): array
    {
        $groupIds = 'SELECT group_id FROM social_group_item WHERE entity_type = ? AND entity_id = ?';
        $groupIds = $social->executeQuery($groupIds, ['lo', $loId])->fetchAll(PDO::FETCH_COLUMN);

        return !$groupIds ? [] : $social
            ->executeQuery(
                'SELECT DISTINCT entity_id FROM social_group_item WHERE entity_type = ? AND group_id IN (?)',
                ['portal', $groupIds],
                [DB::STRING, DB::INTEGERS]
            )
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function parentIds(Connection $db, int $loId, $allParent = true): array
    {
        $q = 'SELECT source_id FROM gc_ro WHERE type IN (?) AND target_id = ?';
        $q = $db->executeQuery($q, [EdgeTypes::LO_HAS_CHILDREN, $loId], [DB::INTEGERS, DB::INTEGER]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $allParent && $ids = array_merge($ids, static::parentIds($db, $id));
            $ids[] = (int) $id;
        }

        return array_unique($ids);
    }

    public static function parentsAuthorIds(Connection $db, int $loId, array $parentLoIds = null): array
    {
        $authorIds = [];
        if (!isset($parentLoIds)) {
            $parentLoIds = static::parentIds($db, $loId);
        }
        $parentLoIds[] = $loId;

        foreach ($parentLoIds as $parentLoId) {
            $authorIds = array_merge($authorIds, self::authorIds($db, $parentLoId));
        }

        $authorIds = array_values(array_unique($authorIds));

        return array_map('intval', $authorIds);
    }

    public static function parentsAssessorIds(Connection $db, int $loId, array $parentLoIds = null, int $learnerProfileId = null): array
    {
        $assessorIds = [];
        if (!isset($parentLoIds)) {
            $parentLoIds = static::parentIds($db, $loId);
        }
        $parentLoIds[] = $loId;

        foreach ($parentLoIds as $parentLoId) {
            $assessorIds = array_merge($assessorIds, self::assessorIds($db, $parentLoId));

            if ($learnerProfileId) {
                $assessorIds = array_merge($assessorIds, self::enrolmentAssessorIds($db, $parentLoId, $learnerProfileId));
            }
        }

        $assessorIds = array_values(array_unique($assessorIds));

        return array_map('intval', $assessorIds);
    }

    public static function childIds(Connection $db, int $loId, $all = false): array
    {
        $q = 'SELECT target_id FROM gc_ro WHERE type IN (?) AND source_id = ?';
        $q = $db->executeQuery($q, [EdgeTypes::LO_HAS_CHILDREN, $loId], [DB::INTEGERS, DB::INTEGER]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $all && $ids = array_merge($ids, static::childIds($db, $id));
            $ids[] = (int) $id;
        }

        return $ids;
    }

    public static function moduleIds(Connection $db, int $loId): array
    {
        return EdgeHelper
            ::select('target_id')
            ->get($db, [$loId], [], [EdgeTypes::HAS_MODULE, EdgeTypes::HAS_ELECTIVE_LO], PDO::FETCH_COLUMN);
    }

    public static function isBelongToGroup(Connection $db, int $loId, int $instanceId): bool
    {
        $sql = 'SELECT 1 FROM gc_lo_group WHERE lo_id = ? AND instance_id = ?';

        return $db->fetchColumn($sql, [$loId, $instanceId]) ? true : false;
    }

    public static function countEnrolment(Connection $db, int $loId)
    {
        $sql = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id = ?';

        return $db->fetchColumn($sql, [$loId]);
    }

    public static function getCustomisation(Connection $db, int $loId, int $portalId): array
    {
        $edges = EdgeHelper::edges($db, [$loId], [$portalId], [EdgeTypes::HAS_LO_CUSTOMISATION]);
        if ($edge = reset($edges)) {
            $data = $edge->data ?? [];

            return is_scalar($data) ? json_decode($data, true) : [];
        }

        return [];
    }

    public static function isSingleLi(stdClass $lo)
    {
        return in_array($lo->type, LiTypes::all())
            ? boolval($lo->data->{self::SINGLE_LI} ?? false)
            : false;
    }

    public static function authorIds(Connection $db, int $loId): array
    {
        return EdgeHelper
            ::select('target_id')
            ->get($db, [$loId], [], [EdgeTypes::HAS_AUTHOR_EDGE], PDO::FETCH_COLUMN);
    }

    public static function authors(Connection $db, int $loId): array
    {
        $authorIds = self::authorIds($db, $loId);

        return !$authorIds ? [] : UserHelper::loadMultiple($db, array_map('intval', $authorIds));
    }

    public static function getSuggestedCompletion(Connection $db, int $loId, int $parentId = 0): array
    {
        if ($lo = LoHelper::load($db, $loId)) {
            $types = [EdgeTypes::HAS_SUGGESTED_COMPLETION];
            $targetId = $parentId ? EdgeHelper::hasLink($db, EdgeTypes::HAS_LI, $parentId, $lo->id) : 0;
            $edges = $targetId ? EdgeHelper::edges($db, [$lo->id], [$targetId], $types) : EdgeHelper::edgesFromSource($db, $lo->id, $types);
            if ($edge = reset($edges)) {
                $data = (is_scalar($edge->data)) ? json_decode($edge->data, true) : [];

                return [
                    $data['type'],
                    $data['value'],
                ];
            }
        }

        return [];
    }

    /**
     * Return the number of LIs in a Course (if having LI.events in the course, they will be counted as one LI)
     *
     */
    public static function countChild(Connection $db, int $id): int
    {
        if (!$childrenId = LoHelper::childIds($db, $id, true)) {
            return 0;
        }

        $result = 0;
        $sql = 'SELECT type, COUNT(*) as count FROM gc_lo WHERE type IN (?) AND id IN (?) GROUP BY type';
        $rows = $db->executeQuery($sql, [LiTypes::all(), $childrenId], [DB::STRINGS, DB::INTEGERS])->fetchAll();

        foreach ($rows as $row) {
            if ($row['type'] == LiTypes::EVENT && $row['count'] > 0) {
                $result++;
            }
            else {
                $result += $row['count'];
            }
        }

        return $result;
    }

    public static function allowReuseEnrolment(stdClass $lo): bool
    {
        return boolval($lo->data->{self::ALLOW_REUSE_ENROLMENT} ?? false);
    }
}
