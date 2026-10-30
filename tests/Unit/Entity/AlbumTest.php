<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    private Album $album;

    protected function setUp(): void
    {
        $this->album = new Album();
        $this->album->setName("Album test");
    }
    public function testGetName(): void
    {
        $this->assertSame("Album test", $this->album->getName());

        $unnamedAlbum = new Album();
        $this->expectException(\Error::class);
        $name = $unnamedAlbum->getName();
    }

    public function testSetDifferentName(): void
    {
        $this->album->setName("Autre nom");
        $this->assertSame("Autre nom", $this->album->getName());
    }
}
