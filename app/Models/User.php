<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'handled_position',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'handled_position' => 'array',
    ];

    /**
     * Check if user is Super Admin (dapat akses semua data)
     * 
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    /**
     * Check if user is ADMIN/PIC (akses terbatas sesuai posisi)
     * 
     * @return bool
     */
    public function isAdminPic(): bool
    {
        return $this->role === 'Admin';
    }

    /**
     * Ambil semua posisi karyawan yang bisa diakses user ini.
     * Menggabungkan semua department yang di-handle.
     *
     * @return array<string>
     */
    public function getManagedPositions(): array
    {
        if ($this->isSuperAdmin()) {
            return [];
        }

        $adminPicDepartments = config('positions.admin_pic_departments', []);

        return collect((array) ($this->handled_position ?? []))
            ->map(fn ($position) => trim((string) $position))
            ->filter()
            ->flatMap(function ($position) use ($adminPicDepartments) {
                if (str_starts_with(strtolower($position), 'position:')) {
                    return [trim(substr($position, strlen('position:')))];
                }

                return $adminPicDepartments[$position] ?? [$position];
            })
            ->map(fn ($position) => trim((string) $position))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if user dapat akses data karyawan tertentu berdasarkan posisi.
     */
    public function canAccessPosition(?string $employeePosition): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $employeePosition = trim(strtoupper((string) $employeePosition));

        if ($employeePosition === '') {
            return false;
        }

        if ($this->isAdminPic()) {
            return in_array(
                $employeePosition,
                collect($this->getManagedPositions())
                    ->map(fn ($position) => trim(strtoupper((string) $position)))
                    ->all(),
                true
            );
        }

        return false;
    }
}
