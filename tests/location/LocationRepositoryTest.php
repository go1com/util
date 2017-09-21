<?php

namespace go1\util\tests\location;

use go1\util\tests\UtilTestCase;
use go1\util\location\LocationRepository;
use go1\util\location\Location;
use go1\util\queue\Queue;

class LocationRepositoryTest extends UtilTestCase
{
    protected $repo;
    protected $location;

    public function setUp()
    {
        parent::setUp();

        $this->repo = new LocationRepository($this->db, $this->queue);

        $this->location = [
            'title'                     => 'GO1',
            'instance_id'               => 11,
            'country'                   => 'AU',
            'administrative_area'       => 'WA',
            'sub_administrative_area'   => 'Sub - WA',
            'locality'                  => 'Perth',
            'dependent_locality'        => 'Sub Perth',
            'thoroughfare'              => 'Rendezvous Hotel Perth Central - 24 Mount St',
            'premise'                   => '30 Graham Street',
            'sub_premise'               => 'sub premise',
            'organisation_name'         => 'GO1',
            'name_line'                 => 'Line',
            'postal_code'               => '2285',
            'author_id'                 => 11,
            'created'                   => time(),
            'updated'                   => time(),
        ];
    }

    public function testCreate()
    {
        $locationOriginal = Location::create((object)$this->location);
        $id = $this->repo->create($locationOriginal);

        $location = $this->repo->load($id)->jsonSerialize();
        foreach ($this->location as $k => $v) {
            $this->assertEquals($location[$k], $v);
        }

        $this->assertCount(1, $this->queueMessages[Queue::LOCATION_CREATE]);
        $this->messageAware($this->queueMessages[Queue::LOCATION_CREATE][0]);

        return $id;
    }

    public function testUpdate()
    {
        $location = $this->repo->load($id = $this->testCreate());
        $location->locality = 'Sai Gon';
        $location->organisationName = 'GO1VN';

        $this->assertTrue($this->repo->update($location));

        $location = $this->repo->load($id);
        $this->assertEquals('Sai Gon', $location->locality);
        $this->assertEquals('GO1VN', $location->organisationName);
        $this->assertCount(1, $this->queueMessages[Queue::LOCATION_UPDATE]);
        $this->messageAware($this->queueMessages[Queue::LOCATION_UPDATE][0]);
        $this->messageAware($this->queueMessages[Queue::LOCATION_UPDATE][0]->original);
    }

    public function testDelete()
    {
        $location = $this->repo->load($id = $this->testCreate());
        $this->assertTrue($location instanceof Location);

        $this->repo->delete($id);
        $this->assertNull($this->repo->load($id));
        $this->assertCount(1, $this->queueMessages[Queue::LOCATION_DELETE]);
        $this->messageAware($this->queueMessages[Queue::LOCATION_DELETE][0]);
    }

    protected function messageAware(Location $message)
    {
        $message = $message->jsonSerialize();
        foreach ($this->location as $k => $v) {
            $this->assertArrayHasKey($k, $message);
        }
    }
}
