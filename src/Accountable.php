<?php

namespace TestMonitor\Accountable;

use RuntimeException;
use Illuminate\Contracts\Auth\Authenticatable;

class Accountable
{
    protected ?bool $enabled = null;

    protected ?Authenticatable $impersonatedUser = null;

    protected ?array $anonymousUser = null;

    /**
     * Start tracking changes.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Stop tracking changes.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Whether Accountable is currently tracking changes.
     */
    public static function enabled(): bool
    {
        return accountable()->enabled ?? (bool) config('accountable.enabled', true);
    }

    /**
     * Whether Accountable is currently not tracking changes.
     */
    public static function disabled(): bool
    {
        return ! static::enabled();
    }

    /**
     * Override user identification with the given user.
     */
    public function actingAs(Authenticatable $user): void
    {
        $this->impersonatedUser = $user;
    }

    /**
     * Perform a callback while acting as the given user, then reset.
     */
    public function whileActingAs(Authenticatable $user, callable $callback): mixed
    {
        $this->actingAs($user);

        try {
            return $callback($this);
        } finally {
            $this->reset();
        }
    }

    /**
     * Stop impersonating.
     */
    public function reset(): void
    {
        $this->impersonatedUser = null;
    }

    /**
     * The user currently being impersonated, if any.
     */
    public function user(): ?Authenticatable
    {
        return $this->impersonatedUser;
    }

    /**
     * Returns the configured authentication driver.
     */
    public static function authDriver(): string
    {
        return config('accountable.auth_driver') ?? config('auth.defaults.guard');
    }

    /**
     * Returns the current user, based on impersonation or the configured authentication driver.
     */
    public static function authenticatedUser(): ?Authenticatable
    {
        return accountable()->user() ?? auth()->guard(static::authDriver())->user();
    }

    /**
     * Returns the user model, based on the configured authentication driver.
     *
     * @throws \RuntimeException when the guard has no configured user model
     */
    public static function userModel(): string
    {
        $guard = static::authDriver();
        $provider = config("auth.guards.{$guard}.provider");

        $model = $provider ? config("auth.providers.{$provider}.model") : null;

        if (! $model) {
            throw new RuntimeException(
                "Accountable could not resolve a user model for the \"{$guard}\" auth guard. " .
                "Check your \"auth.guards\" and \"auth.providers\" configuration."
            );
        }

        return $model;
    }

    /**
     * Override the fallback user attributes for unauthenticated activity.
     */
    public function setAnonymousUser(array $user): void
    {
        $this->anonymousUser = $user;
    }

    /**
     * The fallback user attributes for unauthenticated activity, if configured.
     */
    public static function anonymousUser(): ?array
    {
        return accountable()->anonymousUser ?? config('accountable.anonymous');
    }
}
