<?php

namespace go1\util\command;

interface CommandInterface
{
    public function isPropagationStopped();

    public function stopPropagation();

    public function setPayload($payload);

    public function getPayload();
}
