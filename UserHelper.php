<?php

namespace go1\util;

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
}
