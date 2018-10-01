<?php

namespace go1\util\enrolment\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\lo\LoHelper;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class EnrolmentEventsEmbedder
{
    private $go1;
    private $access;

    public function __construct(Connection $go1, AccessChecker $access)
    {
        $this->go1 = $go1;
        $this->access = $access;
    }

    public function embedded(stdClass $enrolment, Request $req): array
    {
        $embedded = [];

        $portal = PortalHelper::load($this->go1, $enrolment->taken_instance_id);
        if ($portal) {
            $embedded['portal'][$portal->id] = $portal;

            $account = UserHelper::loadByProfileId($this->go1, $enrolment->profile_id, $portal->title);
            if ($account) {
                $embedded['account'][$account->id] = $account;
            }
        }

        $lo = LoHelper::load($this->go1, $enrolment->lo_id);
        if ($lo) {
            $embedded['lo'][$lo->id] = $lo;
        }

        $user = $this->access->validUser($req, $portal ? $portal->title : null);
        if ($user) {
            $embedded['jwt']['user'] = $user;
        }

        return $embedded;
    }
}
