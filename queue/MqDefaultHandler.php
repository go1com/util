<?php

namespace go1\util\queue;

use Exception;
use go1\clients\MqClient;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MqDefaultHandler implements QueueMiddlewareInterface
{
    private $channel;
    private $propertyAccessor;

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function handle(
        string $exchange,
        string $routingKey,
        array &$body,
        array &$context = [],
        Request $req = null): bool
    {
        $this->processMessage($body, $routingKey);
        $context[MqClient::CONTEXT_TIMESTAMP] = $context[MqClient::CONTEXT_TIMESTAMP] ?? time();

        if (!$exchange) {
            $body = json_encode(['routingKey' => $routingKey, 'body' => $body]);
            $routingKey = Queue::WORKER_QUEUE_NAME;
        }

        if ($service = getenv('SERVICE_80_NAME')) {
            $context['app'] = $service;
        }

        $this->channel->basic_publish(
            new AMQPMessage(
                json_encode($body),
                [
                    'content_type'        => 'application/json',
                    'application_headers' => new AMQPTable($context),
                ]
            ),
            $exchange,
            $routingKey
        );

        return true;
    }

    private function processMessage(array $body, string $routingKey)
    {
        # Quiz does not have `id` property.
        if (Queue::QUIZ_USER_ANSWER_UPDATE == $routingKey) {
            return null;
        }

        $explode = explode('.', $routingKey);
        $isLazy = isset($explode[0]) && ('do' == $explode[0]); # Lazy = do.SERVICE.#

        if (strpos($routingKey, '.update') && !$isLazy) {
            if ('post_' === substr($routingKey, 0, 5)) {
                return null;
            }

            $enough = 2 === count(
                    array_filter(
                        $body,
                        function ($value, $key) {
                            return (in_array($key, ['id', 'original']) && $value);
                        },
                        ARRAY_FILTER_USE_BOTH
                    )
                );

            if (!$enough) {
                throw new Exception('Missing entity ID or original data.');
            }
        }
    }
}
