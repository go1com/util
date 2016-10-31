<?php

namespace go1\util;

class GraphEdgeTypes
{
    const HAS_ITEM             = 'HAS_ITEM';
    const HAS_ROLE             = 'HAS_ROLE';
    const HAS_ACCOUNT          = 'HAS_ACCOUNT';
    const HAS_ROOT_ACCOUNT     = 'HAS_ROOT_ACCOUNT';
    const HAS_USER             = 'HAS_USER';
    const HAS_GROUP            = 'HAS_GROUP';
    const HAS_MEMBER           = 'HAS_MEMBER';
    const HAS_CLONE            = 'HAS_CLONE';
    const HAS_TAG              = 'HAS_TAG';
    const HAS_EVENT            = 'HAS_EVENT';
    const HAS_PRODUCT          = 'HAS_PRODUCT';
    const HAS_REMOTE           = 'HAS_REMOTE';
    const HAS_AUTHOR           = 'HAS_AUTHOR';
    const HAS_TUTOR            = 'HAS_TUTOR';
    const HAS_ENROLMENT        = 'HAS_ENROLMENT';
    const HAS_LO               = 'HAS_LO';
    const HAS_FOLLOWER         = 'HAS_FOLLOWER';
    const HAS_FOLLOWING        = 'HAS_FOLLOWING';
    const HAS_NOTE             = 'HAS_NOTE';
    const HAS_SHARED_NOTE      = 'HAS_SHARED_NOTE';
    const HAS_SHARED_LO        = 'HAS_SHARED_LO';
    const HAS_MENTION          = 'HAS_MENTION';

    /**
     * User reaction
     * (user)-[{reaction: 0|1|-1}]->(li)
     */
    const HAS_REACT            = 'HAS_REACT';

    /**
     * User vote
     * (user)-->(tag)
     */
    const HAS_VOTE             = 'HAS_VOTE';

    public static function type($name)
    {
        $name = strtolower($name);
        $name = ('learning_pathways' === $name) ? 'learning_pathway' : $name;
        $name = ('activities' === $name) ? 'activity' : $name;
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        return $name;
    }
}
