<?php

namespace NW\WebService\References\Operations\Notification\Models\Traits;

trait BaseTrait
{
    public function getFullName(): string
    {
        return trim($this->name.' '.$this->id);
    }
}
