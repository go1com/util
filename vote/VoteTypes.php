<?php

namespace go1\util\vote;

class VoteTypes
{
    const LIKE = 1;
    const STAR = 2;

    const ENTITY_TYPE_LO   = 'lo';
    const ENTITY_TYPE_NOTE = 'note';
    const ENTITY_TYPE_TAG  = 'tag';

    const VALUE_LIKE    = 1;
    const VALUE_DISLIKE = 0;
    const VALUE_DISMISS = -1;

    public static function all()
    {
        return [self::ENTITY_TYPE_LO, self::ENTITY_TYPE_NOTE];
    }
}
