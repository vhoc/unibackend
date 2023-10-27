<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MlaProduct;

class MlaImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'mla_product_id',
        'url',
        'filename',
    ];

    /**
     * Get the product that the image belongs to
     */
    public function mla_product(): BelongsTo
    {
        return $this->belongsTo(MlaProduct::class);
    }
}
