<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LikeProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Like
    {
        if (!$data instanceof Like) {
            return $data;
        }

        $post = $data->getPost();

        if ($operation instanceof DeleteOperationInterface) {
            // Décrementer le compteur de likes avant suppression
            if ($post) {
                $post->decrementLikesCount();
                $this->entityManager->persist($post);
            }

            $this->entityManager->remove($data);
            $this->entityManager->flush();

            return null;
        }

        // Pour la création (POST)
        // Assigner automatiquement l'utilisateur connecté
        $user = $this->security->getUser();
        $data->setUser($user);

        // Incrémenter le compteur de likes
        if ($post) {
            $post->incrementLikesCount();
            $this->entityManager->persist($post);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
