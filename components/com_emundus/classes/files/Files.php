<?php

namespace classes\files;

abstract class Files
{
    protected \Joomla\CMS\User\User $current_user;
    protected array $rights;
    protected array $files;
    protected array $columns;
    protected int $page;
    protected int $limit;

    abstract public function setFiles();

    /**
     * @return \Joomla\CMS\User\User
     */
    public function getCurrentUser(): \Joomla\CMS\User\User
    {
        return $this->current_user;
    }

    /**
     * @param \Joomla\CMS\User\User $current_user
     */
    public function setCurrentUser(\Joomla\CMS\User\User $current_user): void
    {
        $this->current_user = $current_user;
    }

    /**
     * @return array
     */
    public function getRights(): array
    {
        return $this->rights;
    }

    /**
     * @param array $rights
     */
    public function setRights(array $rights): void
    {
        $this->rights = $rights;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }


}