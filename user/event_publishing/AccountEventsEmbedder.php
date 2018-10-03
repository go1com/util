<?php

namespace go1\util\user\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\portal\PortalHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class AccountEventsEmbedder
{
    private $go1;
    private $access;

    public function __construct(Connection $go1, AccessChecker $access)
    {
        $this->go1 = $go1;
        $this->access = $access;
    }

    public function embed(stdClass $account, Request $req = null): array
    {
        $embedded = [];

        $portal = PortalHelper::load($this->go1, $account->instance);
        if ($portal) {
            $embedded['portal'][$portal->id] = $portal;
        }

        $user = $req ? $this->access->validUser($req, $portal ? $portal->title : null) : null;
        if ($user) {
            $embedded['jwt']['user'] = $user;
        }

        return $embedded;
    }
}
