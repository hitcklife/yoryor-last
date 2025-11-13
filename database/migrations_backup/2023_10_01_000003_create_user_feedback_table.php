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
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Can be anonymous
            $table->string('email')->nullable(); // For anonymous feedback
            $table->text('feedback_text')->notNullable(); // Required feedback content
            $table->string('status')->default('pending'); // Status of the feedback
            $table->timestamps();

            // Add foreign key constraint if user_id is provided
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Add indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
