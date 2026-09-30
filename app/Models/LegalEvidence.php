<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
            'revoked_at' => 'datetime',
        ];
    }

    public static function hasCurrentOfficialDofEvidenceApprovedByAdministrator(string $topic): bool
    {
        $evidence = self::query()
            ->where('topic', $topic)
            ->where('approved', true)
            ->whereNull('revoked_at')
            ->whereNotNull('approved_at')
            ->where('approved_at', '<=', now())
            ->whereNotNull('effective_at')
            ->whereDate('effective_at', '<=', today())
            ->whereHas('verifiedBy.roles', function (Builder $query): void {
                $query->where('name', 'Administrador');
            })
            ->whereHas('approvedBy.roles', function (Builder $query): void {
                $query->where('name', 'Administrador');
            })
            ->get(['reference', 'evidence_url']);

        return $evidence->contains(fn (self $record): bool => trim($record->reference) !== ''
            && self::isOfficialDofUrl($record->evidence_url));
    }

    private static function isOfficialDofUrl(?string $url): bool
    {
        if ($url === null || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';

        return ($parts['scheme'] ?? null) === 'https'
            && ($host === 'dof.gob.mx' || str_ends_with($host, '.dof.gob.mx'))
            && $path !== ''
            && $path !== '/';
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }
}
