<?php

namespace go1\util\consume;

use Exception;
use go1\util\AccessChecker;
use go1\util\contract\ConsumerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ConsumeController
{
    /** @var ConsumerInterface[] */
    private $consumers;
    private $logger;
    private $accessChecker;

    public function __construct(array $consumers, LoggerInterface $logger, AccessChecker $accessChecker)
    {
        $this->consumers = $consumers;
        $this->logger = $logger;
        $this->accessChecker = $accessChecker;
    }

    public function post(Request $req)
    {
        if (!$this->accessChecker->isAccountsAdmin($req)) {
            return new JsonResponse(['message' => 'Internal resource'], 403);
        }

        $routingKey = $req->get('routingKey');
        $body = $req->get('body');
        $body = is_scalar($body) ? json_decode($body) : json_decode(json_encode($body));

        if ($body) {
            try {
                foreach ($this->consumers as $consumer) {
                    if ($consumer->aware($routingKey)) {
                        $consumer->consume($routingKey, $body);
                    }
                }

                return new JsonResponse(null, 204);
            }
            catch (Exception $e) {
                $this->logger->error(printf('Failed to consume [%s] with %s: %s', $routingKey, json_encode($body), $e->getMessage()));
            }
        }

        return new JsonResponse(null, 500);
    }
}
