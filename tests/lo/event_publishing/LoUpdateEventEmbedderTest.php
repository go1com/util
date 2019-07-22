<?php

namespace go1\util\tests\lo;

use go1\util\lo\event_publishing\LoUpdateEventEmbedder;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/LoCreateEventEmbedderTest.php';

class LoUpdateEventEmbedderTest extends LoCreateEventEmbedderTest
{
    public function test()
    {
        $c = $this->getContainer();
        $event = LoHelper::load($this->go1, $this->eventLiId);
        $embedder = new LoUpdateEventEmbedder($this->go1, $c['access_checker']);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($event, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals(LoTypes::MODULE, $embedded['parents'][1]->type);
        $this->assertEquals(LoTypes::COURSE, $embedded['parents'][0]->type);
    }
}
