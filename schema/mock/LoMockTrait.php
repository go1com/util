<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\LoHelper;

trait LoMockTrait
{
    public function createLearningPathway(Connection $db, array $options = [])
    {
        return $this->createLO($db, [
                'type'  => 'learning_pathway',
                'title' => isset($options['title']) ? $options['title'] : 'Example learning pathway',
            ] + $options);
    }

    public function createModule(Connection $db, array $options = [])
    {
        return $this->createLO($db, [
                'type'  => 'module',
                'title' => isset($options['title']) ? $options['title'] : 'Example module',
            ] + $options);
    }

    public function createCourse(Connection $db, array $options = [])
    {
        return $this->createLO($db, $options);
    }

    public function createVideo(Connection $db, array $options = [])
    {
        return $this->createLO($db, [
                'type'  => 'video',
                'title' => isset($options['title']) ? $options['title'] : 'Example video',
            ] + $options);
    }

    public function createLO(Connection $db, array $options = [])
    {
        if ($event = json_decode(isset($options['event']) ? $options['event'] : 'NULL')) {
            $start = !isset($event->start) ? 0 : (is_numeric($event->start) ? $event->start : strtotime($event->start));
        }

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
            'type'        => isset($options['type']) ? $options['type'] : 'course',
            'instance_id' => $instanceId = isset($options['instance_id']) ? $options['instance_id'] : 0,
            'remote_id'   => isset($options['remote_id']) ? $options['remote_id'] : $db->fetchColumn('SELECT 1 + MAX(remote_id) FROM gc_lo') ?: 1,
            'title'       => isset($options['title']) ? $options['title'] : 'Example course',
            'description' => isset($options['description']) ? $options['description'] : '…',
            'private'     => isset($options['private']) ? $options['private'] : 0,
            'published'   => isset($options['published']) ? $options['published'] : 1,
            'language'    => isset($options['language']) ? $options['language'] : 'en',
            'event'       => isset($options['event']) ? $options['event'] : '',
            'event_start' => isset($start) ? $start : 0,
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

        return $courseId;
    }

    public function postTag(Connection $db, $instanceId, $tag, $parentId = 0)
    {
        $id = $db->fetchColumn('SELECT id FROM gc_tag WHERE instance_id = ? AND title = ?', [$instanceId, $tag]);
        $id
            ? $db->executeQuery('UPDATE gc_tag SET lo_count = lo_count + 1 WHERE instance_id = ? AND title = ?', [$instanceId, $tag])
            : $db->insert('gc_tag', ['instance_id' => $instanceId, 'title' => $tag, 'parent_id' => $parentId, 'timestamp' => time()]);

        return $id ? $id : $db->lastInsertId('gc_tag');
    }
}
