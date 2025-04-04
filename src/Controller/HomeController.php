<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use App\Repository\AuteurRepository;
use App\Repository\GenreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        LivreRepository $livreRepository,
        AuteurRepository $auteurRepository,
        GenreRepository $genreRepository
    ): Response {
        // Récupérer les 8 derniers livres ajoutés
        $livresPopulaires = $livreRepository->findBy([], ['id' => 'DESC'], 8);

        return $this->render('home/index.html.twig', [
            'total_livres' => $livreRepository->count([]),
            'total_auteurs' => $auteurRepository->count([]),
            'total_genres' => $genreRepository->count([]),
            'livres' => $livresPopulaires,
        ]);
    }
} 