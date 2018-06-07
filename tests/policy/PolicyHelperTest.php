<?php

namespace go1\util\tests\policy;


use go1\util\EntityTypes;
use go1\util\policy\PolicyHelper;
use go1\util\policy\Realm;
use go1\util\schema\mock\PolicyMockTrait;
use go1\util\tests\UtilTestCase;

class PolicyHelperTest extends UtilTestCase
{
    use PolicyMockTrait;

    public function testGetLoPolicyByEntity()
    {
        $this->createItem($this->db, [
            'type'             => Realm::ACCESS,
            'portal_id'        => $portalId = 12,
            'host_entity_type' => EntityTypes::LO,
            'host_entity_id'   => $loId = 13,
            'entity_type'      => EntityTypes::USER,
            'entity_id'        => $accountId = 14,
        ]);

        $this->createItem($this->db, [
            'type'             => Realm::VIEW,
            'portal_id'        => $portalId = 12,
            'host_entity_type' => EntityTypes::LO,
            'host_entity_id'   => $loId = 13,
            'entity_type'      => EntityTypes::PORTAL,
            'entity_id'        => $sharedPortalId = 15,
        ]);

        $this->assertEquals(Realm::ACCESS, PolicyHelper::entityRealmOnLO($this->db, EntityTypes::USER, $accountId, $portalId, $loId));
        $this->assertEquals(Realm::VIEW, PolicyHelper::entityRealmOnLO($this->db, EntityTypes::PORTAL, $sharedPortalId, $portalId, $loId));
        $this->assertNull(PolicyHelper::entityRealmOnLO($this->db, EntityTypes::USER, 1, $portalId, $loId));
    }
}
