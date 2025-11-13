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
                Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');

            // Media details
            $table->string('media_url');
            $table->enum('media_type', ['photo', 'video', 'voice', 'other']);

            $table->timestamp('uploaded_at')->default(now());
            $table->timestamps();

            // Indexes
            $table->index(['message_id', 'media_type']);
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
