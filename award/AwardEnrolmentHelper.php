<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use PDO;

class AwardEnrolmentHelper
{
    public static function assessorIds(Connection $db, int $enrolmentId): array
    {
        return EdgeHelper
            ::select('source_id')
            ->get($db, [], [$enrolmentId], [EdgeTypes::HAS_AWARD_TUTOR_ENROLMENT_EDGE], PDO::FETCH_COLUMN);
    }
}
