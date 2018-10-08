<?php

namespace go1\util\award\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class AwardCreateEventEmbedder
{
    protected $go1;
    protected $access;

    public function __construct(Connection $go1, AccessChecker $access)
    {
        $this->go1 = $go1;
        $this->access = $access;
    }

    public function embedded(stdClass $award, Request $req = null): array
    {
        $embedded = [];

        $portal = PortalHelper::load($this->go1, $award->instance_id);
        if ($portal) {
            $embedded['portal'] = $portal;
        }

        if ($req) {
            $user = $this->access->validUser($req, $portal ? $portal->title : null);
            if ($user) {
                $embedded['jwt']['user'] = $user;
            }
        }

        $embedded['authors'][] = UserHelper::load($this->go1, $award->user_id);

        return $embedded;
    }
}
