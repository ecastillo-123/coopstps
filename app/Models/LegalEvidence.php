<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'topic',
    'reference',
    'evidence_url',
    'verified_by_user_id',
    'approved_by_user_id',
    'approved',
    'approved_at',
    'effective_at',
])]
class LegalEvidence extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'legal_evidences';

    protected function casts(): array
    {
        return [
            'approved' => 'boolean',
            'approved_at' => 'datetime',
            'effective_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
