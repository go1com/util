<?php

namespace go1\util\tests\clients;

use go1\clients\S3Client;
use go1\util\tests\UtilTestCase;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;

class S3ClientTest extends UtilTestCase
{
    /** @var S3Client */
    protected $client;
    private   $container;
    private   $file;
    private   $content;

    public function setUp()
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->file = tempnam(sys_get_temp_dir(), 'event_');
        $this->file = "{$this->file}.ics";
        $handle = fopen($this->file, 'w');
        fwrite($handle, 'foo');
        fclose($handle);

        $this->content = (object) [
            'scheme' => 'foo',
            'host'   => 'bar',
            'path'   => 'baz',
            'query'  => 'something',
        ];
    }

    protected function tearDown()
    {
        file_exists($this->file) && unlink($this->file);
    }

    public function testUploadFile()
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['post', 'put'])
            ->getMock();

        $client
            ->expects($this->any())
            ->method('post')
            ->willReturn(new Response(200, [], json_encode($this->content)));

        $client
            ->expects($this->any())
            ->method('put')
            ->willReturn(new Response(200, [], json_encode(['portal' => 'foo'])));

        $this->client = new S3Client($client, $this->container['s3_url']);
        $url = $this->client->uploadFile('mygo1', $this->file, 'something', 'something');
        $this->assertEquals($url, $this->content->scheme . '://' . $this->content->host . $this->content->path);
    }

    public function testSign()
    {
        $this->container->extend('go1.client.go1s3', function () {
            return $this->getMockS3Client($this->getMockClientPost());
        });
        $this->client = $this->container['go1.client.go1s3'];
        $portalName = 'foo';
        $class = new ReflectionClass(S3Client::class);
        $method = $class->getMethod('sign');
        $method->setAccessible(true);
        $expectedMsg = $method->invokeArgs($this->client, [$portalName, 'foo', UserHelper::ROOT_JWT]);
        $this->assertEquals($this->content, $expectedMsg);
    }

    public function testUpload()
    {
        $this->container->extend('go1.client.go1s3', function () {
            return $this->getMockS3Client($this->getMockClientUpdate());
        });
        $content = $this->content;
        $this->client = $this->container['go1.client.go1s3'];
        $this->client->upload($this->file, $content->scheme, $content->host, $content->path, $content->query);
    }

    public function getMockS3Client($client, array $methods = null)
    {
        $s3Ctrl = $this
            ->getMockBuilder(S3Client::class)
            ->setConstructorArgs([$client, $this->container['s3_url']])
            ->setMethods($methods)
            ->getMock();

        return $s3Ctrl;
    }

    public function getMockClientPost()
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['post'])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], json_encode($this->content)));

        return $client;
    }

    public function getMockClientUpdate()
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['put'])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('put')
            ->willReturn(new Response(200, [], json_encode(['portal' => 'foo'])));

        return $client;
    }
}
