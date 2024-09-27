<?php

namespace App\Repository;

use App\Entity\IdentityProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IdentityProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IdentityProvider::class);
    }

    public function findOneByClassName(string $name): ?IdentityProvider
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.class_name = :val')
            ->setParameter('val', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
