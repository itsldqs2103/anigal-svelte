<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->string('tag_id')->primary();
            $table->string('tag_name')->unique();
            $table->string('tag_slug_name');
            $table->string('tag_desc')->nullable();
            $table->timestamps();
        });

        DB::table('tags')->insert([
            [
                'tag_id' => Str::uuid(),
                'tag_name' => 'SFW',
                'tag_slug_name' => Str::slug('SFW'),
                'tag_desc' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tag_id' => Str::uuid(),
                'tag_name' => 'NSFW',
                'tag_slug_name' => Str::slug('NSFW'),
                'tag_desc' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tag_id' => Str::uuid(),
                'tag_name' => 'Big boob',
                'tag_slug_name' => Str::slug('Big boob'),
                'tag_desc' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tag_id' => Str::uuid(),
                'tag_name' => 'Small boob',
                'tag_slug_name' => Str::slug('Small boob'),
                'tag_desc' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
