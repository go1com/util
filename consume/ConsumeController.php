<?php

namespace go1\util\consume;

use Error as SystemError;
use Exception;
use go1\app\domain\TerminateAwareJsonResponse;
use go1\clients\MqClient;
use go1\util\AccessChecker;
use go1\util\consume\exception\NotifyException;
use go1\util\contract\ConsumerInterface;
use go1\util\Error;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ConsumeController
{
    /** @var ConsumerInterface[] */
    private $consumers;
    private $logger;
    private $accessChecker;
    private $logWasteTime;

    public function __construct(
        array $consumers,
        LoggerInterface $logger,
        AccessChecker $accessChecker,
        bool $logWasteTime = false
    )
    {
        $this->consumers = $consumers;
        $this->logger = $logger;
        $this->accessChecker = $accessChecker;
        $this->logWasteTime = $logWasteTime;
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
                        $consumer->consume($routingKey, $body, $context);
                    }
                    catch (NotifyException $e) {
                        $this->logger->log($e->getNotifyExceptionType(), sprintf('Failed to consume [%s] with %s %s: %s', $routingKey, json_encode($body), json_encode($context), json_encode($e->getNotifyExceptionMessage())));
                    }
                    catch (Exception $e) {
                        $errors[] = $e->getMessage();

                        if (defined('APP_ROOT') && class_exists(TestCase::class, false)) {
                            throw $e;
                        }
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

        return !$this->logWasteTime
            ? new JsonResponse(null, 204)
            : new TerminateAwareJsonResponse(null, 204, [
                function () use ($routingKey, $body, $context) {
                    $messageReleaseTime = $context->{MqClient::CONTEXT_TIMESTAMP} ?? null;
                    if ($messageReleaseTime) {
                        $wasteTime = time() - $messageReleaseTime;
                        $this->logger->error(sprintf('consume.waste-time.%s: %d %s', $routingKey, $wasteTime, json_encode($body)));
                    }
                },
            ]);
    }
}
