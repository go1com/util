<?php

namespace go1\util\tests;

use go1\util\eck\EckHelper;
use go1\util\eck\mock\EckMockTrait;
use go1\util\schema\mock\PortalMockTrait;

class EckHelperTest extends UtilTestCase
{
    use EckMockTrait;
    use PortalMockTrait;

    public function testLoadEntity()
    {
        $instanceId = $this->createPortal($this->db, []);

        $this->createField($this->db, ['field' => 'field_first_name', 'instance' => $instanceId, 'entity' => 'user']);
        $this->createField($this->db, ['field' => 'field_last_name', 'instance' => $instanceId, 'entity' => 'user']);
        $this->createEntityValues($this->db, $instanceId, 'user', 100, [
            'field_first_name' => [0 => 'Foo'],
            'field_last_name' => [0 => 'Bar'],
        ]);

        $user = EckHelper::load($this->db, $instanceId, 'user', 100);
        $this->assertEquals('Foo', $user->get('field_first_name')[0]['value']);
        $this->assertEquals('Bar', $user->get('field_last_name')[0]['value']);
    }
}
