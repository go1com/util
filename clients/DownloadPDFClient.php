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

    public function download(string $content, string $name, bool $removeMargins = false)
    {
        $res = new StreamedResponse();
        $disposition = $res->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        $res->headers->set('Content-Disposition', $disposition);
        $res->headers->set('Content-Type', 'application/pdf');
        $res->setCallback(function () use ($content, $removeMargins) {
            echo $this->getPdf($content, $removeMargins);
            flush();
        });

        return $res->send();
    }

    public function getPdf(string $html, bool $removeMargins = false)
    {
        $json = ['contents' => base64_encode($html)];

        if ($removeMargins) {
            $json['options'] = [
                'margin-top' => '0',
                'margin-left' => '0',
                'margin-right' => '0',
                'margin-bottom' => '0',
            ];
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
