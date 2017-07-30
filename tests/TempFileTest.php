<?php

namespace Opti\tests;

use Opti\File\File;
use Opti\File\TempFile;

class TempFileTest extends TestCase
{
    public function testDetectExtensionFromFile()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'Definition_of_Free_Cultural_Works_logo_notext.png');

        $extension = TempFile::getStreamFileExtension(file_get_contents($filePath));

        $this->assertEquals('png', $extension);
    }

    public function testDetectSvgWithoutXmlDefinition()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'without_xml_definition.svg');

        $extension = TempFile::getStreamFileExtension(file_get_contents($filePath));

        $this->assertEquals('svg', $extension);
    }

    public function testPlainTextFile()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'plain_text');

        $extension = TempFile::getStreamFileExtension(file_get_contents($filePath));

        $this->assertFalse($extension);
    }

    public function testCreateTempFileAndDetectExtension()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'without_xml_definition.svg');

        $file = TempFile::create(null, file_get_contents($filePath));

        $this->assertInstanceOf(File::class, $file, 'Created File instance');

        $this->assertEquals('image/svg+xml', $file->getMime(), 'Mime is from svg file');
        $this->assertEquals(File::FORMAT_SVG, $file->getFormat(), 'Format is SVG');

        $extension = pathinfo($file->getPath(), PATHINFO_EXTENSION);

        $this->assertEquals('svg', strtolower($extension), 'Extension is svg');
    }
}