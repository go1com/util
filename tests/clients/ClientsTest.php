<?php

namespace go1\util\tests\clients;

use go1\clients\CurrencyClient;
use go1\clients\EntityClient;
use go1\clients\GraphinClient;
use go1\clients\LoClient;
use go1\clients\MailClient;
use go1\clients\MqClient;
use go1\clients\PortalClient;
use go1\clients\QueueClient;
use go1\clients\RulesClient;
use go1\clients\SmsClient;
use go1\util\tests\UtilTestCase;

class ClientsTest extends UtilTestCase
{
    public function test()
    {
        $c = $this->getContainer();

        $this->assertEquals(true, class_exists(MqClient::class));

        $this->assertTrue($c['go1.client.portal'] instanceof PortalClient);
        $this->assertTrue($c['go1.client.graphin'] instanceof GraphinClient);
        $this->assertTrue($c['go1.client.rules'] instanceof RulesClient);
        $this->assertTrue($c['go1.client.currency'] instanceof CurrencyClient);
        $this->assertTrue($c['go1.client.lo'] instanceof LoClient);
        $this->assertTrue($c['go1.client.queue'] instanceof QueueClient);
        $this->assertTrue($c['go1.client.mail'] instanceof MailClient);
        $this->assertTrue($c['go1.client.sms'] instanceof SmsClient);
        $this->assertTrue($c['go1.client.entity'] instanceof EntityClient);
    }
}
