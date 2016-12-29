<?php

namespace go1\util\model;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\EdgeTypes;
use stdClass;

/**
 * Just for reference, not ready for using yet.
 */
class User
{
    /** @var integer */
    public $id, $profileId;

    /** @var string */
    public $instance, $name, $mail, $firstName, $lastName;

    /** @var bool */
    public $status;

    /** @var integer */
    public $created, $access, $login, $timestamp;

    /** @var string[] */
    public $roles = [];

    /** @var User[] */
    public $accounts = [];

    /** @var object */
    public $data;

    public static function create(stdClass $row, Connection $db = null, $root = true)
    {
        $user = new User;
        $user->id = $row->id;
        $user->profileId = $row->profileId;
        $user->instance = $row->instance;
        $user->name = $row->name;
        $user->mail = $row->mail;
        $user->firstName = $row->first_name;
        $user->lastName = $row->last_name;
        $user->status = $row->status;
        $user->created = $row->created;
        $user->access = $row->access;
        $user->login = $row->login;
        $user->timestamp = $row->timestamp;
        $user->data = is_string($row->data) ? json_decode($row->data) : $row->data;

        if ($db) {
            // Fill the roles
            $roleIds = 'SELECT target_id FROM gc_role WHERE type = ? AND source_id = ?';
            $roleIds = $db->fetchColumn($roleIds, [EdgeTypes::HAS_ROLE, $user->id]);
            $user->roles = $db->fetchColumn('SELECT name FROM gc_role WHERE id IN (?)', [$roleIds], [DB::INTEGERS]);

            // Fill accounts
            if ($root) {
                $accountIds = 'SELECT target_id FROM gc_role WHERE type = ? AND source_id = ?';
                $accountIds = $db->fetchColumn($accountIds, [EdgeTypes::HAS_ACCOUNT, $user->id]);
                if ($accountIds) {
                    $q = $db->executeQuery('SELECT FROM gc_user WHERE status = 1 AND id IN (?)', [$accountIds], [DB::INTEGERS]);
                    while ($account = $q->fetch(DB::OBJ)) {
                        $user->accounts[] = static::create($row, $db);
                    }
                }
            }
        }

        return $user;
    }
}
