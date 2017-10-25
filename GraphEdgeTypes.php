<?php

namespace go1\util;

class GraphEdgeTypes
{
    const HAS_ITEM         = 'HAS_ITEM';
    const HAS_ROLE         = 'HAS_ROLE';
    const HAS_ACCOUNT      = 'HAS_ACCOUNT';
    const HAS_ROOT_ACCOUNT = 'HAS_ROOT_ACCOUNT';
    const HAS_USER         = 'HAS_USER';
    const HAS_GROUP        = 'HAS_GROUP';
    const HAS_MEMBER       = 'HAS_MEMBER';
    const HAS_CLONE        = 'HAS_CLONE';
    const HAS_TAG          = 'HAS_TAG';                 # (:lo/li)-[]->(:tag)
    const HAS_CUSTOM_TAG   = 'HAS_CUSTOM_TAG';          # (:lo/li)-[]->(:tag)
    const HAS_EVENT        = 'HAS_EVENT';
    const HAS_PRODUCT      = 'HAS_PRODUCT';
    const HAS_REMOTE       = 'HAS_REMOTE';
    const HAS_AUTHOR       = 'HAS_AUTHOR';
    const HAS_ASSESSOR     = 'HAS_ASSESSOR';
    const HAS_ENROLMENT    = 'HAS_ENROLMENT';
    const HAS_MANAGER      = 'HAS_MANAGER';
    const HAS_LEARNER      = 'HAS_LEARNER';
    const HAS_LO           = 'HAS_LO';
    const HAS_FOLLOWER     = 'HAS_FOLLOWER';
    const HAS_FOLLOWING    = 'HAS_FOLLOWING';
    const HAS_NOTE         = 'HAS_NOTE';
    const HAS_SHARED_NOTE  = 'HAS_SHARED_NOTE';
    const HAS_SHARED_LO    = 'HAS_SHARED_LO';
    const HAS_SHARED_GROUP = 'HAS_SHARED_GROUP';
    const HAS_REACT        = 'HAS_REACT';               # (:user)-[{reaction: 0|1|-1}]->(:li)
    const HAS_VOTE         = 'HAS_VOTE';                # (:user)-[]->(:tag)
    const HAS_MENTION      = 'HAS_MENTION';             # (:user)-[{offset: int, length: int}]->(:lo/li)
    const HAS_GROUP_OWN    = 'HAS_GROUP_OWN';           # (:user)-[]->(:group)
    const HAS_RO_PARENT    = 'HAS_RO_PARENT';           # (:tag)-[]->(:parent:ro)
    const HAS_RO_CHILD     = 'HAS_RO_CHILD';            # (:tag)-[]->(:parent:ro)
    const HAS_RO_TAG       = 'HAS_RO_TAG';              # (:parent:ro)-[]->(:tag)
    const HAS_RO_PORTAL    = 'HAS_RO_PORTAL';           # (:ro)-[]->(:portal)
    const HAS_RO           = 'HAS_RO';                  # (:portal)-[]->(:ro)

    public static $roles = [
        'student'            => 'student',
        'tutor'              => 'tutor',
        'manager'            => 'manager',
        'admin on #accounts' => 'root',
        'administrator'      => 'administrator',
    ];

    public static function role($name)
    {
        $name = strtolower($name);

        return isset(static::$roles[$name]) ? static::$roles[$name] : null;
    }

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

    public static function getEntityGraphData(string $entityType, int $entityId)
    {
        switch ($entityType) {
            case 'user':
                return ['User', 'id', $entityId];

            case 'lo':
                return ['Group', 'name', "lo:$entityId"];

            case 'portal':
                return ['Group', 'name', "portal:$entityId"];
        }

        throw new \Exception('Invalid entity type.');
    }
}
