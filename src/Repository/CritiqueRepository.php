<?php

namespace App\Repository;

use App\Entity\Critique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Critique>
 *
 * @method Critique|null find($id, $lockMode = null, $lockVersion = null)
 * @method Critique|null findOneBy(array $criteria, array $orderBy = null)
 * @method Critique[]    findAll()
 * @method Critique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CritiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Critique::class);
    }

    /**
     * @return Critique[] Returns an array of critiques for a specific book
     */
    public function findByLivre($livreId, $orderBy = ['dateCreation' => 'DESC']): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.livre = :livreId')
            ->setParameter('livreId', $livreId)
            ->orderBy('c.' . key($orderBy), current($orderBy))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return float Returns the average rating for a specific book
     */
    public function getAverageRatingForLivre($livreId): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('AVG(c.note) as moyenne')
            ->andWhere('c.livre = :livreId')
            ->setParameter('livreId', $livreId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }
} 