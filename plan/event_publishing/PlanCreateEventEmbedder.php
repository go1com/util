<?php

namespace go1\util\plan\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\lo\LoHelper;
use go1\util\plan\Plan;
use go1\util\plan\PlanTypes;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;

class PlanCreateEventEmbedder
{
    protected $go1;

    public function __construct(Connection $go1)
    {
        $this->go1 = $go1;
    }

    public function embedded(Plan $plan): array
    {
        $embedded = [];
        $portal = PortalHelper::load($this->go1, $plan->instanceId);
        if ($portal) {
            $embedded['portal'] = $portal;

            $user = UserHelper::load($this->go1, $plan->userId, null, 'mail');
            $account = $user ? UserHelper::loadByEmail($this->go1, $portal->title, $user->mail) : null;
            if ($account) {
                $embedded['account'] = $account;
            }
        }

        if (PlanTypes::ENTITY_LO == $plan->entityType) {
            if ($lo = LoHelper::load($this->go1, $plan->entityId)) {
                $embedded['entity'] = $lo;
            }
        }

        return $embedded;
    }
}
