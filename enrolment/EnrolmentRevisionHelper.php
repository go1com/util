<?php

namespace go1\util\enrolment;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class EnrolmentRevisionHelper
{
    public static function childIds(Connection $go1, int $enrolmentId, $all = false, string $status = EnrolmentStatuses::COMPLETED): array
    {
        $q = 'SELECT enrolment_id FROM gc_enrolment_revision WHERE parent_enrolment_id = ? AND status = ?';
        $q = $go1->executeQuery($q, [$enrolmentId, $status], [DB::INTEGER, DB::STRING]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $all && $ids = array_merge($ids, static::childIds($go1, $id));
            $ids[] = (int) $id;
        }

        return $ids;
    }
}
