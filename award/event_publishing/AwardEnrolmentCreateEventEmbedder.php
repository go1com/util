<?php

namespace go1\util\award\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\award\AwardHelper;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class AwardEnrolmentCreateEventEmbedder
{
    protected $go1;
    protected $award;
    protected $access;

    public function __construct(Connection $go1, Connection $award, AccessChecker $access)
    {
        $this->go1 = $go1;
        $this->award = $award;
        $this->access = $access;
    }

    public function embedded(stdClass $awardEnrolment, Request $req = null): array
    {
        if ($award = AwardHelper::load($this->award, $awardEnrolment->award_id)) {
            $embedded['award'] = $award;
        }

        if ($portal = PortalHelper::load($this->go1, $awardEnrolment->instance_id)) {
            $embedded['portal'] = $portal;

            $user = UserHelper::load($this->go1, $awardEnrolment->user_id, null, 'mail');
            $account = $user ? UserHelper::loadByEmail($this->go1, $portal->title, $user->mail) : null;
            if ($account) {
                $embedded['account'] = $account;
            }
        }

        if ($req) {
            $user = $this->access->validUser($req, $portal ? $portal->title : null);
            if ($user) {
                $embedded['jwt']['user'] = $user;
            }
        }

        return $embedded;
    }
}
