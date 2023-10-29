<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalog_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('heading_image_url')->nullable();
            $table->string('background_color_1')->nullable()->default('#00ccff');
            $table->string('background_color_2')->nullable()->default('#9a32ff');
            $table->string('background_gradient_shape')->nullable()->default('radial-gradient');
            $table->string('custom_title')->nullable();
            $table->string('custom_subtitle')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_options');
    }
};
