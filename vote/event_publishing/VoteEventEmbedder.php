<?php

namespace go1\util\vote\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\lo\LoHelper;
use go1\util\vote\VoteHelper;
use go1\util\vote\VoteTypes;
use stdClass;

class VoteEventEmbedder
{
    protected $go1;
    protected $vote;

    public function __construct(Connection $go1, Connection $vote)
    {
        $this->go1 = $go1;
        $this->vote = $vote;
    }

    public function embedded(stdClass $vote): array
    {
        $embedded = [];

        if ($vote->entity_type === VoteTypes::ENTITY_TYPE_LO) {
            $lo = LoHelper::load($this->go1, $vote->entity_id);

            if ($lo) {
                $embedded['lo'] = (array)$lo;
                $voteInfo = VoteHelper::getEntityVote($this->vote, VoteTypes::ENTITY_TYPE_LO, (int)$lo->id, $vote->type);

                if ($voteInfo) {
                    $embedded['vote'] = [
                        'percent' => (int)$voteInfo->percent,
                        'like'    => $voteInfo->data['like'] ?? 0,
                        'dislike' => $voteInfo->data['dislike'] ?? 0,
                    ];
                }
            }
        }

        return $embedded;
    }
}
