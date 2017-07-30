<?php

namespace Opti\tests;

use Opti\File\File;

class FileTest extends TestCase
{
    /**
     * @return string
     */
    protected function getImagePath()
    {
        return $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'Definition_of_Free_Cultural_Works_logo_notext.png');
    }

    /**
     * @return File
     */
    protected function getFileObject()
    {
        return new File($this->getImagePath());
    }

    public function testFileExists()
    {
        $file = new File($this->getImagePath());
        $this->assertTrue($file->isExists());

        $file = new File('not_exists_file_path');
        $this->assertFalse($file->isExists());
    }

    public function testFileSize()
    {
        $file = $this->getFileObject();
        $this->assertGreaterThan(0, $file->getSize());
    }

    public function testFileMime()
    {
        $file = $this->getFileObject();
        $this->assertEquals('image/png', $file->getMime());
    }

    public function testFileFormat()
    {
        $file = $this->getFileObject();
        $this->assertEquals(File::FORMAT_PNG, $file->getFormat());
    }

    public function testFileContent()
    {
        $file = $this->getFileObject();
        $this->assertStringEqualsFile($this->getImagePath(), $file->getContent());
    }

    public function testFileFromString()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'without_xml_definition.svg');
        $content = file_get_contents($filePath);

        $file = new File();
        $newFilePath = tempnam(sys_get_temp_dir(), 'Opti_');
        $file->setPath($newFilePath);
        $file->setContent($content);

        $this->assertEquals('SVG', $file->getFormat());
    }

    public function testFileWrite()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'without_xml_definition.svg');
        $content = file_get_contents($filePath);

        $file = new File();
        $newFilePath = tempnam(sys_get_temp_dir(), 'Opti_');
        $file->setPath($newFilePath);
        $file->setContent($content, true);

        $this->assertFileEquals($filePath, $newFilePath);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileWriteWithEmptyPath()
    {
        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'without_xml_definition.svg');
        $content = file_get_contents($filePath);

        $file = new File();
        $file->setContent($content, true);
    }
}