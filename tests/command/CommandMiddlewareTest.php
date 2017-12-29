<?php

namespace go1\util\command;

use go1\util\tests\command\CommandFoo;
use go1\util\tests\command\CommandFooHandler;
use go1\util\tests\UtilTestCase;

class CommandTest extends UtilTestCase
{
    public function test()
    {
        $commandMiddleware = $this
            ->getMockBuilder(CommandMiddlewareInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['invoke'])
            ->getMockForAbstractClass();

        $commandFoo = new CommandFoo();
        $commandFoo->setPayload('foo');
        $commandFooHandler = new CommandFooHandler();

        $commandMiddleware
            ->expects($this->once())
            ->method('invoke')
            ->willReturnCallback(
                function (CommandInterface $command) {
                    $this->assertEquals('foo', $command->getPayload());

                    return true;
                }
            );

        $commandBus = new CommandBus([$commandFooHandler], [$commandMiddleware]);
        $this->assertEquals('foo.executed', $commandBus->execute($commandFoo));
    }

    public function testPropagation()
    {
        $commandMiddleware = $this
            ->getMockBuilder(CommandMiddlewareInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['invoke'])
            ->getMockForAbstractClass();

        $commandFoo = new CommandFoo();
        $commandFoo->setPayload('foo');
        $commandFooHandler = new CommandFooHandler();

        $commandMiddleware
            ->expects($this->once())
            ->method('invoke')
            ->willReturnCallback(
                function (CommandInterface $command) {
                    $command->stopPropagation();
                }
            );

        $commandBus = new CommandBus([$commandFooHandler], [$commandMiddleware]);
        $this->assertNull($commandBus->execute($commandFoo));
    }
}
