<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
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
        'email_verified_at' => 'datetime'
    ];

    public function name() : Attribute
    {
      return new Attribute(
          get: fn ($value) => ( ($value) ? Crypt::decrypt($value) : ''),
          set: fn ($value) => $value,
      );
    }

    public function getAvatar()
    {
      return (!$this->avatar) ? asset('/img/default-avatar.png') : asset('/img/users/'.$this->avatar);
    }

    public function branches()
    {
      return $this->belongsToMany(GlbBranch::class, 'tps_branch_user', 'user_id', 'branch_id')->withPivot('active')->withTimeStamps();
    }

    public function activeCompany()
    {
      return $this->belongsToMany(GlbBranch::class, 'tps_branch_user', 'user_id', 'branch_id')->withPivot('active')->wherePivot('active', true)->first();
    }

    public function passLog()
    {
      return $this->hasMany(PassLog::class, 'user_id');
    }
    
    public function lastLog()
    {
      // return $this->passLog()->latest()->first();
      return $this->hasOne(PassLog::class, 'user_id')->latestOfMany();
    }

    public function loginLogs()
    {
      return $this->hasMany(UserLoginLog::class, 'user_id');  
    }
}
