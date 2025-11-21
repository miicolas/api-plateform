<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as PostOperation;
use ApiPlatform\Metadata\Put;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new PostOperation(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER') and object.getAuthor() == user"),
        new Patch(security: "is_granted('ROLE_USER') and object.getAuthor() == user"),
        new Delete(security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getAuthor() == user)")
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'content' => 'partial',
    'author' => 'exact',
    'topic' => 'exact'
])]
#[ApiFilter(
    DateFilter::class,
    properties: ['createdAt', 'updatedAt']
)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('read')]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 500)]
    #[Groups(['read', 'write'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups('read')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups('read')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups('read')]
    private int $likesCount = 0;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read', 'write'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['read', 'write'])]
    private ?Topic $topic = null;

    #[ORM\OneToOne(mappedBy: 'post', cascade: ['persist', 'remove'])]
    #[Groups('read')]
    private ?Media $media = null;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'post', cascade: ['remove'])]
    private Collection $likes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likesCount): static
    {
        $this->likesCount = $likesCount;

        return $this;
    }

    public function incrementLikesCount(): static
    {
        $this->likesCount++;

        return $this;
    }

    public function decrementLikesCount(): static
    {
        if ($this->likesCount > 0) {
            $this->likesCount--;
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): static
    {
        $this->topic = $topic;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        // unset the owning side of the relation if necessary
        if ($media === null && $this->media !== null) {
            $this->media->setPost(null);
        }

        // set the owning side of the relation if necessary
        if ($media !== null && $media->getPost() !== $this) {
            $media->setPost($this);
        }

        $this->media = $media;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setPost($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }

        return $this;
    }
}
