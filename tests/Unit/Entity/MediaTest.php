<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaTest extends TestCase
{
    private Media $media;

    protected function setUp(): void
    {
        $this->media = new Media;
        $this->media->setTitle("Média test");
        $this->media->setPath("uploads/0001.jpg");
    }

    public function testGetTitlePath(): void
    {
        $this->assertSame("Média test", $this->media->getTitle());
        $this->assertSame("uploads/0001.jpg", $this->media->getPath());
    }

    public function testSetDifferentTitlePath(): void
    {
        $this->media->setTitle("Autre titre");
        $this->media->setPath("uploads/0002.jpg");
        $this->assertSame("Autre titre", $this->media->getTitle());
        $this->assertSame("uploads/0002.jpg", $this->media->getPath());
    }

    public function testGetAlbum(): void
    {
        $this->assertNull($this->media->getAlbum());

        $album = new Album();
        $this->media->setAlbum($album);
        $this->assertSame($album, $this->media->getAlbum());
    }

    public function testGetUser(): void
    {
        $this->assertNull($this->media->getUser());

        $user = new User();
        $this->media->setUser($user);
        $this->assertSame($user, $this->media->getUser());
    }

    public function testGetFile(): void
    {
        $this->assertNull($this->media->getFile());

        $file = $this->createStub(UploadedFile::class);
        $this->media->setFile($file);
        $this->assertSame($file, $this->media->getFile());
    }

    public function testGetId(): void
    {
        $this->assertNull($this->media->getId());
    }

    /**
     * @dataProvider mediaRequiredProperties
     */
    public function testUseGuettersOnEmptyMedia(string $guetter): void
    {
        $this->expectException(\Error::class);
        (new Media())->$guetter();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function mediaRequiredProperties(): iterable
    {
        yield 'title' => ['getTitle'];
        yield 'path' => ['getPath'];
    }
}
