<?php

namespace go1\util\schema\tests;

use go1\util\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
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
            ["^'£$%^&*()}{@'#~?><>,@|-=-_+-¬'", "ps"],
        ];
    }

    public function dataFileExtension()
    {
        // https://tools.ietf.org/html/rfc4288
        return [
            ['test/test.png', 'image/png'],
            ['test/test.jpeg', 'image/jpeg'],
            ['test/test.jpg', 'image/jpeg'],
            ['test/test.gif', 'image/gif'],
            ['test/test.json', 'application/json'],
            ['test/test.zip', 'application/zip'],
            ['test/test.pdf', 'application/pdf'],
        ];
    }

    /**
     * @dataProvider dataFilenameString
     */
    public function testParseFileName($fileName, $validString)
    {
        $this->assertEquals($validString, File::fileName($fileName));
    }

    /**
     * @dataProvider dataFileExtension
     */
    public function testMimeType($fileName, $validMimeType)
    {
        $this->assertEquals($validMimeType, File::fileMimeType($fileName));
    }
}
