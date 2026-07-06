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
        Schema::create('images', function (Blueprint $table) {
            $table->string('image_id')->primary();
            $table->string('image_source')->nullable();
            $table->string('image_path');
            $table->string('preview_image_path');
            $table->string('thumbnail_image_path');
            $table->string('avatar');
            $table->unsignedBigInteger('file_size');
            $table->unsignedBigInteger('width');
            $table->unsignedBigInteger('height');
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
