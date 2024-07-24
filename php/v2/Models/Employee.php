<?php

namespace NW\WebService\References\Operations\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use NW\WebService\References\Operations\Notification\Models\Traits\BaseTrait;

class Employee extends Model
{
    use BaseTrait;

    protected $guarded = false;

    public static function getResellerEmailFrom($sellerId): string
    {
        return 'contractor@example.com';
    }

    public static function getEmailsByPermit($sellerId, $event): array
    {
        return ['someemeil@example.com', 'someemeil2@example.com'];
    }
}