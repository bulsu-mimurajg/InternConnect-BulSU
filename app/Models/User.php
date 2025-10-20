<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'email_verified_at',
        'status',
        'first_name',
        'middle_name',
        'last_name',
        'must_change_password',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    public function student(): HasOne|User
    {
        return $this->hasOne(Student::class);
    }

    public function academeAccounts(): HasMany
    {
        return $this->hasMany(AcademeAccount::class);
    }

    public function studentAcademeAccounts(): HasMany
    {
        return $this->hasMany(AcademeAccount::class)->whereHas('user.roles', function($q) {
            $q->where('name', 'student');
        });
    }

    public function hte(): HasOne
    {
        return $this->hasOne(HTE::class, 'user_id');
    }

    public function adviser(): HasOne
    {
        return $this->hasOne(Adviser::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Check if user must change password on next login
     */
    public function mustChangePassword(): bool
    {
        return (bool) $this->must_change_password;
    }

    /**
     * Mark that user must change password
     */
    public function requirePasswordChange(): void
    {
        $this->update(['must_change_password' => true]);
    }

    /**
     * Mark password as changed
     */
    public function markPasswordChanged(): void
    {
        $this->update([
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);
    }
}
