<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'candidate_name',
        'employee_nik',
        'status',
        'expires_at',
        'submitted_at',
        'applied_at',
        'created_by',
        'full_name',
        'nik_ktp',
        'phone',
        'email',
        'birth_place',
        'birth_date',
        'mother_name',
        'work_location',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'ktp_path',
        'kk_path',
        'diploma_path',
        'cv_path',
        'application_letter_path',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'applied_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_nik', 'nik');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
