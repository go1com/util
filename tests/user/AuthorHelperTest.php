<?php

namespace go1\util\tests;

use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\user\AuthorHelper;

class AuthorHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use LoMockTrait;

    public function testAuthorIds()
    {
        // Setup data
        $userId = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'user@qa.mygo1.com']);
        $userId2 = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'user2@qa.mygo1.com']);
        $loId = $this->createLO($this->db);

        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $loId, $userId);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $loId, $userId2);

        // Check
        $this->assertEquals([$userId, $userId2], AuthorHelper::authorIds($this->db, $loId));
    }
}
