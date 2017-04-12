<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;

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

        $db->insert('gc_lo', [
            'type'        => isset($options['type']) ? $options['type'] : LoTypes::COURSE,
            'instance_id' => $instanceId = isset($options['instance_id']) ? $options['instance_id'] : 0,
            'remote_id'   => isset($options['remote_id']) ? $options['remote_id'] : $db->fetchColumn('SELECT 1 + MAX(remote_id) FROM gc_lo') ?: 1,
            'title'       => isset($options['title']) ? $options['title'] : 'Example course',
            'description' => isset($options['description']) ? $options['description'] : '…',
            'private'     => isset($options['private']) ? $options['private'] : 0,
            'published'   => isset($options['published']) ? $options['published'] : 1,
            'language'    => isset($options['language']) ? $options['language'] : 'en',
            'tags'        => isset($options['tags']) ? $options['tags'] : '',
            'locale'      => isset($locale) ? $locale : null,
            'marketplace' => isset($options['marketplace']) ? $options['marketplace'] : 0,
            'origin_id'   => isset($options['origin_id']) ? $options['origin_id'] : 0,
            'image'       => isset($options['image']) ? $options['image'] : '',
            'data'        => isset($options['data']) ? $options['data'] : '',
            'timestamp'   => isset($options['timestamp']) ? $options['timestamp'] : time(),
            'created'     => isset($options['created']) ? $options['created'] : time(),
            'updated'     => isset($options['updated']) ? $options['updated'] : time(),
            'sharing'     => isset($options['sharing']) ? $options['sharing'] : 0,
        ]);

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
            foreach (explode(' ', $options['tags']) as $tag) {
                $tag = trim($tag, '[] ');
                if ($tag) {
                    $this->postTag($db, $instanceId, $tag);
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

    protected function createEvent(Connection $db, int $sourceId, array $event)
    {
        $location = empty($event['location'])
            ? []
            : [
                'loc_country'                 => $event['location']['country'],
                'loc_administrative_area'     => $event['location']['administrative_area'],
                'loc_sub_administrative_area' => $event['location']['sub_administrative_area'],
                'loc_locality'                => $event['location']['locality'],
                'loc_dependent_locality'      => $event['location']['dependent_locality'],
                'loc_thoroughfare'            => $event['location']['thoroughfare'],
                'loc_premise'                 => $event['location']['premise'],
                'loc_sub_premise'             => $event['location']['sub_premise'],
                'loc_organisation_name'       => $event['location']['organisation_name'],
                'loc_name_line'               => $event['location']['name_line'],
                'loc_postal_code'             => $event['location']['postal_code'],
            ];

        $db->insert('gc_event', [
                'start'     => $event['start'],
                'end'       => isset($event['end']) ? $event['end'] : null,
                'timezone'  => isset($event['timezone']) ? $event['timezone'] : 'UTC',
                'seats'     => isset($event['seats']) ? $event['seats'] : 0,
                'created'   => time(),
                'updated'   => time(),
                'data'      => isset($event['data']) ? (is_scalar($event['data']) ? $event['data'] : json_encode($event['data'])) : '',
            ] + $location
        );

        if ($eventId = $db->lastInsertId('gc_event')) {
            $db->insert('gc_ro', [
                'source_id' => $sourceId,
                'target_id' => $eventId,
                'type'      => EdgeTypes::HAS_EVENT_EDGE,
                'weight'    => 0
            ]);
        }
        return $eventId;
    }
}
