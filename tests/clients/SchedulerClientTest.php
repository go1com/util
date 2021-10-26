<?php

namespace go1\util\schema\tests;

use go1\clients\SchedulerClient;
use go1\util\tests\UtilTestCase;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;

class SchedulerClientTest extends UtilTestCase
{
    private $schedulerUrl  = 'http://dev.scheduler.go1.co';
    private $fooConsumeUrl = 'http://foo.go1.service/scheduler-consume';
    private $jobName       = 'foo';

    public function testCreateJob()
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['put'])
            ->disableOriginalConstructor()
            ->getMock();

        $client
            ->expects($this->any())
            ->method('put')
            ->willReturnCallback(
                function (string $uri, array $options) {
                    $this->assertEquals($uri, "$this->schedulerUrl/job/$this->jobName?jwt=" . UserHelper::ROOT_JWT);
                    $this->assertEquals('* * * * *', $options['json']['cron_expression']);
                    $this->assertEquals('http', $options['json']['actions'][0]['type']);
                    $this->assertEquals($this->fooConsumeUrl, $options['json']['actions'][0]['data']['url']);
                    $this->assertEquals('POST', $options['json']['actions'][0]['data']['method']);
                    $this->assertEquals(['foo' => 'bar'], $options['json']['actions'][0]['data']['body']);
                    $this->assertEquals(['token' => 'foo'], $options['json']['actions'][0]['data']['headers']);

                    return new Response();
                }
            );

        $req = Request::create('http://foo.go1.service/scheduler-consume', 'POST');
        $req->headers->replace([]);
        $req->headers->set('token', 'foo');
        $req->request->replace([
            'foo' => 'bar',
        ]);

        $c = $this->getContainer();
        $scheduler = new SchedulerClient($client, $c['logger'], 'http://dev.scheduler.go1.co');
        $scheduler->saveJob('foo', '* * * * *', $req);
    }

    public function testDeleteJob()
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $client
            ->expects($this->any())
            ->method('delete')
            ->willReturnCallback(
                function (string $uri) {
                    $this->assertEquals($uri, "$this->schedulerUrl/job/$this->jobName?jwt=" . UserHelper::ROOT_JWT);

                    return new Response();
                }
            );

        $c = $this->getContainer();
        $scheduler = new SchedulerClient($client, $c['logger'], 'http://dev.scheduler.go1.co');
        $scheduler->deleteJob('foo');
    }

    public function testGetJob()
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $client
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function (string $uri, array $options) {
                    $this->assertEquals($uri, "$this->schedulerUrl/job?name={$this->jobName}");
                    $this->assertEquals('application/json', $options['headers']['Content-Type']);
                    $this->assertEquals('Bearer ' . UserHelper::ROOT_JWT, $options['headers']['Authorization']);

                    return new Response(200, [], json_encode([(object) ['name' => $this->jobName]]));
                }
            );

        $c = $this->getContainer();
        $scheduler = new SchedulerClient($client, $c['logger'], 'http://dev.scheduler.go1.co');
        $scheduler->getJob('foo');
    }
}
