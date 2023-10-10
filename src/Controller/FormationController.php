<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\SessionRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormationController extends AbstractController
{

    // Crée une nouvelle formation ou édite une formation existante
    #[Route('/formation/new', name: 'new_formation')]
    #[Route('/formation/{id}/edit', name: 'edit_formation')]
    public function new_edit(Formation $formation = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si $formation est nul, crée un nouvel objet Formation
        if (!$formation) {
            $formation = new Formation();
        }

        // Crée un formulaire à partir de la classe FormationType et l'objet Formation
        $form = $this->createForm(FormationType::class, $formation);

        // Gére la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupére les données du formulaire
            $formation = $form->getData();

            // Persiste la formation en base de données
            $entityManager->persist($formation);
            $entityManager->flush();

            // Redirige l'utilisateur vers la route app_formation
            return $this->redirectToRoute('app_formation');
        }

        // Affichez le formulaire dans la vue et les détails de la formation
        return $this->render('formation/new.html.twig', [
            'formAddFormation' => $form,
            'formation' => $formation,
        ]);
    }

    // Supprime une formation
    #[Route('/formation/{id}/delete', name: 'delete_formation')]
    public function delete(EntityManagerInterface $entityManager, Formation $formation)
    {
        // Supprime la formation de la base de données
        $entityManager->remove($formation);
        $entityManager->flush();

        // Redirige l'utilisateur vers la route app_formation
        return $this->redirectToRoute('app_formation');
    }

    // Affiche toutes les formations
    #[Route('/formation', name: 'app_formation')]
    public function index(FormationRepository $formationRepository): Response
    {
        // Récupére toutes les formations à partir du Repository
        $formations = $formationRepository->findAll();

        // Affiche les formations dans la vue
        return $this->render('formation/index.html.twig', [
            'controller_name' => 'FormationController',
            'formations' => $formations,
        ]);
    }

    // Affiche les détails d'une formation spécifique
    #[Route('/formation/{id}', name: 'show_formation')]
    public function show(Formation $formation): Response
    {
        // Récupére les sessions associées à la formation
        $sessions = $formation->getSessions();

        // Affiche les détails de la formation et les sessions dans la vue
        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'sessions' => $sessions,
        ]);
    }
}
