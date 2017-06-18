<?php

namespace Opti\tests;

use Opti\Utils\File;

class FileTest extends TestCase
{

    public function testDetectExtensionFromFile()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'Definition_of_Free_Cultural_Works_logo_notext.png');

        $extension = File::getStreamFileExtension(file_get_contents($filePath));

        $this->assertEquals('png', $extension);
    }

    public function testDetectSvgWithoutXmlDefinition()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'without_xml_definition.svg');

        $extension = File::getStreamFileExtension(file_get_contents($filePath));

        $this->assertEquals('svg', $extension);
    }

    public function testPlainTextFile()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'plain_text');

        $extension = File::getStreamFileExtension(file_get_contents($filePath));

        $this->assertFalse($extension);
    }

}