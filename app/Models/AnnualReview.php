<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'year',
    'outcome',
    'reviewed_by_user_id',
    'notes',
    'reviewed_at',
])]
class AnnualReview extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public static function currentYearReviewed(): bool
    {
        return self::where('year', now()->year)->exists();
    }
}
