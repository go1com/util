<?php

namespace go1\util\tests\lo;

use go1\util\lo\event_publishing\LoUpdateEventEmbedder;
use go1\util\lo\LoHelper;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/LoCreateEventEmbedderTest.php';

class LoUpdateEventEmbedderTest extends LoCreateEventEmbedderTest
{
    public function test()
    {
        $c = $this->getContainer();
        $event = LoHelper::load($this->db, $this->eventLiId);
        $embedder = new LoUpdateEventEmbedder($this->db, $c['access_checker']);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($event, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal'][$this->portalId]->title);
        $this->assertEquals('course', $embedded['lo'][$this->courseId]->type);
    }
}
