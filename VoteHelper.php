<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class VoteHelper
{
    public static function buildCacheData(int $type, int $value, array $data = null)
    {
        switch ($type) {
            case VoteTypes::LIKE:
                $data = $data ?: array_fill_keys(['like', 'dislike'], 0);
                switch ($value) {
                    case VoteTypes::VALUE_LIKE:
                        $data['like']++;
                        break;

                    case VoteTypes::VALUE_DISLIKE:
                        $data['dislike']++;
                        break;
                }
                break;

            case VoteTypes::STAR:
                $data = $data ?: array_fill_keys(range(1, 5), 0);
                $data[$value]++;
                break;
        }

        return (0 !== array_sum($data)) ? $data : false;
    }

    public static function calculatePercent(int $type, array $data)
    {
        $percent = 0;

        switch ($type) {
            case VoteTypes::LIKE:
                $numerator = $data['like'] - $data['dislike'];
                $denominator = $data['like'] + $data['dislike'];
                if (0 === $denominator) {
                    $percent = 0;
                } else {
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

    public static function validateEntityType($entityType)
    {
        if (!in_array($entityType, VoteTypes::all())) {
            throw new \Exception('Entity type is invalid');
        }
    }

    public static function getEntityVote(Connection $db, string $entityType, int $entityId, int $type = VoteTypes::LIKE)
    {
        $vote = $db->executeQuery('SELECT * FROM vote_caches WHERE type = ? AND entity_type = ? AND entity_id = ?', [$type, $entityType, $entityId])->fetch(DB::OBJ);
        $vote && $vote->data = is_scalar($vote->data) ? json_decode($vote->data, true) : $vote->data;
        return $vote;
    }
}
