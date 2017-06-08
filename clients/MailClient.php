<?php

namespace go1\clients;

use Doctrine\DBAL\Connection;
use go1\clients\portal\config\MailTemplate as Template;
use go1\util\MailTemplate;
use go1\util\portal\PortalChecker;
use go1\util\Queue;
use InvalidArgumentException;

class MailClient
{
    private $queue;
    private $instance;
    private $instanceId;

    public function __construct(MqClient $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Usage: $mail
     *              ->instance($db, $instance)
     *              ->post(…);
     */
    public function instance(Connection $db, $instance): MailClient
    {
        $helper = new PortalChecker;
        $portal = is_object($instance) ? $instance : $helper->load($db, $instance);
        if ($portal) {
            $client = clone $this;
            $client->instanceId = $portal->id;
            if ($helper->useCustomSMTP($portal)) {
                $client->instance = $portal->title;
            }

            return $client;
        }

        return $this;
    }

    public function post($recipient, Template $template, array $context = [], array $options = [], $attachments = [], $cc = [], $bcc = [])
    {
        $this->send(null, $recipient, $template->getSubject(), $template->getBody(), $template->getHtml(), $context, $options, $attachments, $cc, $bcc);
    }

    /**
     * @deprecated
     */
    public function send($privateKey, $recipient, $subject, $body, $html, array $context = [], array $options = [], $attachments = [], $cc = [], $bcc = [])
    {
        $data = array_filter(['cc' => $cc, 'bcc' => $bcc]);

        if ($this->instance) {
            $data['instance'] = $this->instance;
        }

        if ($this->instanceId) {
            $data['from_instance'] = $this->instanceId;
        }

        $data += [
            'recipient'   => $recipient,
            'subject'     => $subject,
            'body'        => $body,
            'html'        => $html,
            'context'     => $context,
            'attachments' => $attachments, # array of ['name' => $name, 'url' => $url]
            'options'     => $options,
        ];

        $this->queue->queue($data, Queue::DO_MAIL_SEND);
    }

    public function template(PortalClient $portalClient, string $instance, string $mailKey, string $defaultSubject, string $defaultBody, string $defaultHtml = null): Template
    {
        if (!MailTemplate::has($mailKey)) {
            throw new InvalidArgumentException('Invalid mail key: ' . $mailKey);
        }

        try {
            return $portalClient->mailTemplate($instance, $mailKey);
        }
        catch (InvalidArgumentException $e) {
            return new Template($defaultSubject, $defaultBody, $defaultHtml);
        }
    }
}
