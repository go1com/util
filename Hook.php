<?php

namespace go1\util;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class Hook
{
    public static function alter(EventDispatcherInterface $dispatcher, string $name, &$subject, array $params = [])
    {
        $dispatcher->dispatch(
            $name,
            new class ($subject, $params) extends GenericEvent
            {
                public function __construct(&$subject, array $arguments = [])
                {
                    $this->subject = &$subject;
                    $this->arguments = $arguments;
                }

                public function &getSubject()
                {
                    return $this->subject;
                }
            }
        );
    }
}
