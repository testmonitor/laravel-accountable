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
     * AccountableSettings constructor.
     */
    public function __construct(Repository $config)
    {
        $this->enabled = $config->get('accountable.enabled');
        $this->createdByColumn = $config->get('accountable.column_names.created_by');
        $this->updatedByColumn = $config->get('accountable.column_names.updated_by');
        $this->deletedByColumn = $config->get('accountable.column_names.deleted_by');
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
    public function createdByColumn()
    {
        return $this->createdByColumn;
    }

    /**
     * @return string
     */
    public function updatedByColumn()
    {
        return $this->updatedByColumn;
    }

    /**
     * @return string
     */
    public function deletedByColumn()
    {
        return $this->deletedByColumn;
    }

    /**
     * @param $status
     *
     * @return bool
     */
    protected function updateStatus($status)
    {
        $this->enabled = $status;

        return $this->enabled;
    }
}
