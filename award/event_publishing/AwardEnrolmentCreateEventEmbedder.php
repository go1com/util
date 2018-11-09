<?php

namespace go1\util\award\event_publishing;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\award\AwardHelper;
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
        $embedded = [];

        $award = AwardHelper::load($this->award, $awardEnrolment->award_id);
        if ($award) {
            $embedded['award'] = $award;
        }

        $portal = AwardHelper::load($this->go1, $awardEnrolment->instance_id);
        if ($award) {
            $embedded['portal'] = $award;
        }

        $account = UserHelper::load($this->go1, $awardEnrolment->user_id);
        if ($award) {
            $embedded['account'] = $account;
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
