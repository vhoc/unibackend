<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAppUser extends Model
{
    use HasFactory;

    /**
     * Get the app that owns the app user.
     */
    public function client_app(): BelongsTo
    {
        return $this->belongsTo(ClientApp::class);
    }
}
