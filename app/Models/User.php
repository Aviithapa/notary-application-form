<?php

namespace App\Models;


use App\Infrastructure\Traits\HasFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable 
{
    use HasFactory, Notifiable, HasFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'remember_token',
        'token',
        'reference',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token',
        'reference',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Displays the single role of user from many-many relationship.
     * Reads the loaded `roles` relation, so repeated calls in a request cost one query.
     */
    public function mainRole(): ?Role
    {
        return $this->roles->first();
    }

    public function isAdmin()
    {
        return self::join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->select('roles.*')
            ->where('users.id', $this->id)->first(); // Assuming 'admin' is the admin role
    }



    public function active()
    {
        if ($this->status === 'active') {
            return true;
        } else {
            return false;
        }
    }

    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function qualifications()
    {
        return $this->hasMany(ApplicantQualification::class);
    }

    public function trainings()
    {
        return $this->hasMany(ApplicantTraining::class);
    }

    public function examApplications()
    {
        return $this->hasMany(ExamApplication::class);
    }

    /** Which applicant form steps already have saved data. */
    public function applicationProgress(): array
    {
        return [
            'personal' => $this->applicantProfile()->exists(),
            'qualifications' => $this->qualifications()->exists(),
            'trainings' => $this->trainings()->exists(),
        ];
    }

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'assigned_to');
    }
}
