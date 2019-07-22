<?php
namespace go1\util\tests\portal;

use go1\util\portal\PortalType;
use PHPUnit\Framework\TestCase;

class PortalTypeTest extends TestCase
{
    public function testAll()
    {
        $portalTypes = PortalType::all();
        $this->assertEmpty(array_diff_assoc([
            'content_partner',
            'distribution_partner',
            'internal',
            'customer',
            'complispace',
            'jse_customer',
            'totara_customer',
        ], $portalTypes));
    }

    /**
     * @dataProvider typeToStringProvider
     */
    public function testToString($type, $string)
    {
        $this->assertEquals($string, PortalType::toString($type));
    }

    public function testToStringInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown portal type: foobar');
        PortalType::toString('foobar');
    }

    public function typeToStringProvider()
    {
        return [
            ['content_partner' , 'Content Partner'],
            ['internal' , 'Internal'],
            ['jse_customer' , 'JSE Customer'],
        ];
    }
}
