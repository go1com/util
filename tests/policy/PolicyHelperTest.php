<?php

namespace go1\util\tests\policy;


use go1\util\EntityTypes;
use go1\util\policy\PolicyHelper;
use go1\util\policy\PolicyItem;
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

    public function testLoadItem()
    {
        $id = $this->createItem($this->db, $data =[
            'type'             => Realm::ACCESS,
            'portal_id'        => $portalId = 12,
            'host_entity_type' => EntityTypes::LO,
            'host_entity_id'   => $loId = 13,
            'entity_type'      => EntityTypes::USER,
            'entity_id'        => $accountId = 14,
        ]);
        $policyItem = PolicyHelper::loadItem($this->db, $id);
        $this->assertTrue($policyItem instanceof PolicyItem);
        $this->assertEquals($data['type'], $policyItem->type);
        $this->assertEquals($data['portal_id'], $policyItem->portalId);
        $this->assertEquals($data['host_entity_type'], $policyItem->hostEntityType);
        $this->assertEquals($data['host_entity_id'], $policyItem->hostEntityId);
        $this->assertEquals($data['entity_type'], $policyItem->entityType);
        $this->assertEquals($data['entity_id'], $policyItem->entityId);

        $this->assertNull(PolicyHelper::loadItem($this->db, 'not-existing'));
    }
}
