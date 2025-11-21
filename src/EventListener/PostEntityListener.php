<?php

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Post::class)]
class PostEntityListener
{
    public function preUpdate(Post $post): void
    {
        $post->setUpdatedAt(new \DateTime());
    }
}
