<?php

namespace go1\util\lo\event_publishing;

use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\user\UserHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class LoUpdateEventEmbedder extends LoCreateEventEmbedder
{
    public function embedded(stdClass $lo, Request $req): array
    {
        $embedded = parent::embedded($lo, $req);

        $this->embedAuthors($embedded, $lo->id);
        $this->embedParentLo($embedded, $lo);

        return $embedded;
    }

    private function embedAuthors(array &$embedded, int $loId)
    {
        $hasAuthorEdges = EdgeHelper::edgesFromSource($this->go1, $loId, [EdgeTypes::HAS_AUTHOR_EDGE]);
        if ($hasAuthorEdges) {
            foreach ($hasAuthorEdges as $hasAuthorEdge) {
                $userIds[] = (int) $hasAuthorEdge->target_id;
            }
        }

        if (!empty($userIds)) {
            $users = UserHelper::loadMultiple($this->go1, $userIds);
            if ($users) {
                foreach ($users as &$user) {
                    $embedded['authors'][] = $user;
                }
            }
        }
    }

    private function embedParentLo(array &$embedded, stdClass $lo)
    {
        if (in_array($lo->type, [LoTypes::AWARD, LoTypes::COURSE, LoTypes::MODULE])) {
            return null;
        }

        $parentLoIds = LoHelper::parentIds($this->go1, $lo->id);
        $parentLos = LoHelper::loadMultiple($this->go1, $parentLoIds, $lo->instance_id);
        if ($parentLos) {
            foreach ($parentLos as $parentLo) {
                $embedded['parents'][] = $parentLo;
            }
        }
    }
}
