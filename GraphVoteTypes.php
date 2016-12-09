<?php

namespace go1\util;

class GraphVoteTypes
{
    const LIKE = 'like';
    const STAR = 'star';

    const ENTITY_TYPE_LO = 'lo';
    const ENTITY_TYPE_LI = 'li';
    const ENTITY_TYPE_NOTE = 'note';
    const ENTITY_TYPE_TAG = 'tag';
    const ENTITY_TYPE_COMMENT = 'comment';

    public static $entityTypes = [
        self::ENTITY_TYPE_LO,
        self::ENTITY_TYPE_LI,
        self::ENTITY_TYPE_NOTE,
        self::ENTITY_TYPE_TAG,
        self::ENTITY_TYPE_COMMENT,
    ];

    public static function getIdFromType($entityType)
    {
        self::validateEntityType($entityType);

        switch ($entityType) {
            case self::ENTITY_TYPE_NOTE:
                return 'uuid';

            case self::ENTITY_TYPE_TAG:
                return 'name';

            case self::ENTITY_TYPE_LI:
            case self::ENTITY_TYPE_LO:
            default:
                return 'id';
        }
    }

    public static function getLabelFromType($entityType)
    {
        self::validateEntityType($entityType);

        switch ($entityType) {
            case self::ENTITY_TYPE_NOTE:
                return 'Note';

            case self::ENTITY_TYPE_TAG:
                return 'Tag';

            case self::ENTITY_TYPE_LI:
            case self::ENTITY_TYPE_LO:
            default:
                return 'Group';
        }
    }

    public static function validateEntityType($entityType)
    {
        if (!in_array($entityType, self::$entityTypes)) {
            throw new \Exception('Entity type is invalid');
        }
    }
}
