<?php

namespace go1\util\collection\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\lo\LoHelper;
use stdClass;

class CollectionItemEventEmbedder
{
    protected $go1;

    public function __construct(Connection $go1)
    {
        $this->go1 = $go1;
    }

    public function embedded(stdClass $collectionItem): array
    {
        $embedded = [];
        $learningObject = LoHelper::Load($this->go1, $collectionItem->lo_id);
        $embedded['lo'] = $learningObject;

        return $embedded;
    }
}
