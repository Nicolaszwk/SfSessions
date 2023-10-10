<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StudentController extends AbstractController
{

    // Crée un nouvel étudiant ou édite un étudiant existant
    #[Route('/student/new', name: 'new_student')]
    #[Route('/student/{id}/edit', name: 'edit_student')]
    public function new_edit(Student $student = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si $student est nul (nouvelle création), créez un nouvel objet Student
        if (!$student) {
            $student = new Student();
        }

        // Crée un formulaire à partir de la classe StudentType et l'objet Student
        $form = $this->createForm(StudentType::class, $student);

        // Gére la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupére les données du formulaire
            $student = $form->getData();

            // Persiste l'étudiant en base de données
            $entityManager->persist($student);
            $entityManager->flush();

            // Redirige l'utilisateur vers la route app_student
            return $this->redirectToRoute('app_student');
        }

        // Affiche le formulaire dans la vue avec les détails de l'étudiant
        return $this->render('student/new.html.twig', [
            'formAddStudent' => $form,
            'student' => $student,
        ]);
    }

    // Affiche tous les étudiants
    #[Route('/student', name: 'app_student')]
    public function index(StudentRepository $studentRepository): Response
    {
        // Récupére tous les étudiants à partir du Repository
        $students = $studentRepository->findAll();

        // Affiche les étudiants dans la vue
        return $this->render('student/index.html.twig', [
            'controller_name' => 'StudentController',
            'students' => $students,
        ]);
    }

    // Supprime un étudiant
    #[Route('/student/{id}/delete', name: 'delete_student')]
    public function delete(EntityManagerInterface $entityManager, Student $student)
    {
        // Supprime l'étudiant de la base de données
        $entityManager->remove($student);
        $entityManager->flush();

        // Redirige l'utilisateur vers la route app_student
        return $this->redirectToRoute('app_student');
    }

    // Affiche les détails d'un étudiant spécifique
    #[Route('/student/{id}', name: 'show_student')]
    public function show(Student $student): Response
    {
        // Affiche les détails de l'étudiant dans la vue
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }
}
