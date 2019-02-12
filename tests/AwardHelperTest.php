<?php

namespace go1\util\tests;

use go1\util\award\AwardHelper;
use go1\util\award\AwardItemTypes;
use go1\util\award\AwardQuantityTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\Text;
use stdClass;

class AwardHelperTest extends UtilCoreTestCase
{
    use AwardMockTrait;

    private $awardData;

    public function setUp() : void
    {
        parent::setUp();

        $this->awardData = [
            'id'          => 1,
            'revision_id' => 1,
            'instance_id' => 1,
            'user_id'     => 1,
            'title'       => 'String',
            'description' => 'Just a string',
            'tags'        => '[force] [award]',
            'locale'      => null,
            'data'        => [
                'image'       => 'http://abc/abc.jpg',
                'image_cover' => 'http://abc/abc.jpg',
            ],
            'published'   => 1,
            'quantity'    => 1,
            'expire'      => 1,
            'created'     => 1,
            'enrolment'   => 'false',
            'items'       => [
                [
                    'type'              => AwardItemTypes::LO,
                    'award_revision_id' => 1,
                    'entity_id'         => 1,
                    'quantity'          => 1,
                ],
            ],
        ];
    }

    public function testAwardFormat()
    {
        $c = $this->getContainer();

        $awardData = json_decode(json_encode($this->awardData));
        $correct = clone $awardData;
        $wrong = clone $awardData;

        $correct->tags = Text::parseInlineTags($correct->tags);
        $correct->locale = [];
        AwardHelper::format($awardData, $c['html']);
        $this->assertEquals($correct, $awardData);

        // For some string value, its should convert to int
        $wrong->id = '1';
        $wrong->revision_id = '1';
        AwardHelper::format($wrong, $c['html']);
        $this->assertEquals($correct, $wrong);
    }

    public function dataLoad()
    {
        return [
            ['load'],
            ['loadByRevision'],
        ];
    }

    /** @dataProvider dataLoad */
    public function testLoad($methodName)
    {
        $awardData = $this->awardData;
        $awardData['data'] = json_encode($awardData['data']);
        $awardId = $this->createAward($this->go1, $awardData);

        $award = call_user_func([AwardHelper::class, $methodName], $this->go1, $awardId);
        $this->assertIsInt($award->id);
        $this->assertIsInt($award->revision_id);
        $this->assertIsInt($award->instance_id);
        $this->assertIsInt($award->user_id);
        $this->assertIsInt($award->published);
        $this->assertIsInt($award->expire);
        $this->assertIsInt($award->created);
        $this->assertIsFloat($award->quantity);
        $this->assertEquals((object)$this->awardData['data'], $award->data);
        $this->assertEquals(['force', 'award'], $award->tags);
        $this->assertEquals([], $award->locale);

        $emptyAward = call_user_func([AwardHelper::class, $methodName], $this->go1, 99);
        $this->assertEmpty($emptyAward);
    }

    public function testLoadManualItem()
    {
        $awardManualItemId = $this->createAwardItemManual($this->go1, [
            'award_id' => 1,
            'data'     => $data = [
                'certificate' => [
                    'url'  => 'foo.com',
                    'size' => '1MB',
                    'name' => 'foo',
                ],
            ],
        ]);
        $awardManualItem = AwardHelper::loadManualItem($this->go1, $awardManualItemId);

        $this->assertIsObject($awardManualItem->data);
        $this->assertEquals(json_decode(json_encode($data)), $awardManualItem->data);
    }

    public function dataGetQuantityType()
    {
        return [
            [null, AwardQuantityTypes::COMPLETE_ANY],
            [0, AwardQuantityTypes::TRACK_ONGOING],
            ['0', AwardQuantityTypes::TRACK_ONGOING],
            [0.0, AwardQuantityTypes::TRACK_ONGOING],
            ['0.0', AwardQuantityTypes::TRACK_ONGOING],
            [1, AwardQuantityTypes::REACH_TARGET],
            ['1', AwardQuantityTypes::REACH_TARGET],
            [0.5, AwardQuantityTypes::REACH_TARGET],
            ['0.5', AwardQuantityTypes::REACH_TARGET],
        ];
    }

    /** @dataProvider dataGetQuantityType */
    public function testGetQuantityType($award, $expectedType)
    {
        $this->assertEquals($expectedType, AwardHelper::getQuantityType($award));
    }

    public function dataGetQuantityTypeError()
    {
        return [
            [new stdClass()],
            [-1],
            ['-1'],
        ];
    }

    /** @dataProvider dataGetQuantityTypeError */
    public function testGetQuantityTypeError($award)
    {
        $this->expectException('Exception');
        AwardHelper::getQuantityType($award);
    }
}
