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
            $table->string('photo_url');
            $table->boolean('is_profile_photo')->default(false);
            $table->unsignedInteger('order')->default(0); // Added index for sorting efficiency
            $table->boolean('is_private')->default(false);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->softDeletes(); // Added soft delete support
            $table->timestamps();

            $table->index('uploaded_at');
            $table->index('order');
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
