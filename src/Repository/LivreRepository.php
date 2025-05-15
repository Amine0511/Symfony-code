<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livre>
 *
 * @method Livre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livre[]    findAll()
 * @method Livre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
     * @return Livre[] Returns an array of the most popular books
     */
    public function findMostPopular(int $limit = 4): array
    {
        return $this->createQueryBuilder('l')
            ->select('l')
            ->leftJoin('l.emprunts', 'e')
            ->where('l.disponible = :disponible')
            ->setParameter('disponible', true)
            ->groupBy('l.id')
            ->orderBy('COUNT(e.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Livre[] Returns an array of available books
     */
    public function findAvailable(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.disponible = :val')
            ->setParameter('val', true)
            ->orderBy('l.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Livre[] Returns a paginated array of books
     */
    public function findPaginated(int $page, int $limit, ?string $query = null, ?string $genre = null, ?string $auteur = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.auteur', 'a')
            ->leftJoin('l.genre', 'g')
            ->orderBy('l.titre', 'ASC');

        if ($query) {
            $qb->andWhere('l.titre LIKE :query OR l.isbn LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($genre) {
            $qb->andWhere('g.nom = :genre')
               ->setParameter('genre', $genre);
        }

        if ($auteur) {
            $qb->andWhere('a.nom = :auteur')
               ->setParameter('auteur', $auteur);
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return iterator_to_array(new Paginator($qb));
    }

    /**
     * @return int Returns the total count of books matching the criteria
     */
    public function countTotal(?string $query = null, ?string $genre = null, ?string $auteur = null): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->leftJoin('l.auteur', 'a')
            ->leftJoin('l.genre', 'g');

        if ($query) {
            $qb->andWhere('l.titre LIKE :query OR l.isbn LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($genre) {
            $qb->andWhere('g.nom = :genre')
               ->setParameter('genre', $genre);
        }

        if ($auteur) {
            $qb->andWhere('a.nom = :auteur')
               ->setParameter('auteur', $auteur);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array Returns all unique genres
     */
    public function findAllGenres(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT g.nom')
            ->leftJoin('l.genre', 'g')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array Returns all unique authors
     */
    public function findAllAuteurs(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT a.nom')
            ->leftJoin('l.auteur', 'a')
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

//     * @return Livre[] Returns an array of Livre objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Livre
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}