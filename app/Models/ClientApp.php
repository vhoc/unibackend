<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientApp extends Model
{
    use HasFactory;

    /**
     * Get the users for Client app.
     */
    public function user(): HasMany
    {
        return $this->hasMany(ClientAppUser::class);
    }
}
