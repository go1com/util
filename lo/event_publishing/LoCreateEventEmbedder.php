<?php

namespace go1\util\lo\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\portal\PortalHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class LoCreateEventEmbedder
{
    protected $go1;
    protected $access;

    public function __construct(Connection $go1, AccessChecker $access)
    {
        $this->go1 = $go1;
        $this->access = $access;
    }

    public function embedded(stdClass $lo, Request $req): array
    {
        $embedded = [];

        $portal = PortalHelper::load($this->go1, $lo->instance_id);
        if ($portal) {
            $embedded['portal'] = $portal;
        }

        $user = $this->access->validUser($req, $portal ? $portal->title : null);
        if ($user) {
            $embedded['jwt']['user'] = $user;
        }

        return $embedded;
    }
}
