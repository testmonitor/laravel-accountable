<?php

namespace TestMonitor\Accountable\Test;

use Illuminate\Config\Repository;
use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Accountable\AccountableSettings;

class ModifyStatusTest extends TestCase
{
    #[Test]
    public function it_will_enable_accountable()
    {
        $config = new AccountableSettings(app(Repository::class));

        $config->enable();

        $this->assertTrue($config->enabled());
    }

    #[Test]
    public function it_will_disable_accountable()
    {
        $config = new AccountableSettings(app(Repository::class));

        $config->disable();

        $this->assertTrue($config->disabled());
    }

    #[Test]
    public function it_can_access_the_helper_function()
    {
        $config = accountable();

        $this->assertInstanceOf(AccountableSettings::class, $config);
    }
}
