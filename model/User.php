<?php

namespace go1\util\model;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use JsonSerializable;
use PDO;
use stdClass;

/**
 * Just for reference, not ready for using yet.
 */
class User implements JsonSerializable
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
        $user->profileId = $row->profile_id ?? null;
        $user->instance = $row->instance ?? null;
        $user->name = $row->name ?? null;
        $user->mail = $row->mail ?? null;
        $user->firstName = $row->first_name ?? null;
        $user->lastName = $row->last_name ?? null;
        $user->status = $row->status ?? null;
        $user->created = $row->created ?? null;
        $user->access = $row->access ?? null;
        $user->login = $row->login ?? null;
        $user->timestamp = $row->timestamp ?? null;
        $user->data = is_scalar($row->data) ? json_decode($row->data) : $row->data;
        $user->roles = $row->roles ?? $user->data->roles ?? null;
        $user->avatar = $row->avatar ?? $user->data->avatar->uri ?? null;

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

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    function jsonSerialize()
    {
        return [
            'id'         => $this->id,
            'profile_id' => $this->profileId,
            'instance'   => $this->instance,
            'name'       => $this->name,
            'mail'       => $this->mail,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'avatar'     => $this->avatar,
            'status'     => $this->status,
            'created'    => $this->created,
            'access'     => $this->access,
            'login'      => $this->login,
            'timestamp'  => $this->timestamp,
            'roles'      => $this->roles,
            'accounts'   => $this->accounts,
            'data'       => $this->data,
        ];
    }

    public function diff(stdClass $user2)
    {
        $user1 = $this->jsonSerialize();
        $user2 = static::create($user2)->jsonSerialize();

        $diff = [];
        foreach ($user1 as $property => $value) {
            if ($user2[$property] != $value) {
                $diff[$property] = [
                    'source' => $value,
                    'target' => $user2[$property],
                ];
            }
        }
        return $diff;
    }
}
