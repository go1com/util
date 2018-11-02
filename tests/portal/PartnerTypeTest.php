<?php

namespace go1\util\tests;


use go1\util\portal\PartnerType;

class PartnerTypeTest extends UtilCoreTestCase
{
    public function testPartnerTypeList()
    {
        $partnerTypes = PartnerType::all();
        $this->assertEquals(7, count($partnerTypes));
    }
}