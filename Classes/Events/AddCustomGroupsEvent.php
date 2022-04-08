<?php

namespace BeechIt\FalSecuredownload\Events;

final class AddCustomGroupsEvent
{
    private array $customUserGroups;

    public function __construct(array $customUserGroups)
    {
        $this->customUserGroups = $customUserGroups;
    }

    /**
     * @return array
     */
    public function getCustomUserGroups(): array
    {
        return $this->customUserGroups;
    }

    /**
     * @param array $customUserGroups
     */
    public function setCustomUserGroups(array $customUserGroups): void
    {
        $this->customUserGroups = $customUserGroups;
    }
}