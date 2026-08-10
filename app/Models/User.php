<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; 

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'apellidoPaterno',
'apellidoMaterno', 'username','photo_path','birth_date'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable,HasRoles, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
    public function address(): HasOne
{
    return $this->hasOne(Address::class);
}

public function studentProfile(): HasOne
{
    return $this->hasOne(StudentProfile::class);
}

public function teacherProfile(): HasOne
{
    return $this->hasOne(TeacherProfile::class);
}
public function enrollments(): HasMany
{
    return $this->hasMany(
        Enrollment::class,
        'student_id'
    );
}

public function studentGroups(): BelongsToMany
{
    return $this->belongsToMany(
        SchoolGroup::class,
        'enrollments',
        'student_id',
        'school_group_id'
    )
        ->withPivot([
            'fecha_inscripcion',
            'estado_inscripcion',
        ])
        ->withTimestamps();
}

public function teachingAssignments(): HasMany
{
    return $this->hasMany(
        TeachingAssignment::class,
        'teacher_id'
    );
}

public function teacherGroups(): BelongsToMany
{
    return $this->belongsToMany(
        SchoolGroup::class,
        'teaching_assignments',
        'teacher_id',
        'school_group_id'
    )
        ->withPivot([
            'subject_id',
            'is_active',
        ])
        ->withTimestamps();
}
}
