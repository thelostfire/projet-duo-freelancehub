<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByOwner(User $owner, ?Project $project = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.Project', 'p')
            ->join('p.Client', 'c')
            ->andWhere('c.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('t.id', 'ASC');

        if($project !== null) {
            $qb->andWhere('p = :project')
                ->setParameter('project', $project);
        }

        return $qb->getQuery()->getResult();
    }
}
