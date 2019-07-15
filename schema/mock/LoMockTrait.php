<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\lo\LoAttributeTypes;
use go1\util\Text;

trait LoMockTrait
{
    public function createLearningPathway(Connection $db, array $options = [])
    {
        return $this->createLO($db, [
                'type'  => LoTypes::LEANING_PATHWAY,
                'title' => isset($options['title']) ? $options['title'] : 'Example learning pathway',
            ] + $options);
    }

    public function createCourse(Connection $db, array $options = [])
    {
        return $this->createLO($db, $options);
    }

    public function createModule(Connection $db, array $options = [])
    {
        return $this->createLO($db, [
                'type'  => LoTypes::MODULE,
                'title' => isset($options['title']) ? $options['title'] : 'Example module',
            ] + $options);
    }

    public function createVideo(Connection $db, array $options = [])
    {
        return $this->createLO($db, [
                'type'  => LiTypes::VIDEO,
                'title' => isset($options['title']) ? $options['title'] : 'Example video',
            ] + $options);
    }

    public function createLO(Connection $db, array $options = [])
    {
        if (isset($options['locale'])) {
            if (is_array($options['locale'])) {
                $locale = [];
                foreach ($options['locale'] as &$_) {
                    $locale[] = '[' . trim($_, '[]') . ']';
                }
                $locale = implode(' ', $locale);
            }
        }

        $options['data'] = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : [];
        if (!isset($options['data'][LoHelper::ENROLMENT_RE_ENROL])) {
            $options['data'][LoHelper::ENROLMENT_RE_ENROL] = LoHelper::ENROLMENT_RE_ENROL_DEFAULT;
        }
        $options['data'] = json_encode($options['data']);

        if ((isset($options['event'])) && ($event = is_scalar($options['event']) ? json_decode($options['event'], true) : $options['event'])) {
            $start = !isset($event['start']) ? 0 : (is_numeric($event['start']) ? $event['start'] : strtotime($event['start']));
        }

        $opt = [
            'id'              => $options['id'] ?? null,
            'type'            => isset($options['type']) ? $options['type'] : LoTypes::COURSE,
            'instance_id'     => $instanceId = isset($options['instance_id']) ? $options['instance_id'] : 0,
            'remote_id'       => isset($options['remote_id']) ? $options['remote_id'] : $db->fetchColumn('SELECT 1 + MAX(remote_id) FROM gc_lo') ?: 1,
            'title'           => isset($options['title']) ? $options['title'] : 'Example course',
            'description'     => isset($options['description']) ? $options['description'] : '…',
            'private'         => isset($options['private']) ? $options['private'] : 0,
            'published'       => isset($options['published']) ? $options['published'] : 1,
            'language'        => isset($options['language']) ? $options['language'] : 'en',
            'tags'            => isset($options['tags']) ? $options['tags'] : '',
            'locale'          => isset($locale) ? $locale : null,
            'event'           => isset($options['event']) ? (is_scalar($options['event']) ? $options['event'] : json_encode($options['event'])) : '',
            'event_start'     => isset($start) ? $start : 0,
            'marketplace'     => isset($options['marketplace']) ? $options['marketplace'] : 0,
            'origin_id'       => isset($options['origin_id']) ? $options['origin_id'] : 0,
            'image'           => isset($options['image']) ? $options['image'] : '',
            'enrolment_count' => isset($options['enrolment_count']) ? $options['enrolment_count'] : 0,
            'data'            => isset($options['data']) ? $options['data'] : '',
            'timestamp'       => isset($options['timestamp']) ? $options['timestamp'] : time(),
            'created'         => isset($options['created']) ? $options['created'] : time(),
            'updated'         => isset($options['updated']) ? $options['updated'] : time(),
            'sharing'         => isset($options['sharing']) ? $options['sharing'] : 0,
            'premium'         => isset($options['premium']) ? $options['premium'] : 0,
            'summary'         => isset($options['summary']) ? $options['summary'] : null,
        ];
        $db->insert('gc_lo', $opt);

        $courseId = $db->lastInsertId('gc_lo');
        if (!empty($options['price'])) {
            $db->insert('gc_lo_pricing', [
                'id'           => $courseId,
                'price'        => (float) $options['price']['price'],
                'currency'     => isset($options['price']['currency']) ? $options['price']['currency'] : 'USD',
                'tax'          => isset($options['price']['tax']) ? $options['price']['tax'] : 0.00,
                'tax_included' => isset($options['price']['tax_included']) ? $options['price']['tax_included'] : 0,
            ]);
        }

        if (!empty($options['tags'])) {
            $tags = Text::parseInlineTags($options['tags']);
            foreach ($tags as $tag) {
                $this->postTag($db, $instanceId, $tag);
            }
        }

        if (isset($options['attributes'])) {
            $attrs = array_keys($options['attributes']);
            foreach ($attrs as $att) {
                $lookup = $this->getAttributeLookup($db, $opt["type"], $att);
                if (isset($lookup['key'])) {
                    $this->createAttribute($db, $courseId, $lookup['key'], $this->formatAttributeValue($options['attributes'][$att], $lookup));
                }
            }
        }

        if (!empty($options['event'])) {
            $event = is_scalar($options['event']) ? json_decode($options['event'], true) : $options['event'];
            self::createEvent($db, $courseId, $event);
        }

        return $courseId;
    }

