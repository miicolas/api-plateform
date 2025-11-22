<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Follow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FollowProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Follow
    {
        if (!$data instanceof Follow) {
            return $data;
        }

        if ($operation instanceof DeleteOperationInterface) {
            $this->entityManager->remove($data);
            $this->entityManager->flush();

            return null;
        }

        // Pour la création (POST)
        // Assigner automatiquement l'utilisateur connecté comme follower
        $user = $this->security->getUser();
        $data->setFollower($user);

        // Vérifier qu'on ne se follow pas soi-même
        if ($data->getFollowing() && $data->getFollowing()->getId() === $user->getId()) {
            throw new BadRequestHttpException('You cannot follow yourself.');
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
