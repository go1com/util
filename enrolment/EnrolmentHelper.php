<?php

namespace go1\util\enrolment;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DateTime;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\portal\PortalChecker;
use go1\util\portal\PortalHelper;
use go1\util\Queue;
use PDO;
use stdClass;

/**
 * @TODO We're going to load & attach edges into enrolment.
 *
 *  - assessor
 *  - expiration
 *  - ...
 *
 * Format will like:
 *  $enrolment->edges[edge-type][] = edge
 */
class EnrolmentHelper
{
    public static function enrolmentId(Connection $db, int $loId, int $profileId)
    {
        return $db->fetchColumn('SElECT id FROM gc_enrolment WHERE lo_id = ? AND profile_id = ?', [$loId, $profileId]);
    }

    public static function load(Connection $db, int $id, bool $loadEdges = false)
    {
        return ($enrolments = static::loadMultiple($db, [$id])) ? $enrolments[0] : false;
    }

    public static function loadMultiple(Connection $db, array $ids, bool $loadEdges = false): array
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment WHERE id IN (?)', [$ids], [Connection::PARAM_INT_ARRAY])
            ->fetchAll(DB::OBJ);
    }

    public static function loadByParentLo(Connection $db, int $parentLoId, bool $loadEdges = false): array
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment WHERE parent_lo_id = ?', [$parentLoId])
            ->fetchAll(DB::OBJ);
    }

    public static function loadByLo(Connection $db, int $loId, bool $loadEdges = false): array
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment WHERE lo_id = ?', [$loId])
            ->fetchAll(DB::OBJ);
    }

    public static function loadByLoAndProfileId(Connection $db, int $loId, int $profileId, int $parentLoId = null)
    {
        $q = $db
            ->createQueryBuilder()
            ->select('*')
            ->from('gc_enrolment')
            ->where('lo_id = :lo_id')->setParameter(':lo_id', $loId)
            ->andWhere('profile_id = :profile_id')->setParameter(':profile_id', $profileId);

        $parentLoId && $q->andWhere('parent_lo_id = :parent_lo_id')->setParameter(':parent_lo_id', $parentLoId);

        return $q->execute()->fetch(DB::OBJ);
    }

    public static function becomeCompleted(stdClass $enrolment, stdClass $original, bool $passAware = true): bool
    {
        $status = $enrolment->status;
        $previousStatus = $original->status;

        if ($status != $previousStatus) {
            if (EnrolmentStatuses::COMPLETED === $status) {
                return $passAware ? (1 == $enrolment->pass) : true;
            }
        }

        return false;
    }

    public static function completed(stdClass $enrolment): bool
    {
        return ($enrolment->status == EnrolmentStatuses::COMPLETED) && ($enrolment->pass == 1);
    }

    # Check that all dependencies are completed.
    # Only return true if # of completion = # of dependencies
    public static function dependenciesCompleted(Connection $db, stdClass $enrolment, bool $passAware = true): bool
    {
        $moduleId = $enrolment->lo_id;
        $dependencyIds = 'SELECT target_id FROM gc_ro WHERE type = ? AND source_id = ?';
        $dependencyIds = $db->executeQuery($dependencyIds, [EdgeTypes::HAS_MODULE_DEPENDENCY, $moduleId])->fetchAll(PDO::FETCH_COLUMN);
        if (!$dependencyIds) {
            return false; // If there's no dependencies -> input is wrong -> return false
        }

        if ($passAware) {
            $completion = 'SELECT COUNT(*) FROM gc_enrolment WHERE id IN (?) AND status = ? AND pass = 1';
            $completion = $db->fetchColumn($completion, [$dependencyIds, EnrolmentStatuses::COMPLETED], 0, [DB::INTEGERS, DB::STRING]);
        }
        else {
            $completion = 'SELECT COUNT(*) FROM gc_enrolment WHERE id IN (?) AND status = ?';
            $completion = $db->fetchColumn($completion, [$dependencyIds], 0, [DB::INTEGERS]);
        }

        return $completion == count($dependencyIds);
    }

    public static function assessorIds(Connection $db, int $enrolmentId): array
    {
        return EdgeHelper
            ::select('source_id')
            ->get($db, [], [$enrolmentId], [EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE], PDO::FETCH_COLUMN);
    }

    public static function findParentEnrolment(Connection $db, stdClass $enrolment, $parentLoType = LoTypes::COURSE)
    {
        $loadLo = function ($loId) use ($db) {
            return $db->executeQuery('SELECT id, type FROM gc_lo WHERE id = ?', [$loId])->fetch(DB::OBJ);
        };

        $parentQuery = function (stdClass $lo, stdClass $enrolment) use ($db, $loadLo) {
            $parentLoId = $enrolment->parent_lo_id ?: false;
            if (empty($parentLoId)) {
                $roTypes = [EdgeTypes::HAS_LP_ITEM, EdgeTypes::HAS_MODULE, EdgeTypes::HAS_ELECTIVE_LO, EdgeTypes::HAS_LI, EdgeTypes::HAS_ELECTIVE_LI];
                $query = $db->executeQuery('SELECT source_id FROM gc_ro WHERE type IN (?) AND target_id = ?', [$roTypes, $lo->id], [DB::INTEGERS, DB::INTEGER]);
                $parentLoId = $query->fetchColumn();
            }

            return [
                $parentLo = $parentLoId ? $loadLo($parentLoId) : false,
                $parentEnrolment = $parentLo ? EnrolmentHelper::loadByLoAndProfileId($db, $parentLo->id, $enrolment->profile_id) : false,
            ];
        };
        $lo = $loadLo($enrolment->lo_id);
        list($parentLo, $parentEnrolment) = $parentQuery($lo, $enrolment);
        while ($parentLo && $parentEnrolment && ($parentLo->type != $parentLoType)) {
            list($parentLo, $parentEnrolment) = $parentQuery($parentLo, $parentEnrolment);
        }

        return $parentLo && ($parentLo->type == $parentLoType) ? $parentEnrolment : false;
    }

    public static function sequenceEnrolmentCompleted(Connection $db, int $loId, int $parentLoId, string $parentLoType = LoTypes::COURSE, int $profileId)
    {
        $edgeType         = ($parentLoType == LoTypes::COURSE) ? EdgeTypes::LearningObjectTree['course'] : EdgeTypes::LearningObjectTree['module'];
        $requiredEdgeType = ($parentLoType == LoTypes::COURSE) ? EdgeTypes::HAS_MODULE : EdgeTypes::HAS_LI;

        // Fetching all LOs stay beyond current LO
        $loQuery = $db
            ->createQueryBuilder()
            ->select('required_ro.target_id')
            ->from('gc_ro', 'ro')
            ->join('ro', 'gc_ro', 'required_ro', 'ro.type = required_ro.type AND ro.source_id = required_ro.source_id')
            ->where('ro.type IN (:type)')->setParameter(':type', $edgeType, Connection::PARAM_INT_ARRAY)
            ->andwhere('required_ro.type = :requiredType')->setParameter(':requiredType', $requiredEdgeType)
            ->andWhere('ro.source_id = :source_id')->setParameter(':source_id', $parentLoId)
            ->andWhere('ro.target_id = :target_id')->setParameter(':target_id', $loId)
            ->andWhere('required_ro.weight < ro.weight');

        if (!$requiredLoIds = $loQuery->execute()->fetchAll(PDO::FETCH_COLUMN)) {
            return true;
        }

        // Fetching number of enrolled LO form above LoIds list
        $enrolmentQuery = $db
            ->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('gc_enrolment')
            ->where('lo_id IN (:lo_ids)')->setParameter(':lo_ids', $requiredLoIds, Connection::PARAM_INT_ARRAY)
            ->andWhere('profile_id = :profile_id')->setParameter(':profile_id', $profileId)
            ->andWhere('status = :status')->setParameter(':status', EnrolmentStatuses::COMPLETED);

        $completedRequiredLos = $enrolmentQuery->execute()->fetchColumn();

        return $completedRequiredLos >= count($requiredLoIds);
    }

    public static function childrenProgress(Connection $db, stdClass $enrolment)
    {
        $childrenId = LoHelper::childIds($db, $enrolment->lo_id);
        $progress = ['total' => count($childrenId)];
        if ($childrenId) {
            $q = 'SELECT status, count(id) as totalEnrolment FROM gc_enrolment WHERE lo_id IN (?) AND profile_id = ? AND parent_lo_id = ? GROUP BY status';
            $q = $db->executeQuery($q, [$childrenId, $enrolment->profile_id, $enrolment->lo_id], [DB::INTEGERS, DB::INTEGER, DB::INTEGER]);

            while ($row = $q->fetch(DB::OBJ)) {
                $progress[$row->status] = $row->totalEnrolment;
            }
        }
        return $progress;
    }

    public static function create(
        Connection $db,
        MqClient $queue,
        int $id,
        int $profileId,
        int $parentLoId = 0,
        stdClass $lo,
        int $instanceId,
        string $status = EnrolmentStatuses::IN_PROGRESS,
        string $startDate = null,
        string $endDate = null,
        int $result = 0,
        int $pass = 0,
        string $changed = null,
        array $data = []
    )
    {
        $date = DateTime::formatDate('now');

        $enrolment = [
            'id'                => $id,
            'profile_id'        => $profileId,
            'parent_lo_id'      => $parentLoId,
            'lo_id'             => $lo->id,
            'instance_id'       => 0,
            'taken_instance_id' => $instanceId,
            'status'            => $status,
            'start_date'        => $startDate ?? $date,
            'end_date'          => $endDate,
            'result'            => $result,
            'pass'              => $pass,
            'changed'           => $changed ?? $date,
            'timestamp'         => time(),
            'data'              => json_encode($data),
        ];

        $db->insert('gc_enrolment', $enrolment);

        if ($lo->marketplace) {
            if ($portal = PortalHelper::load($db, $lo->instance_id)) {
                if ((new PortalChecker)->isVirtual($portal)) {
                    $queue->publish(['type' => 'enrolment', 'object' => $enrolment], Queue::DO_USER_CREATE_VIRTUAL_ACCOUNT);
                }
            }
        }

        $queue->publish($enrolment, Queue::ENROLMENT_CREATE);
    }
}
