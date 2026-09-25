<?php

namespace App\Support\Access;

use App\Models\LegalEvidence;
use Carbon\Carbon;

final class IndefiniteRetentionGate
{
    public static function isEnabled(): bool
    {
        return LegalEvidence::query()
            ->where('topic', 'indefinite_retention')
            ->where('approved', true)
            ->whereNotNull('approved_at')
            ->where(function ($query): void {
                $query->whereNull('effective_at')
                    ->orWhere('effective_at', '<=', Carbon::today());
            })
            ->exists();
    }
}
