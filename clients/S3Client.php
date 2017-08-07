<?php

namespace go1\clients;

use Exception;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class S3Client
{
    private $client;
    private $s3Url;

    public function __construct(
        Client $client,
        string $s3Url
    )
    {
        $this->client = $client;
        $this->s3Url = $s3Url;
    }

    public function uploadFile($instance, $fileUrl, $fileName)
    {
        if ($content = $this->signatureFile($instance, $fileName)) {
            return $this->uploadFileToS3($content, $fileUrl);
        }
        throw new Exception('Can not upload file');
    }

    public function signatureFile($instance, $fileName)
    {
        $options['json'] = [
            'portal'    => $instance,
            'app'       => 'notify',
            'timestamp' => time() + 200, // limit upload file
            'filename'  => $fileName,
        ];
        $res = $this->client->post($this->s3Url . "?jwt=" . UserHelper::ROOT_JWT, $options);
        if (200 === $res->getStatusCode()) {
            return json_decode($res->getBody()->getContents());
        }
    }

    public function uploadFileToS3($content, $fileUrl)
    {
        $path = $content->scheme . '://' . $content->host . $content->path;

        $this->client->put($path . '?' . $content->query, [
                'headers' => ['content-length' => filesize($fileUrl)],
                'body' => fopen($fileUrl, 'r+')]
        );
        @unlink($fileUrl);

        return $path;
    }
}