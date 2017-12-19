<?php

namespace go1\util\tests;

use PHPUnit\Framework\TestCase;
use go1\util\Service;

class ServiceUrlTest extends TestCase
{
    public function testGatewayUrls()
    {
        $this->assertEquals('https://api.go1.co', Service::gatewayUrl('production', true));
        $this->assertEquals('https://api-dev.go1.co', Service::gatewayUrl('dev', true));
        $this->assertEquals('https://api-staging.go1.co', Service::gatewayUrl('staging', true));
        $this->assertEquals('http://gateway.production.go1.service', Service::gatewayUrl('production', false));
        $this->assertEquals('http://gateway.dev.go1.service', Service::gatewayUrl('dev', false));
        $this->assertEquals('http://gateway.staging.go1.service', Service::gatewayUrl('staging', false));
    }
}
