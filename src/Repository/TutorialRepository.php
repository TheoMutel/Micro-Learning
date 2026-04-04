<?php

namespace App\Repository;

use App\Entity\Tutorial;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tutorial>
 */
class TutorialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tutorial::class);
    }

    /**
     * Find published tutorials and tutorials owned by the user
     */
    public function findPublishedAndOwn(?User $user): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($user) {
            $qb->where('t.status = :published OR t.author = :author')
                ->setParameter('published', 'published')
                ->setParameter('author', $user);
        } else {
            $qb->where('t.status = :published')
                ->setParameter('published', 'published');
        }

        return $qb->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find tutorials by status
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }
}
