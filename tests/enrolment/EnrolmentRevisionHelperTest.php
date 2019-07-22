<?php

namespace go1\util\tests\enrolment;

use go1\util\enrolment\EnrolmentRevisionHelper;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\tests\UtilCoreTestCase;

class EnrolmentRevisionHelperTest extends UtilCoreTestCase
{
    use EnrolmentMockTrait;

    private $enrolmentId        = 1;
    private $moduleEnrolmentIdA = 2;
    private $moduleEnrolmentIdB = 3;
    private $liEnrolmentIdA1    = 4;
    private $liEnrolmentIdA2    = 5;
    private $liEnrolmentIdA3    = 6;
    private $liEnrolmentIdB1    = 7;

    public function setUp() : void
    {
        parent::setUp();

        $base = ['status' => EnrolmentStatuses::COMPLETED];
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->enrolmentId]);
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->moduleEnrolmentIdA, 'parent_enrolment_id' => $this->enrolmentId]);
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->liEnrolmentIdA1, 'parent_enrolment_id' => $this->moduleEnrolmentIdA]);
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->liEnrolmentIdA2, 'parent_enrolment_id' => $this->moduleEnrolmentIdA]);
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->liEnrolmentIdA3, 'parent_enrolment_id' => $this->moduleEnrolmentIdA]);
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->moduleEnrolmentIdB, 'parent_enrolment_id' => $this->enrolmentId]);
        $this->createRevisionEnrolment($this->go1, $base + ['enrolment_id' => $this->liEnrolmentIdB1, 'parent_enrolment_id' => $this->moduleEnrolmentIdB]);
    }

    public function testChildIds()
    {
        # Enrolment
        $childIds = EnrolmentRevisionHelper::childIds($this->go1, $this->enrolmentId);
        $this->assertEquals(2, count($childIds));
        $this
            ->hasChild($this->moduleEnrolmentIdA, $childIds)
            ->hasChild($this->moduleEnrolmentIdB, $childIds);

        # Enrolment all child
        $childIds = EnrolmentRevisionHelper::childIds($this->go1, $this->enrolmentId, true);
        $this->assertEquals(6, count($childIds));
        $this
            ->hasChild($this->moduleEnrolmentIdA, $childIds)
            ->hasChild($this->liEnrolmentIdA1, $childIds)
            ->hasChild($this->liEnrolmentIdA2, $childIds)
            ->hasChild($this->liEnrolmentIdA3, $childIds)
            ->hasChild($this->moduleEnrolmentIdB, $childIds)
            ->hasChild($this->liEnrolmentIdB1, $childIds);

        # Enrolment module A
        $childIds = EnrolmentRevisionHelper::childIds($this->go1, $this->moduleEnrolmentIdA);
        $this->assertEquals(3, count($childIds));
        $this
            ->hasChild($this->liEnrolmentIdA1, $childIds)
            ->hasChild($this->liEnrolmentIdA2, $childIds)
            ->hasChild($this->liEnrolmentIdA3, $childIds);

        # Enrolment module B
        $childIds = EnrolmentRevisionHelper::childIds($this->go1, $this->moduleEnrolmentIdB);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->liEnrolmentIdB1, $childIds);

        # Enrolment resource A1
        $childIds = EnrolmentRevisionHelper::childIds($this->go1, $this->liEnrolmentIdA1);
        $this->assertEquals(0, count($childIds));

        # Enrolment resource B1
        $childIds = EnrolmentRevisionHelper::childIds($this->go1, $this->liEnrolmentIdB1);
        $this->assertEquals(0, count($childIds));
    }

    private function hasChild($childId, array $source)
    {
        $this->assertTrue(in_array($childId, $source));

        return $this;
    }
}
