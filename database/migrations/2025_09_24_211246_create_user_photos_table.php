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
                Schema::create('user_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Photo URLs
            $table->string('original_url', 500);
            $table->string('thumbnail_url', 500);
            $table->string('medium_url', 500);

            // Photo properties
            $table->boolean('is_profile_photo')->default(false);
            $table->unsignedTinyInteger('order')->default(0);
            $table->boolean('is_private')->default(false);
            $table->boolean('is_verified')->default(false);

            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamp('uploaded_at')->default(now());

            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_profile_photo', 'status']);
            $table->index(['user_id', 'order', 'status']);
            $table->index(['status', 'uploaded_at']);
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_photos');
    }
};
