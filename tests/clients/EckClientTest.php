<?php

namespace go1\util\schema\tests;

use go1\clients\EckClient;
use go1\clients\MailClient;
use go1\clients\portal\config\MailTemplate;
use go1\util\portal\PortalHelper;
use go1\util\Queue;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class EckClientTest extends UtilTestCase
{
    private $instance = 'qa.mygo1.com';
    private $eckUrl = 'http://eck.dev.go1.service';

    private function mockClient()
    {
        $qeliFields = '{"instance": "qeli.mygo1.com","entity_type": "user","fields": {"field_area": {"label": "Specialist area","type": "string"},"field_phone": {"label": "Phone","type": "string"},"field_position": {"label": "Position","type": "string"}}}';
        $client = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
        $client
            ->expects($this->once())
            ->method('get')
            ->with($this->callback(function ($url) use ($qeliFields){
                $this->assertContains("{$this->eckUrl}/fields/{$this->instance}/user", $url);

                return true;
            }))
            ->willReturn(new Response(200, [], $qeliFields));

        return $client;
    }

    public function testUserFields()
    {
        $c = $this->getContainer();

        $c['client'] = $this->mockClient();
        $c['eck_url'] = $this->eckUrl;

        /** @var EckClient $eckClient */
        $eckClient = $c['go1.client.eck'];
        $fields = $eckClient->fields($this->instance, 'user');

        $fieldArea = $fields['field_area'];
        $this->assertEquals('Specialist area', $fieldArea['label']);
        $this->assertEquals('string', $fieldArea['type']);
        $fieldPhone = $fields['field_phone'];
        $this->assertEquals('Phone', $fieldPhone['label']);
        $this->assertEquals('string', $fieldPhone['type']);
    }
}
