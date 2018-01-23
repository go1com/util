<?php

namespace go1\util\tests\es;

use Elasticsearch\Client;
use go1\util\es\Document;
use go1\util\es\DocumentRepository;
use go1\util\es\Schema;
use go1\util\tests\UtilTestCase;

class DocumentRepositoryTest extends UtilTestCase
{
    public function testSave()
    {
        $esClient = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['index'])
            ->getMock();

        $esClient
            ->expects($this->once())
            ->method('index')
            ->willReturnCallback(function($params) {
                $this->assertEquals(Schema::GO1_MY_TEAM_INDEX, $params['index']);
                $this->assertEquals(1, $params['id']);
                $this->assertEquals(Schema::O_MYTEAM_PROGRESS, $params['type']);
                $this->assertEquals(5, $params['routing']);
                $this->assertEquals(10, $params['parent']);
            });

        $document = Document::create((object) [
            'type'     => Schema::O_MYTEAM_PROGRESS,
            'id'       => 1,
            'portalId' => 5,
            'parentId' => 10,
        ]);

        $repository = new DocumentRepository($esClient);
        $repository->save($document);
    }

    public function testDelete()
    {
        $esClient = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();

        $esClient
            ->expects($this->once())
            ->method('delete')
            ->willReturnCallback(function($params) {
                $this->assertEquals(Schema::GO1_MY_TEAM_INDEX, $params['index']);
                $this->assertEquals(1, $params['id']);
                $this->assertEquals(Schema::O_MYTEAM_PROGRESS, $params['type']);
                $this->assertEquals(5, $params['routing']);
                $this->assertFalse(isset($params['parent']));
            });

        $document = Document::create((object) [
            'type'     => Schema::O_MYTEAM_PROGRESS,
            'id'       => 1,
            'portalId' => 5,
            'parentId' => 10,
        ]);

        $repository = new DocumentRepository($esClient);
        $repository->delete($document);
    }

    public function testGet()
    {
        $esClient = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $esClient
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function($params) {
                $this->assertEquals(Schema::GO1_MY_TEAM_INDEX, $params['index']);
                $this->assertEquals(1, $params['id']);
                $this->assertEquals(Schema::O_MYTEAM_PROGRESS, $params['type']);
                $this->assertEquals(10, $params['routing']);
            });

        $repository = new DocumentRepository($esClient);
        $repository->get(Schema::O_MYTEAM_PROGRESS, 1, 10);
    }
}
