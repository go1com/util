<?php

namespace go1\util\policy\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\EntityTypes;
use go1\util\group\GroupHelper;
use go1\util\lo\LoHelper;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use stdClass;

class PolicyEventEmbedder
{
    protected $go1;
    protected $social;

    public function __construct(Connection $go1, Connection $social)
    {
        $this->go1 = $go1;
        $this->social = $social;
    }

    public function embedded(stdClass $policyItem): array
    {
        $embedded = [];
        $learningObject = LoHelper::load($this->go1, $policyItem->host_entity_id);
        $embedded['hostEntity'] = $learningObject;

        if ($entity = $this->loadEntity($policyItem->entity_type, $policyItem->entity_id)) {
            $embedded['entity'] = $entity;
        }

        return $embedded;
    }

    private function loadEntity(string $entityType, int $entityId): ?stdClass
    {
        $entity = false;
        switch ($entityType) {
            case EntityTypes::PORTAL:
                $entity = PortalHelper::load($this->go1, $entityId);
                break;

            case EntityTypes::USER:
                $entity = UserHelper::load($this->go1, $entityId);
                break;

            case EntityTypes::GROUP:
                $entity = GroupHelper::load($this->social, $entityId);
                break;
        }

        return $entity ?: null;
    }
}
