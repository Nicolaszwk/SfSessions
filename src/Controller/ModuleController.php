<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController
{
    // Crée un nouveau module ou édite un module existant
    #[Route('/module/new', name: 'new_module')]
    #[Route('/module/{id}/edit', name: 'edit_module')]
    public function new_edit(Module $module = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si $module est nul (nouvelle création), créez un nouvel objet Module
        if (!$module) {
            $module = new Module();
        }

        // Crée un formulaire à partir de la classe ModuleType et l'objet Module
        $form = $this->createForm(ModuleType::class, $module);

        // Gére la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupére les données du formulaire
            $module = $form->getData();

            // Persiste le module en base de données
            $entityManager->persist($module);
            $entityManager->flush();

            // Redirige l'utilisateur vers la route app_module
            return $this->redirectToRoute('app_module');
        }

        // Affiche le formulaire dans la vue et les détails du module
        return $this->render('module/new.html.twig', [
            'formAddModule' => $form,
            'module' => $module,
        ]);
    }

    // Supprime un module
    #[Route('/module/{id}/delete', name: 'delete_module')]
    public function delete(EntityManagerInterface $entityManager, Module $module)
    {
        // Supprime le module de la base de données
        $entityManager->remove($module);
        $entityManager->flush();

        // Redirige l'utilisateur vers la route app_module
        return $this->redirectToRoute('app_module');
    }

    // Affiche tous les modules
    #[Route('/module', name: 'app_module')]
    public function index(ModuleRepository $moduleRepository): Response
    {
        // Récupére tous les modules à partir du Repository
        $modules = $moduleRepository->findAll();

        // Affiche les modules dans la vue
        return $this->render('module/index.html.twig', [
            'controller_name' => 'ModuleController',
            'modules' => $modules,
        ]);
    }
}
