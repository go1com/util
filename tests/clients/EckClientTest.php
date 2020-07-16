<?php

namespace go1\util\schema\tests;

use go1\api\infrastructure\UserApi;
use go1\clients\EckClient;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class EckClientTest extends UtilTestCase
{
    private $instance = 'qa.mygo1.com';
    private $eckUrl = 'http://eck.dev.go1.service';
    private $userId = 1234567;

    private function mockClient()
    {
        $qeliFieldsResp = new Response(200, [], json_encode([
            'instance'    => 'qeli.mygo1.com',
            'entity_type' => 'user',
            'fields'      => [
                'field_area' => [
                    'label'     => 'Specialist area',
                    'type'      => 'string',
                    'enum'      => [],
                    'mandatory' => 0,
                    'published' => 1,
                ],
                'field_phone' => [
                    'label'     => 'Phone',
                    'type'      => 'string',
                    'enum'      => [],
                    'mandatory' => 0,
                    'published' => 1,
                ],
                'field_position' => [
                    'label'     => 'Position',
                    'type'      => 'string',
                    'enum'      => [],
                    'mandatory' => 0,
                    'published' => 1,
                ],
            ],
        ]));

        $fieldValuesResp = new Response(200, [], json_encode([
            'instance'       => 'qeli.mygo1.com',
            'entity_type'    => 'account',
            'id'             => $this->userId,
            'custom_field_1' => [
                'value' => 'value 1 test'
            ],
            'custom_field_2' => [
                'value' => 'value 2 test'
            ]
        ]));

        $client = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
        $client
            ->expects($this->exactly(2))
            ->method('get')
            ->with($this->callback(function ($url) {
                $fieldsUrl = "{$this->eckUrl}/fields/{$this->instance}/user";
                $accountUrl = "{$this->eckUrl}/entity/{$this->instance}/account/{$this->userId}";
                if (strpos($url, $fieldsUrl) === 0) {
                    $this->assertStringContainsString($fieldsUrl, $url);
                } elseif (strpos($url, $accountUrl) === 0) {
                    $this->assertStringContainsString($accountUrl, $url);
                } else {
                    $this->assertTrue(false, 'Must call an eck service url!');
                    return false;
                }
                return true;
            }))
            ->willReturnOnConsecutiveCalls($qeliFieldsResp, $fieldValuesResp);
        return $client;
    }

    public function testUserFields()
    {
        $c = $this->getContainer();

        $c['client']  = $this->mockClient();
        $c['eck_url'] = $this->eckUrl;

        /** @var EckClient $eckClient */
        $eckClient = $c['go1.client.eck'];
        $fields    = $eckClient->fields($this->instance, 'user');

        $fieldArea = $fields['field_area'];
        $this->assertEquals('Specialist area', $fieldArea['label']);
        $this->assertEquals('string', $fieldArea['type']);
        $fieldPhone = $fields['field_phone'];
        $this->assertEquals('Phone', $fieldPhone['label']);
        $this->assertEquals('string', $fieldPhone['type']);

        $fieldValues = $eckClient->getEntityData($this->instance, 'account', $this->userId);
        $this->assertEquals('qeli.mygo1.com', $fieldValues['instance']);
        $this->assertEquals('account', $fieldValues['entity_type']);
        $this->assertEquals(1234567, $fieldValues['id']);
        $this->assertEquals(['value' => 'value 1 test'], $fieldValues['custom_field_1']);
        $this->assertEquals(['value' => 'value 2 test'], $fieldValues['custom_field_2']);
    }
}
