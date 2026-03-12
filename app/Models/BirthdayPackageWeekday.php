<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayPackageWeekday extends Model
{
    protected $fillable = [
        'birthday_package_id',
        'day_of_week',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function birthdayPackage(): BelongsTo
    {
        return $this->belongsTo(BirthdayPackage::class);
    }
}
