<?php

namespace go1\util\policy\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\lo\LoHelper;
use stdClass;

class PolicyEventEmbedder
{
    protected $go1;

    public function __construct(Connection $go1)
    {
        $this->go1 = $go1;
    }

    public function embedded(stdClass $policyItem): array
    {
        $embedded = [];
        $learningObject = LoHelper::load($this->go1, $policyItem->host_entity_id);
        $embedded['hostEntity'] = $learningObject;

        return $embedded;
    }
}
