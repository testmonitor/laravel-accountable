<?php

namespace TestMonitor\Accountable\Test;

use Illuminate\Config\Repository;
use TestMonitor\Accountable\AccountableSettings;

class ModifyStatusTest extends TestCase
{
    /** @test */
    public function it_will_enable_accountable()
    {
        $config = new AccountableSettings(app(Repository::class));

        $config->enable();

        $this->assertTrue($config->enabled());
    }

    /** @test */
    public function it_will_disable_accountable()
    {
        $config = new AccountableSettings(app(Repository::class));

        $config->disable();

        $this->assertTrue($config->disabled());
    }

    /** @test */
    public function it_can_access_the_helper_function()
    {
        $config = accountable();

        $this->assertInstanceOf(AccountableSettings::class, $config);
    }
}
