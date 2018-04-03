<?php

namespace go1\util\schema\tests;

use go1\clients\MailClient;
use go1\util\notify\MailTemplate;
use go1\util\portal\PortalHelper;
use go1\util\queue\Queue;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;

class MailClientTest extends UtilTestCase
{
    use PortalMockTrait;

    private $c;

    public function setUp()
    {
        parent::setUp();

        $this->c = $this->getContainer();
        $this->c->extend('go1.client.mq', function () {
            return $this->queue;
        });
    }

    public function testSmtpInstance()
    {
        $instanceId = $this->createPortal($this->db, [
            'title' => $instance = 'foo.bar',
            'data'  => [
                'configuration' => [PortalHelper::FEATURE_CUSTOM_SMTP => true],
            ],
        ]);

        /** @var MailClient $mailclient */
        $mailClient = $this->c['go1.client.mail'];
        $mailClient
            ->instance($this->db, $instance)
            ->post('foo@bar.com', new MailTemplate('id', 'subject', 'body', 'html'));

        $this->assertArrayHasKey(Queue::DO_MAIL_SEND, $this->queueMessages);
        $this->assertCount(1, $this->queueMessages[Queue::DO_MAIL_SEND]);
        $this->assertEquals(
            [
                'instance'      => 'foo.bar',
                'from_instance' => $instanceId,
                'recipient'     => 'foo@bar.com',
                'subject'       => 'subject',
                'body'          => 'body',
                'html'          => 'html',
                'context'       => [],
                'attachments'   => [],
                'options'       => [],
            ],
            $this->queueMessages[Queue::DO_MAIL_SEND][0]
        );
    }

    public function testNoSmtpInstance()
    {
        $instanceId = $this->createPortal($this->db, ['title' => $instance = 'foo.bar']);

        /** @var MailClient $mailclient */
        $mailClient = $this->c['go1.client.mail'];
        $mailClient
            ->instance($this->db, $instance)
            ->post('foo@bar.com', new MailTemplate('id', 'subject', 'body', 'html'));

        $this->assertArrayHasKey(Queue::DO_MAIL_SEND, $this->queueMessages);
        $this->assertCount(1, $this->queueMessages[Queue::DO_MAIL_SEND]);
        $this->assertEquals(
            [
                'from_instance' => $instanceId,
                'recipient'     => 'foo@bar.com',
                'subject'       => 'subject',
                'body'          => 'body',
                'html'          => 'html',
                'context'       => [],
                'attachments'   => [],
                'options'       => [],
            ],
            $this->queueMessages[Queue::DO_MAIL_SEND][0]
        );
    }

    public function testNoInstance()
    {
        /** @var MailClient $mailclient */
        $mailClient = $this->c['go1.client.mail'];
        $mailClient->post('foo@bar.com', new MailTemplate('id', 'subject', 'body', 'html'));

        $this->assertArrayHasKey(Queue::DO_MAIL_SEND, $this->queueMessages);
        $this->assertCount(1, $this->queueMessages[Queue::DO_MAIL_SEND]);
        $this->assertEquals(
            [
                'recipient'     => 'foo@bar.com',
                'subject'       => 'subject',
                'body'          => 'body',
                'html'          => 'html',
                'context'       => [],
                'attachments'   => [],
                'options'       => [],
            ],
            $this->queueMessages[Queue::DO_MAIL_SEND][0]
        );
    }
}
