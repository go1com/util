<?php

namespace go1\util\tests\collection;

use go1\util\collection\CollectionHelper;
use go1\util\collection\CollectionTypes;
use go1\util\schema\mock\CollectionMockTrait;
use go1\util\tests\UtilCoreTestCase;

class CollectionHelperTest extends UtilCoreTestCase
{
    use CollectionMockTrait;

    public function testLoadByPortalAndMachineName()
    {
        $this->createCollection($this->go1, $data = [
            'type'         => CollectionTypes::DEFAULT,
            'machine_name' => 'foo',
            'title'        => 'bar',
            'portal_id'    => 2,
            'author_id'    => 3,
            'data'         => [],
            'status'       => 4,
            'created'      => time(),
            'updated'      => time(),
        ]);
        $collection = CollectionHelper::loadByPortalAndMachineName($this->go1, $data['portal_id'], $data['machine_name']);
        $this->assertEquals($data['type'], $collection->type);
        $this->assertEquals($data['machine_name'], $collection->machineName);
        $this->assertEquals($data['portal_id'], $collection->portalId);
        $this->assertEquals($data['created'], $collection->created);
        $this->assertEquals($data['updated'], $collection->updated);
        $this->assertEquals($data['status'], $collection->status);
    }
}
