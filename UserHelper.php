<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class UserHelper
{
    const ROOT_JWT      = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';
    const DEFAULT_ROLES = [Roles::STUDENT, Roles::AUTHENTICATED];

    public static function load(Connection $db, int $id)
    {
        $user = 'SELECT * FROM gc_user WHERE id = ?';
        $user = $db->executeQuery($user, [$id])->fetch(DB::OBJ);

        return $user;
    }

    /**
     * @param \Doctrine\DBAL\Connection $db
     * @param array $ids
     * @return mixed
     */
    public static function loadMultiple(Connection $db, array $ids)
    {
        $sql = 'SELECT * FROM gc_user WHERE id IN (?)';
        return $db->executeQuery($sql, [$ids], [Connection::PARAM_INT_ARRAY])->fetchAll(DB::OBJ);
    }

    /**
     * @param \Doctrine\DBAL\Connection $db
     * @param $profileId
     * @param $portalName
     * @return mixed
     */
    public static function loadByProfileId(Connection $db, int $profileId, string $instanceName)
    {
        $sql = 'SELECT * FROM gc_user WHERE profile_id = ? AND instance = ?';
        return $db->executeQuery($sql, [$profileId, $instanceName])->fetch(DB::OBJ);
    }

    public function uuid2jwt(Client $client, $userUrl, $uuid)
    {
        $url = rtrim($userUrl, '/') . "/account/current/{$uuid}";
        $res = $client->get($url, ['http_errors' => false]);

        return (200 == $res->getStatusCode())
            ? json_decode($res->getBody()->getContents())->jwt
            : false;
    }

    public function profileId2uuid(Client $client, $userUrl, $profileId)
    {
        $jwt = JWT::encode(['admin' => true], 'INTERNAL');
        $url = rtrim($userUrl, '/') . "/account/-/{$profileId}?jwt=$jwt";
        $res = $client->get($url, ['https_errors' => false]);

        return (200 == $res->getStatusCode())
            ? json_decode($res->getBody()->getContents())->uuid
            : false;
    }

    public function profileId2jwt(Client $client, $userUrl, $profileId)
    {
        return ($uuid = $this->profileId2uuid($client, $userUrl, $profileId))
            ? $this->uuid2jwt($client, $userUrl, $uuid)
            : false;
    }

    public function name(stdClass $user, bool $last = false)
    {
        $name = $last ? "{$user->first_name} {$user->last_name}" : $user->first_name;

        return trim($name) ?: $user->mail;
    }

    public static function jwt(Request $req)
    {
        if ($auth = $req->headers->get('Authorization') ?: $req->headers->get('Authorization')) {
            if (0 === strpos($auth, 'Bearer ')) {
                return substr($auth, 7);
            }
        }

        if (!$token = $req->query->get('jwt', isset($token))) {
            if (!$token = $req->cookies->get('jwt')) {
                return false;
            }
        }

        return (2 === substr_count($token, '.')) ? $token : false;
    }

    public static function authorizationHeader(Request $req)
    {
        if (!$jwt = static::jwt($req)) {
            throw new RuntimeException('JWT not found.');
        }

        return [
            'Content-Type'  => 'application/json',
            'Authorization' => "Bearer $jwt",
        ];
    }

    public static function encode(stdClass &$payload): string
    {
        $array = isset($payload->object->content) ? $payload : [
            'iss'    => 'go1.user',
            'ver'    => '2.0',
            'exp'    => strtotime('+ 1 month'),
            'object' => ['type' => 'user', 'content' => $payload],
        ];

        return JWT::encode($array, 'INTERNAL');
    }

    public function format(stdClass $user)
    {
        $data = is_scalar($user->data) ? json_decode($user->data, true) : $user->data;

        return (object) [
            'id'         => (int) $user->id,
            'mail'       => $user->mail,
            'name'       => "{$user->first_name} {$user->last_name}",
            'profile_id' => (int) $user->profile_id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'roles'      => isset($data['roles']) ? $data['roles'] : null,
            'avatar'     => isset($data['avatar']['uri']) ? $data['avatar']['uri'] : null,
            'created'    => (int) $user->created,
            'login'      => (int) $user->login,
            'status'     => (bool) $user->status,
            'data'       => (object) (is_array($data) ? array_diff_key($data, ['avatar' => 0, 'roles' => 0]) : $data),
            'phone'      => isset($data['phone']) ? $data['phone'] : null,
            'root'       => null,
        ];
    }

    public function attachRootAccount(Connection $db, array &$users, $accountsName)
    {
        $mails = array_column($users, 'mail');
        $q = $db->createQueryBuilder();
        $q
            ->select('u.id, u.mail, u.profile_id')
            ->from('gc_user', 'u')
            ->where($q->expr()->in('u.mail', ':mails'))
            ->andWhere('u.instance = :instance')
            ->setParameter(':mails', $mails, Connection::PARAM_STR_ARRAY)
            ->setParameter(':instance', $accountsName);

        $results = $q->execute();
        while ($rootAcc = $results->fetch()) {
            foreach ($users as &$user) {
                if ($rootAcc['mail'] == $user->mail) {
                    $user->root = [
                        'id'         => (int) $rootAcc['id'],
                        'profile_id' => (int) $rootAcc['profile_id'],
                    ];
                }
            }
        }
    }
}
