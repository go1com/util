<?php

namespace go1\util\enrolment;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\queue\Queue;

class ManualRecordRepository
{
    private $db;
    private $queue;

    public function __construct(Connection $db, MqClient $queue)
    {
        $this->db = $db;
        $this->queue = $queue;
    }

    public function db()
    {
        return $this->db;
    }

    public function create(ManualRecord $record)
    {
        $this->db->insert('enrolment_manual', $row = [
            'entity_type' => $record->entityType,
            'entity_id'   => $record->entityId,
            'instance_id' => $record->instanceId,
            'user_id'     => $record->userId,
            'verified'    => $record->verified,
            'data'        => is_scalar($record->data) ? $record->data : json_encode($record->data),
            'created'     => $record->created,
            'updated'     => $record->updated,
        ]);

        $record->id = $row['id'] = $this->db->lastInsertId('enrolment_manual');
        $this->queue->publish($record, Queue::MANUAL_RECORD_CREATE);
    }

    public function update(ManualRecord $record, int $actorId = null)
    {
        if ($origin = $this->load($record->id)) {
            if ($diff = $origin->diff($record)) {
                $record->original = $origin;
                $diff['updated'] = $record->updated = time();

                if ($actorId) {
                    if ($origin->verified != $record->verified) {
                        $record->data['verify'][] = [
                            'action'    => $record->verified ? 'approved' : 'declined',
                            'actor_id'  => $actorId,
                            'timestamp' => time(),
                        ];
                    }
                }

                $diff['data'] = json_encode($record->data);
                $this->db->update('enrolment_manual', $diff, ['id' => $record->id]);
                $this->queue->publish($record, Queue::MANUAL_RECORD_UPDATE);
            }
        }
    }

    public function delete(int $id)
    {
        if ($record = $this->load($id)) {
            $this->db->delete('enrolment_manual', ['id' => $id]);
            $this->queue->publish($record, Queue::MANUAL_RECORD_DELETE);
        }
    }

    /**
     * @param int $id
     * @return ManualRecord
     */
    public function load(int $id)
    {
        $row = 'SELECT * FROM enrolment_manual WHERE id = ?';
        $row = $this->db->executeQuery($row, [$id])->fetch(DB::OBJ);

        return $row ? ManualRecord::create($row) : null;
    }

    /**
     * @param int    $instanceId
     * @param string $entityType
     * @param string $entityId
     * @param int    $userId
     * @return ManualRecord
     */
    public function loadByEntity($instanceId, $entityType, $entityId, $userId)
    {
        $row = 'SELECT * FROM enrolment_manual WHERE instance_id = ? AND entity_type = ? AND entity_id = ? AND user_id = ?';
        $row = $this->db->executeQuery($row, [$instanceId, $entityType, $entityId, $userId])->fetch(DB::OBJ);

        return $row ? ManualRecord::create($row) : null;
    }
}
