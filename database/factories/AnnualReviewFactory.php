<?php

namespace Database\Factories;

use App\Models\AnnualReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnualReview>
 */
class AnnualReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => now()->year,
            'outcome' => 'approved',
            'reviewed_by_user_id' => User::factory(),
            'notes' => null,
            'reviewed_at' => now(),
        ];
    }
}
