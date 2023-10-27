<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MlaImage;

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

    /**
     * Get all the images that belong to the product.
     */
    public function mla_images(): HasMany
    {
        return $this->hasMany(MlaImage::class);
    }
}
