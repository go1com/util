<?php

namespace go1\util\tests\toggle;

use go1\util\toggle\FeatureToggleClient;
use go1\util\UtilServiceProvider;
use Pimple\Container;
use Qandidate\Toggle\Operator\HasIntersection;
use Qandidate\Toggle\Operator\InSet;
use Qandidate\Toggle\OperatorCondition;
use Qandidate\Toggle\Toggle;
use Qandidate\Toggle\ToggleCollection\InMemoryCollection;
use Qandidate\Toggle\ToggleManager;
use PHPUnit\Framework\TestCase;

class ToggleManagerTest extends TestCase
{
    public function test()
    {
        $c = (new Container(['toggle.manager.collection' => new InMemoryCollection()]))
            ->register(new UtilServiceProvider());

        /** @var $toggleManager ToggleManager**/
        $toggleManager = $c['toggle.manager'];

        $toggle = new Toggle('foo', [
            new OperatorCondition('portal', new HasIntersection(['dev.go1.co'])),
            new OperatorCondition('env', new InSet(['dev', 'staging'])),
        ], Toggle::STRATEGY_UNANIMOUS);
        $toggleManager->add($toggle);

        /** @var $client FeatureToggleClient **/
        $client = $c['toggle.manager.client'];
        $this->assertTrue($client->available('foo', 'dev.go1.co', 'dev'));
        $this->assertTrue($client->available('foo', 'dev.go1.co', 'staging'));
        $this->assertFalse($client->available('foo', 'dev.go1.co', 'production'));
        $this->assertFalse($client->available('foo', 'staging.go1.co'));
    }
}
