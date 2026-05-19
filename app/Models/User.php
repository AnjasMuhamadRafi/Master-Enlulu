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
     * Get posisi yang di-handle user
     * 
     * @return string|null
     */
    public function getHandledPosition(): ?string
    {
        return $this->handled_position;
    }

    /**
     * Check if user dapat akses data karyawan tertentu berdasarkan posisi
     * 
     * @param string $employeePosition Posisi karyawan yang ingin diakses
     * @return bool
     */
    public function canAccessPosition(string $employeePosition): bool
    {
        // Super Admin bisa akses semua
        if ($this->isSuperAdmin()) {
            return true;
        }

        // ADMIN/PIC hanya bisa akses sesuai posisi yang di-handle
        if ($this->isAdminPic() && $this->handled_position) {
            // Get list of positions handled by this ADMIN/PIC
            $adminPicDepartments = config('positions.admin_pic_departments', []);
            $managedPositions = $adminPicDepartments[$this->handled_position] ?? [];
            
            return in_array($employeePosition, $managedPositions);
        }

        return false;
    }
}
