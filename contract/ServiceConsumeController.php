<?php

namespace go1\util\contract;

use Error as SystemError;
use go1\util\AccessChecker;
use go1\util\consume\exception\NotifyException;
use go1\util\Error;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Exception;

class ServiceConsumeController
{
    /** @var ServiceConsumerInterface[] */
    private $consumers;
    private $logger;

    public function __construct(array $consumers, LoggerInterface $logger)
    {
        $this->consumers = $consumers;
        $this->logger = $logger;
    }

    public function get(): JsonResponse
    {
        foreach ($this->consumers as $consumer) {
            foreach ($consumer->aware() as $routingKey => $description) {
                $info[get_class($consumer)][$routingKey] = $description;
            }
        }

        return new JsonResponse($info ?? []);
    }

    public function post(Request $req): JsonResponse
    {
        if (!(new AccessChecker)->isAccountsAdmin($req)) {
            return Error::simpleErrorJsonResponse('Internal resource', 403);
        }

        $routingKey = $req->get('routingKey');
        $body = $req->get('body');
        $body = is_scalar($body) ? json_decode($body) : json_decode(json_encode($body));
        $context = $req->get('context');
        $context = is_scalar($context) ? json_decode($context) : json_decode(json_encode($context, JSON_FORCE_OBJECT));

        return $body
            ? $this->consume($routingKey, $body, $context)
            : new JsonResponse(null, 204);
    }

    private function consume(string $routingKey, stdClass $body, $context): JsonResponse
    {
        foreach ($this->consumers as $consumer) {
            if ($consumer->aware()[$routingKey] ?? false) {
                try {
                    $consumer->consume($routingKey, $body, $context);
                    $headers['X-CONSUMERS'][] = get_class($consumer);
                } catch (NotifyException $e) {
                    $this->logger->log($e->getNotifyExceptionType(), sprintf('Failed to consume [%s] with %s %s: %s', $routingKey, json_encode($body), json_encode($context), json_encode($e->getNotifyExceptionMessage())));
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();

                    if (class_exists(TestCase::class, false)) {
                        throw $e;
                    }
                } catch (SystemError $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            $err = 'failed to consume [%s] [%s] %s %s';
            $err = sprintf($err, $routingKey, json_encode($errors), json_encode($body), json_encode($context));
            $this->logger->error($err);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(null, 204, $headers ?? []);
    }
}
