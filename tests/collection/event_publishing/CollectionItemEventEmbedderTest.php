<?php

namespace go1\util\tests;

use go1\util\collection\event_publishing\CollectionItemEventEmbedder;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\DB;
use go1\util\collection\CollectionTypes;
use go1\util\schema\mock\CollectionMockTrait;

class CollectionItemEventEmbedderTest extends UtilCoreTestCase
{
    use CollectionMockTrait;
    use PortalMockTrait;
    use LoMockTrait;

    protected $edgeIds;
    protected $expectLo = [
        'id'          => 1,
        'type'        => 'course',
        'instance_id' => 1,
    ];


    public function test()
    {
        $embedder = new CollectionItemEventEmbedder($this->go1);
        $portalId = $this->createPortal($this->go1, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $portalId]);
        $collectionId = $this->createCollection(
            $this->go1,
            $data = [
                'type'         => CollectionTypes::DEFAULT,
                'machine_name' => 'foo',
                'title'        => 'bar',
                'portal_id'    => $portalId,
                'author_id'    => 3,
                'data'         => [],
                'status'       => 4,
                'created'      => time(),
                'updated'      => time(),
            ]
        );

        $id = $this->createCollectionItem(
            $this->go1,
            $data = [
                'collection_id' => $collectionId,
                'lo_id'         => $courseId,
            ]
        );

        $collectionItem = $this->go1
            ->executeQuery('SELECT * FROM collection_collection_item WHERE id = ?', [$id], [DB::STRING])
            ->fetch(DB::OBJ);

        $embedded = $embedder->embedded($collectionItem);

        $this->assertArrayHasKey('lo', $embedded);
        $this->assertArraySubset($this->expectLo, (array)$embedded['lo']);
    }
}
