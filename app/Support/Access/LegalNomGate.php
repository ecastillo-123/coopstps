<?php

namespace App\Support\Access;

use App\Models\LegalEvidence;
use Carbon\Carbon;

final class LegalNomGate
{
    public static function isActive(): bool
    {
        return LegalEvidence::query()
            ->where('topic', 'legal_nom_activation')
            ->where('approved', true)
            ->whereNotNull('approved_at')
            ->where(function ($query): void {
                $query->whereNull('effective_at')
                    ->orWhere('effective_at', '<=', Carbon::today());
            })
            ->exists();
    }
}
