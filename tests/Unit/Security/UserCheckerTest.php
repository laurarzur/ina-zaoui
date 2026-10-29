<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    private UserChecker $checker;
    private User $user;

    protected function setUp(): void
    {
        $this->checker = new UserChecker();
        $this->user = new User();
    }

    public function testGuestIsBlocked(): void
    {
        $this->user->setIsBlocked(true);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->checker->checkPreAuth($this->user);
    }

    public function testGuestIsActive(): void
    {
        $this->checker->checkPreAuth($this->user);
        $this->addToAssertionCount(1);
    }

    public function testOtherUserType(): void
    {
        $this->checker->checkPreAuth($this->createStub(UserInterface::class));
        $this->addToAssertionCount(1);
    }
}
