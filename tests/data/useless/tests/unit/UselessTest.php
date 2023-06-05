<?php

use Codeception\Stub\Expected;

class UselessTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testMakeNoAssertions(): void
    {
    }

    public function testExpectsNotToPerformAssertions(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testMakeUnexpectedAssertion(): void
    {
        $this->expectNotToPerformAssertions();
        $this->assertTrue(true);
    }

    public function testMockExpectations(): void
    {
        $user = $this->make(
            UserModel::class,
            [
                'setName' => Expected::once('Foo'),
            ],
        );

        $userService = new UserService($user);
        $userService->create('Foo');
    }
}
