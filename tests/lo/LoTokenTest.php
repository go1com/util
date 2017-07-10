<?php

namespace go1\util\tests\lo;

use go1\util\lo\LoHelper;
use go1\util\lo\LoToken;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\user\UserHelper;

class LoTokenTest extends UtilTestCase
{
    use InstanceMockTrait;
    use UserMockTrait;
    use LoMockTrait;

    private $instanceId;
    private $userId;
    private $courseId;

    public function setUp()
    {
        parent::setUp();

        $this->instanceId = $this->createInstance($this->db, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->db, ['first_name' => 'John', 'last_name' => 'Doe']);
        $this->courseId = $this->createLO($this->db, [
            'instance_id' => $this->instanceId,
            'title'       => 'Example token course for [user:first_name]',
            'description' => 'How are you [user:last_name]?',
            'data'        => ['hasToken' => true],
        ]);
    }

    public function testUserToken()
    {
        $course = LoHelper::load($this->db, $this->courseId);
        $user = UserHelper::load($this->db, $this->userId);
        $context = [
            'user'   => $user,
            'portal' => PortalHelper::load($this->db, $this->instanceId),
        ];
        LoToken::replace($this->db, $course, $context);

        $this->assertContains('Example token course for John', $course->title);
        $this->assertContains('How are you Doe?', $course->description);
    }
}
