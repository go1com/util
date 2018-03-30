<?php

namespace go1\util\tests;

use BadFunctionCallException;
use go1\clients\MqClient;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\model\Edge;
use go1\util\queue\Queue;
use PDO;
use ReflectionClass;

class EdgeTest extends UtilTestCase
{
    /** @var MqClient */
    protected $mqClient;
    protected $edgeIds;

    public function setUp()
    {
        parent::setUp();

        $this->mqClient = $this->getMockBuilder(MqClient::class)->setMethods(['publish'])->disableOriginalConstructor()->getMock();

        // User has 3 accounts
        $this->edgeIds[] = EdgeHelper::link($this->db, $this->mqClient, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 2, $weight = 0);
        $this->edgeIds[] = EdgeHelper::link($this->db, $this->mqClient, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 3, $weight = 1);
        $this->edgeIds[] = EdgeHelper::link($this->db, $this->mqClient, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 4, $weight = 2);

        // Course has 3 modules
        $this->edgeIds[] = EdgeHelper::link($this->db, $this->mqClient, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 2, $weight = 0);
        $this->edgeIds[] = EdgeHelper::link($this->db, $this->mqClient, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 3, $weight = 1);
        $this->edgeIds[] = EdgeHelper::link($this->db, $this->mqClient, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 4, $weight = 2);
    }

    public function testLoad()
    {
        foreach ($this->edgeIds as $edgeId) {
            $this->assertEquals($edgeId, EdgeHelper::load($this->db, $edgeId)->id);
        }
    }

    public function testChangeType()
    {
        // Create an edge
        $id = EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_CREDIT_REQUEST, $sourceId = 1, $targetId = 2);

        // Change its type
        EdgeHelper::changeType($this->db, $this->queue, $id, EdgeTypes::HAS_CREDIT_REQUEST_REJECTED);
        $this->assertEquals(EdgeTypes::HAS_CREDIT_REQUEST_REJECTED, EdgeHelper::load($this->db, $id)->type);
        $msg = &$this->queueMessages[Queue::RO_UPDATE][0];

