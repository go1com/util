<?php

namespace go1\util;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use stdClass;

class UserHelper
{
    const ROOT_JWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7InJvbGVzIjpbIkFkbWluIG9uICNBY2NvdW50cyJdLCJtYWlsIjoiand0QGdvMS5jb20ifX19.rCKoEXiqTQAtDofak30NESqYoSgkOIclS1SPaHx4WqU';

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
        $jwt = JWT::encode(['admin' => true], 'GO1INTERNAL');
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
}
