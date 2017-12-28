<?php
namespace go1\util\tests\command;

use go1\util\command\CommandBus;
use go1\util\command\CommandInterface;
use go1\util\tests\UtilTestCase;

class CommandHandlerTest extends UtilTestCase
{
    /**
     * @expectedException \go1\util\command\CommandHandlerNotFoundException
     */
    public function testNotFoundCommandHandler()
    {
        $command = $this
            ->getMockBuilder(CommandInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $commandBus = new CommandBus();
        $commandBus->execute($command);
    }

    public function test()
    {
        $commandFoo = new CommandFoo();
        $commandFooHandler = new CommandFooHandler();

        $commandBus = new CommandBus([$commandFooHandler]);
        $this->assertEquals('foo.executed', $commandBus->execute($commandFoo));
    }
}
