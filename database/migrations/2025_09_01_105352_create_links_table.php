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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 30)->nullable()->unique(); // يسمح بأكتر من NULL
            $table->string('target_url', 2048);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('clicks_count')->default(0); // Added back the clicks_count column
            $table->softDeletes();
            $table->timestamps();
            $table->index(['user_id', 'is_active']);
            $table->index('expires_at');
            $table->index('created_at');
            $table->index('clicks_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
