<?php
namespace go1\util\command;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command);
}
