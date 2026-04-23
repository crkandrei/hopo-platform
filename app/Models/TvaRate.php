<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TvaRate extends Model
{
    protected $fillable = [
        'name',
        'percentage',
        'vat_class',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'vat_class' => 'integer',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public static function getSgrVatClass(): int
    {
        return (int) (static::where('percentage', 0)->where('is_active', true)->value('vat_class') ?? 1);
    }
}
