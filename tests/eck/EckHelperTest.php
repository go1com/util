<?php

namespace go1\util\tests;

use go1\util\eck\EckHelper;
use go1\util\eck\mock\EckMockTrait;
use go1\util\eck\model\Entity;
use go1\util\schema\mock\PortalMockTrait;

class EckHelperTest extends UtilTestCase
{
    use EckMockTrait;
    use PortalMockTrait;

    public function testLoadEntity()
    {
        $instanceId = $this->createPortal($this->go1, []);

        $this->createField($this->go1, ['field' => 'field_first_name', 'instance' => $instanceId, 'entity' => Entity::USER]);
        $this->createField($this->go1, ['field' => 'field_last_name', 'instance' => $instanceId, 'entity' => Entity::USER]);
        $this->createEntityValues($this->go1, $instanceId, Entity::USER, 100, [
            'field_first_name' => [0 => 'Foo'],
            'field_last_name' => [0 => 'Bar'],
        ]);

        $user = EckHelper::load($this->go1, $instanceId, Entity::USER, 100);
        $this->assertEquals('Foo', $user->get('field_first_name')[0]['value']);
        $this->assertEquals('Bar', $user->get('field_last_name')[0]['value']);
    }

    public function testLoadEntityLo()
    {
        $instanceId = $this->createPortal($this->go1, []);

        $this->createField($this->go1, ['field' => 'field_description', 'instance' => $instanceId, 'entity' => Entity::LO]);
        $this->createEntityValues($this->go1, $instanceId, Entity::LO, 101, [
            'field_description' => [0 => 'Foo']
        ]);

        $lo = EckHelper::load($this->go1, $instanceId, Entity::LO, 101);
        $this->assertEquals('Foo', $lo->get('field_description')[0]['value']);
    }
}