        $this->assertEquals(EdgeTypes::HAS_CREDIT_REQUEST, $msg['original']['type']);
        $this->assertEquals(EdgeTypes::HAS_CREDIT_REQUEST_REJECTED, $msg['type']);
    }

    public function testChangeTypeData()
    {
        // Create an edge
        $id = EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_CREDIT_REQUEST, $sourceId = 1, $targetId = 2);

        // Change its type
        EdgeHelper::changeType($this->db, $this->queue, $id, EdgeTypes::HAS_CREDIT_REQUEST_REJECTED);
        $ro = EdgeHelper::load($this->db, $id);
        $this->assertEquals(EdgeTypes::HAS_CREDIT_REQUEST_REJECTED, $ro->type);
        $this->assertTrue(property_exists($ro->data->oldType, EdgeTypes::HAS_CREDIT_REQUEST));

        EdgeHelper::changeType($this->db, $this->queue, $id, EdgeTypes::HAS_CREDIT_REQUEST_DONE);
        $ro = EdgeHelper::load($this->db, $id);
        $this->assertEquals(EdgeTypes::HAS_CREDIT_REQUEST_DONE, $ro->type);
        $this->assertTrue(property_exists($ro->data->oldType, EdgeTypes::HAS_CREDIT_REQUEST_REJECTED));
    }

    public function testNoDuplication()
    {
        $rClass = new ReflectionClass(EdgeTypes::class);

        $values = [];
        foreach ($rClass->getConstants() as $key => $value) {
            if (is_scalar($value)) {
                $this->assertNotContains($value, $values, "Duplication: {$key}");
                $values[] = $value;
            }
        }
    }

    public function testHasLink()
    {
        $this->assertNotEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 2));
        $this->assertNotEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 3));
        $this->assertNotEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 4));
        $this->assertEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 5));
    }

    public function testRemove()
    {
        $id = EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_CREDIT_REQUEST, $sourceId = 1, $targetId = 2);

        # Before removing
        $edge = EdgeHelper::load($this->db, $id);
        $this->assertTrue($edge instanceof Edge);

        # After removing
        EdgeHelper::remove($this->db, $this->mqClient, $edge);
        $this->assertFalse(EdgeHelper::load($this->db, $id) instanceof Edge);
    }

    public function testUnlinkBadCall()
    {
        $this->expectException(BadFunctionCallException::class);
        EdgeHelper::unlink($this->db, $this->mqClient, EdgeTypes::HAS_ACCOUNT);
    }

    public function testUnlinkBySource()
    {
        EdgeHelper::unlink($this->db, $this->mqClient, EdgeTypes::HAS_ACCOUNT, $userId = 1);

        // All accounts are removed
        $this->assertFalse(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId, $accountId = 2));
        $this->assertFalse(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId, $accountId = 3));
        $this->assertFalse(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId, $accountId = 4));

        // Other relationships should not be removed by accident.
        $this->assertNotEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 2));
        $this->assertNotEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 3));
        $this->assertNotEmpty(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 4));
    }

    public function unlinkByTarget()
    {
        EdgeHelper::unlink($this->db, $this->mqClient, EdgeTypes::HAS_ACCOUNT, null, $accountId = 2);

        // Only one account is removed.
        $this->assertEquals(false, EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 2));
        $this->assertEquals(true, EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 3));
        $this->assertEquals(true, EdgeHelper::hasLink($this->db, EdgeTypes::HAS_ACCOUNT, $userId = 1, $accountId = 4));

        // Other relationships should not be removed by accident.
        $this->assertFalse(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 2));
        $this->assertFalse(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 3));
        $this->assertFalse(EdgeHelper::hasLink($this->db, EdgeTypes::HAS_MODULE, $courseId = 1, $moduleId = 4));
    }

    public function testEdgesFromSource()
    {
        $edges = EdgeHelper::edgesFromSource($this->db, $userId = 1, [EdgeTypes::HAS_ACCOUNT]);

        $this->assertCount(3, $edges);
        array_map(
            function ($edge) use ($userId) {
                $this->assertEquals(EdgeTypes::HAS_ACCOUNT, $edge->type);
                $this->assertEquals($userId, $edge->source_id);
            },
            $edges
        );
    }

    public function testCustomSelect()
    {
        $targetIds = EdgeHelper
            ::select('target_id')
            ->get($this->db, [$userId = 1], [], [EdgeTypes::HAS_ACCOUNT], PDO::FETCH_COLUMN);

        $this->assertCount(3, $targetIds);
        $this->assertEquals($accountId = 2, $targetIds[0]);
        $this->assertEquals($accountId = 3, $targetIds[1]);
        $this->assertEquals($accountId = 4, $targetIds[2]);
    }

    public function testCustomSelectSingle()
    {
        $select = EdgeHelper::select('target_id');
        $source = [$userId = 1];
        $hasAcc = EdgeTypes::HAS_ACCOUNT;

        $this->assertEquals($accountId = 2, $select->getSingle($this->db, $source, [2], [$hasAcc], PDO::FETCH_COLUMN));
        $this->assertEquals($accountId = 3, $select->getSingle($this->db, $source, [3], [$hasAcc], PDO::FETCH_COLUMN));
        $this->assertEquals($accountId = 4, $select->getSingle($this->db, $source, [4], [$hasAcc], PDO::FETCH_COLUMN));
    }

    public function testCreditTransfer()
    {
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::CREDIT_TRANSFER, 1, 100000, 0, ['old_owner' => 1, 'new_owner' => 2, 'actor' => 'abc@go1.com']);
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::CREDIT_TRANSFER, 1, 100001, 0, ['old_owner' => 2, 'new_owner' => 3, 'actor' => 'abc1@go1.com']);
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::CREDIT_TRANSFER, 1, 100002, 0, ['old_owner' => 3, 'new_owner' => 1, 'actor' => 'abc@go1.com']);
    }

    public function testLoHasToken()
    {
        $courseId = 123;
        $instanceId = 555;
        $edgeId = EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_LO_CUSTOMISATION, $courseId, $instanceId, 0, [
            'tokens' => $tokens = [
                'token_1' => 'value 1',
                'token_2' => 'value 2',
            ],
        ]);

        $this->assertEquals($tokens, (array) EdgeHelper::load($this->db, $edgeId)->data->tokens);
    }
}
