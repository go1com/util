<?php

namespace go1\clients;

use Exception;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;

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
        if ($content = $this->sign($instance, $fileName)) {
            return $this->upload($fileUrl, $content->schema, $content->host, $content->path, $content->query);
        }
        throw new Exception('Can not upload file');
    }

    public function sign($instance, $fileName)
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

    /**
     * @param string $url
     * @param string $schema "https"
     * @param string $host "s3-ap-southeast-2.amazonaws.com"
     * @param string $path "/dev.mygo1.com/public.mygo1.com/notify/1%401.1/1502157877/event-1502157677.ics"
     * @param string $query "x-amz-acl=public-read&x-amz-meta-id=1&x-amz-meta-mail=1%401.1&X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=%2F20170808%2Fap-southeast-2%2Fs3%2Faws4_request&X-Amz-Date=20170808T020118Z&X-Amz-SignedHeaders=host&X-Amz-Expires=120&X-Amz-Signature=
     * @return string
     */
    public function upload(string $url, string $schema, string $host, string $path, string $query)
    {
        $path = $schema . '://' . $host . $path;
        $this->client->put($path . '?' . $query, [
                'headers' => ['content-length' => filesize($url)],
                'body'    => fopen($url, 'r+')]
        );
        @unlink($url);

        return $path;
    }
}