<?php

namespace App\Repository;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Follow>
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    /**
     * Find all followers of a user
     * @return Follow[]
     */
    public function findFollowersByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.following = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all users that a user is following
     * @return Follow[]
     */
    public function findFollowingByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if a user is following another user
     */
    public function isFollowing(User $follower, User $following): bool
    {
        $result = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.follower = :follower')
            ->andWhere('f.following = :following')
            ->setParameter('follower', $follower)
            ->setParameter('following', $following)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }

    /**
     * Get followers count for a user
     */
    public function getFollowersCount(User $user): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.following = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get following count for a user
     */
    public function getFollowingCount(User $user): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
