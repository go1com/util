<?php

namespace go1\util\consume;

use go1\util\AccessChecker;
use go1\util\contract\ServiceConsumerInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    private function getConsumersInfo(): JsonResponse
    {
        foreach ($this->consumers as $consumer) {
            foreach ($consumer->aware() as $routingKey => $description) {
                $info[$consumer->name()][$routingKey] = $description;
            }
        }

        return $info ?? [];
    }

    public function post(Request $req): JsonResponse
    {
        if (!(new AccessChecker)->isAccountsAdmin($req)) {
            return Error::simpleErrorJsonResponse('Internal resource', 403);
        }

        if ($req->query->get('info')) {
            return $this->getConsumersInfo();
        }

        $routingKey = $req->get('routingKey');
        $body = $req->get('body');
        $body = is_scalar($body) ? json_decode($body) : json_decode(json_encode($body));
        $context = $req->get('context');
        $context = is_scalar($context) ? json_decode($context) : json_decode(json_encode($context, JSON_FORCE_OBJECT));

        return $body
            ? $this->doConsume($routingKey, $body, $context)
            : new JsonResponse(null, 204);
    }

    private function consume(string $routingKey, stdClass $body, $context): JsonResponse
    {
        foreach ($this->consumers as $consumer) {
            if (in_array($routingKey, $consumer->aware())) {
                try {
                    $consumer->consume($routingKey, $body, $context);
                    $headers['X-CONSUMERS'][] = get_class($consumer);
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
            $err = 'failed to consume [%s] with %s %s: %s';
            $err = sprintf($err, $routingKey, json_encode($body), json_encode($context), json_encode($errors));
            $this->logger->error($err);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(null, 204, $headers ?? []);
    }
}
