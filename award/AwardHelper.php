<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use stdClass;
use go1\util\text\Xss;


class AwardHelper
{
    public static function format(stdClass $award)
    {
        $data = is_scalar($award->data) ? json_decode($award->data, true) : $award->data;

        return (object) [
            'id'            => (int) $award->id,
            'revision_id'   => (int) $award->revision_id,
            'instance_id'   => (int) $award->instance_id,
            'user_id'       => (int) $award->user_id,
            'title'         => Xss::filter($award->title),
            'description'   => Xss::filter($award->description),
            'tags'          => $award->tags, // We filter in the CRUD
            'locale'        => $award->locale,
            'data'          => (object) (is_array($data) ? array_diff_key($data, ['avatar' => 0, 'roles' => 0]) : $data),
            'published'     => (int) $award->published,
            'quantity'      => isset($award->quantity) ? (int) $award->quantity : null,
            'expire'        => ctype_digit($award->expire) ? (int) $award->expire : $award->expire,
            'created'       => (int) $award->created,
            'items'         => isset($award->items) ? $award->items : [],
            'enrolment'     => isset($award->enrolment) ? $award->enrolment : null,
        ];
    }

    public static function loadByRevision(Connection $db, int $revisionId)
    {
        return $db
            ->executeQuery('SELECT * FROM award_award WHERE revision_id = ?', [$revisionId])
            ->fetch(DB::OBJ);
    }

    public static function loadItem(Connection $db, int $awardItemId)
    {
        return $db
            ->executeQuery('SELECT * FROM award_item WHERE id = ?', [$awardItemId])
            ->fetch(DB::OBJ);
    }
}
