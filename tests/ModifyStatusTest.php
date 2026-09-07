<?php

namespace TestMonitor\Accountable\Test;

use RuntimeException;
use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Accountable\Accountable;

class ModifyStatusTest extends TestCase
{
    #[Test]
    public function it_will_enable_accountable()
    {
        accountable()->enable();

        $this->assertTrue(Accountable::enabled());
        $this->assertFalse(Accountable::disabled());
    }

    #[Test]
    public function it_will_disable_accountable()
    {
        accountable()->disable();

        $this->assertFalse(Accountable::enabled());
        $this->assertTrue(Accountable::disabled());
    }

    #[Test]
    public function it_can_access_the_helper_function()
    {
        $this->assertInstanceOf(Accountable::class, accountable());
    }

    #[Test]
    public function it_will_not_redeclare_the_helper_function_when_loaded_twice()
    {
        $this->assertTrue(function_exists('accountable'));

        require __DIR__ . '/../src/helpers.php';

        $this->assertInstanceOf(Accountable::class, accountable());
    }

    #[Test]
    public function it_will_throw_when_the_auth_guard_has_no_configured_user_model()
    {
        config(['auth.guards.web.provider' => 'ghost']);

        $this->expectException(RuntimeException::class);

        Accountable::userModel();
    }
}
