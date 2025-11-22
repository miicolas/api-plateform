<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as PostOperation;
use App\Repository\FollowRepository;
use App\State\FollowProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: FollowRepository::class)]
#[ORM\Table(name: 'follow')]
#[ORM\UniqueConstraint(name: 'unique_follower_following', columns: ['follower_id', 'following_id'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new PostOperation(
            security: "is_granted('ROLE_USER')",
            processor: FollowProcessor::class
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getFollower() == user",
            processor: FollowProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['follow:read']],
    denormalizationContext: ['groups' => ['follow:write']],
    forceEager: false
)]
#[UniqueEntity(fields: ['follower', 'following'], message: 'You are already following this user.')]
#[ApiFilter(SearchFilter::class, properties: [
    'follower' => 'exact',
    'following' => 'exact'
])]
class Follow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['follow:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'following')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['follow:read'])]
    private ?User $follower = null;

    #[ORM\ManyToOne(inversedBy: 'followers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['follow:read', 'follow:write'])]
    #[Assert\NotNull()]
    private ?User $following = null;

    #[ORM\Column]
    #[Groups(['follow:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->follower && $this->following && $this->follower->getId() === $this->following->getId()) {
            $context->buildViolation('You cannot follow yourself.')
                ->atPath('following')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): static
    {
        $this->follower = $follower;

        return $this;
    }

    public function getFollowing(): ?User
    {
        return $this->following;
    }

    public function setFollowing(?User $following): static
    {
        $this->following = $following;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
