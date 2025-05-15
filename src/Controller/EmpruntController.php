<?php

namespace App\Controller;

use App\Entity\Emprunt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/emprunts')]
#[IsGranted('ROLE_USER')]
class EmpruntController extends AbstractController
{
    #[Route('/mes-emprunts', name: 'app_emprunts_mes_emprunts')]
    public function mesEmprunts(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Récupérer les emprunts actifs (non retournés)
        $empruntsActifs = $entityManager->getRepository(Emprunt::class)
            ->createQueryBuilder('e')
            ->andWhere('e.utilisateur = :user')
            ->andWhere('e.date_retour IS NULL')
            ->setParameter('user', $user)
            ->orderBy('e.date_retour', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('emprunt/mes_emprunts.html.twig', [
            'empruntsActifs' => $empruntsActifs
        ]);
    }
} 