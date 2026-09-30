<?php

namespace App\Support\Access;

use App\Models\LegalEvidence;

final class IndefiniteRetentionGate
{
    public static function isEnabled(): bool
    {
        return LegalEvidence::hasCurrentOfficialDofEvidenceApprovedByAdministrator('indefinite_retention');
    }
}
