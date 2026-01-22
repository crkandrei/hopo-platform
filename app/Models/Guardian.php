<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_id',
        'name',
        'phone',
        'notes',
        'terms_accepted_at',
        'gdpr_accepted_at',
        'terms_version',
        'gdpr_version',
    ];

    protected $casts = [
        'terms_accepted_at' => 'datetime',
        'gdpr_accepted_at' => 'datetime',
    ];


    /**
     * Get the children for the guardian.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    /**
     * Check if guardian has accepted terms and conditions
     */
    public function hasAcceptedTerms(): bool
    {
        return !is_null($this->terms_accepted_at);
    }

    /**
     * Check if guardian has accepted GDPR policy
     */
    public function hasAcceptedGdpr(): bool
    {
        return !is_null($this->gdpr_accepted_at);
    }

    /**
     * Check if guardian needs to accept terms (hasn't accepted or version changed)
     */
    public function needsToAcceptTerms(): bool
    {
        if (!$this->hasAcceptedTerms()) {
            return true;
        }

        $currentVersion = \App\Http\Controllers\LegalController::TERMS_VERSION;
        return $this->terms_version !== $currentVersion;
    }

    /**
     * Check if guardian needs to accept GDPR (hasn't accepted or version changed)
     */
    public function needsToAcceptGdpr(): bool
    {
        if (!$this->hasAcceptedGdpr()) {
            return true;
        }

        $currentVersion = \App\Http\Controllers\LegalController::GDPR_VERSION;
        return $this->gdpr_version !== $currentVersion;
    }

    /**
     * Check if guardian needs to accept any legal documents
     */
    public function needsToAcceptLegalDocuments(): bool
    {
        return $this->needsToAcceptTerms() || $this->needsToAcceptGdpr();
    }
}
