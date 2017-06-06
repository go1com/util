<?php

namespace go1\util\model;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use PDO;
use stdClass;

/**
 * Just for reference, not ready for using yet.
 */
class User
{
    /** @var integer */
    public $id, $profileId;

    /** @var string */
    public $instance, $name, $mail, $firstName, $lastName, $avatar;

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

    /**
     * @param stdClass        $row
     * @param Connection|null $db
     * @param bool            $root
     * @param string|null     $instance
     *   Only need this param if $root is true.
     *   When the param is provided, sub account will be filled.
     * @return User
     */
    public static function create(stdClass $row, Connection $db = null, $root = true, string $instance = null)
    {
        $user = new User;
        $user->id = $row->id;
        $user->profileId = isset($row->profile_id) ? $row->profile_id : null;
        $user->instance = $row->instance;
        $user->name = isset($row->name) ? $row->name : null;
        $user->mail = $row->mail;
        $user->firstName = $row->first_name;
        $user->lastName = $row->last_name;
        $user->status = $row->status;
        $user->created = $row->created;
        $user->access = $row->access;
        $user->login = $row->login;
        $user->timestamp = $row->timestamp;
        $user->data = is_string($row->data) ? json_decode($row->data) : $row->data;
        $user->avatar = $row->data->avatar->uri ?? null;

        if ($db) {
            // Fill the roles
            $roleIds = 'SELECT target_id FROM gc_ro WHERE type = ? AND source_id = ?';
            $roleIds = $db->executeQuery($roleIds, [EdgeTypes::HAS_ROLE, $user->id])->fetchAll(PDO::FETCH_COLUMN);
            $user->roles = $db->executeQuery('SELECT name FROM gc_role WHERE id IN (?)', [$roleIds], [DB::INTEGERS])->fetchAll(PDO::FETCH_COLUMN);

            // Fill accounts
            if ($root && $instance) {
                $accountIds = 'SELECT target_id FROM gc_ro WHERE type = ? AND source_id = ?';
                $accountIds = $db->executeQuery($accountIds, [EdgeTypes::HAS_ACCOUNT, $user->id])->fetchAll(PDO::FETCH_COLUMN);
                if ($accountIds) {
                    $q = 'SELECT * FROM gc_user WHERE status = 1 AND id IN (?) AND instance = ?';
                    $q = $db->executeQuery($q, [$accountIds, $instance], [DB::INTEGERS]);
                    while ($account = $q->fetch(DB::OBJ)) {
                        $user->accounts[] = static::create($account, $db, false);
                    }
                }
            }
        }

        return $user;
    }
}
