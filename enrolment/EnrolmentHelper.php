<?php

namespace go1\util\enrolment;

use DateTime as DefaultDateTime;
use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DateTime;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\event_publishing\EnrolmentEventsEmbedder;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\model\Enrolment;
use go1\util\plan\PlanHelper;
use go1\util\plan\PlanTypes;
use go1\util\portal\PortalChecker;
use go1\util\portal\PortalHelper;
use go1\util\queue\Queue;
use go1\util\user\UserHelper;
use LengthException;
use PDO;
use ReflectionClass;
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
    public static function isEmbeddedPortalActive(stdClass $enrolment): bool
    {
        $portal = $enrolment->embedded->portal ?? null;

        return $portal ? $portal->status : true;
    }

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

    /**
     * @deprecated
     * @see EnrolmentHelper::loadByLoProfileAndPortal()
     */
    public static function loadByLoAndProfileId(Connection $db, int $loId, int $profileId, int $parentLoId = null, $select = '*', $fetchMode = DB::OBJ)
    {
        $q = $db
            ->createQueryBuilder()
            ->select($select)
            ->from('gc_enrolment')
            ->where('lo_id = :lo_id')->setParameter(':lo_id', (int)$loId, DB::INTEGER)
            ->andWhere('profile_id = :profile_id')->setParameter(':profile_id', (int)$profileId, DB::INTEGER);

        $parentLoId && $q->andWhere('parent_lo_id = :parent_lo_id')->setParameter(':parent_lo_id', (int)$parentLoId, DB::INTEGER);
        $enrolments = $q->execute()->fetchAll($fetchMode);
        if (count($enrolments) > 1) {
            throw new LengthException('More than one enrolment return.');
        }

        return $enrolments ? $enrolments[0] : null;
    }

    public static function loadByLoProfileAndPortal(Connection $db, int $loId, int $profileId, int $portalId, int $parentLoId = null, $select = '*', $fetchMode = DB::OBJ)
    {
        $q = $db
            ->createQueryBuilder()
            ->select($select)
            ->from('gc_enrolment')
            ->where('lo_id = :lo_id')->setParameter(':lo_id', (int)$loId)
            ->andWhere('profile_id = :profile_id')->setParameter(':profile_id', (int)$profileId, DB::INTEGER)
            ->andWhere('taken_instance_id = :taken_instance_id')->setParameter(':taken_instance_id', (int)$portalId, DB::INTEGER);

        $parentLoId && $q->andWhere('parent_lo_id = :parent_lo_id')->setParameter(':parent_lo_id', (int)$parentLoId, DB::INTEGER);

        return $q->execute()->fetch($fetchMode);
    }

    public static function loadRevision(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment_revision WHERE enrolment_id = ?', [$id])
            ->fetch(DB::OBJ);
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
            $completion = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id IN (?) AND status = ? AND pass = 1';
            $completion = $db->fetchColumn($completion, [$dependencyIds, EnrolmentStatuses::COMPLETED], 0, [DB::INTEGERS, DB::STRING]);
        } else {
            $completion = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id IN (?) AND status = ?';
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

    public static function assessors(Connection $db, int $enrolmentId): array
    {
        $assessorIds = self::assessorIds($db, $enrolmentId);

        return !$assessorIds ? [] : UserHelper::loadMultiple($db, array_map('intval', $assessorIds));
    }

    /**
     * @deprecated
     */
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
                $parentEnrolment = $parentLo ? EnrolmentHelper::loadByLoProfileAndPortal($db, $parentLo->id, $enrolment->profile_id, $enrolment->taken_instance_id) : false,
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
        $edgeType = ($parentLoType == LoTypes::COURSE) ? EdgeTypes::LearningObjectTree['course'] : EdgeTypes::LearningObjectTree['module'];
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

    public static function childrenProgressCount(Connection $db, Enrolment $enrolment, $all = false, array $childTypes = [])
    {
        $childIds = LoHelper::childIds($db, $enrolment->loId, $all);
        $parentIds = array_merge($childIds, [$enrolment->loId]);
        if ($childIds && $childTypes) {
            $childIds = $db->executeQuery('SELECT id FROM gc_lo WHERE type IN (?) AND id IN (?)', [$childTypes, $childIds], [DB::STRINGS, DB::INTEGERS])->fetchAll(DB::COL);
        }
        $progress = ['total' => count($childIds)];
        if ($childIds) {
            $q = 'SELECT status, count(id) as totalEnrolment FROM gc_enrolment WHERE lo_id IN (?) AND profile_id = ? AND parent_lo_id IN (?) GROUP BY status';
            $q = $db->executeQuery($q, [$childIds, $enrolment->profileId, $parentIds], [DB::INTEGERS, DB::INTEGER, DB::INTEGERS]);
            while ($row = $q->fetch(DB::OBJ)) {
                $progress[$row->status] = $row->totalEnrolment;
            }
        }

        $numCompleted = $progress[EnrolmentStatuses::COMPLETED] ?? 0;
        $progress[EnrolmentStatuses::PERCENTAGE] = ($progress['total'] > 0) ? ($numCompleted / $progress['total']) : 0;
        $progress[EnrolmentStatuses::PERCENTAGE] = round($progress[EnrolmentStatuses::PERCENTAGE] * 100);

        return $progress;
    }

    public static function create(
        Connection $db,
        MqClient $queue,
        Enrolment $enrolment,
        stdClass $lo,
        EnrolmentEventsEmbedder $enrolmentEventsEmbedder,
        $assignerId = null,
        $notify = true
    )
    {
        $date = DateTime::formatDate('now');
        if (!$enrolment->startDate && ($enrolment->status != EnrolmentStatuses::NOT_STARTED)) {
            $enrolment->startDate = $date;
        }

        $data = [
            'id'                  => $enrolment->id,
            'profile_id'          => $enrolment->profileId,
            'parent_lo_id'        => $enrolment->parentLoId,
            'parent_enrolment_id' => $enrolment->parentEnrolmentId,
            'lo_id'               => $lo->id,
            'instance_id'         => 0,
            'taken_instance_id'   => $enrolment->takenPortalId,
            'status'              => $enrolment->status,
            'start_date'          => $enrolment->startDate,
            'end_date'            => $enrolment->endDate,
            'result'              => $enrolment->result,
            'pass'                => $enrolment->pass,
            'changed'             => $enrolment->changed ?? $date,
            'timestamp'           => time(),
            'data'                => json_encode($enrolment->data),
        ];

        $db->insert('gc_enrolment', $data);

        if ($lo->marketplace) {
            if ($portal = PortalHelper::load($db, $lo->instance_id)) {
                if ((new PortalChecker)->isVirtual($portal)) {
                    $queue->publish(['type' => 'enrolment', 'object' => $data], Queue::DO_USER_CREATE_VIRTUAL_ACCOUNT);
                }
            }
        }

        $rMqClient = new ReflectionClass(MqClient::class);
        $actorIdKey = $rMqClient->hasConstant('CONTEXT_ACTOR_ID') ? $rMqClient->getConstant('CONTEXT_ACTOR_ID') : 'actor_id';

        $data['embedded'] = $enrolmentEventsEmbedder->embedded((object) $data);
        $queue->publish($data, Queue::ENROLMENT_CREATE, ['notify_email' => $notify, $actorIdKey => $assignerId]);
    }

    public static function hasEnrolment(Connection $db, int $loId, int $profileId, int $parentLoId = null)
    {
        return static::loadByLoAndProfileId($db, $loId, $profileId, $parentLoId, '1', DB::COL);
    }

    public static function countUserEnrolment(Connection $db, int $profileId, int $takenInstanceId = null): int
    {
        $q = $db->createQueryBuilder();
        $q
            ->select('count(*)')
            ->from('gc_enrolment')
            ->where('profile_id = :profile_id')
            ->setParameter('profile_id', $profileId);

        $takenInstanceId && $q
            ->andWhere('taken_instance_id = :taken_instance_id')
            ->setParameter('taken_instance_id', $takenInstanceId);

        return $q->execute()->fetchColumn();
    }

    public static function dueDate(Connection $db, int $enrolmentId): ?DefaultDateTime
    {
        $edges = EdgeHelper::edgesFromSources($db, [$enrolmentId], [EdgeTypes::HAS_PLAN]);
        if ($edges) {
            $dueDate = null;
            foreach ($edges as $edge) {
                if ($edge && ($plan = PlanHelper::load($db, $edge->target_id))) {
                    if ($plan->due_date && (PlanTypes::SUGGESTED == $plan->type)) {
                        return DateTime::create($plan->due_date);
                    }

                    if ($plan->due_date) {
                        $dueDate = DateTime::create($plan->due_date);
                    }
                }
            }

            return $dueDate;
        }

        return null;
    }

    public static function loadUserEnrolment(Connection $db, int $portalId, int $profileId, int $loId, int $parentEnrolmentId = null): ?Enrolment
    {
        $q = $db
            ->createQueryBuilder()
            ->select('*')
            ->from('gc_enrolment')
            ->where('lo_id = :loId')->setParameter(':loId', $loId)
            ->andWhere('profile_id = :profileId')->setParameter(':profileId', $profileId)
            ->andWhere('taken_instance_id = :takenInstanceId')->setParameter(':takenInstanceId', $portalId);

        !is_null($parentEnrolmentId) && $q
            ->andWhere('parent_enrolment_id = :parentEnrolmentId')
            ->setParameter(':parentEnrolmentId', $parentEnrolmentId);

        $row = $q->execute()->fetch(DB::OBJ);

        return $row ? Enrolment::create($row) : null;
    }

    public static function childIds(Connection $db, int $enrolmentId): array
    {
        return $db
            ->createQueryBuilder()
            ->select('id')
            ->from('gc_enrolment')
            ->where('parent_enrolment_id = :parentEnrolmentId')
            ->setParameter(':parentEnrolmentId', $enrolmentId)
            ->execute()
            ->fetchAll(DB::COL);
    }

    public static function loadSingle(Connection $db, int $enrolmentId): ?Enrolment
    {
        $row = 'SELECT * FROM gc_enrolment WHERE id = ?';
        $row = $db->executeQuery($row, [$enrolmentId], [DB::INTEGER])->fetch(DB::OBJ);

        return $row ? Enrolment::create($row) : null;
    }

    public static function parentEnrolment(Connection $db, Enrolment $enrolment, $parentLoType = LoTypes::COURSE): ?Enrolment
    {
        if ($db->fetchColumn('SELECT 1 FROM gc_lo WHERE type = ? AND id = ?', [$parentLoType, $enrolment->loId])) {
            return $enrolment;
        }
        $parentEnrolment = $enrolment->parentEnrolmentId ? EnrolmentHelper::loadSingle($db, $enrolment->parentEnrolmentId) : null;

        return $parentEnrolment ? static::parentEnrolment($db, $parentEnrolment, $parentLoType) : null;
    }
}
