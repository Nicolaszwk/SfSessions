<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{

    // Crée une nouvelle catégorie ou édite une catégorie existante
    #[Route('/category/new', name: 'new_category')]
    #[Route('/category/{id}/edit', name: 'edit_category')]
    public function new_edit(Category $category = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si $category est nul (nouvelle création), crée un nouvel objet Category
        if (!$category) {
            $category = new Category();
        }

        // Crée un formulaire à partir de la classe CategoryType et l'objet Category
        $form = $this->createForm(CategoryType::class, $category);

        // Gére la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupére les données du formulaire
            $category = $form->getData();

            // Persiste la catégorie en base de données
            $entityManager->persist($category);
            $entityManager->flush();

            // Redirige l'utilisateur vers la route app_category
            return $this->redirectToRoute('app_category');
        }

        // Affiche le formulaire dans la vue et les détails de la catégorie
        return $this->render('category/new.html.twig', [
            'formAddCategory' => $form,
            'category' => $category,
        ]);
    }

    // Supprime une catégorie
    #[Route('/category/{id}/delete', name: 'delete_category')]
    public function delete(EntityManagerInterface $entityManager, Category $category)
    {
        // Supprime la catégorie de la base de données
        $entityManager->remove($category);
        $entityManager->flush();

        // Redirige l'utilisateur vers la route app_category
        return $this->redirectToRoute('app_category');
    }

    // Affiche toutes les catégories
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // Récupére toutes les catégories à partir du Repository
        $categories = $categoryRepository->findAll();

        // Affiche les catégories dans la vue
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
        ]);
    }
}
