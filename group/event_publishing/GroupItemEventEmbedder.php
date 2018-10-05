<?php

namespace go1\util\group\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\award\AwardHelper;
use go1\util\group\GroupHelper;
use go1\util\group\GroupItemTypes;
use go1\util\lo\LoHelper;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use stdClass;

class GroupItemEventEmbedder
{
    private $go1;
    private $social;
    private $award;

    public function __construct(Connection $go1, Connection $social, Connection $award)
    {
        $this->go1 = $go1;
        $this->social = $social;
        $this->award = $award;
    }

    public function embedded(stdClass $groupItem): array
    {
        $embedded = [];
        $embedded['entity'] = $this->loadEntity($groupItem->entity_type, $groupItem->entity_id);
        $embedded['group'] = GroupHelper::load($this->social, $groupItem->group_id);

        return $embedded;
    }

    private function loadEntity(string $entityType, int $entityId): ?stdClass
    {
        $entity = false;
        switch ($entityType) {
            case GroupItemTypes::LO:
                $entity = LoHelper::load($this->go1, $entityId);
                break;

            case GroupItemTypes::USER:
                $entity = UserHelper::load($this->go1, $entityId);
                break;

            case GroupItemTypes::PORTAL:
                $entity = PortalHelper::load($this->go1, $entityId);
                break;

            case GroupItemTypes::AWARD:
                $entity = AwardHelper::load($this->award, $entityId);
                break;

            case GroupItemTypes::GROUP:
                $entity = GroupHelper::load($this->social, $entityId);
                break;
        }

        return $entity ?: null;
    }
}
