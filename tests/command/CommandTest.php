<?php
namespace go1\util\tests\command;

use go1\util\command\CommandBus;
use go1\util\command\CommandInterface;
use go1\util\tests\UtilTestCase;

class CommandTest extends UtilTestCase
{
    public function test()
    {
        $command = $this
            ->getMockBuilder(CommandInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();

        $command
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $commandBus = new CommandBus();
        $commandBus->execute($command);
    }
}
