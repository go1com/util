<?php

namespace go1\clients;

use go1\util\queue\Queue;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

trait RequestTrait
{
    /** @var  LoggerInterface */
    private $logger;

    /** @var  Client */
    private $client;

    /** @var  QueueClient */
    private $queueClient;

    /** @var  MqClient */
    private $mqClient;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function setQueueClient(QueueClient $queueClient)
    {
        $this->queueClient = $queueClient;
    }

    /**
     * Make HTTP request, queue to try again if failed.
     *
     * @return mixed|null|ResponseInterface
     */
    protected function request(string $method, string $url, array $headers, $body)
    {
        try {
            $options = ['headers' => $headers, 'json' => $body];
            if (method_exists($this->client, 'createRequest')) {
                $request = $this->client->createRequest($method, $url, $options);

                return $this->client->send($request);
            }

            return $this->client->request($method, $url, $options);
        }
        catch (BadResponseException $e) {
            if ($this->logger) {
                $this->logger->error('Failed to send request: ' . $e->getMessage());
            }

            if ($this->mqClient) {
                $msg = [
                    'method'  => $method,
                    'url'     => $url,
                    'query'   => 'foo=bar&bar=baz&time=' . time(),
                    'headers' => $headers,
                    'body'    => is_scalar($body) ? $body : json_encode($body),
                ];

                $this->mqClient->publish($msg, Queue::DO_CONSUMER_HTTP_REQUEST);
            }
            else {
                if ($this->queueClient) {
                    $this
                        ->queueClient
                        ->queue('accounts-legacy', 'accounts.worker.legacy.post', [$method, $url, [], $body]);
                }

                return $e->getResponse();
            }
        }
    }
}
