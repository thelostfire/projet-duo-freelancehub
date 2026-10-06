<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.Client', 'c')
            ->andWhere('c.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getResult();
    }
}
