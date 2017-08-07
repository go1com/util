<?php

namespace go1\util\tests;

use go1\clients\S3Client;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class S3ClientTest extends UtilTestCase
{
    /** @var S3Client s3Client */
    protected $s3Client;
    private $c;
    private $file;

    public function setUp()
    {
        parent::setUp();
        $this->c = $this->getContainer();
        $this->file = sys_get_temp_dir() . '/event.ics';
        $handle = fopen($this->file, 'w');
        fwrite($handle, 'foo');
        fclose($handle);
    }

    public function getMockS3Client($client, array $methods = null)
    {
        $s3Ctrl = $this
            ->getMockBuilder(S3Client::class)
            ->setConstructorArgs([
                $client,
                $this->c['s3_url'],
            ])
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
            ->willReturn(new Response(200, [], json_encode(['portal' => 'foo'])));

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
    public function testSignatureFile()
    {
        $this->c->extend('go1.client.go1s3', function () {
            return $this->getMockS3Client($this->getMockClientPost());
        });
        $this->s3Client = $this->c['go1.client.go1s3'];
        $instanceName = 'foo';
        $this->s3Client->signatureFile($instanceName,'foo', UserHelper::ROOT_JWT);
    }

    public function testUploadFile()
    {
        $content = (object) [
            'scheme' => 'foo',
            'host'   => 'bar',
            'path'   => 'baz',
            'query'  => 'something',
        ];
        $app = $this->c;
        $this->c->extend('go1.client.go1s3', function () use ($app, $content) {
            $s3 = $this->getMockS3Client($this->c['client'], ['signatureFile', 'uploadFileToS3']);
            $s3->expects($this->once())
               ->method('signatureFile')
               ->willReturn($content);
            $s3->expects($this->once())
               ->method('uploadFileToS3')
               ->willReturn('foo');

            return $s3;
        });
        $this->s3Client = $this->c['go1.client.go1s3'];
        $this->s3Client->uploadFile('mygo1', 'foo', $this->file);
    }

    public function testUploadToS3()
    {
        $this->c->extend('go1.client.go1s3', function () {
            return $this->getMockS3Client($this->getMockClientUpdate());
        });
        $this->s3Client = $this->c['go1.client.go1s3'];
        $content = (object) [
            'scheme' => 'foo',
            'host'   => 'bar',
            'path'   => 'baz',
            'query'  => 'something',
        ];
        $this->s3Client->uploadFileToS3($content, $this->file);
    }

}
