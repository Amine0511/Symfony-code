<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\Genre;
use App\Entity\Livre;
use App\Entity\Emprunt;
use App\Form\AuteurType;
use App\Form\GenreType;
use App\Form\LivreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/auteurs', name: 'app_admin_auteurs')]
    public function auteurs(EntityManagerInterface $entityManager): Response
    {
        $auteurs = $entityManager->getRepository(Auteur::class)->findAll();
        return $this->render('admin/auteurs/index.html.twig', [
            'auteurs' => $auteurs
        ]);
    }

    #[Route('/auteurs/new', name: 'app_admin_auteur_new')]
    public function newAuteur(Request $request, EntityManagerInterface $entityManager): Response
    {
        $auteur = new Auteur();
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($auteur);
            $entityManager->flush();

            $this->addFlash('success', 'L\'auteur a été créé avec succès.');
            return $this->redirectToRoute('app_admin_auteurs');
        }

        return $this->render('admin/auteurs/form.html.twig', [
            'form' => $form->createView(),
            'auteur' => $auteur
        ]);
    }

    #[Route('/auteurs/{id}/edit', name: 'app_admin_auteur_edit')]
    public function editAuteur(Request $request, Auteur $auteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'auteur a été modifié avec succès.');
            return $this->redirectToRoute('app_admin_auteurs');
        }

        return $this->render('admin/auteurs/form.html.twig', [
            'form' => $form->createView(),
            'auteur' => $auteur
        ]);
    }

    #[Route('/auteurs/{id}/delete', name: 'app_admin_auteur_delete', methods: ['POST'])]
    public function deleteAuteur(Auteur $auteur, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($auteur);
        $entityManager->flush();

        $this->addFlash('success', 'L\'auteur a été supprimé avec succès.');
        return $this->redirectToRoute('app_admin_auteurs');
    }

    #[Route('/genres', name: 'app_admin_genres')]
    public function genres(EntityManagerInterface $entityManager): Response
    {
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        return $this->render('admin/genres/index.html.twig', [
            'genres' => $genres
        ]);
    }

    #[Route('/genres/new', name: 'app_admin_genre_new')]
    public function newGenre(Request $request, EntityManagerInterface $entityManager): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($genre);
            $entityManager->flush();

            $this->addFlash('success', 'Le genre a été créé avec succès.');
            return $this->redirectToRoute('app_admin_genres');
        }

        return $this->render('admin/genres/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau genre'
        ]);
    }

    #[Route('/genres/{id}/edit', name: 'app_admin_genre_edit')]
    public function editGenre(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le genre a été modifié avec succès.');
            return $this->redirectToRoute('app_admin_genres');
        }

        return $this->render('admin/genres/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le genre'
        ]);
    }

    #[Route('/genres/{id}/delete', name: 'app_admin_genre_delete', methods: ['POST'])]
    public function deleteGenre(Genre $genre, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($genre);
        $entityManager->flush();

        $this->addFlash('success', 'Le genre a été supprimé avec succès.');
        return $this->redirectToRoute('app_admin_genres');
    }

    #[Route('/livres', name: 'app_admin_livres')]
    public function livres(EntityManagerInterface $entityManager): Response
    {
        $livres = $entityManager->getRepository(Livre::class)->findAll();
        return $this->render('admin/livres/index.html.twig', [
            'livres' => $livres
        ]);
    }

    #[Route('/livres/new', name: 'app_admin_livre_new')]
    public function newLivre(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $livre->setDisponible(true);
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            $this->addFlash('success', 'Le livre a été créé avec succès.');
            return $this->redirectToRoute('app_admin_livres');
        }

        return $this->render('admin/livres/form.html.twig', [
            'form' => $form->createView(),
            'livre' => $livre
        ]);
    }

    #[Route('/livres/{id}/edit', name: 'app_admin_livre_edit')]
    public function editLivre(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le livre a été modifié avec succès.');
            return $this->redirectToRoute('app_admin_livres');
        }

        return $this->render('admin/livres/form.html.twig', [
            'form' => $form->createView(),
            'livre' => $livre
        ]);
    }

    #[Route('/livres/{id}/delete', name: 'app_admin_livre_delete', methods: ['POST'])]
    public function deleteLivre(Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($livre);
        $entityManager->flush();

        $this->addFlash('success', 'Le livre a été supprimé avec succès.');
        return $this->redirectToRoute('app_admin_livres');
    }

    #[Route('/admin/emprunt/{id}/return', name: 'app_admin_emprunt_return', methods: ['POST'])]
    public function returnBook(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('return'.$livre->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        // Récupérer l'emprunt en cours pour ce livre
        $emprunt = $entityManager->getRepository(Emprunt::class)->findOneBy([
            'livre' => $livre,
            'date_retour' => null
        ]);

        if ($emprunt) {
            // Marquer la date de retour
            $emprunt->setDate_retour(new \DateTime());
            // Marquer le livre comme disponible
            $livre->setDisponible(true);
            
            $entityManager->flush();

            $this->addFlash('success', 'Le livre a été retourné avec succès.');
        } else {
            $this->addFlash('error', 'Aucun emprunt en cours trouvé pour ce livre.');
        }

        return $this->redirectToRoute('app_admin_livres');
    }
} 