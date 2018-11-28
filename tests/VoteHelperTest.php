<?php

namespace go1\util\tests;

use Doctrine\DBAL\DriverManager;
use go1\util\schema\mock\VoteMockTrait;
use go1\util\vote\VoteHelper;
use go1\util\vote\VoteTypes;

class VoteHelperTest extends UtilTestCase
{
    use VoteMockTrait;

    public function setUp()
    {
        $this->go1 = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($this->go1, $coreOnly = false);
    }

    public function testgetEntityVote()
    {
        $entityType = VoteTypes::ENTITY_TYPE_LO;
        $entityId = 101;
        $this->createVote($this->go1, VoteTypes::LIKE, $entityType, $entityId, 10, VoteTypes::VALUE_LIKE);
        $this->createVote($this->go1, VoteTypes::LIKE, $entityType, $entityId, 11, VoteTypes::VALUE_LIKE);
        $this->createVote($this->go1, VoteTypes::LIKE, $entityType, $entityId, 12, VoteTypes::VALUE_LIKE);
        $this->createVote($this->go1, VoteTypes::LIKE, $entityType, $entityId, 13, VoteTypes::VALUE_DISLIKE);

        $vote = VoteHelper::getEntityVote($this->go1, $entityType, $entityId);
        $this->assertEquals($entityType, $vote->entity_type);
        $this->assertEquals($entityId, $vote->entity_id);
        $this->assertEquals(3, $vote->data['like']);
        $this->assertEquals(1, $vote->data['dislike']);
    }
}
