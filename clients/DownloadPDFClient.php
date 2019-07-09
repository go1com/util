<?php

namespace go1\clients;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadPDFClient
{
    private $client;
    private $wkhtmltopdfUrl;

    public function __construct(Client $client, string $wkhtmltopdfUrl)
    {
        $this->client = $client;
        $this->wkhtmltopdfUrl = $wkhtmltopdfUrl;
    }

    public function download(string $content, string $name, array $options = [])
    {
        $res = new StreamedResponse();
        $disposition = $res->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        $res->headers->set('Content-Disposition', $disposition);
        $res->headers->set('Content-Type', 'application/pdf');
        $res->setCallback(function () use ($content, $options) {
            echo $this->getPdf($content, $options);
            flush();
        });

        return $res->send();
    }

    public function getPdf(string $html, array $options = [])
    {
        $json = ['contents' => base64_encode($html)];

        if (!empty($options)) {
            $json['options'] = $options;
        }

        $res = $this->client->post($this->wkhtmltopdfUrl,
            [
                'headers'   => ['content-type' => 'application/json'],
                'json'      => $json
            ]
        );

        return $res->getBody()->getContents();
    }
}
