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

    public function testIsStudentManager()
    {
        $manager2Id = $this->createUser($this->db, ['mail' => $manager2Mail ='manager2@mail.com', 'instance' => $accountsName = 'accounts.gocatalyze.com']);
        $managerId = $this->createUser($this->db, ['mail' => $managerMail = 'manager@mail.com', 'instance' => $accountsName = 'accounts.gocatalyze.com']);
        $studentId = $this->createUser($this->db, ['mail' => $studentMail = 'student@mail.com', 'instance' => $instanceName = 'portal.mygo1.com']);
        $this->link($this->db, EdgeTypes::HAS_MANAGER, $managerId, $studentId);

        # Is manager
        $req = new Request(['jwt.payload' => $this->getPayload(['id' => $managerId, 'mail' => $managerMail])]);
        $access = new AccessChecker();
        $this->assertTrue($access->isStudentManager($this->db, $req, $studentMail, $instanceName, EdgeTypes::HAS_MANAGER));

        # Is not manager
        $req = new Request(['jwt.payload' => $this->getPayload(['id' => $manager2Id, 'mail' => $manager2Mail])]);
        $access = new AccessChecker();
        $this->assertFalse($access->isStudentManager($this->db, $req, $studentMail, $instanceName, EdgeTypes::HAS_MANAGER));
    }

}
