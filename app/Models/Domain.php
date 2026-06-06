<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $table = 'domains';
    protected $fillable = [
        'domain',
        'zone_id',
    ];

    protected $hidden = [
        'zone_id',
    ];

    public function subdomains(): HasMany
    {
        return $this->hasMany(Subdomain::class);
    }
}
