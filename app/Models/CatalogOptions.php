<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogOptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'heading_image_url',
        'background_color_1',
        'background_color_2',
        'background_gradient_shape',
        'custom_title',
        'custom_subtitle',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
