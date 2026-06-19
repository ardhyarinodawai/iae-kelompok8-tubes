<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'contract_id',
        'tenant_name',
        'tenant_email',
        'description',
        'status',
        'soap_receipt',
    ];
}