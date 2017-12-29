<?php

namespace go1\util\command;

interface CommandMiddlewareInterface
{
    public function invoke(CommandInterface $command);
}
