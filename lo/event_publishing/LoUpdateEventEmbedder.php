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
                    $embedded['user'][$user->id] = $user;
                }
            }
        }
    }

    private function embedParentLo(array &$embedded, stdClass $lo)
    {
        if (in_array($lo->id, [LoTypes::AWARD, LoTypes::COURSE, LoTypes::MODULE])) {
            return null;
        }

        $hasLiEdges = EdgeHelper::edgesFromTarget($this->go1, $lo->id, [EdgeTypes::HAS_LI, EdgeTypes::HAS_ELECTIVE_LI]);
        if ($hasLiEdges) {
            foreach ($hasLiEdges as $hasLiEdge) {
                $embedded['ro'][$hasLiEdge] = $hasLiEdge;
                $moduleIds[] = (int) $hasLiEdge->source_id;
            }
        }

        if (!empty($moduleIds)) {
            $hasModuleEdges = EdgeHelper::edgesFromTargets($this->go1, $moduleIds, [EdgeTypes::HAS_MODULE]);
            foreach ($hasModuleEdges as $hasModuleEdge) {
                $embedded['ro'][$hasModuleEdge->id] = $hasModuleEdge;
                $courseIds[] = (int) $hasModuleEdge;
            }
        }

        if (!empty($courseIds)) {
            $courses = LoHelper::loadMultiple($this->go1, $courseIds, $lo->instance_id);
            if ($courses) {
                foreach ($courses as $course) {
                    $embedded['lo'][$course->id] = $course;
                }
            }
        }
    }
}
