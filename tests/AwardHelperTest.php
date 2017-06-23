<?php

namespace go1\util\tests;

use go1\util\award\AwardHelper;

class AwardHelperTest extends UtilTestCase
{
    private $format_item;

    public function setUp()
    {
        $this->format_item = (object) [
          'id'            => 1,
          'revision_id'   => 1,
          'instance_id'   => 1,
          'user_id'       => 1,
          'title'         => 'String',
          'description'   => 'Just a string',
          'tags'          => '[force] [award]',
          'locale'        => 'en',
          'data'          => (object) [
            'image'         => 'http://abc/abc.jpg',
            'image_cover'   => 'http://abc/abc.jpg',
          ],
          'published'     => 1,
          'quantity'      => 1,
          'expire'        => 1,
          'created'       => 1,
          'enrolment'     => 'false',
          'items'         => [
            (object) [
              'award_revision_id' => 1,
              'entity_id' => 1,
              'quantity' => 1,
            ],
          ],
        ];
    }

    public function testAwardFormatSame()
    {
        $container = $this->getContainer();
        $this->assertEquals($this->format_item, AwardHelper::format($this->format_item, $container['html']));

        $wrong = $this->format_item;
        // For some string value, its should convert to int
        $wrong->id = '1';
        $wrong->revision_id = '1';
        $this->assertEquals($this->format_item, AwardHelper::format($wrong, $container['html']));
    }
}
