<?php
namespace go1\util\tests\command;

use go1\util\command\CommandHandlerInterface;
use go1\util\command\CommandInterface;

class CommandFooHandler implements CommandHandlerInterface
{
   public function handle(CommandInterface $command)
   {
       return 'foo.executed';
   }
}
