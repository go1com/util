<?php

namespace go1\util\tests\dimensions;
use go1\util\schema\mock\DimensionsMockTrait;
use go1\util\dimensions\DimensionHelper;
use go1\util\tests\UtilCoreTestCase;
use go1\util\DateTime;

class DimesionsHelperTest extends UtilCoreTestCase
{
    use DimensionsMockTrait;

    public function testLoad()
    {
        $createdDate = DateTime::create('-2 day')->format(DATE_ISO8601);
        $modifiedDate = DateTime::create('-1 day')->format(DATE_ISO8601);

        $this->createTable($this->go1);
        $dimensionId = $this->createDimension($this->go1,
            [
                'parent_id' => 0,
                'name' => 'Design and Animation',
                'type' => '1',
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        );

        $dimension = DimensionHelper::load($this->go1, $dimensionId);

        $this->assertEquals(0, $dimension->parent_id);
        $this->assertEquals('Design and Animation', $dimension->name);
        $this->assertEquals('1', $dimension->type);
        $this->assertEquals($createdDate, $dimension->created_date);
        $this->assertEquals($modifiedDate, $dimension->modified_date);
    }

    public function testLoadMultiple()
    {
        $createdDate = DateTime::create('-2 day')->format(DATE_ISO8601);
        $modifiedDate = DateTime::create('-1 day')->format(DATE_ISO8601);

        $dimensionIds = array();

        $this->createTable($this->go1);
        array_push($dimensionIds, $this->createDimension($this->go1,
            [   
                'parent_id' => 0,
                'name' => 'Design and Animation',
                'type' => '1', 
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        ));
        array_push($dimensionIds, $this->createDimension($this->go1,
            [   
                'parent_id' => 0,
                'name' => 'Investment and Trading',
                'type' => '1', 
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        ));

        $dimensions = DimensionHelper::loadMultiple($this->go1, $dimensionIds);

        $this->assertEquals(2, count($dimensions));
        $this->assertEquals('Design and Animation', $dimensions[0]->name);
        $this->assertEquals('Investment and Trading', $dimensions[1]->name);
    }

    public function testLoadAllForType()
    {
        $createdDate = DateTime::create('-2 day')->format(DATE_ISO8601);
        $modifiedDate = DateTime::create('-1 day')->format(DATE_ISO8601);

        $this->createTable($this->go1);
        $this->createDimension($this->go1,
            [
                'parent_id' => 0,
                'name' => 'Design and Animation',
                'type' => '1',
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        );
        $this->createDimension($this->go1,
            [
                'parent_id' => 0,
                'name' => 'Investment and Trading',
                'type' => '1',
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        );

        $dimensions = DimensionHelper::loadAllForType($this->go1, '1');

        $this->assertEquals(2, count($dimensions));
        $this->assertEquals('Design and Animation', $dimensions[0]->name);
        $this->assertEquals('Investment and Trading', $dimensions[1]->name);
    }

    public function testLoadAllForLevelAndType()
    {
        $createdDate = DateTime::create('-2 day')->format(DATE_ISO8601);
        $modifiedDate = DateTime::create('-1 day')->format(DATE_ISO8601);
        $id = $this->createDimension($this->go1,
            [
                'parent_id' => 0,
                'name' => 'Design and Animation',
                'type' => '1',
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        );
        $id2 = $this->createDimension($this->go1,
            [
                'parent_id' => $id,
                'name' => 'Investment and Trading',
                'type' => '1',
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        );
        $this->createDimension($this->go1,
            [
                'parent_id' => $id2,
                'name' => 'Third level',
                'type' => '1',
                'created_date' => $createdDate,
                'modified_date' => $modifiedDate,
            ]
        );
        DimensionsMockTrait::createViews($this->go1);
        $dimensions = DimensionHelper::loadAllForLevelAndType($this->go1, 2, 1);
        $this->assertEquals('Investment and Trading', $dimensions[0]->name);
    }
}
