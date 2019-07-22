<?php

namespace go1\util\tests\enrolment;

use Doctrine\DBAL\Schema\Schema;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\enrolment\ManualRecord;
use go1\util\enrolment\ManualRecordRepository;
use go1\util\queue\Queue;
use go1\util\schema\EnrolmentSchema;
use go1\util\tests\UtilCoreTestCase;

class ManualRecordTest extends UtilCoreTestCase
{
    /** @var  ManualRecordRepository */
    private $repository;

    /** @var MqClient */
    protected $queue;

    public function setUp() : void
    {
        parent::setUp();

        DB::install($this->go1, [
            function (Schema $schema) {
                EnrolmentSchema::installManualRecord($schema);
            },
        ]);

        $this->queue = $this
            ->getMockBuilder(MqClient::class)
            ->setMethods(['publish'])
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->queue
            ->expects($this->any())
            ->method('publish')
            ->willReturnCallback(function ($payload, $subject) {
                $this->queueMessages[$subject][] = $payload;
            });

        $this->repository = new ManualRecordRepository($this->go1, $this->queue);
    }

    public function testCreateAndLoad()
    {
        $record = ManualRecord::create((object) [
            'entity_type' => 'lo',
            'entity_id'   => 555,
            'instance_id' => 888,
            'user_id'     => 999,
            'verified'    => false,
            'data'        => ['some' => 'thing'],
            'created'     => time(),
            'updated'     => time(),
        ]);

        // Create, check publishing message
        $this->repository->create($record);
        $msg = $this->queueMessages[Queue::MANUAL_RECORD_CREATE][0];
        $this->assertTrue(is_numeric($record->id));

        // Load & check
        $load = $this->repository->load($record->id);
        $this->assertEquals('lo', $load->entityType);
        $this->assertEquals('lo', $msg->entityType);
        $this->assertEquals(555, $load->entityId);
        $this->assertEquals(555, $msg->entityId);
        $this->assertEquals(888, $load->instanceId);
        $this->assertEquals(888, $msg->instanceId);
        $this->assertEquals(999, $load->userId);
        $this->assertEquals(999, $msg->userId);
        $this->assertEquals(false, $load->verified);
        $this->assertEquals(false, $msg->verified);
        $this->assertEquals(['some' => 'thing'], $load->data);
        $this->assertEquals(['some' => 'thing'], $msg->data);

        // Load by entity
        $_ = $this->repository->loadByEntity($load->instanceId, $load->entityType, $load->entityId, $load->userId);
        $this->assertEquals($_, $load);

        return $record;
    }

    public function testUpdate()
    {
        $record = $this->testCreateAndLoad();
        $record->verified = true;
        $this->repository->update($record);
        $msg = $this->queueMessages[Queue::MANUAL_RECORD_UPDATE][0];

        // Load & check
        $load = $this->repository->load($record->id);
        $this->assertEquals(true, $load->verified);
        $this->assertEquals(true, $msg->verified);
    }

    public function testDelete()
    {
        $record = $this->testCreateAndLoad();
        $this->repository->delete($record->id);
        $count = $this->go1->fetchColumn('SELECT COUNT(*) FROM enrolment_manual WHERE id = ?', [$record->id]);
        $msg = $this->queueMessages[Queue::MANUAL_RECORD_DELETE][0];

        $this->assertTrue($msg instanceof ManualRecord);
        $this->assertEquals(0, $count);
    }
}
