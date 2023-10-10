<?php

namespace App\Controller;

use App\Entity\Program;
use App\Entity\Session;
use App\Entity\Student;
use App\Form\SessionType;
use App\Repository\ProgramRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{

    // Crée une nouvelle session ou édite une session existante
    #[Route('/session/new/{formationId}', name: 'new_session')]
    public function new_edit(FormationRepository $formationRepository, Session $session = null, $formationId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si $session est nul (nouvelle création), créez un nouvel objet Session
        if (!$session) {
            $session = new Session();
        }

        // Crée un formulaire à partir de la classe SessionType et l'objet Session
        $form = $this->createForm(SessionType::class, $session);

        // Récupére la formation associée à partir de l'ID de formation
        $formation = $formationRepository->find($formationId);

        // Gére la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupére les données du formulaire
            $session = $form->getData();

            // Associe la session à la formation (si disponible)
            if ($formation) {
                $session->setFormation($formation);
            }

            // Persiste la session en base de données
            $entityManager->persist($session);
            $entityManager->flush();

            // Redirige l'utilisateur vers la route show_formation avec l'ID de la formation
            return $this->redirectToRoute('show_formation', ['id' => $formationId]);
        }

        // Affiche le formulaire dans la vue avec l'ID de la session
        return $this->render('session/new.html.twig', [
            'form' => $form,
            'sessionId' => $session->getId()
        ]);
    }

    // Supprime une session
    #[Route('/session/{id}/delete', name: 'delete_session')]
    public function delete(EntityManagerInterface $entityManager, Session $session)
    {
        // Supprime la session de la base de données
        $entityManager->remove($session);
        $entityManager->flush();

        // Redirige l'utilisateur vers la route app_session
        return $this->redirectToRoute('app_session');
    }

    // Affiche toutes les sessions
    #[Route('/session', name: 'app_session')]
    public function index(SessionRepository $sessionRepository): Response
    {
        // Récupére toutes les sessions à partir du Repository
        $sessions = $sessionRepository->findAll();

        // Affiche les sessions dans la vue
        return $this->render('session/index.html.twig', [
            'controller_name' => 'SessionController',
            'sessions' => $sessions,
        ]);
    }

    // Affiche les détails d'une session spécifique
    #[Route('/session/{id}', name: 'show_session')]
    public function show(Session $session, $id, EntityManagerInterface $entityManager, ProgramRepository $programRepository): Response
    {
        // Récupére les programmes associés à cette session
        $programs = $programRepository->findBy(['session' => $id], []);

        // Recherche les étudiants qui ne sont pas dans cette session
        $studentsNotInSession = $entityManager->getRepository(Student::class)->createQueryBuilder('e')
            ->where(':session NOT MEMBER OF e.sessions')
            ->setParameter('session', $session)
            ->getQuery()
            ->getResult();

        // Affiche les détails de la session, les programmes et les étudiants
        return $this->render('session/show.html.twig', [
            'session' => $session,
            'programs' => $programs,
            'studentsNotInSession' => $studentsNotInSession,
        ]);
    }

    // Supprime un étudiant d'une session
    #[Route('/session/{id}/remove_student/{student_id}', name: 'remove_student')]
    public function removeStudent(EntityManagerInterface $entityManager, $id, $student_id, StudentRepository $studentRepository, SessionRepository $sessionRepository)
    {
        // Récupére l'étudiant et la session
        $student = $studentRepository->find($student_id);
        $session = $sessionRepository->find($id);

        // Retire l'étudiant de la session
        $session->removeStudent($student);

        $entityManager->flush();

        return $this->redirectToRoute('show_session', ['id' => $id]);
    }

    // Ajoute un étudiant à une session
    // Cette méthode gère la route /session/{id}/add_student/{student_id}
    #[Route('/session/{id}/add_student/{student_id}', name: 'add_student')]
    public function addStudent(EntityManagerInterface $entityManager, $id, $student_id, StudentRepository $studentRepository, SessionRepository $sessionRepository)
    {
        $student = $studentRepository->find($student_id);
        $session = $sessionRepository->find($id);
        $session->addStudent($student);

        $entityManager->flush();

        return $this->redirectToRoute('show_session', ['id' => $id]);
    }
}
