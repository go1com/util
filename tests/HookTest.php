<?php

namespace go1\util\tests;

use go1\util\Hook;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class HookTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $dispatcher = new EventDispatcher;
        $dispatcher->addListener('foo', function (GenericEvent $event) {
            $subject = &$event->getSubject();
            $subject = 'changed';
        });

        $subject = 'original';
        Hook::alter($dispatcher, 'foo', $subject);

        $this->assertEquals('changed', $subject);
    }
}
