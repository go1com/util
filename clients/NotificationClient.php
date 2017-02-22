<?php

namespace go1\clients;

use GuzzleHttp\Client;

class NotificationClient
{
    private $client;
    private $notificationUrl;

    public function __construct(Client $client, $notificationUrl)
    {
        $this->client = $client;
        $this->notificationUrl = rtrim($notificationUrl, '/');
    }

    public function notify($profileId, array $data)
    {
        return 200 == $this
                ->client
                ->post(
                    "{$this->notificationUrl}/notification", [
                        'json' => [
                            'pid'         => $profileId,
                            'message'     => $data['message'],
                            'image'       => $data['image'] ?: null,
                            'tag'         => $data['tag'] ?: null,
                            'from'        => $data['from'] ?: null,
                            'instance_id' => $data['instance_id'] ?: null,
                        ]]
                )
                ->getStatusCode();
    }
}
