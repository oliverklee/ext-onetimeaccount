<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Service\CredentialsGenerator
 */
final class CredentialsGeneratorTest extends UnitTestCase
{
    /**
     * @var CredentialsGenerator
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $subject;

    /**
     * @var ObjectProphecy<FrontendUserRepository>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $userRepositoryProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CredentialsGenerator();

        $this->userRepositoryProphecy = $this->prophesize(FrontendUserRepository::class);
        $userRepository = $this->userRepositoryProphecy->reveal();
        $this->subject->injectFrontendUserRepository($userRepository);
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @return \Generator<string, array<int, FrontendUser>>
     */
    public function userWithUsernameDataProvider(): \Generator
    {
        $userWithUsernameWithoutEmail = new FrontendUser();
        $userWithUsernameWithoutEmail->setUsername('max');
        yield 'with username, without email' => [$userWithUsernameWithoutEmail];

        $userWithUsernameAndWithEmail = new FrontendUser();
        $userWithUsernameAndWithEmail->setUsername('max');
        $userWithUsernameAndWithEmail->setEmail('max@exampl.com');
        yield 'with username, with email' => [$userWithUsernameAndWithEmail];
    }

    /**
     * @test
     *
     * @dataProvider userWithUsernameDataProvider
     */
    public function generateUsernameForUserWithUsernameKeepsUsernameUnchanged(FrontendUser $user): void
    {
        $existingUsername = $user->getUsername();

        $this->subject->generateUsernameForUser($user);

        self::assertSame($existingUsername, $user->getUsername());
    }

    /**
     * @test
     */
    public function generateUsernameForUserWithUniqueEmailUsesEmailAsUsername(): void
    {
        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail($email);
        $this->userRepositoryProphecy->findOneByUsername($email)->willReturn(null);

        $this->subject->generateUsernameForUser($user);

        self::assertSame($email, $user->getUsername());
    }

    /**
     * @test
     */
    public function generateUsernameForUserWithUniqueEmailTrimsEmailAsUsername(): void
    {
        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail(' ' . $email . ' ');
        $this->userRepositoryProphecy->findOneByUsername($email)->willReturn(null);

        $this->subject->generateUsernameForUser($user);

        self::assertSame($email, $user->getUsername());
    }

    /**
     * @test
     */
    public function generateUsernameForUserWithExistingEmailUsesEmailWithUniqueSuffixAsUsername(): void
    {
        $email = 'unique@example.com';
        $emailWithSuffix = $email . '_1';
        $user = new FrontendUser();
        $user->setEmail($email);
        $this->userRepositoryProphecy->findOneByUsername($email)->willReturn($user);
        $this->userRepositoryProphecy->findOneByUsername($emailWithSuffix)->willReturn(null);

        $this->subject->generateUsernameForUser($user);

        self::assertSame($emailWithSuffix, $user->getUsername());
    }

    /**
     * @test
     */
    public function generateUsernameForUserWithExistingEmailWithSuffixUsesEmailWithNextUniqueSuffixAsUsername(): void
    {
        $email = 'unique@example.com';
        $emailWithSuffix1 = $email . '_1';
        $emailWithSuffix2 = $email . '_2';
        $user = new FrontendUser();
        $user->setEmail($email);
        $this->userRepositoryProphecy->findOneByUsername($email)->willReturn($user);
        $this->userRepositoryProphecy->findOneByUsername($emailWithSuffix1)->willReturn($user);
        $this->userRepositoryProphecy->findOneByUsername($emailWithSuffix2)->willReturn(null);

        $this->subject->generateUsernameForUser($user);

        self::assertSame($emailWithSuffix2, $user->getUsername());
    }

    /**
     * @test
     */
    public function generateUsernameForUserWithoutEmailUsesUuidAsUsername(): void
    {
        $user = new FrontendUser();

        $this->subject->generateUsernameForUser($user);

        self::assertRegExp('/^[a-z\\d]{32}$/', $user->getUsername());
    }
}
