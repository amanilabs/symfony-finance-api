<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Transaction;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class TransactionProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ) {
        if ($data instanceof Transaction && null === $data->getOwner()) {
            $user = $this->security->getUser();

            if ($user) {
                $data->setOwner($user);
            }
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
