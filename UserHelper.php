<?php

namespace go1\util;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class UserHelper
{
    public function uuid2jwt(Client $client, $userUrl, $uuid)
    {
        $url = rtrim($userUrl, '/') . "/account/current/{$uuid}";
        $res = $client->get($url, ['http_errors' => false]);

        return (200 == $res->getStatusCode())
            ? json_decode($res->getBody()->getContents())->jwt
            : false;
    }

    public function profileId2jwt(Client $client, $userUrl, $profileId)
    {
        $jwt = JWT::encode(['admin' => true], 'GO1INTERNAL');
        $url = rtrim($userUrl, '/') . "/account/-/{$profileId}?jwt=$jwt";
        $res = $client->get($url, ['https_errors' => false]);

        return (200 == $res->getStatusCode())
            ? $this->uuid2jwt($client, $userUrl, json_decode($res->getBody()->getContents())->uuid)
            : false;
    }
}
