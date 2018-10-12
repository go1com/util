<?php

namespace go1\util\lo\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\lo\LoHelper;
use go1\util\portal\PortalHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class LoCustomTagCreateEventEmbedder
{
    protected $go1;
    protected $access;

    public function __construct(Connection $go1, AccessChecker $access)
    {
        $this->go1 = $go1;
        $this->access = $access;
    }

    public function embedded(stdClass $customTag, Request $req = null): array
    {
        $embedded = [];

        if ($portal = PortalHelper::load($this->go1, $customTag->instance_id)) {
            $embedded['portal'] = $portal;
        }

        if ($lo = LoHelper::load($this->go1, $customTag->lo_id)) {
            $embedded['lo'] = $lo;
        }

        $user = $req ? $this->access->validUser($req, $portal ? $portal->title : null) : null;
        if ($user) {
            $embedded['jwt']['user'] = $user;
        }

        return $embedded;
    }
}
