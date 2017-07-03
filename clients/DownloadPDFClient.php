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

    public function download(string $content, string $name)
    {
        $res = new StreamedResponse();
        $disposition = $res->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        $res->headers->set('Content-Disposition', $disposition);
        $res->headers->set('Content-Type', 'application/pdf');
        $res->setCallback(function () use ($content) {
            echo $this->getPdf($content);
            flush();
        });

        return $res->send();
    }

    public function getPdf(string $html)
    {
        $res = $this->client->post($this->wkhtmltopdfUrl,
            [
                'headers'   => ['content-type' => 'application/json'],
                'json'      => ['contents' => base64_encode($html)]
            ]
        );

        return $res->getBody()->getContents();
    }
}
