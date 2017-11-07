<?php

namespace go1\util\vote;

use Doctrine\DBAL\Connection;
use Exception;
use go1\util\DB;
use PDO;

class VoteHelper
{
    public static function load(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM vote_items WHERE id = ?', [$id])
            ->fetch(DB::OBJ);
    }

    public static function getCacheData(Connection $db, $type, $entityType, $entityId)
    {
        $qb = $db->createQueryBuilder();
        $qb
            ->select('value, COUNT(1) as count')
            ->from('vote_items')
            ->where('type = :type')
            ->andWhere('entity_type = :entityType')
            ->andWhere('entity_id = :entityId')
            ->groupBy('value')
            ->setParameters([
                'type'        => $type,
                'entityType' => $entityType,
                'entityId'   => $entityId,
            ]);

        $data = $qb->execute()->fetchAll(PDO::FETCH_ASSOC);
        $data = array_combine(array_column($data, 'value'), array_column($data, 'count'));

        $cacheData = [];
        switch ($type) {
            case VoteTypes::LIKE:
                $cacheData = [
                    'like' => isset($data[VoteTypes::VALUE_LIKE]) ? $data[VoteTypes::VALUE_LIKE] : 0,
                    'dislike' => isset($data[VoteTypes::VALUE_DISLIKE]) ? $data[VoteTypes::VALUE_DISLIKE] : 0,
                    'dismiss' => isset($data[VoteTypes::VALUE_DISMISS]) ? $data[VoteTypes::VALUE_DISMISS] : 0,
                ];
                break;

            case VoteTypes::STAR:
                for ($i = 1; $i <= 5; $i++) {
                    $cacheData[$i] = isset($data[$i]) ? $data[$i] : 0;
                }
                break;
        }

        return array_sum($cacheData) ? $cacheData : false;
    }

    public static function calculatePercent(int $type, array $data)
    {
        $percent = 0;

        switch ($type) {
            case VoteTypes::LIKE:
                $numerator = $data['like'];
                $denominator = $data['like'] + $data['dislike'];
                if (0 === $denominator) {
                    $percent = 0;
                }
                else {
                    $percent = $numerator / $denominator * 100;
                }
                break;

            case VoteTypes::STAR:
                $numerator = $denominator = 0;
                foreach ($data as $star => $voteCount) {
                    $numerator += $star * $voteCount;
                    $denominator += $voteCount;
                }
                $percent = ($numerator * 100) / ($denominator * 5);
                break;
        }

        return $percent;
    }

    public static function getGraphIdFromType(string $entityType)
    {
        self::validateEntityType($entityType);

        switch ($entityType) {
            case VoteTypes::ENTITY_TYPE_NOTE:
                return 'uuid';

            case VoteTypes::ENTITY_TYPE_TAG:
                return 'name';

            case VoteTypes::ENTITY_TYPE_LO:
            default:
                return 'id';
        }
    }

    public static function getGraphLabelFromType(string $entityType)
    {
        self::validateEntityType($entityType);

        switch ($entityType) {
            case VoteTypes::ENTITY_TYPE_NOTE:
                return 'Note';

            case VoteTypes::ENTITY_TYPE_TAG:
                return 'Tag';

            case VoteTypes::ENTITY_TYPE_LO:
            default:
                return 'Group';
        }
    }

    public static function validateEntityType(string $entityType)
    {
        if (!in_array($entityType, VoteTypes::all())) {
            throw new Exception('Invalid entity type.');
        }
    }

    public static function getEntityVote(Connection $db, string $entityType, int $entityId, int $type = VoteTypes::LIKE)
    {
        $vote = 'SELECT * FROM vote_caches WHERE type = ? AND entity_type = ? AND entity_id = ?';
        $vote = $db->executeQuery($vote, [$type, $entityType, $entityId])->fetch(DB::OBJ);
        $vote && $vote->data = is_scalar($vote->data) ? json_decode($vote->data, true) : $vote->data;

        return $vote;
    }
}
