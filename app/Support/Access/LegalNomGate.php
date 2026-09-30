<?php

namespace App\Support\Access;

use App\Models\LegalEvidence;

final class LegalNomGate
{
    public static function isActive(): bool
    {
        return LegalEvidence::hasCurrentOfficialDofEvidenceApprovedByAdministrator('legal_nom_activation');
    }
}
