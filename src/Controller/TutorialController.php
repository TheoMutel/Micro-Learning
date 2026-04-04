<?php

namespace App\Controller;

use App\Entity\Step;
use App\Entity\Tutorial;
use App\Form\TutorialType;
use App\Repository\TutorialRepository;
use App\Security\Voter\TutorialVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Registry;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/tutorials', name: 'app_tutorial_')]
class TutorialController extends AbstractController
{
    public function __construct(
        private TutorialRepository $tutorialRepository,
        private EntityManagerInterface $entityManager,
        private Registry $workflowRegistry,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TutorialRepository $repository): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            // Admins see all tutorials
            $tutorials = $repository->findAll();
        } else {
            // Regular users see only published tutorials and their own
            $tutorials = $repository->findPublishedAndOwn($user);
        }

        return $this->render('tutorial/index.html.twig', [
            'tutorials' => $tutorials,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $tutorial = new Tutorial();
        $tutorial->setAuthor($this->getUser());
        $tutorial->addStep((new Step())->setPosition(1));

        $form = $this->createForm(TutorialType::class, $tutorial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($tutorial);
            $this->entityManager->flush();

            $this->addFlash('success', 'Tutoriel créé avec succès!');

            return $this->redirectToRoute('app_tutorial_show', ['id' => $tutorial->getId()]);
        }

        return $this->render('tutorial/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Tutorial $tutorial): Response
    {
        $this->denyAccessUnlessGranted(TutorialVoter::VIEW, $tutorial);

        return $this->render('tutorial/show.html.twig', [
            'tutorial' => $tutorial,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tutorial $tutorial): Response
    {
        $this->denyAccessUnlessGranted(TutorialVoter::EDIT, $tutorial);

        $form = $this->createForm(TutorialType::class, $tutorial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tutorial->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Tutoriel modifié avec succès!');

            return $this->redirectToRoute('app_tutorial_show', ['id' => $tutorial->getId()]);
        }

        return $this->render('tutorial/edit.html.twig', [
            'form' => $form,
            'tutorial' => $tutorial,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Tutorial $tutorial): Response
    {
        $this->denyAccessUnlessGranted(TutorialVoter::DELETE, $tutorial);

        if ($this->isCsrfTokenValid('delete' . $tutorial->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($tutorial);
            $this->entityManager->flush();

            $this->addFlash('success', 'Tutoriel supprimé avec succès!');
        }

        return $this->redirectToRoute('app_tutorial_index');
    }

    #[Route('/{id}/submit-review', name: 'submit_review', methods: ['POST'])]
    public function submitReview(Request $request, Tutorial $tutorial): Response
    {
        $this->denyAccessUnlessGranted(TutorialVoter::SUBMIT_REVIEW, $tutorial);

        if ($this->isCsrfTokenValid('submit' . $tutorial->getId(), $request->request->get('_token'))) {
            $workflow = $this->workflowRegistry->get($tutorial, 'tutorial_publishing');
            if ($workflow->can($tutorial, 'to_review')) {
                $workflow->apply($tutorial, 'to_review');
                $this->entityManager->flush();

                $this->addFlash('success', 'Tutoriel soumis pour validation!');
            }
        }

        return $this->redirectToRoute('app_tutorial_show', ['id' => $tutorial->getId()]);
    }
}
