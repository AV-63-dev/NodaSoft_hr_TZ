<?php

namespace NW\WebService\References\Operations\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use NW\WebService\References\Operations\Notification\Models\Traits\BaseTrait;
use NW\WebService\References\Operations\Notification\Models\Seller;

class Client extends Model
{
    use BaseTrait;

    const TYPE_CUSTOMER = 0;

    protected $guarded = false;
    /* TODO на реальном проете расписал бы поля, но тут достаточно $guarded=false
    protected $fillable = [
        'type',
        'name',
        'seller_id',
        'email',
        'mobile',
    ]; */

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }
}