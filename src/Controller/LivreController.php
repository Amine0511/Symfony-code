<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Critique;
use App\Entity\Emprunt;
use App\Form\CritiqueType;
use App\Form\EmpruntType;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/livre')]
class LivreController extends AbstractController
{
    #[Route('', name: 'app_livre_index', methods: ['GET'])]
    public function index(Request $request, LivreRepository $livreRepository): Response
    {
        // Récupérer les paramètres de recherche et de filtrage
        $query = $request->query->get('q');
        $genre = $request->query->get('genre');
        $auteur = $request->query->get('auteur');
        $page = $request->query->getInt('page', 1);
        $limit = 12; // Nombre de livres par page

        // Récupérer les livres avec pagination
        $livres = $livreRepository->findPaginated($page, $limit, $query, $genre, $auteur);
        $totalLivres = $livreRepository->countTotal($query, $genre, $auteur);
        $totalPages = ceil($totalLivres / $limit);

        // Récupérer tous les genres et auteurs pour les filtres
        $genres = $livreRepository->findAllGenres();
        $auteurs = $livreRepository->findAllAuteurs();

        return $this->render('livre/index.html.twig', [
            'livres' => $livres,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLivres' => $totalLivres,
            'genres' => $genres,
            'auteurs' => $auteurs,
            'currentQuery' => $query,
            'currentGenre' => $genre,
            'currentAuteur' => $auteur
        ]);
    }

    #[Route('/{id}', name: 'app_livre_show', methods: ['GET', 'POST'])]
    public function show(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST') && $this->getUser()) {
            if (!$livre->isDisponible()) {
                $this->addFlash('error', 'Ce livre n\'est pas disponible pour le moment.');
                return $this->redirectToRoute('app_livre_show', ['id' => $livre->getId()]);
            }

            $user = $this->getUser();

            // Vérifications de sécurité
            if (!$user->canBorrow()) {
                if ($user->hasEmpruntEnRetard()) {
                    $this->addFlash('error', 'Vous avez des emprunts en retard. Veuillez les retourner avant d\'emprunter un nouveau livre.');
                } else {
                    $this->addFlash('error', 'Vous avez atteint la limite de 3 emprunts simultanés.');
                }
                return $this->redirectToRoute('app_livre_show', ['id' => $livre->getId()]);
            }

            if ($user->hasAlreadyBorrowed($livre)) {
                $this->addFlash('error', 'Vous avez déjà emprunté ce livre.');
                return $this->redirectToRoute('app_livre_show', ['id' => $livre->getId()]);
            }

            $emprunt = new Emprunt();
            $emprunt->setUtilisateur($user);
            $emprunt->setLivre($livre);
            $emprunt->setDate_emprunt(new \DateTime());
            $emprunt->setDate_retour(null);
            
            $livre->setDisponible(false);
            
            $entityManager->persist($emprunt);
            $entityManager->flush();

            $this->addFlash('success', 'Le livre a été emprunté avec succès.');
            return $this->redirectToRoute('app_emprunts_mes_emprunts');
        }

        // Récupérer les critiques du livre
        $critiques = $entityManager->getRepository(Critique::class)
            ->findBy(['livre' => $livre], ['dateCreation' => 'DESC']);

        // Calculer la moyenne des notes
        $moyenneNotes = 0;
        if (count($critiques) > 0) {
            $moyenneNotes = array_sum(array_map(fn($c) => $c->getNote(), $critiques)) / count($critiques);
        }

        // Créer le formulaire de critique
        $critique = new Critique();
        $critique->setLivre($livre);
        $critiqueForm = $this->createForm(CritiqueType::class, $critique);

        // Créer le formulaire d'emprunt
        $empruntForm = $this->createForm(EmpruntType::class, new Emprunt());

        // Traiter les formulaires
        $critiqueForm->handleRequest($request);
        if ($critiqueForm->isSubmitted() && $critiqueForm->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour ajouter une critique.');
                return $this->redirectToRoute('app_livre_show', ['id' => $livre->getId()]);
            }

            $critique->setUtilisateur($this->getUser());
            $critique->setDateCreation(new \DateTime());
            $entityManager->persist($critique);
            $entityManager->flush();

            $this->addFlash('success', 'Votre critique a été ajoutée avec succès.');
            return $this->redirectToRoute('app_livre_show', ['id' => $livre->getId()]);
        }

        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
            'critiques' => $critiques,
            'moyenneNotes' => $moyenneNotes,
            'critiqueForm' => $critiqueForm->createView(),
            'empruntForm' => $empruntForm->createView(),
            'canBorrow' => $this->getUser() ? $this->getUser()->canBorrow() : false
        ]);
    }
} 