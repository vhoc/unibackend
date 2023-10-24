<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MlaProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'hexId',
        'user_id',
        'title',
        'price',
        'description',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * Get the app that owns the product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
