<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Property;

class PropertyVoter extends Voter
{

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public const PROPERTY_EDIT = 'PROPERTY_EDIT';
    public const PROPERTY_VIEW = 'PROPERTY_VIEW';

    protected function supports($attribute, $subject): bool
    {

        return in_array($attribute, [self::PROPERTY_EDIT, self::PROPERTY_VIEW])
            && $subject instanceof Property;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        /**
         * @var Property $property
         */
        $property = $subject;

        $user = $token->getUser();

        /*if (!$user instanceof UserInterface) {
            return false;
        }*/

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::PROPERTY_EDIT:
                return $this->canEdit($property, $user);
                break;
            case self::PROPERTY_VIEW:
                return $this->canView($property, $user);
                break;
        }

        return false;
    }

    private function canView(Property $property, $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        return !$property->getSold() && $property->getIsPublished();
    }

    private function canEdit(Property $property, $user): bool
    {
        return $property->getProprietary() === $user;
    }
}
