<?php

namespace go1\clients\portal\config;

class MailTemplate
{
    private $subject, $body, $html;

    public function __construct(string $subject, string $body = null, string $html = null)
    {
        $this->subject = $subject;
        $this->body = is_null($body) ? '' : $body;
        $this->html = $html;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHtml()
    {
        return $this->html;
    }
}
