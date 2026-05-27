<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color', 'icon', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function adAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class);
    }

    public function adSpends(): HasMany
    {
        return $this->hasMany(AdSpend::class);
    }
}
