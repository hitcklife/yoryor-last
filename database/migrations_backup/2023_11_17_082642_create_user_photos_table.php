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
            $table->string('original_url', 500); // Original high-res photo
            $table->string('thumbnail_url', 500)->nullable(); // Optimized thumbnail
            $table->string('medium_url', 500)->nullable(); // Medium resolution
            $table->boolean('is_profile_photo')->default(false);
            $table->unsignedTinyInteger('order')->default(0); // 0-5 for dating apps
            $table->boolean('is_private')->default(false);
            $table->boolean('is_verified')->default(false); // For photo verification
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->json('metadata')->nullable(); // EXIF data, dimensions, etc.
            $table->timestamp('uploaded_at')->useCurrent();
            $table->softDeletes(); // Added soft delete support
            $table->timestamps();

            // Optimized indexes
            $table->index(['user_id', 'is_profile_photo', 'status'], 'photos_profile_index');
            $table->index(['user_id', 'order', 'status'], 'photos_gallery_index');
            $table->index(['status', 'uploaded_at'], 'photos_moderation_index');
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
