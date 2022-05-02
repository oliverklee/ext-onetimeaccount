<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Generate a unique username for the given user (either from the email address possibly with a number attached, or
 * a 32-character random hex username if the email address is empty).
 */
class UsernameGenerator implements SingletonInterface
{
    /**
     * @var FrontendUserRepository
     */
    private $userRepository;

    public function injectFrontendUserRepository(FrontendUserRepository $repository): void
    {
        $this->userRepository = $repository;
    }

    /**
     * Generates and sets a unique username for the given user.
     */
    public function generateUsernameForUser(FrontendUser $user): void
    {
        if ($user->getUsername() !== '') {
            return;
        }

        if ($user->getEmail() !== '') {
            $this->generateUsernameFromEmail($user);
        } else {
            $this->generateRandomHexUsername($user);
        }
    }

    private function generateUsernameFromEmail(FrontendUser $user): void
    {
        $email = \trim($user->getEmail());
        $userByEmail = $this->userRepository->findOneByUsername($email);
        if ($userByEmail === null) {
            $user->setUsername($email);
            return;
        }

        $suffix = 1;
        do {
            $username = $email . '_' . $suffix;
            $userByEmail = $this->userRepository->findOneByUsername($username);
            $suffix++;
        } while ($userByEmail !== null);

        $user->setUsername($username);
    }

    private function generateRandomHexUsername(FrontendUser $user): void
    {
        $username = \bin2hex(\random_bytes(16));
        $user->setUsername($username);
    }
}
