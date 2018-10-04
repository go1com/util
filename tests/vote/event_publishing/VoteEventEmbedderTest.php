<?php

namespace go1\util\tests\lo;

use go1\util\DB;
use go1\util\schema\mock\VoteMockTrait;
use go1\util\vote\event_publishing\VoteEventEmbedder;
use go1\util\tests\UtilCoreTestCase;
use go1\util\vote\VoteTypes;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;

class VoteEventEmbedderTest extends UtilCoreTestCase
{
    use VoteMockTrait;
    use PortalMockTrait;
    use LoMockTrait;

    protected $embedder;
    protected $courseId;
    protected $portalId;
    protected $expectVote = [
        "percent" => 100,
        "like"    => "1",
        "dislike" => 0,
    ];
    protected $expectLo   = [
        "id"          => 1,
        "type"        => "course",
        "instance_id" => 1,
    ];

    public function setUp()
    {
        parent::setUp();

        $this->installGo1Schema($this->db, $coreOnly = false);
        $this->embedder = new VoteEventEmbedder($this->db);
        $this->portalId = $this->createPortal($this->db, ['title' => 'ngoc.mygo1.com']);
        $this->courseId = $this->createCourse($this->db, ['instance_id' => $this->portalId]);
    }

    public function test()
    {
        $id = $this->createVote($this->db, VoteTypes::LIKE, VoteTypes::ENTITY_TYPE_LO, $this->courseId, 10, VoteTypes::VALUE_LIKE);
        $vote = $this->db->executeQuery('SELECT * FROM vote_items WHERE id = ?', [$id])
            ->fetch(DB::OBJ);
        $embedded = $this->embedder->embedded($vote);

        $this->assertArrayHasKey('vote', $embedded);
        $this->assertArrayHasKey('lo', $embedded);
        $this->assertEquals($this->expectVote, $embedded['vote']);
        $this->assertArraySubset($this->expectLo, $embedded['lo']);
    }
}
