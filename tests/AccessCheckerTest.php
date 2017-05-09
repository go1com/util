<?php

namespace go1\util\tests;

use go1\util\AccessChecker;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\UserMockTrait;
use Symfony\Component\HttpFoundation\Request;

class AccessCheckerTest extends UtilTestCase
{
    use UserMockTrait;

    public function testVirtualAccount()
    {
        $userId = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com']);
        $instance = 'portal.mygo1.com';
        $accountId = $this->createUser($this->db, ['instance' => $instance]);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT_VIRTUAL, $userId, $accountId);

        $payload = $this->getPayload([]);
        $req = new Request(['jwt.payload' => $payload]);

        $access = new AccessChecker();
        $account1 = $access->validUser($req, $instance);
        $this->assertFalse($account1);

        $account2 = $access->validUser($req, $instance, $this->db);
        $this->assertEquals($accountId, $account2->id);
    }
}
