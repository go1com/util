<?php

namespace go1\clients;

use go1\util\user\UserHelper;
use GuzzleHttp\Client;

class EckClient
{
    private $client;
    private $url;

    public function __construct(Client $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function portalFields(string $instance) : array
    {
        $payload = (object) ['mail' => "admin@{$instance}", 'roles' => ['Admin on #Accounts']];
        $jwt = UserHelper::encode($payload);

        $data = $this->client->get("{$this->url}/fields/{$instance}/user?jwt={$jwt}")->getBody()->getContents();
        $data = json_decode($data, true);

        $fields = [];
        if (!empty($data)) {
            foreach ($data['fields'] as $fieldName => $item) {
                $fields[$fieldName] = ['label' => $item['label'], 'type' => $item['type']];
            }
        }

        return $fields;
    }
}
