<?php

namespace go1\util\edge\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\lo\LoHelper;
use stdClass;

class LoSaveAssessorEventEmbedder
{
    protected $go1;

    public function __construct(Connection $go1)
    {
        $this->go1 = $go1;
    }

    public function embedded(stdClass $body): array
    {
        $embedded = [];
        $learningObject = LoHelper::load($this->go1, $body->id);
        $embedded['lo'] = $learningObject;

        return $embedded;
    }
}
