<?php

namespace App\Support\Sst;

use App\Models\NomRegla;
use App\Support\Access\LegalNomGate;

final class NomRuleEvaluator
{
    public static function isActive(NomRegla $rule): bool
    {
        return LegalNomGate::isActive();
    }
}
