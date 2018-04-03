<?php

namespace go1\util\notify;
use JsonSerializable;

class MailTemplate implements JsonSerializable
{
    private $id, $subject, $body, $html;

    public function __construct(string $id, string $subject, string $body = null, string $html = null)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->body = $body ?? '';
        $this->html = $html;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function jsonSerialize()
    {
        return [
            'id'      => $this->id,
            'subject' => $this->subject,
            'body'    => $this->body,
            'html'    => $this->html,
        ];
    }
}
