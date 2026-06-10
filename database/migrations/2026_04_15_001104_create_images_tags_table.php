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
        Schema::create('images_tags', function (Blueprint $table) {
            $table->uuid('image_id');
            $table->uuid('tag_id');

            $table->foreign('image_id')
                ->references('image_id')
                ->on('images')
                ->cascadeOnDelete();

            $table->foreign('tag_id')
                ->references('tag_id')
                ->on('tags')
                ->cascadeOnDelete();

            $table->unique(['image_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_tags');
    }
};
