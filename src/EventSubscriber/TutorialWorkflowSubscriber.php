<?php

namespace App\EventSubscriber;

use App\Entity\Tutorial;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Workflow\Event\Event;

class TutorialWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.tutorial_publishing.transition' => 'onTransition',
        ];
    }

    public function onTransition(Event $event): void
    {
        /** @var Tutorial $tutorial */
        $tutorial = $event->getSubject();
        $transition = $event->getTransition();

        match ($transition->getName()) {
            'to_review' => $this->onSubmitReview($tutorial),
            'publish' => $this->onPublish($tutorial),
            'reject' => $this->onReject($tutorial),
            default => null,
        };
    }

    private function onSubmitReview(Tutorial $tutorial): void
    {
        $email = (new Email())
            ->from('noreply@microlearning.local')
            ->to('admin@microlearning.local')
            ->subject('Nouveau tutoriel en attente de validation')
            ->html($this->renderTemplate('review_notification', [
                'title' => $tutorial->getTitle(),
                'author' => $tutorial->getAuthor()?->getFirstName() . ' ' . $tutorial->getAuthor()?->getLastName(),
                'id' => $tutorial->getId(),
            ]));

        $this->mailer->send($email);
    }

    private function onPublish(Tutorial $tutorial): void
    {
        $email = (new Email())
            ->from('noreply@microlearning.local')
            ->to($tutorial->getAuthor()?->getEmail())
            ->subject('Votre tutoriel a été publié!')
            ->html($this->renderTemplate('publish_notification', [
                'title' => $tutorial->getTitle(),
                'firstName' => $tutorial->getAuthor()?->getFirstName(),
            ]));

        $this->mailer->send($email);
    }

    private function onReject(Tutorial $tutorial): void
    {
        $email = (new Email())
            ->from('noreply@microlearning.local')
            ->to($tutorial->getAuthor()?->getEmail())
            ->subject('Votre tutoriel a été rejeté')
            ->html($this->renderTemplate('reject_notification', [
                'title' => $tutorial->getTitle(),
                'firstName' => $tutorial->getAuthor()?->getFirstName(),
                'reason' => $tutorial->getRejectionReason(),
            ]));

        $this->mailer->send($email);
    }

    private function renderTemplate(string $template, array $context): string
    {
        return match ($template) {
            'review_notification' => $this->renderReviewNotification($context),
            'publish_notification' => $this->renderPublishNotification($context),
            'reject_notification' => $this->renderRejectNotification($context),
            default => '',
        };
    }

    private function renderReviewNotification(array $context): string
    {
        return <<<HTML
<h2>Nouveau tutoriel en attente de validation</h2>
<p><strong>Titre:</strong> {$context['title']}</p>
<p><strong>Auteur:</strong> {$context['author']}</p>
<p>Un nouveau tutoriel a été soumis pour validation. Veuillez le consulter et approuver ou rejeter.</p>
<p><a href="http://localhost:8000/admin/tutorials/{$context['id']}/review">Voir le tutoriel</a></p>
HTML;
    }

    private function renderPublishNotification(array $context): string
    {
        return <<<HTML
<h2>Votre tutoriel a été publié!</h2>
<p>Bonjour {$context['firstName']},</p>
<p>Félicitations! Votre tutoriel "<strong>{$context['title']}</strong>" a été approuvé et est maintenant publié.</p>
<p>Merci de votre contribution!</p>
HTML;
    }

    private function renderRejectNotification(array $context): string
    {
        return <<<HTML
<h2>Votre tutoriel a été rejeté</h2>
<p>Bonjour {$context['firstName']},</p>
<p>Malheureusement, votre tutoriel "<strong>{$context['title']}</strong>" a été rejeté.</p>
<p><strong>Raison:</strong> {$context['reason']}</p>
<p>Vous pouvez le modifier et le soumettre à nouveau.</p>
HTML;
    }
}
