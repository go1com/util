<?php

namespace go1\clients;

use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class SchedulerClient
{
    private $client;
    private $schedulerUrl;
    private $logger;

    public function __construct(Client $client, LoggerInterface $logger, string $schedulerUrl)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->schedulerUrl = $schedulerUrl;
    }

    public function saveJob($jobNameOrId, $expression, Request $actionReq, $retry = false, $status = 1)
    {
        try {
            $headers = [];
            foreach ($actionReq->headers->keys() as $key) {
                $headers[$key] = $actionReq->headers->get($key);
            }
            $this->client->put("$this->schedulerUrl/job/$jobNameOrId?jwt=" . UserHelper::ROOT_JWT, [
                'json' => [
                    'status'          => $status,
                    'cron_expression' => $expression,
                    'actions'         => [
                        [
                            'type' => 'http',
                            'data' => array_filter([
                                'url'     => $actionReq->getUri(),
                                'method'  => $actionReq->getMethod(),
                                'headers' => $headers ?: null,
                                'body'    => $actionReq->request->all() ?: null,
                            ]),
                        ],
                    ],
                ],
            ]);
        } catch (RequestException $e) {
            if ($retry) {
                return $this->saveJob($jobNameOrId, $expression, $actionReq);
            }

            $this->logger->error("Failed to put scheduler job", [
                'jobNameOrId' => $jobNameOrId,
                'exception'   => $e->getMessage(),
            ]);
        }
    }

    public function deleteJob($jobNameOrId, $retry = false)
    {
        try {
            $this->client->delete("$this->schedulerUrl/job/$jobNameOrId?jwt=" . UserHelper::ROOT_JWT);
        } catch (RequestException $e) {
            if (404 === $e->getResponse()->getStatusCode()) {
                return;
            }
            if ($retry) {
                return $this->deleteJob($jobNameOrId);
            }
            $this->logger->error("Failed to delete scheduler job", [
                'jobNameOrId' => $jobNameOrId,
                'exception'   => $e->getMessage(),
            ]);
        }
    }

    public function getJob(string $jobName): array
    {
        try {
            $res = $this->client->get("$this->schedulerUrl/job?name=$jobName", [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . UserHelper::ROOT_JWT,
                ],
            ]);

            if (200 === $res->getStatusCode()) {
                return json_decode($res->getBody()->getContents());
            }

            return [];
        } catch (RequestException $e) {
            $this->logger->error("Can not get scheduler job", [
                'jobName'   => $jobName,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
