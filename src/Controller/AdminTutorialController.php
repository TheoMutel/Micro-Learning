<?php

namespace App\Controller;

use App\Entity\Tutorial;
use App\Repository\TutorialRepository;
use App\Security\Voter\TutorialVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Workflow\Registry;

#[Route('/admin/tutorials', name: 'app_admin_tutorial_')]
#[IsGranted('ROLE_ADMIN')]
class AdminTutorialController extends AbstractController
{
    public function __construct(
        private TutorialRepository $tutorialRepository,
        private EntityManagerInterface $entityManager,
        private Registry $workflowRegistry,
    ) {
    }

    #[Route('/review', name: 'review_list', methods: ['GET'])]
    public function reviewList(): Response
    {
        $tutorials = $this->tutorialRepository->findBy(['status' => 'review']);

        return $this->render('admin/tutorial/review_list.html.twig', [
            'tutorials' => $tutorials,
        ]);
    }

    #[Route('/{id}/review', name: 'review', methods: ['GET'])]
    public function review(Tutorial $tutorial): Response
    {
        if ($tutorial->getStatus() !== 'review') {
            throw $this->createNotFoundException('Tutoriel non disponible pour révision');
        }

        return $this->render('admin/tutorial/review.html.twig', [
            'tutorial' => $tutorial,
        ]);
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, Tutorial $tutorial): Response
    {
        if ($tutorial->getStatus() !== 'review') {
            $this->addFlash('error', 'Ce tutoriel ne peut pas être approuvé');
            return $this->redirectToRoute('app_admin_tutorial_review_list');
        }

        if ($this->isCsrfTokenValid('approve' . $tutorial->getId(), $request->request->get('_token'))) {
            $workflow = $this->workflowRegistry->get($tutorial, 'tutorial_publishing');
            if ($workflow->can($tutorial, 'publish')) {
                $workflow->apply($tutorial, 'publish');
                $tutorial->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->flush();

                $this->addFlash('success', 'Tutoriel approuvé et publié!');
            }
        }

        return $this->redirectToRoute('app_admin_tutorial_review_list');
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(Request $request, Tutorial $tutorial): Response
    {
        if ($tutorial->getStatus() !== 'review') {
            $this->addFlash('error', 'Ce tutoriel ne peut pas être rejeté');
            return $this->redirectToRoute('app_admin_tutorial_review_list');
        }

        if ($this->isCsrfTokenValid('reject' . $tutorial->getId(), $request->request->get('_token'))) {
            $reason = $request->request->get('rejection_reason', '');
            
            if (empty($reason)) {
                $this->addFlash('error', 'Vous devez fournir une raison de rejet');
                return $this->redirectToRoute('app_admin_tutorial_review', ['id' => $tutorial->getId()]);
            }

            $workflow = $this->workflowRegistry->get($tutorial, 'tutorial_publishing');
            if ($workflow->can($tutorial, 'reject')) {
                $tutorial->setRejectionReason($reason);
                $workflow->apply($tutorial, 'reject');
                $tutorial->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->flush();

                $this->addFlash('success', 'Tutoriel rejeté. L\'auteur a été notifié.');
            }
        }

        return $this->redirectToRoute('app_admin_tutorial_review_list');
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        $tutorials = $this->tutorialRepository->findAll();

        return $this->render('admin/tutorial/list.html.twig', [
            'tutorials' => $tutorials,
        ]);
    }
}
