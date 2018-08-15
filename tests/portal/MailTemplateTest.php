<?php

namespace go1\util\tests\portal;

use go1\util\portal\MailTemplate;
use go1\util\tests\UtilCoreTestCase;

class MailTemplateTest extends UtilCoreTestCase
{
    public function test()
    {
        $mailTemplate = new MailTemplate($id = 'foo', $subject = 'bar', $body = 'body', $html = 'html');
        $this->assertEquals($id, $mailTemplate->getId());
        $this->assertEquals($subject, $mailTemplate->getSubject());
        $this->assertEquals($body, $mailTemplate->getBody());
        $this->assertEquals($html, $mailTemplate->getHtml());

        $json = $mailTemplate->jsonSerialize();
        $this->assertEquals($id, $json['id']);
        $this->assertEquals($subject, $json['subject']);
        $this->assertEquals($body, $json['body']);
        $this->assertEquals($html, $json['html']);

        $mailTemplate = new MailTemplate($id = 'foo', $subject = 'bar');
        $this->assertEquals('', $mailTemplate->getBody());
    }
}
