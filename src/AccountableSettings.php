<?php

namespace TestMonitor\Accountable;

use Illuminate\Config\Repository;

class AccountableSettings
{
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $createdByColumn;

    /**
     * @var string
     */
    protected $updatedByColumn;

    /**
     * @var string
     */
    protected $deletedByColumn;

    /**
     * @var array|null
     */
    protected $anonymousUser;

    /**
     * AccountableSettings constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->enabled = $config->get('accountable.enabled');

        $this->createdByColumn = $config->get('accountable.column_names.created_by');
        $this->updatedByColumn = $config->get('accountable.column_names.updated_by');
        $this->deletedByColumn = $config->get('accountable.column_names.deleted_by');

        $this->anonymousUser = $config->get('accountable.anonymous');
    }

    /**
     * @return bool
     */
    public function enable(): bool
    {
        return $this->enabled = true;
    }

    /**
     * @return bool
     */
    public function disable(): bool
    {
        return $this->enabled = false;
    }

    /**
     * @return bool
     */
    public function disabled(): bool
    {
        return $this->enabled === false;
    }

    /**
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function createdByColumn(): string
    {
        return $this->createdByColumn;
    }

    /**
     * @return string
     */
    public function updatedByColumn(): string
    {
        return $this->updatedByColumn;
    }

    /**
     * @return string
     */
    public function deletedByColumn(): string
    {
        return $this->deletedByColumn;
    }

    /**
     * @return array|null
     */
    public function anonymousUser()
    {
        return $this->anonymousUser;
    }

    /**
     * @param array $user
     *
     * @return array
     */
    public function setAnonymousUser(array $user)
    {
        return $this->anonymousUser = $user;
    }
}
