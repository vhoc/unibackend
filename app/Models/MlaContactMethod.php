<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MlaContactMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'value',
    ];

    /**
     * Get the app that owns the app user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
