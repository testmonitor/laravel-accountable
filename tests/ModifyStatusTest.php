<?php

namespace TestMonitor\Accountable\Test;

use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use TestMonitor\Accountable\Accountable;

class ModifyStatusTest extends TestCase
{
    #[Test]
    public function it_will_enable_accountable()
    {
        // When
        accountable()->enable();

        // Then
        $this->assertTrue(Accountable::enabled());
        $this->assertFalse(Accountable::disabled());
    }

    #[Test]
    public function it_will_disable_accountable()
    {
        // When
        accountable()->disable();

        // Then
        $this->assertFalse(Accountable::enabled());
        $this->assertTrue(Accountable::disabled());
    }

    #[Test]
    public function it_can_access_the_helper_function()
    {
        // Then
        $this->assertInstanceOf(Accountable::class, accountable());
    }

    #[Test]
    public function it_will_not_redeclare_the_helper_function_when_loaded_twice()
    {
        // Given
        $this->assertTrue(function_exists('accountable'));

        // When
        require __DIR__ . '/../src/helpers.php';

        // Then
        $this->assertInstanceOf(Accountable::class, accountable());
    }

    #[Test]
    public function it_will_throw_when_the_auth_guard_has_no_configured_user_model()
    {
        // Given
        config(['auth.guards.web.provider' => 'ghost']);

        // When
        $this->expectException(RuntimeException::class);

        Accountable::userModel();
    }
}
