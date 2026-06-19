<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalRole extends Model
{
    protected $fillable = ['name', 'display_name', 'description'];

    public function ssoUsers(): HasMany
    {
        return $this->hasMany(SsoUser::class);
    }

    // Helper: cek apakah nama role ini adalah admin
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }
}