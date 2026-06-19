<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    /**
     *
     * @var array<int, string>
     */
    protected $primaryKey = 'tenant_id';
    protected $fillable = [
        'tenant_name',
        'tenant_email',
    ];

    /**
     * Relasi ke model Contract.
     * Satu tenant bisa memiliki banyak kontrak.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'tenant_id');
    }
}
