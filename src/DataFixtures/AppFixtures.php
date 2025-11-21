<?php

namespace App\DataFixtures;

use App\Entity\Follow;
use App\Entity\Post;
use App\Entity\Topic;
use App\Entity\User;
use App\Entity\Like;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create topics
        $topic1 = (new Topic())
            ->setTitle('Technology');
        $manager->persist($topic1);

        $topic2 = (new Topic())
            ->setTitle('Sports');
        $manager->persist($topic2);

        $topic3 = (new Topic())
            ->setTitle('Music');
        $manager->persist($topic3);

        // Create users
        $user1 = (new User())
            ->setEmail('john@example.com')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password123'));
        $manager->persist($user1);

        $user2 = (new User())
            ->setEmail('jane@example.com')
            ->setFirstname('Jane')
            ->setLastname('Smith')
            ->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password123'));
        $manager->persist($user2);

        $admin = (new User())
            ->setEmail('admin@example.com')
            ->setFirstname('Admin')
            ->setLastname('User')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Create posts
        $post1 = (new Post())
            ->setContent('Just learned about the new PHP 8.4 features! Amazing improvements to the language.')
            ->setAuthor($user1)
            ->setTopic($topic1);
        $manager->persist($post1);

        $post2 = (new Post())
            ->setContent('What an incredible game last night! The atmosphere was electric!')
            ->setAuthor($user2)
            ->setTopic($topic2);
        $manager->persist($post2);

        $post3 = (new Post())
            ->setContent('Symfony 7.3 has some really cool new features for API development.')
            ->setAuthor($user1)
            ->setTopic($topic1);
        $manager->persist($post3);

        $post4 = (new Post())
            ->setContent('Listening to my favorite album on repeat today. Never gets old!')
            ->setAuthor($user2)
            ->setTopic($topic3);
        $manager->persist($post4);

        // Create likes
        $like1 = (new Like())
            ->setUser($user2)
            ->setPost($post1);
        $manager->persist($like1);

        $like2 = (new Like())
            ->setUser($user1)
            ->setPost($post2);
        $manager->persist($like2);

        $like3 = (new Like())
            ->setUser($admin)
            ->setPost($post1);
        $manager->persist($like3);

        $like4 = (new Like())
            ->setUser($admin)
            ->setPost($post3);
        $manager->persist($like4);

        // Update likes count
        $post1->setLikesCount(2);
        $post2->setLikesCount(1);
        $post3->setLikesCount(1);

        // Update topics posts count
        $topic1->setPostsCount(2); // Technology has 2 posts
        $topic2->setPostsCount(1); // Sports has 1 post
        $topic3->setPostsCount(1); // Music has 1 post

        // Create follows
        $follow1 = (new Follow())
            ->setFollower($user1)
            ->setFollowing($user2);
        $manager->persist($follow1);

        $follow2 = (new Follow())
            ->setFollower($user1)
            ->setFollowing($admin);
        $manager->persist($follow2);

        $follow3 = (new Follow())
            ->setFollower($user2)
            ->setFollowing($user1);
        $manager->persist($follow3);

        $follow4 = (new Follow())
            ->setFollower($admin)
            ->setFollowing($user1);
        $manager->persist($follow4);

        $manager->flush();
    }
}
