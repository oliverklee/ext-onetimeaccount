<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Service\CredentialsGenerator
 */
final class CredentialsGeneratorTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private CredentialsGenerator $subject;

    /**
     * @var FrontendUserRepository&MockObject
     */
    private MockObject $userRepositoryMock;

    /**
     * @var PasswordHashInterface&MockObject
     */
    private MockObject $passwordHasherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasherMock = $this->createMock(PasswordHashInterface::class);
        $passwordHashFactoryMock = $this->createMock(PasswordHashFactory::class);
        $passwordHashFactoryMock->method('getDefaultHashInstance')->with('FE')->willReturn($this->passwordHasherMock);

        GeneralUtility::addInstance(PasswordHashFactory::class, $passwordHashFactoryMock);

        $this->userRepositoryMock = $this->createMock(FrontendUserRepository::class);
        $this->subject = new CredentialsGenerator($this->userRepositoryMock);
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
        $this->userRepositoryMock->method('findOneByUsername')->with($email)->willReturn(null);

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
        $this->userRepositoryMock->method('findOneByUsername')->with($email)->willReturn(null);

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
        $this->userRepositoryMock->method('findOneByUsername')->willReturnMap([
            [$email, $user],
            [$emailWithSuffix, null],
        ]);

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

        $this->userRepositoryMock->method('findOneByUsername')->willReturnMap([
            [$email, $user],
            [$emailWithSuffix1, $user],
            [$emailWithSuffix2, null],
        ]);

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

        self::assertMatchesRegularExpression('/^[a-z\\d]{32}$/', $user->getUsername());
    }

    /**
     * @test
     */
    public function generatePasswordForUserWithExistingPasswordKeepsOldPassword(): void
    {
        $user = new FrontendUser();
        $existingPassword = 'gzuio134tfgzuiobft1234';
        $user->setPassword($existingPassword);

        $this->subject->generatePasswordForUser($user);

        self::assertSame($existingPassword, $user->getPassword());
    }

    /**
     * @test
     */
    public function generatePasswordForUserWithExistingPasswordReturnsNull(): void
    {
        $user = new FrontendUser();
        $existingPassword = 'gzuio134tfgzuiobft1234';
        $user->setPassword($existingPassword);

        $result = $this->subject->generatePasswordForUser($user);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function generatePasswordForUserWithoutExistingPasswordReturnsTwelveCharacterPassword(): void
    {
        $user = new FrontendUser();
        $this->passwordHasherMock->method('getHashedPassword')->with(self::anything())->willReturn('');

        $result = $this->subject->generatePasswordForUser($user);

        self::assertIsString($result);
        self::assertMatchesRegularExpression('/^\\w{32}$/', $result);
    }

    /**
     * @test
     */
    public function generatePasswordForUserWithoutExistingPasswordSetsHashOfTwelveCharacterPassword(): void
    {
        $passwordHash
            = '$argon2i$v=19$m=65536,t=16,p=1$ODBXYmZrYkQ2akMwa1lHYg$iWz2uY5XHXAhjqG69uFSQDWvy/y1G931gk/s19sfBxo';
        $this->passwordHasherMock->method('getHashedPassword')->with(self::isType('string'))->willReturn($passwordHash);
        $user = new FrontendUser();

        $this->subject->generatePasswordForUser($user);

        self::assertSame($passwordHash, $user->getPassword());
    }
}
