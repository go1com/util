<?php

namespace go1\util\schema\tests;

use Doctrine\DBAL\DriverManager;
use go1\util\schema\InstallTrait;
use go1\util\Text;
use PHPUnit_Framework_TestCase;

class TextTest extends PHPUnit_Framework_TestCase
{

    public function testToSnakeCaseSpecialCharater()
    {
        $input = "&*^*^)(*instance - id &*%^%&*123";
        $this->assertEquals('instance_id', Text::toSnakeCase($input));
    }

    public function testToSnakeCaseSpecialIncludeNumber()
    {
        $input = "&*^*^)(*instance - id123&*%^%&*123";
        $this->assertEquals('instance_id123', Text::toSnakeCase($input));
    }

    public function testToSnakeCaseUpcaseFirst()
    {
        $input = "Instance-id";
        $this->assertEquals('instance_id', Text::toSnakeCase($input));
    }

    public function testToSnakeCaseUpcaseAll()
    {
        $input = "INSTANCE";
        $this->assertEquals('instance', Text::toSnakeCase($input));
    }

    public function testToSnakeCaseAllSpecial()
    {
        $input = " &*^*^)(*&*%^%&*123";
        $this->assertEquals('', Text::toSnakeCase($input));
    }

    public function dataFileNameString()
    {
        return [
            ['     ', ''],
            ['the-file-name-123', 'the-file-name-123'],
            ['the-File-namE-123', 'the-file-name-123'],
            ['the-File_namE-123', 'the-file-name-123'],
            ['the-File   -namE-123', 'the-file-name-123'],
            ['the-File***-namE-123', 'the-file-name-123'],
            ['    the-File-namE-123', 'the-file-name-123'],
            ['the-File-namE-123    ', 'the-file-name-123'],
            ['    the-File-namE-123    ', 'the-file-name-123'],
            ['c:/Temp/ほげほげ', 'c-temp-hogehoge'],
            ['你好', 'ni-hao'],
            ['Žluťoučký kůň\n', 'zlutoucky-kun-n'],
            ['test mấy cái liên quan tới string', 'test-may-cai-lien-quan-toi-string'],
            ['1CD-Trung & V43-Level...', '1cd-trung-v43-level'],
            ["^'£$%^&*()}{@'#~?><>,@|-=-_+-¬'", "ps"]
        ];
    }

    /**
     * @dataProvider dataFilenameString
     */
    public function testParseFileName($fileName, $validString)
    {
        $this->assertEquals($validString, Text::parseFileName($fileName));
    }
}
