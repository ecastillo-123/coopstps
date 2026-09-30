<?php

namespace Database\Factories;

use App\Models\LegalEvidence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalEvidence>
 */
class LegalEvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic' => 'legal_nom_activation',
            'reference' => 'DOF '.now()->format('Y-m-d'),
            'evidence_url' => null,
            'verified_by_user_id' => null,
            'approved_by_user_id' => null,
            'approved' => false,
            'approved_at' => null,
            'effective_at' => null,
        ];
    }
}
