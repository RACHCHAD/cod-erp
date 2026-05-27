<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'website', 'contact_name', 'contact_email',
        'contact_phone', 'country_code', 'notes',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sourcingRequests(): HasMany
    {
        return $this->hasMany(SourcingRequest::class);
    }
}