    public function postTag(Connection $db, $instanceId, $tag, $parentId = 0)
    {
        ($id = $db->fetchColumn('SELECT id FROM gc_tag WHERE instance_id = ? AND title = ?', [$instanceId, $tag]))
            ? $db->executeQuery('UPDATE gc_tag SET lo_count = lo_count + 1 WHERE instance_id = ? AND title = ?', [$instanceId, $tag])
            : $db->insert('gc_tag', ['instance_id' => $instanceId, 'title' => $tag, 'parent_id' => $parentId, 'timestamp' => time()]);

        return $id ? $id : $db->lastInsertId('gc_tag');
    }

    public function createEvent(Connection $db, int $loId, array $event)
    {
        $location = empty($event['location']) || is_numeric($event['location']) ? [] : [
            'loc_country'                 => $event['location']['country'],
            'loc_administrative_area'     => $event['location']['administrative_area'],
            'loc_sub_administrative_area' => $event['location']['sub_administrative_area'] ?? null,
            'loc_locality'                => $event['location']['locality'] ?? null,
            'loc_dependent_locality'      => $event['location']['dependent_locality'] ?? null,
            'loc_thoroughfare'            => $event['location']['thoroughfare'],
            'loc_premise'                 => $event['location']['premise'] ?? null,
            'loc_sub_premise'             => $event['location']['sub_premise'] ?? null,
            'loc_organisation_name'       => $event['location']['organisation_name'] ?? null,
            'loc_name_line'               => $event['location']['name_line'] ?? null,
            'loc_postal_code'             => $event['location']['postal_code'] ?? null,
        ];

        $db->insert('gc_event', [
                'start'    => $event['start'],
                'end'      => isset($event['end']) ? $event['end'] : null,
                'timezone' => isset($event['timezone']) ? $event['timezone'] : 'UTC',
                'seats'    => isset($event['seats']) ? $event['seats'] : 0,
                'created'  => time(),
                'updated'  => time(),
                'data'     => isset($event['data']) ? (is_scalar($event['data']) ? $event['data'] : json_encode($event['data'])) : '',
            ] + $location
        );

        if ($eventId = $db->lastInsertId('gc_event')) {
            $db->insert('gc_ro', [
                'source_id' => $loId,
                'target_id' => $eventId,
                'type'      => EdgeTypes::HAS_EVENT_EDGE,
                'weight'    => 0,
            ]);

            if (isset($event['location'])) {
                $event['location']['title'] = $event['location']['title'] ?? '';
                $event['location']['created'] = $event['location']['created'] ?? time();
                $event['location']['updated'] = $event['location']['updated'] ?? time();

                $db->insert('gc_location', $event['location']);
                $db->insert('gc_ro', [
                    'source_id' => $eventId,
                    'target_id' => $db->lastInsertId('gc_location'),
                    'type'      => EdgeTypes::HAS_LOCATION,
                    'weight'    => 0,
                ]);
            }
        }

        return $eventId;
    }

    public function createCustomTag(Connection $db, int $instanceId, int $loId, string $tag, bool $status = true)
    {
        $db->insert('gc_lo_tag', [
            'instance_id' => $instanceId,
            'lo_id'       => $loId,
            'tag'         => $tag,
            'status'      => $status,
        ]);
    }

    public function createAttributeLookup(Connection $db, $key, $name, $attributeType, $loType, $required,
                                          $permission, $defaultValue, $isArray = 0, $dimensionId = null)
    {
        $db->insert('gc_lo_attributes_lookup', [
            '`key`'             => $key,
            'name'              => $name,
            'attribute_type'    => $attributeType,
            'lo_type'           => $loType,
            'required'          => $required,
            'permission'        => $permission,
            'default_value'     => $defaultValue,
            'is_array'          => $isArray,
            'dimension_id'      => $dimensionId
        ]);
    }

    public function createAttribute(Connection $db, $loId, $key, $value)
    {
        $db->insert('gc_lo_attributes', [
            'lo_id'             => $loId,
            'key'               => $key,
            'value'             => $value,
            'created'           => time()
        ]);
    }

    public function getAttributeLookup(Connection $db, $loType, $name)
    {
        $q = 'SELECT * FROM gc_lo_attributes_lookup WHERE lo_type = ? AND name = ?';
        $q = $db->fetchAll($q, [$loType, $name], [DB::STRING, DB::STRING]);
        return isset($q[0]) ? $q[0] : null;
    }


    public function formatAttributeValue($value, $lookup)
    {
        if (!isset($lookup) || count($lookup) <= 0) {
            return $value;
        }
        if ($lookup['attribute_type'] === LoAttributeTypes::DIMENSION) {
            $oldValue = $value;
            $value = [];
            foreach($oldValue as $val) {
                $value[] = is_array($val) ? $val['key'] : $val;
            }
        }
        if ($lookup['is_array']) {
            $value = is_array($value) ? json_encode($value) : json_encode([$value]);
        }

        return $value;
    }
}
