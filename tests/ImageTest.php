<?php

namespace go1\util\schema\tests;

use go1\util\Image;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;

class ImageTest extends UtilTestCase
{
    use PortalMockTrait;

    public function dataScale()
    {
        return [
            ['https://res.cloudinary.com/go1/image/fetch/w_100/http://www.go1.com/logo.png', 100, 0],
            ['https://res.cloudinary.com/go1/image/fetch/w_100,h_200/http://www.go1.com/logo.png', 100, 200],
            ['http://www.go1.com/logo.png', 0, 0],
        ];
    }

    /**
     * @dataProvider dataScale
     */
    public function testScale($expected, $width, $height)
    {
        $instanceId = $this->createPortal($this->db, ['data' => ['files' => ['logo' => 'http://www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->db, $instanceId);
        $logo = PortalHelper::logo($portal);

        $url = Image::scale($logo, $width, $height);
        $this->assertEquals($expected, $url);
    }
}
