<?php

namespace App\EventListener;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[AsEntityListener(event: Events::postRemove, entity: Media::class)]
class MediaFileRemover
{
    public function __construct(#[Autowire('%kernel.project_dir%/public')] private string $publicDir, private Filesystem $filesystem) {}

    public function postRemove(Media $media, PostRemoveEventArgs $args): void
    {
        $this->filesystem->remove($this->publicDir . '/' . $media->getPath());
    }
}
