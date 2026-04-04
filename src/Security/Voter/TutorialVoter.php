<?php

namespace App\Security\Voter;

use App\Entity\Tutorial;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TutorialVoter extends Voter
{
    public const EDIT = 'tutorial_edit';
    public const DELETE = 'tutorial_delete';
    public const SUBMIT_REVIEW = 'tutorial_submit_review';
    public const APPROVE = 'tutorial_approve';
    public const REJECT = 'tutorial_reject';
    public const VIEW = 'tutorial_view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::EDIT, self::DELETE, self::SUBMIT_REVIEW, self::APPROVE, self::REJECT, self::VIEW])) {
            return false;
        }

        if (!$subject instanceof Tutorial) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Tutorial $tutorial */
        $tutorial = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($tutorial, $user),
            self::DELETE => $this->canDelete($tutorial, $user),
            self::SUBMIT_REVIEW => $this->canSubmitReview($tutorial, $user),
            self::APPROVE => $this->canApprove($tutorial, $user),
            self::REJECT => $this->canReject($tutorial, $user),
            self::VIEW => $this->canView($tutorial, $user),
            default => false,
        };
    }

    private function canEdit(Tutorial $tutorial, User $user): bool
    {
        // Only the author can edit their own tutorial while it is in draft or rejected status
        return $tutorial->getAuthor() === $user && in_array($tutorial->getStatus(), ['draft', 'rejected'], true);
    }

    private function canDelete(Tutorial $tutorial, User $user): bool
    {
        // Only the author can delete their own tutorial while it is in draft or rejected status
        return $tutorial->getAuthor() === $user && in_array($tutorial->getStatus(), ['draft', 'rejected'], true);
    }

    private function canSubmitReview(Tutorial $tutorial, User $user): bool
    {
        // Only the author can submit their tutorial for review when it is a draft or rejected
        return $tutorial->getAuthor() === $user && in_array($tutorial->getStatus(), ['draft', 'rejected'], true);
    }

    private function canApprove(Tutorial $tutorial, User $user): bool
    {
        // Only admins can approve (review) tutorials
        return in_array('ROLE_ADMIN', $user->getRoles()) && $tutorial->getStatus() === 'review';
    }

    private function canReject(Tutorial $tutorial, User $user): bool
    {
        // Only admins can reject tutorials
        return in_array('ROLE_ADMIN', $user->getRoles()) && $tutorial->getStatus() === 'review';
    }

    private function canView(Tutorial $tutorial, User $user): bool
    {
        // Published tutorials are viewable by everyone
        if ($tutorial->getStatus() === 'published') {
            return true;
        }

        // Authors can view their own tutorials
        if ($tutorial->getAuthor() === $user) {
            return true;
        }

        // Admins can view all tutorials (including drafts and review)
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
