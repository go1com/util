<?php

namespace go1\clients;

use go1\util\queue\Queue;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use stdClass;

class UserClient
{
    private $client;
    private $userUrl;
    private $mqClient;

    public function __construct(Client $client, string $userUrl, MqClient $mqClient)
    {
        $this->client = $client;
        $this->userUrl = rtrim($userUrl, '/');
        $this->mqClient = $mqClient;
    }

    public function userUrl(): string
    {
        return $this->userUrl;
    }

    public function client(): Client
    {
        return $this->client;
    }

    public function unblockEmail($mail)
    {
        $this->mqClient->publish(['mail' => $mail], Queue::DO_USER_UNBLOCK_MAIL);
    }

    public function unblockIp($ip)
    {
        $this->mqClient->publish(['ip' => $ip], Queue::DO_USER_UNBLOCK_IP);
    }

    public function login(string $name, string $pass, string $instance = null, $jwtExpire = '+ 1 month'): stdClass
    {
        $json = $this
            ->client
            ->post("{$this->userUrl}/account/login", [
                'headers' => ['JWT-Expire-Time' => $jwtExpire],
                'json'    => array_filter([
                    'portal'   => $instance,
                    'username' => $name,
                    'password' => $pass,
                ]),
            ])
            ->getBody()
            ->getContents();

        return json_decode($json);
    }

    public function current($uuid, $instance, $jwtExpire = '+ 1 month')
    {
        $body = $this
            ->client
            ->get(
                "{$this->userUrl}/account/current/{$uuid}/$instance", [
                'headers' => ['JWT-Expire-Time' => $jwtExpire],
            ])
            ->getBody()
            ->getContents();

        return json_decode($body);
    }

    public function register($accountsName, $instance, $mail, $pass, $first, $last, $data = null, $jwtExpire = '+ 1 month')
    {
        return $this->client->post("$this->userUrl/account", [
            'http_errors' => false,
            'headers'     => ['JWT-Expire-Time' => $jwtExpire],
            'json'        => array_filter([
                'instance'   => $accountsName,
                'portal'     => $instance,
                'email'      => $mail,
                'password'   => $pass ?: Uuid::uuid4()->toString(),
                'random'     => !$pass,
                'first_name' => $first,
                'last_name'  => $last,
                'data'       => $data,
            ]),
        ]);
    }

    /**
     * @param string   $portalName
     * @param string[] $roles
     * @param bool     $all
     * @param int      $limit
     * @param int      $offset
     * @return stdClass[]
     */
    public function findUsers($portalName, array $roles, $all = false, $limit = 50, $offset = 0)
    {
        $roles = implode(',', $roles);
        while (true) {
            $res = $this->client->get("{$this->userUrl}/account/find/{$portalName}/{$roles}?limit=$limit&offset=$offset");
            $users = json_decode($res->getBody()->getContents());
            if ($users) {
                foreach ($users as $user) {
                    yield $user;
                }
            }

            $offset += $limit;
            if (!$users || !$all) {
                break;
            }
        }
    }

    public function findAdministrators($portalName, $all = false, $limit = 10, $offset = 0)
    {
        return $this->findUsers($portalName, ['administrator'], $all, $limit, $offset);
    }
}
