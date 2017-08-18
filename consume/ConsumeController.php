<?php

namespace go1\util\consume;

use Error as SystemError;
use Exception;
use go1\clients\MqClient;
use go1\util\AccessChecker;
use go1\util\contract\ConsumerInterface;
use go1\util\Error;
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
            return Error::simpleErrorJsonResponse('Internal resource', 403);
        }

        $routingKey = $req->get('routingKey');
        $body = $req->get('body');
        $body = is_scalar($body) ? json_decode($body) : json_decode(json_encode($body));
        $context = $req->get('context');
        $context = is_scalar($context) ? json_decode($context) : json_decode(json_encode($context, JSON_FORCE_OBJECT));
        $errors = [];

        if ($body) {
            foreach ($this->consumers as $consumer) {
                if ($consumer->aware($routingKey)) {
                    try {
                        if (isset($context->messagePriority)) {
                            MqClient::$priority = $context->messagePriority;
                        }

                        $consumer->consume($routingKey, $body, $context);
                    }
                    catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                    catch (SystemError $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }

        if ($errors) {
            $this->logger->error(sprintf('Failed to consume [%s] with %s %s: %s', $routingKey, json_encode($body), json_encode($context), json_encode($errors)));

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(null, 204);
    }
}
