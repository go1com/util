<?php

namespace go1\clients;

use Firebase\FirebaseLib;

class FirebaseClient
{
    /** @var  FirebaseLib */
    private $client;
    private $defaultPath;

    public function __construct(string $baseUri, string $token, string $defaultPath)
    {
        $this->client = new FirebaseLib($baseUri, $token);
        $this->defaultPath = $defaultPath;
    }

    public function push($name, $data)
    {
        return $this->client->push("{$this->defaultPath}/{$name}", $data);
    }

    public function set($name, $data)
    {
        return $this->client->set("{$this->defaultPath}/{$name}", $data);
    }

    public function get($name)
    {
        return $this->client->get("{$this->defaultPath}/{$name}");
    }

    public function delete($name)
    {
        return $this->client->delete("{$this->defaultPath}/{$name}");
    }
}
