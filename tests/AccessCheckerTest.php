<?php

namespace go1\util\tests;

use go1\util\AccessChecker;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

class AccessCheckerTest extends UtilCoreTestCase
{
    use UserMockTrait;

    public function testValidAccount()
    {
        $userId = $this->createUser($this->go1, ['instance' => 'accounts.gocatalyze.com']);
        $accountId = $this->createUser($this->go1, ['instance' => 'qa.mygo1.com']);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $userId, $accountId);

        $req = new Request;
        $jwt = $this->jwtForUser($this->go1, $userId, 'qa.mygo1.com');
        $payload = Text::jwtContent($jwt);
        $req->attributes->set('jwt.payload', $payload);
        $account = (new AccessChecker)->validAccount($req, 'qa.mygo1.com');
        $this->assertEquals($account->id, $accountId);
    }

    public function testVirtualAccount()
    {
        $userId = $this->createUser($this->go1, ['instance' => 'accounts.gocatalyze.com']);
        $portalName = 'portal.mygo1.com';
        $accountId = $this->createUser($this->go1, ['instance' => $portalName]);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT_VIRTUAL, $userId, $accountId);

        $payload = $this->getPayload([]);
        $req = new Request;
        $req->attributes->set('jwt.payload', $payload);

        $access = new AccessChecker();
        $account1 = $access->validUser($req, $portalName);
        $this->assertFalse($account1);

        $account2 = $access->validUser($req, $portalName, $this->go1);
        $this->assertEquals($accountId, $account2->id);
    }

    public function testIsStudentManager()
    {
        $manager2Id = $this->createUser($this->go1, ['mail' => $manager2Mail = 'manager2@mail.com', 'instance' => $accountsName = 'accounts.gocatalyze.com']);
        $managerId = $this->createUser($this->go1, ['mail' => $managerMail = 'manager@mail.com', 'instance' => $accountsName = 'accounts.gocatalyze.com']);
        $studentId = $this->createUser($this->go1, ['mail' => $studentMail = 'student@mail.com', 'instance' => $portalName = 'portal.mygo1.com']);
        $this->link($this->go1, EdgeTypes::HAS_MANAGER, $studentId, $managerId);

        # Is manager
        $req = new Request;
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => $managerId, 'mail' => $managerMail]));
        $this->assertTrue((new AccessChecker)->isStudentManager($this->go1, $req, $studentMail, $portalName, EdgeTypes::HAS_MANAGER));

        # Is not manager
        $req = new Request;
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => $manager2Id, 'mail' => $manager2Mail]));
        $this->assertFalse((new AccessChecker)->isStudentManager($this->go1, $req, $studentMail, $portalName, EdgeTypes::HAS_MANAGER));
    }
}
